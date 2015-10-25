<?php

include_once("setup/trie.php");
include_once("setup/dbconnect.php");
ini_set('memory_limit', '912M');

$result_arr = array();
$count = 0;

function search_inverted_index($termIds)       
{   
    $sql = "SELECT dblp.dblpId, dblpKey, dblpTitle, authors, year, journal 
            FROM dblp, inverted_index 
            WHERE termId IN (".$termIds.") and dblp.dblpId = inverted_index.dblpId 
            GROUP BY inverted_index.dblpId 
            ORDER BY sum(score) DESC";
    return $sql;
}

function search_trie($terms)
{
    $i = $j = 0;
    $s = file_get_contents('setup\data\index\trie\trie_index_'.$nrec.'k'); // load trie index
    $trie = unserialize($s);

    foreach($terms as $term) 
    {
        $leaf_result[$i++] = $trie->search($term); // returns the trie node for each query term
    }

    $selected = array();
    $final = array();
    $len = count($leaf_result);
    
    // calculate combined score of each result for each term in the query
    for($i = 0; $i < $len; $i++)
    {
        foreach($leaf_result[$i] as $rid => $score)
        {
            if(in_array($rid, $selected))
            {
                $final[$rid] += $score;
            }
            else
            {
                $selected[$j++] = $rid;
                $final[$rid] = $score;
            }
        }
        
    } 
    arsort($final); // sort the results in descending order of score
   
    $sorted = array_keys($final); // sorted list of document ids
    $ridList = implode(",", $sorted);

    $sql = "SELECT dblpId, dblpKey, dblpTitle, authors, year, journal 
            FROM dblp 
            WHERE dblpId IN (".$ridList.")
            ORDER BY FIELD(dblpId,".$ridList.")";
    return $sql;
    
}

function search_ginix($termIds)
{
    $sql = "select termId, S from ginix_single where termId in (".$termIds.")";
    $result = mysql_query($sql) or die(mysql_error());
    $snum = mysql_num_rows($result);
    $final_sql = "select dblpId,dblpKey,dblpTitle,authors,year,journal from dblp WHERE ";

    if($snum!=0)
    {
        $singles = array();

        while($srow = mysql_fetch_array($result))
        {
            array_push($singles, $srow['S']);
        }

        $singles = implode(",",array_unique($singles));
        $final_sql .= "dblpId IN (".$singles.") OR ";
    }


    $sql = "select termId,L,U from ginix_interval where termId in (".$termIds.")";
    $result = mysql_query($sql) or die(mysql_error());
    $inum = mysql_num_rows($result);

    if($inum!=0)
    {
        $lbs = array();
        $ubs = array();

        while($srow = mysql_fetch_array($result))
        {
            array_push($lbs, $srow['L']);
            array_push($ubs, $srow['U']);
        }

        $lbs = array_unique($lbs); 
        $ubs = array_unique($ubs);

        sort($lbs);
        sort($ubs);
        
        for($i=0;$i<count($lbs);$i++)
        {
            $final_sql .= "dblpId between ".$lbs[$i]." and ".$ubs[$i]." OR ";
        }
        $final_sql = substr($final_sql,0,-4);
    }
    else
    {
        $final_sql = substr($final_sql,0,-4);
    }
    return $final_sql;
}

