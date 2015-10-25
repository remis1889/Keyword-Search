<?php
namespace PHPTrie;
include_once("trie.php");
include_once("dbconnect.php");


$trie = new Trie();

$sql = "SELECT dblpId,score,index_terms.term FROM inverted_index, index_terms where index_terms.termId=inverted_index.termId order by term,score desc";
$result = mysqli_query($connection, $sql) or die(mysqli_error($connection));

$term = '';

while($row = mysqli_fetch_array($result))
{
    $dblpid = $row['dblpId'];
    $score = $row['score']; 

    if($term!=$row['term'])
       $term = $row['term'];

	$data[$term][$dblpid] = $score;

}

$count = 0;

foreach ($data as $term => $leaf)
{
        $trie->add($term, $leaf);
        $count +=count($leaf);
}

$s = serialize($trie);
file_put_contents('data\index\trie\trie_index_'.$nrec.'k', $s);
echo "Trie index created succesfully!";
?>
