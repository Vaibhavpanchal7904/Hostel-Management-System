<?php
include("../config/config.php");

// Role check
if(!isset($_SESSION['role']) || $_SESSION['role'] != "collegeadmin") {
    header("Location: ../login.php");
    exit;
}

$college_id = $_SESSION['college_id'];

// Count students
$query = mysqli_query($conn, "SELECT COUNT(*) as total FROM students WHERE college_id=$college_id");
$total_students = mysqli_fetch_assoc($query)['total'];
?>

<!DOCTYPE html>
<html>
<head>
<title>College Admin Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../css/superadmin.css"> <!-- SAME CSS -->

</head>

<body>

<!-- ================= TOPBAR ================= -->

<div class="topbar d-flex justify-content-between align-items-center">

<div class="d-flex align-items-center gap-3">
<h5 class="mb-0">College Dashboard</h5>
<input type="text" class="form-control search-box" placeholder="Search...">
</div>

<div>
👤 <?php echo $_SESSION['name']; ?>
</div>

</div>


<div class="container-fluid">
<div class="row">

<!-- ================= SIDEBAR ================= -->

<div class="col-md-2 sidebar">

<h5 class="sidebar-title text-center mb-3">College Admin</h5>

<div class="menu-heading">MAIN</div>

<a href="dashboard.php" class="menu-link active">
<span>🏠</span> Dashboard
</a>

<a href="add_student.php" class="menu-link">
<span>➕</span> Add Student
</a>

<a href="view_students.php" class="menu-link">
<span>👨‍🎓</span> View Students
</a>

<div class="menu-heading">ACCOUNT</div>

<a href="../logout.php" class="menu-link danger">
<span>🚪</span> Logout
</a>

</div>


<!-- ================= MAIN ================= -->

<div class="col-md-10 p-4">

<!-- ===== TOP CARDS ===== -->

<div class="row g-3 mb-4">

<div class="col-md-4">
<div class="card info-card blue">
<h6>Date & Day</h6>
<h4 id="date"></h4>
<p id="day"></p>
</div>
</div>

<div class="col-md-4">
<div class="card info-card green">
<h5>Welcome 👋</h5>
<p>Manage your college efficiently</p>
</div>
</div>

<div class="col-md-4">
<div class="card info-card blue">
<h6>Current Time</h6>
<h4 id="time"></h4>
</div>
</div>

</div>


<!-- ===== STATS ===== -->

<div class="row g-4">

<div class="col-md-4">
<div class="card stat-card primary text-center">
<h6>Total Students</h6>
<h2><?php echo $total_students; ?></h2>
</div>
</div>

</div>


<!-- ===== MAIN CONTENT ===== -->

<div class="row mt-4">

<div class="col-md-8">

<div class="card p-5 text-center main-card">

<img src="../images/images.png" width="200">
<h5 class="mt-3">College Management Panel</h5>

</div>

</div>

<div class="col-md-4">

<div class="card notice-card p-3">

<h6>Quick Actions</h6>
<hr>

<a href="add_student.php" class="btn btn-primary w-100 mb-2">Add Student</a>
<a href="view_students.php" class="btn btn-outline-primary w-100">View Students</a>

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

</body>
</html>