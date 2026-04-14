<?php
include("../config/config.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != "superadmin") {
    header("Location: ../login.php");
    exit;
}

/* FILTER */
$college_id = isset($_GET['college']) ? intval($_GET['college']) : 0;

$where = "";
if($college_id > 0){
    $where = "WHERE college_id = $college_id";
}

/* 🚀 SINGLE OPTIMIZED QUERY */
$data = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT 
COUNT(CASE WHEN status='reserved' THEN 1 END) as reserved_total,
COUNT(CASE WHEN status='allotted' THEN 1 END) as allotted_total,

COUNT(CASE WHEN status='reserved' AND room_type='AC' THEN 1 END) as ac_reserved,
COUNT(CASE WHEN status='allotted' AND room_type='AC' THEN 1 END) as ac_allotted,

COUNT(CASE WHEN status='reserved' AND room_type='NON-AC' THEN 1 END) as nonac_reserved,
COUNT(CASE WHEN status='allotted' AND room_type='NON-AC' THEN 1 END) as nonac_allotted

FROM students
$where
"));

extract($data);

/* COLLEGES */
$colleges = mysqli_query($conn,"SELECT * FROM colleges");

/* TOTAL */
$total_students = $reserved_total + $allotted_total;
?>

<!DOCTYPE html>
<html>
<head>
<title>Hostel Analysis</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="superadmin.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
.card-blue{background:linear-gradient(135deg,#dbeafe,#bfdbfe);}
.card-green{background:linear-gradient(135deg,#dcfce7,#bbf7d0);}
.card-yellow{background:linear-gradient(135deg,#fef9c3,#fde68a);}
.card-purple{background:linear-gradient(135deg,#ede9fe,#ddd6fe);}
</style>

</head>

<body>

<div class="container-fluid">
<div class="row">

<?php include("sidebar.php"); ?>

<div class="col-md-10 content">

<h3>📊 Hostel Student Analysis</h3>
<hr>

<!-- FILTER -->
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

<div class="col-md-3">
<div class="card card-yellow text-center p-3">
<h6>Reserved</h6>
<h3><?php echo $reserved_total; ?></h3>
</div>
</div>

<div class="col-md-3">
<div class="card card-green text-center p-3">
<h6>Allotted</h6>
<h3><?php echo $allotted_total; ?></h3>
</div>
</div>

<div class="col-md-3">
<div class="card card-blue text-center p-3">
<h6>Total Students</h6>
<h3><?php echo $total_students; ?></h3>
</div>
</div>

<div class="col-md-3">
<div class="card card-purple text-center p-3">
<h6>Allocation Rate</h6>
<h3>
<?php 
echo $total_students > 0 
? round(($allotted_total/$total_students)*100) . "%" 
: "0%";
?>
</h3>
</div>
</div>

</div>

<!-- 📊 ROOM TYPE CARDS -->
<div class="row g-3 mb-4">

<div class="col-md-6">
<div class="card p-3">
<h6>AC Rooms</h6>
<p>Reserved: <?php echo $ac_reserved; ?></p>
<p>Allotted: <?php echo $ac_allotted; ?></p>
</div>
</div>

<div class="col-md-6">
<div class="card p-3">
<h6>Non-AC Rooms</h6>
<p>Reserved: <?php echo $nonac_reserved; ?></p>
<p>Allotted: <?php echo $nonac_allotted; ?></p>
</div>
</div>

</div>

<!-- 📊 CHARTS -->
<div class="row">

<div class="col-md-6">
<div class="card p-3">
<canvas id="barChart"></canvas>
</div>
</div>

<div class="col-md-6">
<div class="card p-3">
<canvas id="pieChart"></canvas>
</div>
</div>

</div>

</div>
</div>
</div>

<script>

/* BAR CHART */
new Chart(document.getElementById('barChart'), {
type: 'bar',
data: {
labels: ['AC Reserved','AC Allotted','Non-AC Reserved','Non-AC Allotted'],
datasets: [{
data: [
<?php echo $ac_reserved; ?>,
<?php echo $ac_allotted; ?>,
<?php echo $nonac_reserved; ?>,
<?php echo $nonac_allotted; ?>
],
backgroundColor: ['#fbbf24','#22c55e','#38bdf8','#a78bfa']
}]
},
options: {plugins:{legend:{display:false}}}
});

/* PIE CHART */
new Chart(document.getElementById('pieChart'), {
type: 'doughnut',
data: {
labels: ['Reserved','Allotted'],
datasets: [{
data: [
<?php echo $reserved_total; ?>,
<?php echo $allotted_total; ?>
],
backgroundColor: ['#facc15','#4ade80']
}]
}
});

</script>

</body>
</html>