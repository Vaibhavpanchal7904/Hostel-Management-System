<?php
session_start();
include("../config/config.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != "superadmin") {
    header("Location: ../login.php");
    exit;
}

$college_id = $_GET['college_id'] ?? 0;

/* COLLEGE NAME */
$college = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT * FROM colleges WHERE id=$college_id
"));

/* STUDENTS */
$students = mysqli_query($conn,"
SELECT s.*, h.name as hostel_name 
FROM students s
JOIN hostels h ON s.hostel_id = h.id
WHERE s.college_id = $college_id
ORDER BY s.id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>College Students</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="superadmin.css">

</head>

<body>

<div class="container-fluid">
<div class="row">

<?php include("sidebar.php"); ?>

<div class="col-md-10 p-4">

<h4><?php echo $college['name']; ?> - Students</h4>
<hr>

<div class="card p-3">

<table class="table table-bordered table-hover">

<thead>
<tr>
<th>Name</th>
<th>Hostel</th>
<th>Room Type</th>
<th>Total Fees</th>
<th>Paid</th>
<th>Pending</th>
<th>Status</th>
</tr>
</thead>

<tbody>

<?php 
if(mysqli_num_rows($students) > 0){
while($row = mysqli_fetch_assoc($students)) {

$pending = $row['total_fees'] - $row['paid_amount'];
?>

<tr>

<td><b><?php echo $row['name']; ?></b></td>

<td><?php echo $row['hostel_name']; ?></td>

<td><?php echo $row['room_type']; ?></td>

<td>₹<?php echo $row['total_fees']; ?></td>

<td class="text-success">₹<?php echo $row['paid_amount']; ?></td>

<td class="text-danger">₹<?php echo $pending; ?></td>

<td>
<?php if($row['status']=="reserved"){ ?>
<span class="badge bg-warning text-dark">Reserved</span>
<?php } else { ?>
<span class="badge bg-success">Allotted</span>
<?php } ?>
</td>

</tr>

<?php } } else { ?>

<tr>
<td colspan="7" class="text-center text-muted">No Students Found</td>
</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>
</div>
</div>

</body>
</html>