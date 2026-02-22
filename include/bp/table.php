<?php
include('dbconnection.php');

$sql1=mysqli_query($con,"create table if not exists edu_users(
USERS_ID int(10) NOT NULL AUTO_INCREMENT,
NAME varchar(50) DEFAULT NULL,
USERNAME varchar(30) DEFAULT NULL,
PASSWORD varchar(30) DEFAULT NULL,
PHONE varchar(15) DEFAULT NULL,
ADDRESS varchar(200) DEFAULT NULL,
PRIVILAGE varchar(100) DEFAULT NULL,
UFLAG int(2) DEFAULT 1,
CREATEDBY varchar(10) DEFAULT NULL,
profilepic varchar(50) DEFAULT NULL,
ENTRY_DATE date,
primary key (USERS_ID)
);");


$sql2=mysqli_query($con,"create table if not exists edu_course(
C_ID int(10) NOT NULL AUTO_INCREMENT,
CNAME varchar(30) DEFAULT NULL,
primary key (C_ID)
);");


$sql3=mysqli_query($con,"create table if not exists edu_division(
D_ID int(10) NOT NULL AUTO_INCREMENT,
DNAME varchar(30) DEFAULT NULL,
primary key (D_ID)
);");


$sql4=mysqli_query($con,"create table if not exists edu_term(
T_ID int(10) NOT NULL AUTO_INCREMENT,
TERM_NAME varchar(30) DEFAULT NULL,
C_ID int(10) DEFAULT NULL,
primary key (T_ID)
);");


//$drop=mysqli_query($con,"DROP TABLE IF EXISTS edu_admission;");

$sql5=mysqli_query($con,"create table if not exists edu_admission(
A_ID int(10) NOT NULL AUTO_INCREMENT,
FNAME varchar(50) DEFAULT NULL,
LNAME varchar(50) DEFAULT NULL,
DOB date,
GENDER varchar(10) DEFAULT NULL,
RELIGION varchar(100) DEFAULT NULL,
CASTE varchar(100) DEFAULT NULL,
CATEGORY varchar(100) DEFAULT NULL,
HOUSENO varchar(15) DEFAULT NULL,
ADDRESS varchar(500) DEFAULT NULL,
PINCODE int(6) DEFAULT NULL,
MOBILE varchar(15) DEFAULT NULL,
EMAIL_ID varchar(50) DEFAULT NULL,
NATIONALITY varchar(100) DEFAULT NULL,
STATE varchar(100) DEFAULT NULL,
CITY varchar(100) DEFAULT NULL,
A_FLAG int(2) DEFAULT 1,
photo_name varchar(50) DEFAULT NULL,
ADMISSION_DATE date,
ENTRY_DATE date,
primary key (A_ID)
);");


$drop=mysqli_query($con,"DROP TABLE IF EXISTS edu_services;");

$sql6=mysqli_query($con,"create table if not exists edu_services(
S_ID int(10) NOT NULL AUTO_INCREMENT,
SERVICE_NAME varchar(200) DEFAULT NULL,
T_ID int(10) DEFAULT NULL,
S_AMOUNT double(15,2) DEFAULT NULL,
primary key (S_ID)
);");


$sql7=mysqli_query($con,"create table if not exists edu_billmaster(
B_ID int(10) NOT NULL AUTO_INCREMENT,
A_ID int(10) DEFAULT NULL,
T_ID int(10) DEFAULT NULL,
DISCOUNT double(15,2) DEFAULT NULL,
PAID_AMOUNT double(15,2) DEFAULT NULL,
CONCESSION_FLAG int(2) DEFAULT NULL,
CHECK_NO varchar(20),
CHECK_FLAG int(2) DEFAULT 1,
FYEAR_ID int(10) DEFAULT NULL,
BILL_NO varchar(20) DEFAULT NULL,
BILL_DATE date,
ENTRY_DATE date,
primary key (B_ID)
);");


$sql8=mysqli_query($con,"create table if not exists edu_generalbill(
GB_ID int(10) NOT NULL AUTO_INCREMENT,
A_ID int(10) DEFAULT NULL,
GB_DESCRIPTION varchar(500) DEFAULT NULL,
AMOUNT double(15,2) DEFAULT NULL,
GENERALBILL_DATE date,
ENTRY_DATE date,
primary key (GB_ID)
);");


$sql9=mysqli_query($con,"create table if not exists edu_teachermaster(
TE_ID int(10) NOT NULL AUTO_INCREMENT,
NAME varchar(30) DEFAULT NULL,
GENDER char(1) DEFAULT NULL,
DOB date,
QUALIFICATION varchar(20),
SUBJECT varchar(20),
ENTRY_DATE date,
primary key (TE_ID)
);");

$sql9=mysqli_query($con,"create table if not exists edu_promotion_master(
P_ID int(10) NOT NULL AUTO_INCREMENT,
A_ID int(10),
C_ID int(10),
D_ID int(10),
p_flag int(2) DEFAULT 1,
primary key (P_ID)
);");

$sql10=mysqli_query($con,"create table if not exists edu_College(
col_ID int(10) NOT NULL AUTO_INCREMENT,
col_name varchar(30) DEFAULT NULL,
ADDRESS varchar(500) DEFAULT NULL,
PINCODE int(6) DEFAULT NULL,
MOBILE int(12) DEFAULT NULL,
EMAIL_ID varchar(50) DEFAULT NULL,
STATE int(10) DEFAULT NULL,
CITY int(10) DEFAULT NULL,
ENTRY_DATE date,
logopic varchar(50) DEFAULT NULL,
primary key (col_ID)
)");



if(!$sql1 || !$sql2 || !$sql3 || !$sql4 || !$sql5 || !$sql6 || !$sql7 || !$sql8 || !$sql9 || !$sql10)
{
echo "Table Creation Failed";	
}
else
{
echo "Table Creation Successfull";	
}

?>