function search_trie_ginix($terms)
{
    $i = 0;
    $s = file_get_contents('setup\data\index\trie\trie_ginix_'.$nrec.'k');
    $trie = unserialize($s);
    
    foreach($terms as $term)
    {
        $leaf_result[$i] = $trie->search($term);
        $i++;
    }
                    
    $final_leaf_result = array();
    for($i=0;$i<count($leaf_result);$i++)
    {
        $final_leaf_result = array_merge_recursive($final_leaf_result, $leaf_result[$i]);
    }

    $new_sql = "select dblpId,dblpKey,dblpTitle,authors,year,journal from dblp WHERE ";
    if(isset($final_leaf_result['S']))
    {
        $final_leaf_result['S'] = implode(",",array_unique($final_leaf_result['S']));
        $new_sql .= "dblpId IN (".$final_leaf_result['S'].") OR ";
    }
        
    if(isset($final_leaf_result['L']))
    {
        $final_leaf_result['L'] = array_values(array_unique($final_leaf_result['L']));
        $final_leaf_result['U'] = array_values(array_unique($final_leaf_result['U']));
        for($i=0;$i<count($final_leaf_result['L']);$i++)
        {
            $new_sql .= "dblpId between ".$final_leaf_result['L'][$i]." and ".$final_leaf_result['U'][$i]." OR ";
        }
        $new_sql = substr($new_sql,0,-4);
    }
    else
    {
        $new_sql = substr($new_sql,0,-4);
    }
    return $new_sql;
}


?>

<?php
$topk = 10;

if (!empty($_POST))
{
    $query = $_POST['query'];  
    $index = $_POST['indexType'];

    // remove whitespaces from user query
    $orig_terms = $terms = array_filter(explode(" ", $query));
    
    $sql_term = "SELECT GROUP_CONCAT(termId) as terms, 
                        GROUP_CONCAT(term) as termVal 
                        FROM index_terms 
                        WHERE term like '";
    
    foreach ($terms as $term) {
        $sql_term .= $term."%' or term like '";
    }
    
    // remove extra ' or term like '        
    $sql_term = substr($sql_term,0,-15); 

    // select term ids of query terms
    $result_term = $connection->query($sql_term);
    $row = $result_term->fetch_assoc();
    $termIds = $row['terms'];
    $terms = explode(",",$row['termVal']);

    if($termIds==NULL)
    {
        $count = 0;
    }
    else
    {
        switch($index)
        {
            case 1 :    // search inverted index using term ids
                        $sql = search_inverted_index($termIds); 
                        break;

            case 2 :    // search trie using terms
                        $sql = search_trie($terms);
                        break;

            case 3 :    // search ginix using term ids
                        $sql = search_ginix($termIds); 
                        break;

            case 4 :    // search trie + ginix using terms
                        $sql = search_trie_ginix($terms); 
                        break;
        }
        
        // execute sql query
        $result = mysqli_query($connection, $sql);
        $count = mysqli_num_rows($result);

        while($srow = mysqli_fetch_assoc($result))
        {
            $dblpId = $srow['dblpId'];
            $result_arr[$dblpId]['dblpKey'] = $srow['dblpKey'];
            $result_arr[$dblpId]['dblpTitle'] = $srow['dblpTitle'];
            $result_arr[$dblpId]['authors'] = $srow['authors'];
            $result_arr[$dblpId]['year'] = $srow['year'];
            $result_arr[$dblpId]['journal'] = $srow['journal'];
        }
    }
?>
<?php

// highlight search terms in the result sets
function highlight($str,$terms)
{
    foreach ($terms as $val) {
        
        $replace = "<span class='highlight'>".$val."</span>";
        $str = str_ireplace($val, $replace, $str);
    }
    return $str;
}

?>

<?php
            if($count>0) {
            ?>
            <div style="font-size: 16px; font-weight: bold;">
                <?php echo $count; ?> results found
                <hr>
            </div> 
             
        <?php
            
            foreach($result_arr as $key=>$val)
            {
        ?>
            <div class='record'>
                <span class="dblptitle">
                     <?php echo highlight($val['dblpTitle'],$orig_terms); ?>
                </span>
                <br>
                <span class='author'>
                    <?php echo highlight($val['authors'],$orig_terms); ?>
                </span>
                <br>
                <span class='journal'>
                    <?php echo highlight($val['journal'],$orig_terms); ?>
                </span>
                <br>
                <span>
                    dblp key : <?php echo highlight($val['dblpKey'],$orig_terms); ?>
                </span>
            </div> 
        <?php
            }
                    }
        else
        {?>
            <div class='error'>
                No results found for <i><b>"<?=$query?>"</b></i>. Please try again.
            </div>

        <?php
        }
}
        ?>   
