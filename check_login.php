<?php
session_start();
ob_start();

$_SESSION['maindb']='zoqa';
$_SESSION['first']='hygiene_';

include "include/library.php";
$obj=new Library();

if(isset($_POST['login']))
{
	
	
	 $uname=$_POST['username'];
	 $passwd=$_POST['password'];
	
	$query1="select U.USERS_ID USERS_ID,U.NAME USERS_NAME,U.profilepic profilepic,U.PRIVILAGE PRIVILAGE,U.edit_password edit_password,U.remove_password remove_password ,U.branch branch ,U.order_series order_series ,U.series_start series_start ,usergroup
	from edu_users U 
	
	where U.USERNAME='$uname' and U.PASSWORD='$passwd' and U.UFLAG='1' "; 
	
	
	$ret1=$obj->generalquery($query1);
	if(mysqli_num_rows($ret1)==1)
	{
		$res1=mysqli_fetch_array($ret1);
		$_SESSION['EUSERS_ID']=$res1['USERS_ID'];
		$_SESSION['EUSERS_NAME']=$res1['USERS_NAME'];
		
		$_SESSION['edit_password']=$res1['edit_password'];
		
		$_SESSION['remove_password']=$res1['remove_password'];
		$_SESSION['order_series']=$res1['order_series'];
		$_SESSION['series_start']=$res1['series_start'];
		$_SESSION['usergroup']=$res1['usergroup'];
		
		$pro_pic=$res1['profilepic'];
		if($pro_pic!="")
		{
			$_SESSION['EUSERS_PIC']=$pro_pic;
		}
		else
		{
			$_SESSION['EUSERS_PIC']="default.jpg";
		}
		
		
		$_SESSION['I_TYPE']="C";
		$privilage=$res1['PRIVILAGE'];
			
		if($privilage=="admin")
		{
			// header("Location:admin/index.php");
				header("Location:project/index.php");
			
		}
		else if($privilage=="staff" || $privilage=="hr" || $privilage=="dtdc")
		{
				$company_id=1;
			
			
			
							
				$query3="select UID,CompanyCode,BranchCode,BranchName,CompanyType,out_kerala, GSTSTATE from companysettings where  UID='$company_id' ";
				$ret3=$obj->generalquery($query3);
				$res3=mysqli_fetch_array($ret3);
				
				$db=($res3['CompanyType']==1) ? $_SESSION['maindb']: $_SESSION['first'].$res3['UID'];
				
				$_SESSION['TUID']=$res3['UID'];
				$_SESSION['db']=$db;
				$_SESSION['GSTSTATE']=$res3['GSTSTATE'];
				$_SESSION['user_privilage']=$privilage;
				$_SESSION['TCompanyCode']=$res3['CompanyCode'];
				$_SESSION['TBranchCode']=$res3['BranchCode'];
				$_SESSION['TCompanyName']=$res3['BranchName'];
				$_SESSION['TCompanyType']=$res3['CompanyType'];
				$_SESSION['OUT']=$res3['out_kerala'];
				header("Location:project/crm.php");
		}
		else
		{
			header("Location:index.php?ms=1");
		}
		
	}
	else
	{
		header("Location:index.php?ms=5");
	}
}
else
{
	header("Location:index.php?ms=4");
}
?>