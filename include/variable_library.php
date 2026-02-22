<?php
date_default_timezone_set('Asia/Kolkata');
class Var_Library
{
	var $con;
	var $ret;
	
	function Var_Library($id)
	{
		$localhost='localhost';
		$db_user='root';
		$db_pass='';
		$masterdb='canteen';
		$m_con=mysqli_connect($localhost,$db_user,$db_pass,$masterdb);
		$db_query=mysqli_query($m_con,"select genx from companysettings where UID='$id'");
		$db_arr=mysqli_fetch_array($db_query);		
		$switching_db=$db_arr['genx'];
		
		$this->con=mysqli_connect($localhost,$db_user,$db_pass,$switching_db) or die(mysqli_error());
		$this->dbname=$switching_db; 
		
		return $this->con;
	}
	
	function generalquery($sql)
	{
		$ret=mysqli_query($this->con, $sql) or die($sql);
		return $ret;
	}
	
	function showall_with_condition($table,$condition)
	{
		$sql="select * from $table where $condition ";
		$ret=$this->generalquery($sql);
		return $ret;
	}
	function showdata_with_condition($field,$table,$condition)
	{
		 $sql="select $field from $table where $condition ";
		$ret=$this->generalquery($sql);
		return $ret;
	}
	
	function fill_combo_not_exist($table,$table2,$field1,$field2,$field3,$field4,$field5,$chk_txt)
	{
		
		$sqll="select $field1,$field2 from $table  where  NOT EXISTS  (SELECT * FROM $table2 WHERE $table2.$field3 = $table.$field1 AND $table2.$field4='$chk_txt')";
		
		$ret=$this->generalquery($sqll);
		while($row=mysqli_fetch_array($ret))
			 {
				
					echo "<option value='$row[0]'>$row[1]</option>";
     
			 }
			
		return $ret;
		
	}
	
	function get_details($id)
	{
		$sqll="select u.NAME NAME,c.BranchName BranchName from users u inner join companysettings c on u.UID=c.UID  and u.UID='$id' ";
		
		$ret=$this->generalquery($sqll);
		$row=mysqli_fetch_array($ret);	
		return $row['NAME'].'#'.$row['BranchName'];
	}
		
	function showall_with_jointable_condition($table_arr,$condition)
	{
		$tables=array();
		foreach($table_arr as $x=>$x_val)
		{
			$tables[]=$x_val;
		}
		$tables=implode(",",$tables);
		
		$sql="select * from $tables where $condition ";
		$ret=$this->generalquery($sql);
		return $ret;
	}
	
	
	function last_row($table,$fld,$id)
	{
		$sql="SELECT $fld FROM $table ORDER BY $id DESC LIMIT 1";
		$ret=$this->generalquery($sql);
		$res=mysqli_fetch_array($ret);
		return $res;
	}
	
	function last_insert_id($table,$fld)
	{
		$sql="select max($fld) as max from $table";
		$ret=$this->generalquery($sql);
		$res=mysqli_fetch_array($ret);
		$s=$res['max'];
		return $s;
	}
	
	function last_insert_id_with_condition($table,$fld,$condition)
	{
		$sql="select max($fld) as max from $table  where $condition";
		$ret=$this->generalquery($sql);
		$res=mysqli_fetch_array($ret);
		$s=$res['max'];
		return $s;
	}
	
	
	function last_insert_id_plus_one($table,$fld)
	{
		$sql="select max($fld) as num from $table";
		$ret=$this->generalquery($sql);
		$res=mysqli_fetch_array($ret);
		$s=$res['num']+1;
		return $s;
	}
	
	function last_insert_id_plus_one_with_condition($table,$fld,$condition)
	{
		$sql="select max($fld) as max from $table where $condition";
		$ret=$this->generalquery($sql);
		$res=mysqli_fetch_array($ret);
		$s=$res['max']+1;
		return $s;
	}
	
