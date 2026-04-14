<?php
include("../config/config.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != "superadmin") {
    header("Location: ../login.php");
    exit;
}

$limit = 5;

$college_id = isset($_GET['college']) ? intval($_GET['college']) : 0;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if($page < 1) $page = 1;

$start = ($page - 1) * $limit;

$colleges = mysqli_query($conn, "SELECT * FROM colleges");

$where = "";
if($college_id > 0){
    $where = "WHERE s.college_id = $college_id";
}

/* MAIN DATA */
$query = "
SELECT 
    s.name as student_name,
    c.name as college_name,
    h.name as hostel_name,
    s.total_fees,
    s.paid_amount,
    (s.total_fees - s.paid_amount) as pending
FROM students s
JOIN colleges c ON s.college_id = c.id
JOIN hostels h ON s.hostel_id = h.id
$where
ORDER BY s.id DESC
LIMIT $start, $limit
";

$report = mysqli_query($conn, $query);

/* COUNT */
$count_query = mysqli_query($conn,"
SELECT COUNT(*) as total FROM students s $where
");
$total_rows = mysqli_fetch_assoc($count_query)['total'];
$total_pages = ceil($total_rows / $limit);

/* TOTALS */
$total_query = mysqli_query($conn,"
SELECT 
SUM(total_fees) as total_fees,
SUM(paid_amount) as paid,
SUM(total_fees - paid_amount) as pending
FROM students
");
$totals = mysqli_fetch_assoc($total_query);
?>

<!DOCTYPE html>
<html>
<head>
<title>Payment Pending Report</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="superadmin.css">

<style>
.card-blue{background:linear-gradient(135deg,#dbeafe,#bfdbfe);}
.card-green{background:linear-gradient(135deg,#dcfce7,#bbf7d0);}
.card-red{background:linear-gradient(135deg,#fee2e2,#fecaca);}
.progress{height:6px;}
</style>

</head>

<body>

<div class="container-fluid">
<div class="row">

<?php include("sidebar.php"); ?>

<div class="col-md-10 content">

<h3>Payment Pending Report</h3>
<hr>

<!-- 🔍 FILTER -->
<form method="GET" class="row mb-4">

<div class="col-md-3">
<select name="college" class="form-control">
<option value="">All Colleges</option>
<?php while($c = mysqli_fetch_assoc($colleges)){ ?>
<option value="<?php echo $c['id']; ?>"
<?php if($college_id == $c['id']) echo "selected"; ?>>
<?php echo $c['name']; ?>
</option>
<?php } ?>
</select>
</div>

<div class="col-md-2">
<button class="btn btn-primary w-100">Filter</button>
</div>

</form>

<!-- 📊 CARDS -->
<div class="row g-3 mb-4">

<div class="col-md-4">
<div class="card card-blue text-center p-3">
<h6>Total Fees</h6>
<h3>₹ <?php echo number_format($totals['total_fees'],2); ?></h3>
</div>
</div>

<div class="col-md-4">
<div class="card card-green text-center p-3">
<h6>Paid</h6>
<h3>₹ <?php echo number_format($totals['paid'],2); ?></h3>
</div>
</div>

<div class="col-md-4">
<div class="card card-red text-center p-3">
<h6>Pending</h6>
<h3>₹ <?php echo number_format($totals['pending'],2); ?></h3>
</div>
</div>

</div>

<!-- 📋 TABLE -->
<div class="card p-4">

<table class="table">

<thead>
<tr>
<th>Student</th>
<th>College</th>
<th>Hostel</th>
<th>Total</th>
<th>Paid</th>
<th>Pending</th>
<th>Progress</th>
</tr>
</thead>

<tbody>

<?php
if($report && mysqli_num_rows($report) > 0){
while($row = mysqli_fetch_assoc($report)){

$percent = ($row['total_fees'] > 0) 
? ($row['paid_amount'] / $row['total_fees']) * 100 : 0;
?>

<tr>
<td><?php echo htmlspecialchars($row['student_name']); ?></td>
<td><?php echo htmlspecialchars($row['college_name']); ?></td>
<td><?php echo htmlspecialchars($row['hostel_name']); ?></td>

<td>₹ <?php echo number_format($row['total_fees'],2); ?></td>
<td class="text-success">₹ <?php echo number_format($row['paid_amount'],2); ?></td>

<td class="text-danger fw-bold">
₹ <?php echo number_format($row['pending'],2); ?>
</td>

<td>
<div class="progress">
<div class="progress-bar bg-success" style="width:<?php echo $percent; ?>%"></div>
</div>
<small><?php echo round($percent); ?>%</small>
</td>

</tr>

<?php } } else { ?>
<tr>
<td colspan="7" class="text-center">No Records Found</td>
</tr>
<?php } ?>

</tbody>
</table>

</div>

<!-- PAGINATION -->
<?php if($total_pages > 1){ ?>
<nav class="mt-3">
<ul class="pagination justify-content-center">
<?php for($i=1; $i <= $total_pages; $i++){ ?>
<li class="page-item <?php if($i==$page) echo 'active'; ?>">
<a class="page-link"
href="?college=<?php echo $college_id; ?>&page=<?php echo $i; ?>">
<?php echo $i; ?>
</a>
</li>
<?php } ?>
</ul>
</nav>
<?php } ?>

</div>
</div>
</div>

</body>
</html>