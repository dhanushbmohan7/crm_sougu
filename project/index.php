<?php
require("../include/session_chk.php");
include "../include/library.php";
$obj = new Library();

$username = $_SESSION['EUSERS_NAME'] ?? 'User';
$usergroup = $_SESSION['usergroup'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>CRM Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
body {
    overflow-x: hidden;
    background: #f5f6fa;
}
.card {
    border-radius: 10px;
}
</style>
</head>
<body>

<?php include('header.php'); ?>

<div id="content">

<?php if($usergroup == 1){ ?>

<div class="container-fluid mt-4">

    <h4 class="fw-bold mb-4">CRM Dashboard</h4>




<div class="card shadow-sm mb-4">
<?php
$currentYear = date('Y');
?>
<div class="card shadow-sm mb-4">
<div class="card-body">
<div class="row g-3 align-items-end">

<!-- Filter Type -->
<div class="col-md-2">
<label class="form-label">Filter Type</label>
<select class="form-select" id="filterType">
<option value="year">Year</option>
<option value="month">Month</option>
<option value="week">Week</option>
<option value="custom">Custom Range</option>
</select>
</div>

<!-- Year -->
<div class="col-md-2" id="yearDiv">
<label class="form-label">Year</label>
<select class="form-select" id="year">
<?php for($y=$currentYear-5;$y<=$currentYear+1;$y++){ ?>
<option value="<?=$y?>" <?=$y==$currentYear?'selected':''?>>
<?=$y?>
</option>
<?php } ?>
</select>
</div>

<!-- Month -->
<div class="col-md-2 d-none" id="monthDiv">
<label class="form-label">Month</label>
<select class="form-select" id="month">
<option value="">Select Month</option>
<?php
for($m=1;$m<=12;$m++){
$monthName=date('F',mktime(0,0,0,$m,1));
?>
<option value="<?=$m?>"><?=$monthName?></option>
<?php } ?>
</select>
</div>

<!-- Week -->
<div class="col-md-2 d-none" id="weekDiv">
<label class="form-label">Week</label>
<select class="form-select" id="week">
<option value="">Select Week</option>
<?php for($w=1;$w<=53;$w++){ ?>
<option value="<?=$w?>">Week <?=$w?></option>
<?php } ?>
</select>
</div>

<!-- Custom Date -->
<div class="col-md-2 d-none" id="fromDiv">
<label class="form-label">From</label>
<input type="date" class="form-control" id="fromDate">
</div>

<div class="col-md-2 d-none" id="toDiv">
<label class="form-label">To</label>
<input type="date" class="form-control" id="toDate">
</div>

<!-- User -->
<div class="col-md-2">
<label class="form-label">User</label>
<select class="form-select" id="userFilter">
<option value="0">All Users</option>
<?php 
$users = $obj->generalquery("SELECT USERS_ID, NAME FROM edu_users WHERE usergroup=2");
foreach($users as $u){
?>
<option value="<?=$u['USERS_ID']?>"><?=$u['NAME']?></option>
<?php } ?>
</select>
</div>

<div class="col-md-2">
<button class="btn btn-primary w-100" onclick="loadDashboard()">Apply</button>
</div>

</div>
</div>
</div>
</div>


    <!-- SUMMARY CARDS -->
    <div class="row g-4 mb-4">

        <div class="col-md-2">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <small class="text-muted">Total Leads</small>
                    <h3 class="fw-bold mt-2" id="totalLeads">0</h3>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card shadow-sm text-center border-start border-primary border-4">
                <div class="card-body">
                    <small class="text-muted">New</small>
                    <h3 class="fw-bold mt-2" id="newLeads">0</h3>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card shadow-sm text-center border-start border-success border-4">
                <div class="card-body">
                    <small class="text-muted">Won</small>
                    <h3 class="fw-bold mt-2" id="wonLeads">0</h3>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card shadow-sm text-center border-start border-danger border-4">
                <div class="card-body">
                    <small class="text-muted">Lost</small>
                    <h3 class="fw-bold mt-2" id="lostLeads">0</h3>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card shadow-sm text-center border-start border-warning border-4">
                <div class="card-body">
                    <small class="text-muted">Follow-up</small>
                    <h3 class="fw-bold mt-2" id="followupLeads">0</h3>
                </div>
            </div>
        </div>

    </div>

    <!-- MONTH WISE GRAPH -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h6 class="fw-semibold mb-3">Month-wise Lead Trend</h6>
            <canvas id="monthChart" height="90"></canvas>
        </div>
    </div>

    <!-- STATUS GRAPH -->
    <div class="row justify-content-center mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3">Leads by Status</h6>
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- USER WISE TABLE -->
    <div class="card shadow-sm">
        <div class="card-body">
            <h6 class="fw-semibold mb-3">User-wise Lead Performance</h6>

            <div class="table-responsive">
                <table class="table table-bordered table-striped text-center align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>User</th>
                            <th>Total</th>
                            <th>New</th>
                            <th>Won</th>
                            <th>Lost</th>
                            <th>Follow-up</th>
                        </tr>
                    </thead>
                    <tbody id="userTable"></tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php } ?>

</div>

<?php include('scripts.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

    $('#filterType').on('change', function(){

    let type = $(this).val();

    $('#yearDiv, #monthDiv, #weekDiv, #fromDiv, #toDiv').addClass('d-none');

    if(type === 'year'){
        $('#yearDiv').removeClass('d-none');
    }

    if(type === 'month'){
        $('#yearDiv, #monthDiv').removeClass('d-none');
    }

    if(type === 'week'){
        $('#yearDiv, #weekDiv').removeClass('d-none');
    }

    if(type === 'custom'){
        $('#fromDiv, #toDiv').removeClass('d-none');
    }

});
let monthChart=null;
let statusChart=null;

document.getElementById('filterType').addEventListener('change',function(){
if(this.value==='custom'){
document.getElementById('fromDiv').classList.remove('d-none');
document.getElementById('toDiv').classList.remove('d-none');
}else{
document.getElementById('fromDiv').classList.add('d-none');
document.getElementById('toDiv').classList.add('d-none');
}
});

function loadDashboard(){

    let data = {
        filter_type: $('#filterType').val(),
        year: $('#year').val(),
        month: $('#month').val(),
        week: $('#week').val(),
        from_date: $('#fromDate').val(),
        to_date: $('#toDate').val(),
        user_id: $('#userFilter').val()
    };

    $.ajax({
        url: 'dashboard_data.php',
        type: 'POST',
        data: data,
        dataType: 'json',
        success: function(d){

            $('#totalLeads').text(d.summary.total ?? 0);
            $('#newLeads').text(d.summary.new_leads ?? 0);
            $('#wonLeads').text(d.summary.won_leads ?? 0);
            $('#lostLeads').text(d.summary.lost_leads ?? 0);
            $('#followupLeads').text(d.summary.followup_leads ?? 0);

            if(monthChart) monthChart.destroy();
            monthChart = new Chart(document.getElementById('monthChart'), {
                type:'bar',
                data:{
                    labels:d.months.map(x=>x.month),
                    datasets:[{
                        data:d.months.map(x=>x.total),
                        backgroundColor:'rgba(13,110,253,0.2)',
                        borderColor:'#0d6efd',
                        borderWidth:2
                    }]
                },
                options:{responsive:true,plugins:{legend:{display:false}}}
            });

            if(statusChart) statusChart.destroy();
            statusChart = new Chart(document.getElementById('statusChart'), {
                type:'doughnut',
                data:{
                    labels:d.status.map(x=>x.status_name),
                    datasets:[{
                        data:d.status.map(x=>x.total),
                        
                        backgroundColor: [
    '#0d6efd', // primary
    '#ffc107', // warning
    '#198754', // success
    '#dc3545', // danger
    '#6c757d', // secondary

    // +5 new colors
    
    '#fd7e14', // orange
    '#20c997', // teal
    '#0dcaf0', // cyan
    '#d63384'  // pink
]
                    }]
                }
            });

            let userHtml='';
            $.each(d.users,function(i,u){
                userHtml+=`
                <tr>
                <td>${u.NAME}</td>
                <td>${u.total}</td>
                <td>${u.new_leads}</td>
                <td class="text-success fw-bold">${u.won_leads}</td>
                <td class="text-danger fw-bold">${u.lost_leads}</td>
                <td class="text-warning fw-bold">${u.followup_leads}</td>
                </tr>`;
            });

            $('#userTable').html(userHtml);

        }
    });
}

loadDashboard();
</script>

</body>
</html>
