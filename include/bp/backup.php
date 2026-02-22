<?php

function backup_tables($host,$user,$pass,$name,$tables = '*')
{
	
	$db_name="hrsoft";
	//$db_name="bpulze_college";
	
	$link = mysqli_connect("localhost","root","",$db_name);
	
	//$link = mysqli_connect("localhost","bpulze_user","user@123",$db_name);
	
	
		$tables = array();
	
	$query = "SHOW TABLES FROM $db_name";
	$sql = mysqli_query($link,$query);
	while($arr=mysqli_fetch_array($sql))
	{
	array_push($tables,$arr['0']);	
	}
	
	
	
		$tables = is_array($tables) ? $tables : explode(',',$tables);
	
	$return="";
	
	//cycle through
	foreach($tables as $table)
	{
		$result = mysqli_query($link,'SELECT * FROM '.$table);
		$num_fields = mysqli_num_fields($result);
		
		$row2 = mysqli_fetch_row(mysqli_query($link,'SHOW CREATE TABLE '.$table));
		$return.= "\n\n".$row2[1].";\n\n";
		
		for ($i = 0; $i < $num_fields; $i++) 
		{
			while($row = mysqli_fetch_row($result))
			{
				$return.= 'INSERT INTO '.$table.' VALUES(';
				for($j=0; $j<$num_fields; $j++) 
				{
					$row[$j] = addslashes($row[$j]);
					$row[$j] = preg_replace("/\n/","\\n",$row[$j]);
					if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
					if ($j<($num_fields-1)) { $return.= ','; }
				}
				$return.= ");\n";
			}
		}
		$return.="\n\n\n";
	}
	
	$return.="\n\n\n \n\n\n";
	
	$return.= "delimiter $$   \n \n\n\n";
	
	$query2="show procedure status where db=database()";
	$sql=mysqli_query($link,$query2);
	while($arr=mysqli_fetch_array($sql))
	{
	$sp_name=$arr['1'];
	
	$query3="show create procedure $sp_name";
		$sql3=mysqli_query($link,$query3);
		$arr3=mysqli_fetch_array($sql3);
		
	
		$return.=$arr3['2']."   $$  \n \n\n\n";
		
	
	}
	
	$return.= "delimiter ;   \n \n\n\n";
	
	
	$query2 = "select backup_path from companysettings";
	$sql2 = mysqli_query($link,$query2);
	$arr2=mysqli_fetch_array($sql2);
	if($arr2['backup_path']=="")
	{
		$backup_path="";
	}
	else
	{
	$backup_path=$arr2['backup_path']."\\";
	}
	
	$file_name="one";
	$b_file_name=$file_name.date('d-m-y h-i-s');
	
	//save file
	$handle = fopen($backup_path.$b_file_name.'.sql','w+');
	fwrite($handle,$return);
	fclose($handle);
}

$sql=backup_tables('localhost','root','','');



?>