	function last_row_with_condition($table,$fld,$id,$condition)
	{
		$sql="SELECT $fld FROM $table WHERE $condition ORDER BY $id DESC LIMIT 1";
		$ret=$this->generalquery($sql);
		$res=mysqli_fetch_array($ret);
		return $res;
	}
	function number_of_row_like($table,$fld,$pattern,$row){
		$sql="select $row   from $table where concat(',',$fld ,',') LIKE '%$pattern%' ";
		$ret=$this->generalquery($sql);
		$res=mysqli_fetch_array($ret); 
		return $res;
	}
	
	function select_max_id_plus_one($table,$fld,$pattern)
	{
		$sql="SELECT $fld FROM $table  where `$fld` LIKE '$pattern%' ORDER BY   LENGTH($fld), $fld ;";
		
		$ret=$this->generalquery($sql);
		while($res=mysqli_fetch_array($ret))
		{
			$s=$res['0'];	
		}		
		
		if(@$s=="")
		{
			return $pattern."1";
		}
		else
		{
			$split=@explode($pattern,$s);
			$last=$split[1];
			$s=$last+1;
			return $pattern.$s;
		}
	}
	
	
	function select_max_id_plus_one_condition($table,$fld,$pattern,$condition)
	{
		$sql="SELECT $fld FROM $table  where `$fld` LIKE '$pattern%'  $condition  ORDER BY   LENGTH($fld), $fld ;";
		
		$ret=$this->generalquery($sql);
		while($res=mysqli_fetch_array($ret))
		{
			$s=$res['0'];	
		}		
		
		if(@$s=="")
		{
			return $pattern."1";
		}
		else
		{
			$split=@explode($pattern,$s);
			$last=$split[1];
			$s=$last+1;
			return $pattern.$s;
		}
	}
	
	
	
	
	function select_count($table,$condition)
	{
		$sql="select count(*) as c  from $table where $condition";
		$ret=$this->generalquery($sql);
		$res=mysqli_fetch_array($ret);
		$s=$res['c'];
		return $s;
	}
	
	
	
		
	
	function select_max_count($table,$fld,$pattern)
	{
		$sql="select count(*) as c  from $table where `$fld` LIKE '$pattern%'";
		$ret=$this->generalquery($sql);
		$res=mysqli_fetch_array($ret);
		$s=$res['c']+1;
		return $s;
	}
	
	
	function fill_combo($table,$field1,$field2,$check_id)
	{
		 $sqll="select $field1,$field2 from $table order by $field2 ASC"; 
		$ret=$this->generalquery($sqll);
		while($row=mysqli_fetch_array($ret))
			 {
				if($check_id==$row['0'])
				{				
		            echo "<option value='$row[0]' selected='selected'>$row[1]</option>";
				}
				else
				{
					echo "<option value='$row[0]'>$row[1]</option>";
           
				}
			 }
			
		return $ret;
		
	}
	function fill_combo_extra($table,$field1,$field2,$check_id,$extra,$condition)
	{
		 $sqll="select $field1,$field2,$extra from $table where $condition"; 
		$ret=$this->generalquery($sqll);
		while($row=mysqli_fetch_array($ret))
			 {
				if($check_id==$row['0'])
				{				
		            echo "<option value='$row[0]' $extra='$row[$extra]' selected='selected'>$row[1]</option>";
				}
				else
				{
					echo "<option $extra='$row[$extra]' value='$row[0]'>$row[1]</option>";
           
				}
			 }
			
		return $ret;
		
	}
	
