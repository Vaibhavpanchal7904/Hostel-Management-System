<?php
include("../config/config.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != "superadmin") {
    header("Location: ../login.php");
    exit;
}

$college_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM colleges"))['total'];
$hostel_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM hostels"))['total'];
$user_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users"))['total'];

$notice = mysqli_query($conn,"SELECT * FROM notices ORDER BY id DESC LIMIT 5");
?>

<!DOCTYPE html>
<html>
<head>

<title>Super Admin Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="superadmin.css">

</head>
<style></style>
<body>

<!-- ================= TOPBAR ================= -->

<div class="topbar d-flex justify-content-between align-items-center">

<div class="d-flex align-items-center gap-3" style="background:#f8f9fa; padding:10px 15px; border-radius:8px;">
    <h5 class="mb-0" style="color:#2a5298; font-weight:600;">
        Admin Dashboard
    </h5>
</div>

<div>
👤 <?php echo $_SESSION['name']; ?>
</div>

</div>


<div class="container-fluid">
<div class="row">

<!-- ================= SIDEBAR ================= -->

<?php include("sidebar.php"); ?>


<!-- ================= MAIN ================= -->

<div class="col-md-10 p-4">

<!-- ===== TOP CARDS (DATE + WELCOME + TIME) ===== -->

<div class="row g-3 mb-4">

<div class="col-md-4">
<div class="card info-card blue">
<h6>Date & Day | India</h6>
<h4 id="date"></h4>
<p id="day"></p>
</div>
</div>

<div class="col-md-4">
<div class="card info-card green">
<h5>Hello ! Welcome</h5>
<p>Stay Updated - Quick Access</p>
</div>
</div>

<div class="col-md-4">
<div class="card info-card blue">
<h6>Current Time (IST)</h6>
<h4 id="time"></h4>
</div>
</div>

</div>


<!-- ===== STATS ===== -->

<div class="row g-4">

<div class="col-md-4">
<div class="card stat-card primary text-center">
<h6>Total Colleges</h6>
<h2><?php echo $college_count; ?></h2>
</div>
</div>

<div class="col-md-4">
<div class="card stat-card success text-center">
<h6>Total Hostels</h6>
<h2><?php echo $hostel_count; ?></h2>
</div>
</div>

<div class="col-md-4">
<div class="card stat-card danger text-center">
<h6>Total Users</h6>
<h2><?php echo $user_count; ?></h2>
</div>
</div>

</div>


<!-- ===== MAIN CONTENT ===== -->

<div class="row mt-4">

<!-- LEFT (LOGO / IMAGE) -->
<div class="col-md-8">

<div class="card p-4 text-center main-card">

<div id="demoCarousel" class="carousel slide" data-bs-ride="false">

    <div class="carousel-inner">

        <!-- Slide 1 -->
        <div class="carousel-item active">
            <img src="../images/images.png" width="220" class="mb-4">

            <h4 class="text-primary fw-bold">
                Hostel Management System
            </h4>

            <p class="text-muted">
                Welcome to Hostel Management System.  
                Manage hostels, students, fees, rooms and reports efficiently.
            </p>
        </div>


        <!-- Slide 2 -->
        <div class="carousel-item">
            <h4 class="mb-3 text-success fw-bold">
                Website Demonstration
            </h4>

            <p class="text-muted mb-3">
                Watch how the system works step by step.
            </p>

            <video width="100%" controls class="rounded shadow">
                <source src="../images/demo.mp4" type="video/mp4">
                Your browser does not support video playback.
            </video>
        </div>


        <!-- Slide 3 -->
        <div class="carousel-item">
            <img src="../images/images.png" width="180" class="mb-4">

            <h4 class="fw-bold text-dark">
                Need Help?
            </h4>

            <p class="text-muted">
                If you have any doubts regarding the software,  
                you can check complete documentation, demo videos, 
                and support details here.
            </p>
        </div>

    </div>


    <!-- Previous Button -->
    <button class="carousel-control-prev"
            type="button"
            data-bs-target="#demoCarousel"
            data-bs-slide="prev">

        <span class="carousel-control-prev-icon bg-dark rounded-circle p-3"></span>
    </button>


    <!-- Next Button -->
    <button class="carousel-control-next"
            type="button"
            data-bs-target="#demoCarousel"
            data-bs-slide="next">

        <span class="carousel-control-next-icon bg-dark rounded-circle p-3"></span>
    </button>

</div>

</div>
</div>

<!-- RIGHT (LATEST UPDATES) -->
<div class="col-md-4">

<div class="card notice-card p-3">

<div class="d-flex justify-content-between align-items-center mb-2">
<h6 class="mb-0">Latest Updates</h6>
<small class="text-muted">Today</small>
</div>

<hr class="mt-2">

<?php 
if(mysqli_num_rows($notice) > 0){
while($row = mysqli_fetch_assoc($notice)) { 
?>

<div class="update-item">

<div class="update-dot"></div>

<div class="update-content">

<b class="update-title"><?php echo $row['title']; ?></b>

<p class="update-desc">
<?php echo substr($row['description'],0,80); ?>...
</p>

<?php if($row['file']) { ?>
<a href="../uploads/<?php echo $row['file']; ?>" target="_blank" class="btn btn-sm view-btn">
View File
</a>
<?php } ?>

</div>

</div>

<?php } } else { ?>
<p class="text-muted">No updates available</p>
<?php } ?>

</div>

</div>

</div>

</div>


</div>
</div>
</div>


<!-- ================= JS ================= -->

<script>
function updateTime(){
    let now = new Date();

    document.getElementById("date").innerHTML = now.toLocaleDateString();
    document.getElementById("day").innerHTML = now.toLocaleDateString('en-US',{weekday:'long'});
    document.getElementById("time").innerHTML = now.toLocaleTimeString();
}
setInterval(updateTime,1000);
updateTime();
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>