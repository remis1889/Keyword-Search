<?php
namespace PHPTrie;
include_once("trie.php");
include_once("dbconnect.php");

$trie = new Trie();
      
$query = 'SET GLOBAL group_concat_max_len=15000';
mysqli_query($connection, $query);

$sql = "SELECT GROUP_CONCAT(S) as S,term FROM ginix_single, index_terms where ginix_single.termId=index_terms.termId group by term order by term";
$result = mysqli_query($connection, $sql) or die(mysqli_error($connection));

while($row = mysqli_fetch_array($result))
{
    $term = $row['term'];   
    $data[$term]['S'] = explode(",",$row['S']);
}

$sql = "SELECT GROUP_CONCAT(L) as L, GROUP_CONCAT(U) as U, term FROM ginix_interval, index_terms where ginix_interval.termId=index_terms.termId group by term order by term";
$result = mysqli_query($connection, $sql) or die(mysqli_error($connection));

      
while($row = mysqli_fetch_array($result))
{
    $term               =    $row['term'];   
    $data[$term]['L']   =    explode(",",$row['L']);
    $data[$term]['U']   =    explode(",",$row['U']);
}

foreach ($data as $term => $leaf)
    $trie->add($term, $leaf);

$s = serialize($trie);
file_put_contents('data\index\trie_ginix\trie_ginix_'.$nrec.'k', $s);
echo "Trie + GINIX index created succesfully!";
?>
