<?php
include("../config/config.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != "superadmin") {
    header("Location: ../login.php");
    exit;
}

/* ADD */
if(isset($_POST['add'])) {

    $name = trim($_POST['name']);
    $total = intval($_POST['total_seats']);
    $maintenance = intval($_POST['maintenance_seats']);

    $stmt = mysqli_prepare($conn, "
        INSERT INTO hostels (name,total_seats,maintenance_seats) 
        VALUES (?,?,?)
    ");
    mysqli_stmt_bind_param($stmt, "sii", $name, $total, $maintenance);
    mysqli_stmt_execute($stmt);

    $success = "✅ Hostel Added Successfully!";
}

/* DELETE */
if(isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM hostels WHERE id=$id");
    header("Location: manage_hostels.php");
    exit;
}

/* FETCH */
$hostels = mysqli_query($conn, "SELECT * FROM hostels");

/* TOTALS */
$total_hostels = 0;
$total_seats = 0;
$total_maintenance = 0;

$data = [];

while($row = mysqli_fetch_assoc($hostels)){

    $row['available'] = $row['total_seats'] - $row['maintenance_seats'];

    $total_hostels++;
    $total_seats += $row['total_seats'];
    $total_maintenance += $row['maintenance_seats'];

    $data[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Hostels</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="superadmin.css">

<style>
.card-blue{background:linear-gradient(135deg,#dbeafe,#bfdbfe);}
.card-green{background:linear-gradient(135deg,#dcfce7,#bbf7d0);}
.card-yellow{background:linear-gradient(135deg,#fef9c3,#fde68a);}
</style>

</head>

<body>

<div class="container-fluid">
<div class="row">

<?php include("sidebar.php"); ?>

<div class="col-md-10 content">

<h3>🏨 Manage Hostels</h3>
<hr>

<?php if(isset($success)){ ?>
<div class="alert alert-success"><?php echo $success; ?></div>
<?php } ?>

<!-- 📊 CARDS -->
<div class="row g-3 mb-4">

<div class="col-md-4">
<div class="card card-blue text-center p-3">
<h6>Total Hostels</h6>
<h3><?php echo $total_hostels; ?></h3>
</div>
</div>

<div class="col-md-4">
<div class="card card-green text-center p-3">
<h6>Total Seats</h6>
<h3><?php echo $total_seats; ?></h3>
</div>
</div>

<div class="col-md-4">
<div class="card card-yellow text-center p-3">
<h6>Maintenance Seats</h6>
<h3><?php echo $total_maintenance; ?></h3>
</div>
</div>

</div>

<!-- 🔵 FORM -->
<div class="card p-4 mb-4">

<h5>Add New Hostel</h5>

<form method="POST" class="row g-3">

<div class="col-md-3">
<input type="text" name="name" class="form-control" placeholder="Hostel Name" required>
</div>

<div class="col-md-3">
<input type="number" name="total_seats" class="form-control" placeholder="Total Seats" required>
</div>

<div class="col-md-3">
<input type="number" name="maintenance_seats" class="form-control" placeholder="Maintenance Seats" required>
</div>

<div class="col-md-2">
<button type="submit" name="add" class="btn btn-primary w-100">
Add
</button>
</div>

</form>

</div>

<!-- 🔍 SEARCH -->
<input type="text" id="search" class="form-control mb-3" placeholder="Search hostel...">

<!-- 📋 TABLE -->
<div class="card p-3">

<table class="table" id="hostelTable">

<thead>
<tr>
<th>ID</th>
<th>Name</th>
<th>Total</th>
<th>Maintenance</th>
<th>Available</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php foreach($data as $row){ ?>

<tr>
<td><?php echo $row['id']; ?></td>
<td><?php echo $row['name']; ?></td>
<td><?php echo $row['total_seats']; ?></td>
<td><?php echo $row['maintenance_seats']; ?></td>
<td><?php echo $row['available']; ?></td>

<td>
<?php 
$percent = ($row['maintenance_seats'] / $row['total_seats']) * 100;

if($percent > 50){
echo '<span class="badge bg-danger">High Maintenance</span>';
}else{
echo '<span class="badge bg-success">Active</span>';
}
?>
</td>

<td>
<a href="?delete=<?php echo $row['id']; ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Delete hostel?')">
Delete
</a>
</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>
</div>
</div>

<!-- SEARCH -->
<script>
document.getElementById("search").addEventListener("keyup", function(){
let val = this.value.toLowerCase();
document.querySelectorAll("#hostelTable tbody tr").forEach(row=>{
row.style.display = row.innerText.toLowerCase().includes(val) ? "" : "none";
});
});
</script>

</body>
</html>