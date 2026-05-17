<?php
include("../config/config.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != "superadmin") {
    header("Location: ../login.php");
    exit;
}

$college_id = isset($_GET['college']) ? intval($_GET['college']) : 0;

/* GRAND TOTALS (ALL COLLEGES) */
$total_query = mysqli_query($conn,"
SELECT 
SUM(total_fees) as total_fees,
SUM(paid_amount) as paid,
SUM(total_fees-paid_amount) as pending
FROM students
");

$totals = mysqli_fetch_assoc($total_query);


/* COLLEGE SUMMARY */
$college_summary = mysqli_query($conn,"
SELECT 
    c.id,
    c.name,
    SUM(s.total_fees) as total_fees,
    SUM(s.paid_amount) as paid,
    SUM(s.total_fees-s.paid_amount) as pending
FROM students s
JOIN colleges c ON s.college_id=c.id
GROUP BY c.id
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Payment Report</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="superadmin.css">

<style>
.card-blue{
    background:linear-gradient(135deg,#dbeafe,#bfdbfe);
}

.card-green{
    background:linear-gradient(135deg,#dcfce7,#bbf7d0);
}

.card-red{
    background:linear-gradient(135deg,#fee2e2,#fecaca);
}
</style>

</head>

<body>

<div class="container-fluid">
<div class="row">

<?php include("sidebar.php"); ?>

<div class="col-md-10 content">

<h3>Payment Pending Report</h3>
<hr>

<!-- GRAND TOTAL CARDS -->
<div class="row g-3 mb-4">

<div class="col-md-4">
<div class="card card-blue text-center p-3">
<h6>Total Fees</h6>
<h3>₹ <?php echo number_format($totals['total_fees'],2); ?></h3>
</div>
</div>

<div class="col-md-4">
<div class="card card-green text-center p-3">
<h6>Total Paid</h6>
<h3>₹ <?php echo number_format($totals['paid'],2); ?></h3>
</div>
</div>

<div class="col-md-4">
<div class="card card-red text-center p-3">
<h6>Total Pending</h6>
<h3>₹ <?php echo number_format($totals['pending'],2); ?></h3>
</div>
</div>

</div>


<?php if($college_id > 0){ 

/* STUDENT DETAILS */
$report = mysqli_query($conn,"
SELECT 
    s.name as student_name,
    h.name as hostel_name,
    s.total_fees,
    s.paid_amount,
    (s.total_fees-s.paid_amount) as pending
FROM students s
JOIN hostels h ON s.hostel_id=h.id
WHERE s.college_id='$college_id'
");
?>

<!-- STUDENT DETAILS TABLE -->
<div class="card p-4">

<h4 class="mb-3">Student Payment Details</h4>

<a href="payment_report.php" class="btn btn-secondary mb-3">
Back
</a>

<table class="table">

<thead>
<tr>
<th>Student</th>
<th>Hostel</th>
<th>Total Fees</th>
<th>Paid</th>
<th>Pending</th>
</tr>
</thead>

<tbody>

<?php while($row=mysqli_fetch_assoc($report)){ ?>
<tr>

<td><?php echo $row['student_name']; ?></td>
<td><?php echo $row['hostel_name']; ?></td>

<td>
₹ <?php echo number_format($row['total_fees'],2); ?>
</td>

<td class="text-success">
₹ <?php echo number_format($row['paid_amount'],2); ?>
</td>

<td class="text-danger fw-bold">
₹ <?php echo number_format($row['pending'],2); ?>
</td>

</tr>
<?php } ?>

</tbody>
</table>

</div>

<?php } else { ?>

<!-- COLLEGE SUMMARY TABLE -->
<div class="card p-4">

<h4 class="mb-3">College Wise Payment Summary</h4>

<table class="table">

<thead>
<tr>
<th>College</th>
<th>Total Fees</th>
<th>Paid</th>
<th>Pending</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php while($college=mysqli_fetch_assoc($college_summary)){ ?>
<tr>

<td><?php echo $college['name']; ?></td>

<td>
₹ <?php echo number_format($college['total_fees'],2); ?>
</td>

<td class="text-success">
₹ <?php echo number_format($college['paid'],2); ?>
</td>

<td class="text-danger fw-bold">
₹ <?php echo number_format($college['pending'],2); ?>
</td>

<td>
<a href="payment_report.php?college=<?php echo $college['id']; ?>"
class="btn btn-primary btn-sm">
View Details
</a>
</td>

</tr>
<?php } ?>

</tbody>
</table>

</div>

<?php } ?>

</div>
</div>
</div>

</body>
</html>