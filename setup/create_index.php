<?php
include_once("dbconnect.php");
  
$sql = "select termId,term from index_terms order by termId";
$result = mysqli_query($connection, $sql) or die(mysqli_error($connection));

while($row = mysqli_fetch_array($result))
{
      $termId     = $row['termId']; 
      $term       = $row['term'];
      
      $newsql     = "select dblpId from dblp where dblpTitle like '%$term%' or authors like '%$term%' or journal like '%$term%'";
      $newresult  = mysqli_query($connection, $newsql) or die(mysqli_error($connection));
      
      $insql      = "insert into inverted_index (termId,dblpId) values ";
      while($newrow = mysqli_fetch_array($newresult))
      {   
          $record = $newrow['dblpId']; 
          $insql .= "(".$termId.",".$record."),"; 
      }
      $insql = trim($insql, ",");
      
      mysqli_query($connection, $insql) or die(mysqli_error($connection));
      printf ("Records inserted: %d\n", mysqli_affected_rows($connection));
}
?>
