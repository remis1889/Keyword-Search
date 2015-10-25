<?php
include_once("dbconnect.php");

$filename 		= 	"data/terms/terms_'.$nrec.'k.txt";
$content 		= 	strtolower(file_get_contents($filename));
$wordArray 		= 	preg_split('/[^a-z]/', $content, -1, PREG_SPLIT_NO_EMPTY);
$filteredArray 	= 	array_filter($wordArray, function($x){
	return !preg_match("/^(.|a|hox|can|th|via|vs|ii|as|an|and|the|this|at|in|or|of|is|for|to|on|data|with|ieee|user|by|from|eng|two|three|notes)$/",$x);
});
 
$wordFrequencyArray = array_count_values($filteredArray);
arsort($wordFrequencyArray);
 
$sql = "INSERT INTO index_terms (term, termCount) VALUES ";

foreach ($wordFrequencyArray as $topWord => $frequency)
	$sql .= "('".$topWord."',".$frequency."),";

 
$sql = trim($sql, ",");
mysqli_query($connection, $sql) or die(mysqli_error($connection));
printf ("Records inserted: %d\n", mysqli_affected_rows($connection));
?>
