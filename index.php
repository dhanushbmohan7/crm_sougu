<?php
include "include/library.php";
$obj = new Library();

@$ms = $_GET['ms'];
// $CRX = $_GET['CRX'];



$msg = "";
if ($ms == 1) $msg = "No Privilege Set";
else if ($ms == 2) $msg = "Logged Out Successfully";
else if ($ms == 3) $msg = "Default Financial Year Not Set";
else if ($ms == 4) $msg = "Not A Proper Way To Login";
else if ($ms == 5) $msg = "Username Or Password Error";
else if ($ms == 6) $msg = "Year Closing Done Successfully";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>CRM</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet" href="css/bootstrap.min.css">

<style>
body {
    font-family: 'Roboto', sans-serif;
    background: linear-gradient(135deg, #3f4673 0%, #8994c7 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Glassmorphism Card */
.login-card {
    background: rgba(255, 255, 255, 0.12);
    border-radius: 15px;
    padding: 40px 35px;
    border: 1px solid rgba(255, 255, 255, 0.25);
    backdrop-filter: blur(12px);
    color: #fff;
    width: 360px;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
}

.login-card h3 {
    text-align: center;
    margin-bottom: 25px;
    font-size: 26px;
    font-weight: 600;
}

.login-card input {
    height: 48px;
    font-size: 15px;
    border-radius: 8px;
    padding-left: 15px;
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255,255,255,0.3);
    color: #fff;
}

.login-card input::placeholder {
    color: #f0f0f0;
}

.login-card input:focus {
    background: rgba(255, 255, 255, 0.25);
    color: #fff;
    box-shadow: none;
}

.btn-login {
    width: 100%;
    background: #ffffff;
    color: #3f4673;
    border-radius: 8px;
    font-size: 17px;
    font-weight: 600;
    height: 48px;
    border: none;
}

.btn-login:hover {
    background: #dde1ff;
    color: #3f4673;
}

.error-msg {
    text-align: center;
    margin-top: 10px;
    color: #ffdddd;
    font-size: 15px;
    font-weight: 500;
}
</style>

</head>
<body>

<div class="login-card">

    <h3>Login</h3>

    <form action="check_login.php" method="post">

        <div class="mb-3">
            <input type="text" name="username" class="form-control" placeholder="Username" required>
        </div>

        <div class="mb-3">
            <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>

        <?php if ($msg != ""): ?>
            <div class="error-msg"><?php echo $msg; ?></div>
        <?php endif; ?>

        <div class="mt-4">
            <button type="submit" name="login" class="btn btn-login">Sign In</button>
        </div>

    </form>
</div>

<script src="js/bootstrap.min.js"></script>

</body>
</html>