	function fill_combo_where($table,$field1,$field2,$check_id,$condition)
	{
		 $sqll="select $field1,$field2 from $table where $condition";
		$ret=$this->generalquery($sqll);
		while($row=mysqli_fetch_array($ret))
			 {
				if($check_id==$row['0'])
				{				
		            echo "<option value='$row[0]' selected='selected'>$row[1]</option>";
				}
				else
				{
					echo "<option value='$row[0]'>$row[1]</option>";
           
				}
			 }
			
		return $ret;
		
	}
	
	
	function insert($table,$data)
	{
		$key=array();
		$val=array();
		foreach($data as $x=>$x_val)
		{
			$x_val=mysqli_real_escape_string($this->con,$x_val);
			$key[]=$x;
			$val[]=$x_val;
		}
		$key=implode(",",$key);
		$val="'".implode("','",$val)."'";
		$sql="insert into $table(".$key.") values(".$val.")";
		
		$ret=$this->generalquery($sql);
		return $sql;
	}
	
	
	function update($table, $values, $where,$id)
	{
		foreach ($values as $key => $val)
		{
			$val=mysqli_real_escape_string($this->con,$val);
			
			$valstr[] = $key . " = " ."'".$val."'";
		}

	    $sql = "UPDATE ".$table." SET ".implode(', ', $valstr)." WHERE ".$where." = "."'$id'";
		
	   $ret=$this->generalquery($sql);
		 return $ret;
	}
	
	
	function update_in($table, $values, $where,$id)
	{
		foreach ($values as $key => $val)
		{
			$val=mysqli_real_escape_string($this->con,$val);
			$valstr[] = $key . " = " ."'".$val."'";
		}

	   $sql = "UPDATE ".$table." SET ".implode(', ', $valstr)." WHERE ".$where." IN ($id)";
		
	   $ret=$this->generalquery($sql);
		 return $ret;
	}
	
	
		function update_in_with_condition($table, $values, $where,$id)
	{
		foreach ($values as $key => $val)
		{
			$val=mysqli_real_escape_string($this->con,$val);
			$valstr[] = $key . " = " ."'".$val."'";
		}

	   $sql = "UPDATE ".$table." SET ".implode(', ', $valstr)." WHERE ".$where." IN ($id)";
		
	   $ret=$this->generalquery($sql);
		 return $ret;
	}
	
	function update_with_condition($table, $values, $where,$id,$cond)
	{
		foreach ($values as $key => $val)
		{
			$val=mysqli_real_escape_string($this->con,$val);
			$valstr[] = $key . " = " ."'".$val."'";
		}

	    $sql = "UPDATE ".$table." SET ".implode(', ', $valstr)." WHERE ".$where." = "."'$id' and $cond";
		
	    $ret=$this->generalquery($sql);
	    return $ret;
	}
		
	
	function remove_data($table,$field1,$id)
	{
	  	$sql="delete from $table where $field1='$id'";
		$ret=$this->generalquery($sql);
		 return true;
	}
	
	
	function remove_data_condition($table,$field1,$id,$cond)
	{
	  	$sql="delete from $table where $field1='$id' and $cond ";
		$this->generalquery($sql);
		return true;
		
	 }
	
	
	function convert_format($s,$val)
	{
		
		switch ($s) {
			
			case "I" : 
			$ret=$val;
			break;
			
			case "S" :
			$ret=$val;
    		break;
			
			case "i" : 
			if($val==0)  $ret='-'; else $ret=$val;
			break;
			
			case "T" :
			$ret=$this->long_time_format1($val);
    		break;
			
			case "t" :
			$ret=$this->short_time_format2($val);
    		break;
			
			case "d" :
			$ret=$this->date_format1($val);
			break;
			
			case "D" :
			$ret=$this->date_format2($val);
    		break;
			
			case "m" :
			$ret=$this->date_format3($val);
    		break;
			
			case "M" :
			$ret=$this->date_format4($val);
    		break;
			
			case "C" :
			$ret=$this->number_format1($val);
    		break;
			
			case "c" :
			$ret=$this->number_format2($val);
    		break;
			
			case "R" :
			$ret=$this->valueformat($val);
    		break;
			
			default :
			$ret=$val;
    		break;
			
		}
		
		return $ret;
	}
	
