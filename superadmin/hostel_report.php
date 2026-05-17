<?php
include("../config/config.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != "superadmin") {
    header("Location: ../login.php");
    exit;
}

/* FILTER */
$college_filter = $_GET['college_id'] ?? '';
$hostel_filter  = $_GET['hostel_id'] ?? '';

$where = [];

if($college_filter != ''){
    $where[] = "sa.college_id = $college_filter";
}

if($hostel_filter != ''){
    $where[] = "sa.hostel_id = $hostel_filter";
}

$where_sql = count($where) ? "WHERE " . implode(" AND ", $where) : "";

/* DATA */
$colleges = mysqli_query($conn,"SELECT * FROM colleges");
$hostels  = mysqli_query($conn,"SELECT * FROM hostels");

/* 🚀 OPTIMIZED QUERY (NO LOOP QUERY) */
$query = "
SELECT 
    h.name AS hostel_name,
    c.name AS college_name,
    sa.allocated_seats,
    sa.hostel_id,
    sa.college_id,
    COUNT(s.id) AS admitted
FROM seat_allocation sa
JOIN hostels h ON sa.hostel_id = h.id
JOIN colleges c ON sa.college_id = c.id
LEFT JOIN students s 
    ON s.hostel_id = sa.hostel_id 
    AND s.college_id = sa.college_id
$where_sql
GROUP BY sa.hostel_id, sa.college_id
";

$result = mysqli_query($conn,$query);

/* TOTALS */
$total_allocated = 0;
$total_admitted  = 0;
$total_vacant    = 0;

$data=[];

while($row = mysqli_fetch_assoc($result)){

    $row['vacant'] = $row['allocated_seats'] - $row['admitted'];

    $total_allocated += $row['allocated_seats'];
    $total_admitted  += $row['admitted'];
    $total_vacant    += $row['vacant'];

    $data[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Hostel Report</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="superadmin.css">

<style>
.card-blue{background:linear-gradient(135deg,#dbeafe,#bfdbfe);}
.card-green{background:linear-gradient(135deg,#dcfce7,#bbf7d0);}
.card-yellow{background:linear-gradient(135deg,#fef9c3,#fde68a);}
.progress{height:6px;}
</style>

</head>

<body>

<div class="container-fluid">
<div class="row">

<?php include("sidebar.php"); ?>

<div class="col-md-10 content">

<h3>Hostel Report</h3>
<hr>

<!-- 🔍 FILTER -->
<form method="GET" class="row mb-4">

<div class="col-md-3">
<label>College</label>
<select name="college_id" class="form-select">
<option value="">All Colleges</option>
<?php while($c = mysqli_fetch_assoc($colleges)){ ?>
<option value="<?php echo $c['id']; ?>"
<?php if($college_filter==$c['id']) echo "selected"; ?>>
<?php echo $c['name']; ?>
</option>
<?php } ?>
</select>
</div>

<div class="col-md-3">
<label>Hostel</label>
<select name="hostel_id" class="form-select">
<option value="">All Hostels</option>
<?php while($h = mysqli_fetch_assoc($hostels)){ ?>
<option value="<?php echo $h['id']; ?>"
<?php if($hostel_filter==$h['id']) echo "selected"; ?>>
<?php echo $h['name']; ?>
</option>
<?php } ?>
</select>
</div>

<div class="col-md-2 mt-4">
<button class="btn btn-primary w-100">Filter</button>
</div>

<div class="col-md-3 mt-4">
<a href="download_report.php?college_id=<?php echo $college_filter ?>&hostel_id=<?php echo $hostel_filter ?>" 
class="btn btn-danger w-100">
Download PDF
</a>
</div>

</form>

<!-- 📊 CARDS -->
<div class="row g-3 mb-4">

<div class="col-md-4">
<div class="card card-blue text-center p-3">
<h6>Total Allocated</h6>
<h3><?php echo $total_allocated; ?></h3>
</div>
</div>

<div class="col-md-4">
<div class="card card-green text-center p-3">
<h6>Admitted</h6>
<h3><?php echo $total_admitted; ?></h3>
</div>
</div>

<div class="col-md-4">
<div class="card card-yellow text-center p-3">
<h6>Vacant</h6>
<h3><?php echo $total_vacant; ?></h3>
</div>
</div>

</div>

<!-- 📋 TABLE -->
<div class="card p-4">

<table class="table">

<thead>
<tr>
<th>Hostel</th>
<th>College</th>
<th>Allocated</th>
<th>Admitted</th>
<th>Vacant</th>
<th>Occupancy</th>
<th>Status</th>
</tr>
</thead>

<tbody>

<?php foreach($data as $row){

$percent = $row['allocated_seats']>0 
? ($row['admitted']/$row['allocated_seats'])*100 : 0;
?>

<tr>
<td><?php echo $row['hostel_name']; ?></td>
<td>
<a href="college_students.php?college_id=<?php echo $row['college_id']; ?>" 
class="text-decoration-none fw-bold text-primary">
<?php echo $row['college_name']; ?>
</a>
</td>
<td><?php echo $row['allocated_seats']; ?></td>
<td><?php echo $row['admitted']; ?></td>
<td><?php echo $row['vacant']; ?></td>

<td>
<div class="progress">
<div class="progress-bar bg-success" style="width:<?php echo $percent; ?>%"></div>
</div>
<small><?php echo round($percent); ?>%</small>
</td>

<td>
<?php 
if($percent==100){
echo '<span class="badge bg-danger">Full</span>';
}elseif($percent>70){
echo '<span class="badge bg-warning text-dark">Almost Full</span>';
}else{
echo '<span class="badge bg-success">Available</span>';
}
?>
</td>

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