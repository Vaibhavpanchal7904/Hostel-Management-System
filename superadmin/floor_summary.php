<?php
include("../config/config.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != "superadmin") {
    header("Location: ../login.php");
    exit;
}

/* FILTERS */
$where = "1";

if(isset($_GET['hostel']) && $_GET['hostel'] != ""){
    $hostel = intval($_GET['hostel']);
    $where .= " AND h.id = $hostel";
}

/* QUERY */
$summary=mysqli_query($conn,"
SELECT 
h.id as hostel_id,
h.name as hostel_name,
f.floor_number,
COUNT(r.id) as total_rooms,
COALESCE(SUM(r.total_beds),0) as total_beds,
COALESCE(SUM(r.filled_beds),0) as filled_beds
FROM floors f
JOIN hostels h ON f.hostel_id=h.id
LEFT JOIN rooms r ON r.floor_id=f.id
WHERE $where
GROUP BY f.id
ORDER BY h.name,f.floor_number
");

/* HOSTELS FOR FILTER */
$hostels = mysqli_query($conn,"SELECT * FROM hostels");

/* PROCESS */
$total_rooms=0;
$total_beds=0;
$total_filled=0;

$data=[];
while($row=mysqli_fetch_assoc($summary)){
    $row['available'] = $row['total_beds'] - $row['filled_beds'];

    $total_rooms += $row['total_rooms'];
    $total_beds += $row['total_beds'];
    $total_filled += $row['filled_beds'];

    $data[]=$row;
}

$total_available = $total_beds - $total_filled;
?>

<!DOCTYPE html>
<html>
<head>
<title>Floor Summary</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="superadmin.css">

<style>

/* COLOR CARDS */
.card-blue{
background: linear-gradient(135deg,#dbeafe,#bfdbfe);
}
.card-green{
background: linear-gradient(135deg,#dcfce7,#bbf7d0);
}
.card-yellow{
background: linear-gradient(135deg,#fef9c3,#fde68a);
}
.card-red{
background: linear-gradient(135deg,#fee2e2,#fecaca);
}

/* search */
.search-box{
border-radius:20px;
padding:5px 15px;
}

/* progress bar */
.progress{
height:6px;
}

/* table hover */
.table tbody tr:hover{
background:#f8fafc;
}

</style>

</head>

<body>

<div class="container-fluid">
<div class="row">

<?php include("sidebar.php"); ?>

<div class="col-md-10 content">

<h3>Floor-wise Room Summary</h3>
<hr>

<!-- 🔍 FILTER -->
<form class="row mb-4">

<div class="col-md-3">
<select name="hostel" class="form-control">
<option value="">All Hostels</option>
<?php while($h=mysqli_fetch_assoc($hostels)){ ?>
<option value="<?php echo $h['id']; ?>"
<?php if(isset($_GET['hostel']) && $_GET['hostel']==$h['id']) echo "selected"; ?>>
<?php echo $h['name']; ?>
</option>
<?php } ?>
</select>
</div>

<div class="col-md-3">
<input type="text" id="search" class="form-control search-box" placeholder="Search...">
</div>

<div class="col-md-2">
<button class="btn btn-primary">Filter</button>
</div>

</form>

<!-- 📊 TOP CARDS -->
<div class="row g-3 mb-4">

<div class="col-md-3">
<div class="card card-blue text-center p-3">
<h6>Total Rooms</h6>
<h3><?php echo $total_rooms; ?></h3>
</div>
</div>

<div class="col-md-3">
<div class="card card-green text-center p-3">
<h6>Total Beds</h6>
<h3><?php echo $total_beds; ?></h3>
</div>
</div>

<div class="col-md-3">
<div class="card card-yellow text-center p-3">
<h6>Filled Beds</h6>
<h3><?php echo $total_filled; ?></h3>
</div>
</div>

<div class="col-md-3">
<div class="card card-red text-center p-3">
<h6>Available Beds</h6>
<h3><?php echo $total_available; ?></h3>
</div>
</div>

</div>

<!-- 📋 TABLE -->
<div class="card p-4">

<table class="table" id="table">

<thead>
<tr>
<th>Hostel</th>
<th>Floor</th>
<th>Rooms</th>
<th>Total Beds</th>
<th>Filled</th>
<th>Available</th>
<th>Occupancy</th>
<th>Status</th>
</tr>
</thead>

<tbody>

<?php foreach($data as $row){ 

$percent = $row['total_beds']>0 ? ($row['filled_beds']/$row['total_beds'])*100 : 0;
?>

<tr>
<td><?php echo $row['hostel_name']; ?></td>
<td>Floor <?php echo $row['floor_number']; ?></td>
<td><?php echo $row['total_rooms']; ?></td>
<td><?php echo $row['total_beds']; ?></td>
<td><?php echo $row['filled_beds']; ?></td>
<td><?php echo $row['available']; ?></td>

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

<!-- 🔍 SEARCH SCRIPT -->
<script>
document.getElementById("search").addEventListener("keyup", function() {
let value = this.value.toLowerCase();
let rows = document.querySelectorAll("#table tbody tr");

rows.forEach(row => {
row.style.display = row.innerText.toLowerCase().includes(value) ? "" : "none";
});
});
</script>

</body>
</html>