	function long_time_format1($value)
	{
		$h=substr($value,11,2);
		if($h<=12) { $a='AM'; $h=$h; } else { $a='PM'; $h=$h%12; } 
		$m=substr($value,14,2);
		$s=substr($value,17,2);
		return $h.":".$m.":".$s." ".$a;    // 25:15:48 PM
	}
	
	function short_time_format2($value)
	{
		$h=substr($value,11,2);
		$m=substr($value,14,2);
		return $h.":".$m;     // 25:15
	}
	
	function short_time_format3($value)
	{
		$h=substr($value,11,2);
		$m=substr($value,14,2);
		$s=substr($value,17,2);
		return $h.":".$m.":".$s;  // 25:15:48
	}
	
	
	function date_format1($value)
	{
		$mon=array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sept","Oct","Nov","Dec");		
		$d=substr($value,8,2);
		$m=substr($value,5,2);
		$y=substr($value,2,2);
		return $d."-".$mon[$m-1]."-".$y; // 25-Feb-15
	}
	
	function date_format2($value)
	{  
	
		$mon=array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sept","Oct","Nov","Dec");		
		$d=substr($value,8,2);
		$m=substr($value,5,2);
		$y=substr($value,0,4);
		return $d."-".$mon[$m-1]."-".$y;  // 25-Feb-2015
	
	}
	
	function date_format3($value)
	{
		$mon=array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sept","Oct","Nov","Dec");
		$m=substr($value,5,2);
		$y=substr($value,2,2);
		return $mon[$m-1]."-".$y; // Feb-15
	}
	
	function date_format4($value)
	{
		$mon=array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sept","Oct","Nov","Dec");
		$m=substr($value,5,2);
		$y=substr($value,0,4);
		return $mon[$m-1]."-".$y; // Feb-2015
	}
	
	
	function date_format5($value)
	{
		$d=substr($value,0,2);
		$m=substr($value,3,2);
		$y=substr($value,6,4);
		return $y."-".$m."-".$d; // 2016-03-25
	}
	
	
	function date_format5b($value)
	{
		if($value=="0000-00-00 00:00:00")
		{
		return "Nil";	
		}
		else
		{
		$datecreate=date_create($value);
		$date=date_format($datecreate,"Y-m-d H:i:s");
		
		return $date; // 2019-04-20 15:38:07
		}
		
	}
	
	
	function date_format6($value)
	{
		$d=substr($value,8,2);
		$m=substr($value,5,2);
		$y=substr($value,0,4);
		return $d."-".$m."-".$y; // 25-03-2016 
	}
	
	function date_format7($value)
	{ 
		$d=date('d',strtotime($value));
		if($d==01) $d=$d.'<sup>st</sup>';
		if($d==02) $d=$d.'<sup>nd</sup>';
		if($d==03) $d=$d.'<sup>rd</sup>';
		if($d>04) $d=$d.'<sup>th</sup>';
		$m=date('F',strtotime($value));
		$y=date('Y',strtotime($value));
		return $d." ".$m." ".$y; // 25-03-2016 
	}
	
	
	function full_date_time($value)
	{
		if($value=="0000-00-00 00:00:00")
		{
		return "Nil";	
		}
		else
		{
		$datecreate=date_create($value);
		$date=date_format($datecreate,"d-M-Y H:i:s A");
		
		return $date; // 25-jan-2018 9:20:15 am 
		}
		
	}
	
	
	
	
	
	function number_format1($value)
	{
		if($value!='')
		{
			return number_format($value,2);	
		}
		else
		{
			
			$value=0;
			return number_format($value,2);
		}
	}
	
	
	function number_format2($value)
	{
		if($value!='')
		{
			return number_format($value,3);	
		}
		else
		{
			
			$value=0;
			return number_format($value,3);
		}
	}
	
	
	function valueformat2($value)
	{
		if($value!='')
		{
			return number_format($value,2);	
		}
		else
		{
			
			$value=0;
			return number_format($value,2);
		}
	}
	
	
	
	
	
	
	
