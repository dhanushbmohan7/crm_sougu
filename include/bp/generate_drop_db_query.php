<?php

$link = mysqli_connect("localhost","root","","buraque");
$query3="";
$count=0;
$query2="show procedure status where db=database()";
	$sql=mysqli_query($link,$query2);
	while($arr=mysqli_fetch_array($sql))
	{
	$sp_name=$arr['1'];
	$query3.="DROP PROCEDURE IF EXISTS $sp_name; <br>";
	$count++;
	}
	echo $query3;
	echo "<br>";
	echo $count;
	?>