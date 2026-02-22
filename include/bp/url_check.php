<?php
$zzz=$_SESSION['EUSERS_ID'];
$allocate=1;
if($sort=="")
{
?>
<script>
alert("THIS PAGE IS NOT ALLOCATED FOR CURRENT USER !!");
window.location.href='../index.php?ms=1';
</script>
<?php	
}

$queryurl="select FLD10304 from tab103 where FLD10301='$zzz';";
$sqlurl=$obj->generalquery($queryurl);
$arrurl=mysqli_fetch_array($sqlurl);
$list=$arrurl['FLD10304'];
$list=substr($list, 0, -1);
	$pieces = explode(",", $list);
	foreach($pieces as $val)
	{

		if($sort==$val)
		{
		$allocate=0;
		}
	}
	
	if($allocate==1)
	{
?>
<script>
alert("THIS PAGE IS NOT ALLOCATED FOR CURRENT USER !!");
window.location.href='../index.php?ms=1';
</script>
<?php
	}
?>