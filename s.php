<?php

require_once 'database.php';


$s=  $conn ->prepare("SELECT * FROM freelancers");
$s ->execute();

foreach($s AS $v){
echo "<h3>".$v['name'] ."<h3>";
echo $v['id'];

}


?>