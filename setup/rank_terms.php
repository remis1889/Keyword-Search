<?php
include_once("dbconnect.php");

function term_frequency()
{
    $sql = "select termId,term from index_terms order by termId"; 
    $result = mysqli_query($connection, $sql) or die(mysqli_error($connection));

    while($row = mysqli_fetch_array($result))
    {
        $termId = $row['termId'];
        $term = $row['term']; 
           
        $newsql = "select dblpId, concat(dblpTitle,', ',authors,', ',journal) as data from dblp where dblpTitle like '%$term%' or authors like '%$term%' or journal like '%$term%'";
        $newresult = mysqli_query($connection, $newsql) or die(mysqli_error($connection));

        while($newrow = mysqli_fetch_array($newresult))
        {   $record = $newrow['dblpId']; 
            $data = strtolower($newrow['data']);
            $tf = substr_count($data, $term);

            $updsql = "update inverted_index set tf=$tf where termId=$termId and dblpId=$record;";
            mysqli_query($connection, $updsql) or die(mysqli_error($connection));
        }
        printf ("Records inserted: %d\n", mysqli_affected_rows($connection));
    }
}

function document_frequency()
{
    $sql = "SELECT termId, count(dblpId) as df FROM `inverted_index` group by termId";
    $result = mysqli_query($connection, $sql) or die(mysqli_error($connection));

    while($row = mysqli_fetch_array($result))
    {
        $termId = $row['termId'];
        $df = $row['df']; 

        $updsql = "update index_terms set df=$df where termId=$termId";
        mysqli_query($connection, $updsql) or die(mysqli_error($connection));
    }
}

function inverse_document_frequency()
{
    $sql = "SELECT count(dblpId) as N FROM dblp";
    $result = mysqli_query($connection, $sql) or die(mysqli_error($connection));
    $row = mysqli_fetch_array($result);
    
    $N = $row['N'];
    $sql = "select termId,df from index_terms";
    $result = mysqli_query($connection, $sql) or die(mysqli_error($connection));
    
    while($row = mysqli_fetch_array($result))
    {
        $termId = $row['termId'];
        $df = $row['df']; 

        $idf = log($N)/$df; 
        $updsql = "update index_terms set idf=$idf where termId=$termId";
        mysqli_query($connection, $updsql) or die(mysqli_error($connection));
    }
}

function tfidf()
{
    $sql = "SELECT indexId, tf, index_terms.idf as idf FROM inverted_index, index_terms where index_terms.termId=inverted_index.termId";
    $result = mysqli_query($connection, $sql) or die(mysqli_error($connection));
    
    while($row = mysqli_fetch_array($result))
    {
        $indexId = $row['indexId'];
        $tf = $row['tf']; 
        $idf = $row['idf']; 

        $score = $tf*$idf;

        $updsql = "update inverted_index set score=$score where indexId=$indexId";
        mysqli_query($connection, $updsql) or die(mysqli_error($connection));
    }
}

term_frequency();
document_frequency();
inverse_document_frequency();  
tfidf();
?>
