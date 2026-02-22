<style>
/* Sidebar */

#toggleSidebar {
    position: fixed;
    top: 4px;
    left: 12px;
    z-index: 9999;
    background: #212529;
    color: white;
    padding: 8px 10px;
    border-radius: 5px;
    font-size: 22px;
    cursor: pointer;
}



#sidebar {
    height: 100vh;
    width: 250px;
    background: #212529;
    position: fixed;
    left: 0;
    top: 0;
    padding-top: 60px;
    transition: all 0.3s ease;
}
#sidebar.collapsed {
/*    width: 70px;*/
display: none;
}

/* Sidebar items */
#sidebar .nav-link {
    color: #d4d4d4;
    padding: 12px 20px;
}
#sidebar .nav-link:hover {
    background: #333;
}

/* Hide menu text on collapse */
#sidebar.collapsed .menu-text {
    display: none !important;
}
#sidebar.collapsed ~ .page-container {
    margin-left: 0px;
}
/* Content */
#content {
    margin-left: 250px;
    padding: 20px;
    transition: 0.3s;
}
#content.expanded {
    margin-left: 0px;
}

/* Top navbar */
.navbar {
    margin-left: 250px;
    transition: 0.3s;
}
.navbar.expanded {
    margin-left: 70px;
}
</style>


<!-- ALWAYS VISIBLE Toggle Button -->
<div id="toggleSidebar">
    <i class="bi bi-list"></i>
</div>

<!-- TOP NAVBAR -->
<nav class="navbar navbar-dark bg-dark px-3" id="topNavbar">
    <span class="navbar-brand fw-bold"> <a href="index.php" class="nav-link">CRM Dashboard</a></span>

    <div class="ms-auto text-white">
        <i class="bi bi-person-circle"></i> <?= $username ?>
    </div>
</nav>

<!-- LEFT SIDEBAR -->
<div id="sidebar">
    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="crm.php" class="nav-link">
                <i class="bi bi-grid"></i>
                <span class="menu-text ms-2">CRM</span>
            </a>
        </li>

        <?php 

$usergroup=$_SESSION['usergroup'];

if($usergroup==1){


?>


 <li class="nav-item">
            <a href="lead_status.php" class="nav-link">
                <i class="bi bi-chat-dots"></i>
                <span class="menu-text ms-2">Lead Status</span>
            </a>
        </li>  <li class="nav-item">
            <a href="staff_master.php" class="nav-link">
            <i class="bi bi-person-x"></i> 
                <span class="menu-text ms-2">Staff Creation</span>
            </a>
        </li>

         
<?php


}




        ?>
        <li class="nav-item">
            <a href="../include/logout.php" class="nav-link">
                <i class="bi bi-box-arrow-right"></i> 
                <span class="menu-text ms-2">Logout</span>
            </a>
        </li>

       
    </ul>
</div>

<!-- MAIN CONTENT -->

