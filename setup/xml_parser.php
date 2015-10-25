<?php
include_once("dbconnect.php");

function get_reader($file)
{
  $reader = new XMLReader;
  $reader->open($file,NULL,XMLReader::SUBST_ENTITIES);
  return $reader;
}

$sql = "INSERT INTO dblp (dblpKey, dblpTitle, authors, year, journal, url) VALUES ";
$xml = get_reader('data/dataset/dblp.xml');

$i = $j = 0;
$doc = new DOMDocument;

while($xml->read()){ 
  $isNewArticle = 'article' === $xml->name && $xml->nodeType === XMLReader::ELEMENT; 
  if($isNewArticle){
    $i++;
    $j++;
    $article = simplexml_import_dom($xml->expand($doc)); 
 
    $dblpKey      = mysqli_real_escape_string($connection, $article->attributes()->key); 
    $dblpTitle    = mysqli_real_escape_string($connection, $article->title);
    $dblpYear     = mysqli_real_escape_string($connection, $article->year);
    $dblpJournal  = mysqli_real_escape_string($connection, $article->journal);
    $dblpUrl      = mysqli_real_escape_string($connection, $article->url);
  
    $dblpAuthor = "";
    foreach($article->author as $author)
    {
      $dblpAuthor .= $author.", ";
    }
    $dblpAuthor = mysqli_real_escape_string($connection, trim($dblpAuthor,", "));

    $sql .= "('".$dblpKey."','".$dblpTitle."','".$dblpAuthor."',".$dblpYear.",'".$dblpJournal."','".$dblpUrl."'),";
  
    // execute insert query after reading 1000 records
    if($j==1000)
    {
      $sql = trim($sql,",");
      mysqli_query($connection, $sql) or die(mysqli_error($connection));
      printf ("<br>Records inserted: %d\n", mysqli_affected_rows($connection));
      
      $j=0;
      $sql = "INSERT INTO dblp (dblpKey, dblpTitle, authors, year, journal, url) VALUES ";
    }
   
    if($i>=20000) // insert a total of 20k articles
      break;
  }
} 
?>
