<?php
include_once("dbconnect.php");

$query = 'SET GLOBAL group_concat_max_len=15000';
mysqli_query($connection, $query);
  
$sql = "SELECT group_concat(dblpId) as dblpList,termId FROM inverted_index group by termId";
$result = mysqli_query($connection, $sql) or die(mysqli_error($connection));

while($row = mysqli_fetch_array($result))
{
    $termId = $row['termId'];
    $dblpList[$termId] = explode(",",trim($row['dblpList'],","));
}

foreach($dblpList as $termId => $numbers)
{
    $groups                   = array();
    $ginix[$termId]['single'] = array();
    $ginix[$termId]['list']   = array();

    for($i = 0; $i < count($numbers); $i++)
    {
        if($i > 0 && ($numbers[$i - 1] == $numbers[$i] - 1))
          array_push($groups[count($groups) - 1], $numbers[$i]);
        else
          array_push($groups, array($numbers[$i])); 
    }

    foreach($groups as $group)
    {
      if(count($group) == 1) // Single value
        array_push($ginix[$termId]['single'],$group[0]);
      
      else
        array_push($ginix[$termId]['list'],array($group[0],$group[count($group) - 1]));
    }
}

$inssingle = "Insert into ginix_single (termId,S) Values";
$insint = "Insert into ginix_interval (termId,L,U) Values";

foreach($ginix as $termId => $dblpArr)
{
  if(count($dblpArr['single'])>0)
    foreach ($dblpArr['single'] as $key) 
      $inssingle .= "(".$termId.",".$key."),";

  if(count($dblpArr['list'])>0)
    foreach ($dblpArr['list'] as $key=>$list) 
      $insint .= "(".$termId.",".$list[0].",".$list[1]."),";
}

$inssingle = trim($inssingle, ",");
$insint = trim($insint, ",");

mysqli_query($connection, $inssingle) or die(mysqli_error($connection));
printf ("Records inserted: %d\n", mysqli_affected_rows($connection));

mysqli_query($connection, $insint) or die(mysqli_error($connection));
printf ("Records inserted: %d\n", mysqli_affected_rows($connection));
?>
