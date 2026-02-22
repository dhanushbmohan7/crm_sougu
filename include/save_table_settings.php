<?php
if(isset($_POST['tablesettings']))
{
	$h=$_POST['rowid'];
	
	//echo "row id=".$h."<br>";
	$length='';
	$colname='';
	$format='';
	$t_alighn='';
	
	
	
			foreach(@$_POST['wid'] as $selected)
			{
			$length.=$selected."`";
			}
			foreach(@$_POST['colname'] as $selected)
			{
			$colname.=$selected."`";
			}
			
			foreach(@$_POST['format'] as $selected)
			{
			$format.=$selected."`";
			}
			
		
			foreach(@$_POST['td_alighn'] as $selected)
			{
			$t_alighn.=$selected."`";
			}
			
			
			$length=substr($length, 0, -1);
			$colname=substr($colname, 0, -1);
			$format=substr($format, 0, -1);
			$t_alighn=substr($t_alighn, 0, -1);
			
			
			
			$query2="update tab101 set  fld10109='$length', fld10108='$colname', FLD10110='$format', fld10107='$t_alighn' where fld10101='$h';";
			$sql2=$obj->generalquery($query2);
			
$_SESSION['D_MESSAGE']=11;
			
}	

?>