	function valueformat($num){
 $pos = strpos((string)$num, ".");
 if ($pos === false) {
 $decimalpart="00";
 }
 if (!($pos === false)) {
 $decimalpart= substr($num, $pos+1, 2); $num = substr($num,0,$pos);
 }

 if(strlen($num)>3 & strlen($num) <= 12){
 $last3digits = substr($num, -3 );
 $numexceptlastdigits = substr($num, 0, -3 );
 $formatted = $this->makeComma($numexceptlastdigits);
 $stringtoreturn = $formatted.",".$last3digits.".".$decimalpart ;
 }elseif(strlen($num)<=3){
 $stringtoreturn = $num.".".$decimalpart ;
 }elseif(strlen($num)>12){
 $stringtoreturn = number_format($num, 2);
 }

 if(substr($stringtoreturn,0,2)=="-,"){
 $stringtoreturn = "-".substr($stringtoreturn,2 );
 }

 return $stringtoreturn;
 }

 function makeComma($input){
 // This function is written by some anonymous person - I got it from Google
 if(strlen($input)<=2)
 { return $input; }
 $length=substr($input,0,strlen($input)-2);
 $formatted_input = $this->makeComma($length).",".substr($input,-2);
 return $formatted_input;
 }
	
	



function CallingChild($fld1,$fld2,$table,$parent, $user_tree_array = ' ') {
		

    if (!is_array($user_tree_array))
    $user_tree_array = array();
	
	 $sql2 = "SELECT $fld1, $fld2, PARENTID FROM $table WHERE PARENTID ='$parent'  AND $fld1=PARENTID  ORDER BY $fld1  ASC";
  $query2 =$this->generalquery($sql2);
	 $arr2=mysqli_fetch_array($query2);
	 $count=mysqli_num_rows($query2);
  if($count==1)
  {   
    $user_tree_array[] = "<tr data-tt-id='".$arr2['0']."'><td><span class='childspan  anim' style='color:#036'  onClick='copyrow(".$arr2['0'].");' >".$arr2['1']."</span></td></tr>";
  }
 
  $sql = "SELECT $fld1, $fld2, PARENTID FROM $table WHERE PARENTID ='$parent'  AND $fld1!=PARENTID ORDER BY $fld1 ASC";
  $query = $this->generalquery($sql);
 
			  if (mysqli_num_rows($query) > 0) {
				 /*$user_tree_array[] = "<ul>";*/
				while ($row = mysqli_fetch_array($query)) {
				  $user_tree_array[] = "<tr data-tt-id='".$row['0']."'  data-tt-parent-id='".$row['2']."' ><td><span class='childspan anim'  onClick='copyrow(".$row['0'].");'>".$row['1']."</span></td></tr>";
				
				  $user_tree_array = $this->CallingChild($fld1,$fld2,$table,$row['0'], $user_tree_array);
				  
				   
				  
				}
				/*$user_tree_array[] = "</ul>";*/
				
			  }
  return $user_tree_array;
	
}




function CallingTree($fld1,$fld2,$table) {
	
	$sql3 = "SELECT $fld1, $fld2, PARENTID FROM $table WHERE  $fld1=PARENTID  ORDER BY $fld1 ASC";
  $query3 = $this->generalquery($sql3);
	 while($arr3=mysqli_fetch_array($query3))
	 {
		 $p=$arr3['0'];

  $res =$this->CallingChild($fld1,$fld2,$table,$p);

  foreach ($res as $r) {
    echo  $r;
  }
	 }
	 
	
	
}



function CallingChildId($fld1,$fld2,$table,$parent, $user_tree_array = ' ', $side= '', $fld3 = '') {
		$code="";
		if($side!='' && $fld3!='')
		{
		$code="AND $fld3='$side' ";
		}

    if (!is_array($user_tree_array))
    $user_tree_array = array();
	
	 $sql2 = "SELECT $fld1, $fld2, PARENTID FROM $table WHERE PARENTID ='$parent'  AND $fld1=PARENTID  $code ORDER BY $fld1  ASC";

  $query2 =$this->generalquery($sql2);
	 $arr2=mysqli_fetch_array($query2);
	 $count=mysqli_num_rows($query2);
  if($count==1)
  {   
    $user_tree_array[] = $arr2['0'];
  }
 
  $sql = "SELECT $fld1, $fld2, PARENTID FROM $table WHERE PARENTID ='$parent'  AND $fld1!=PARENTID $code ORDER BY $fld1 ASC";
  $query = $this->generalquery($sql);
 
			  if (mysqli_num_rows($query) > 0) {
				while ($row = mysqli_fetch_array($query)) {
				  $user_tree_array[] = $row['0'];
				
				  $user_tree_array = $this->CallingChildId($fld1,$fld2,$table,$row['0'], $user_tree_array, $side, $fld3);			   
				  
				}
				
				
			  }
  return $user_tree_array;
	
}


function singledata($fld1,$fld2,$val2,$table)
{
	
		$sql2 = "SELECT $fld1 FROM $table WHERE $fld2='$val2'";
		$query2 =$this->generalquery($sql2);
		 $arr2=mysqli_fetch_array($query2);
	 return $arr2['0'];
}


function singledata_condition($fld1,$table,$condition)
{
	
		$sql2 = "SELECT $fld1 FROM $table WHERE $condition";
		$query2 =$this->generalquery($sql2);
		 $arr2=mysqli_fetch_array($query2);
	 return $arr2['0'];
}


function multi_twodata($fld1,$fld2,$fld3,$val,$table) 
{
	
		$sql2 = "SELECT $fld1,$fld2 FROM $table WHERE $fld3='$val'";
		$query2 =$this->generalquery($sql2);
	 	return $query2;
}



function savelog($form_name,$action,$description)
{
	$id=$this->last_insert_id_plus_one('tab105','FLD10501');
	$user=$_SESSION['EUSERS_ID'];
	$date=date('Y-m-d h:i:s');
	$sql2 = "insert into tab105 values ('$id','$user','$date','$form_name','$action','$description','','','');";
	$query2 =$this->generalquery($sql2);
}



function echopositive($value){
			if($value<0)
			{
				echo $value*(-1);	
			}
			else
			{
				echo $value;	
			}
	
}

function echopositive2($value){
	
			if($value<0)
			{
				return $value*(-1);	
			}
			else
			{
				return $value;	
			}
	
}

function echonegative2($value){
			if($value>0)
			{
				return $value*(-1);	
			}
			else
			{
				return $value;	
			}
	
}


function get_absent($id,$C_ID,$D_ID)
	{
		$sqll=" SELECT SUM(IF(LOCATE(',$id,',CONCAT(',',absent_list,','))>0,1,0)) absent_days from edu_attendance where C_ID='$C_ID' AND D_ID='$D_ID'";		
		$ret=$this->generalquery($sqll);
		$row=mysqli_fetch_array($ret);	
		return $row['absent_days'];
		
	}


function encryptIt( $q ) {
    $cryptKey  = '7cf7b9aba327b5294b17ce06';
    $qEncoded      = base64_encode( mcrypt_encrypt( MCRYPT_RIJNDAEL_256, md5( $cryptKey ), $q, MCRYPT_MODE_CBC, md5( md5( $cryptKey ) ) ) );
    return( $qEncoded );
}

function decryptIt( $q ) {
    $cryptKey  = '7cf7b9aba327b5294b17ce06';
    $qDecoded      = rtrim( mcrypt_decrypt( MCRYPT_RIJNDAEL_256, md5( $cryptKey ), base64_decode( $q ), MCRYPT_MODE_CBC, md5( md5( $cryptKey ) ) ), "\0");
    return( $qDecoded );
}

}
?>