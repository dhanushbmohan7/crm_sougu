<?php
session_start();
date_default_timezone_set("Asia/Kolkata");
if(!isset($_SESSION['EUSERS_ID']))
{
?>
<script>
window.location.href='../index.php?ms=4';
</script>
<?php
}
$edit_password=$_SESSION['edit_password'];
$remove_password=$_SESSION['remove_password'];

?>