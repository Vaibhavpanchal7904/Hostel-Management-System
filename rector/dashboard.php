<?php
include("../config/config.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != "rector") {
    header("Location: ../login.php");
    exit;
}

$hostel_id = $_SESSION['hostel_id'];

/* =============================
   BASIC COUNTS
============================= */
$reserved = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) as total FROM students WHERE hostel_id=$hostel_id AND status='reserved'"
))['total'];

$allotted = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) as total FROM students WHERE hostel_id=$hostel_id AND status='allotted'"
))['total'];

/* =============================
   SMART ANALYTICS
============================= */

// total beds
$totalBeds = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT SUM(total_beds) as total FROM rooms WHERE hostel_id=$hostel_id"
))['total'] ?? 0;

// filled beds
$filledBeds = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT SUM(filled_beds) as total FROM rooms WHERE hostel_id=$hostel_id"
))['total'] ?? 0;

// pending fees
$pendingFees = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) as total FROM students 
 WHERE hostel_id=$hostel_id 
 AND (total_fees > paid_amount)"
))['total'] ?? 0;

// occupancy %
$occupancy = ($totalBeds > 0) ? round(($filledBeds / $totalBeds) * 100) : 0;

/* =============================
   COLLEGE WISE
============================= */
$collegeWise = mysqli_query($conn,"
SELECT c.name as college_name,
SUM(CASE WHEN s.status='reserved' THEN 1 ELSE 0 END) as reserved,
SUM(CASE WHEN s.status='allotted' THEN 1 ELSE 0 END) as allotted,
COUNT(*) as total
FROM students s
JOIN colleges c ON s.college_id = c.id
WHERE s.hostel_id = $hostel_id
GROUP BY s.college_id
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Rector Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="rector.css">

</head>

<body>

<!-- ================= TOPBAR ================= -->

<div class="topbar d-flex justify-content-between align-items-center">

<div class="d-flex align-items-center gap-3">
<h5 class="mb-0">Smart Rector Dashboard</h5>
<input type="text" class="form-control search-box" placeholder="Search...">
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

<!-- HEADER -->
<div class="card info-card blue mb-4">
<h5>Hostel Intelligence Panel</h5>
<p>Real-time analytics & monitoring</p>
</div>

<!-- ================= STATS ================= -->

<div class="row g-4">

<div class="col-md-3">
<div class="card stat-card primary text-center">
<h6>Total Beds</h6>
<h2><?php echo $totalBeds; ?></h2>
</div>
</div>

<div class="col-md-3">
<div class="card stat-card success text-center">
<h6>Filled Beds</h6>
<h2><?php echo $filledBeds; ?></h2>
</div>
</div>

<div class="col-md-3">
<div class="card stat-card danger text-center">
<h6>Pending Fees</h6>
<h2><?php echo $pendingFees; ?></h2>
</div>
</div>

<div class="col-md-3">
<div class="card stat-card text-center">
<h6>Occupancy</h6>
<h2><?php echo $occupancy; ?>%</h2>
</div>
</div>

</div>

<!-- ================= OCCUPANCY BAR ================= -->

<div class="card p-4 mt-4 main-card">

<h6>Hostel Occupancy</h6>

<div class="progress mt-3" style="height:25px;">
<div class="progress-bar bg-success" 
style="width: <?php echo $occupancy; ?>%">
<?php echo $occupancy; ?>%
</div>
</div>

<?php if($occupancy >= 90){ ?>
<div class="alert alert-danger mt-3">
⚠️ Hostel almost full!
</div>
<?php } ?>

</div>

<!-- ================= QUICK ACTIONS ================= -->

<div class="row mt-4">

<div class="col-md-6">
<div class="card p-4 main-card">

<h6>Quick Actions</h6>

<a href="view_students.php?status=reserved" 
class="btn btn-warning w-100 mb-2">
View Reserved Students
</a>

<a href="view_students.php?status=allotted" 
class="btn btn-success w-100">
View Allotted Students
</a>

</div>
</div>

<div class="col-md-6">
<div class="card p-4 main-card">

<h6>Insights</h6>

<p>🛏️ Available Beds: <b><?php echo $totalBeds - $filledBeds; ?></b></p>
<p>⚠️ Students Pending Fees: <b><?php echo $pendingFees; ?></b></p>
<p>📊 Reserved: <b><?php echo $reserved; ?></b></p>
<p>✅ Allotted: <b><?php echo $allotted; ?></b></p>

</div>
</div>

</div>

<!-- ================= TABLE ================= -->

<div class="card p-3 main-card mt-4">

<h6 class="mb-3">College-wise Distribution</h6>

<div class="table-responsive">

<table class="table align-middle">

<thead>
<tr>
<th>College</th>
<th>Reserved</th>
<th>Allotted</th>
<th>Total</th>
</tr>
</thead>

<tbody>

<?php 
if($collegeWise && mysqli_num_rows($collegeWise) > 0){
    while($row = mysqli_fetch_assoc($collegeWise)){
?>
<tr>
<td><strong><?php echo htmlspecialchars($row['college_name']); ?></strong></td>
<td><?php echo $row['reserved']; ?></td>
<td><?php echo $row['allotted']; ?></td>
<td><?php echo $row['total']; ?></td>
</tr>
<?php
    }
}else{
?>
<tr>
<td colspan="4" class="text-center text-muted">No Students Found</td>
</tr>
<?php } ?>

</tbody>
</table>

</div>

</div>

</div>
</div>
</div>

</body>
</html>