<?php
include("../config/config.php");

if(!isset($_SESSION['role']) || $_SESSION['role']!="superadmin"){
    header("Location: ../login.php");
    exit;
}

/* DELETE */
if(isset($_GET['delete'])){
    $id=intval($_GET['delete']);
    mysqli_query($conn,"DELETE FROM rooms WHERE id=$id");
    header("Location: manage_rooms.php");
    exit;
}

/* ADD */
if(isset($_POST['save'])){

    $hostel_id=$_POST['hostel_id'];
    $floor_id=$_POST['floor_id'];
    $room_number=$_POST['room_number'];
    $room_type=$_POST['room_type'];
    $total_beds=$_POST['total_beds'];

    $stmt=mysqli_prepare($conn,
        "INSERT INTO rooms 
        (hostel_id,floor_id,room_number,room_type,total_beds)
        VALUES (?,?,?,?,?)");

    mysqli_stmt_bind_param($stmt,"iissi",
        $hostel_id,$floor_id,$room_number,$room_type,$total_beds);

    mysqli_stmt_execute($stmt);

    header("Location: manage_rooms.php");
    exit;
}

$hostels=mysqli_query($conn,"SELECT * FROM hostels");
$floors=mysqli_query($conn,"SELECT * FROM floors");

$rooms=mysqli_query($conn,"
SELECT r.*, h.name as hostel_name, f.floor_number
FROM rooms r
JOIN hostels h ON r.hostel_id=h.id
JOIN floors f ON r.floor_id=f.id
ORDER BY h.name,f.floor_number,r.room_number
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Rooms</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="superadmin.css">

</head>

<body>

<div class="container-fluid">
<div class="row">

<!-- ✅ SIDEBAR -->
<?php include("sidebar.php"); ?>

<!-- ✅ MAIN CONTENT -->
<div class="col-md-10 content">

<h3 class="mb-3">Manage Rooms</h3>
<hr>

<!-- ===== FORM CARD ===== -->
<div class="card allocation-card p-4 mb-4">

<form method="POST" class="row g-3">

<div class="col-md-2">
<label>Hostel</label>
<select name="hostel_id" class="form-control" required>
<option value="">Select</option>
<?php while($h=mysqli_fetch_assoc($hostels)){ ?>
<option value="<?php echo $h['id']; ?>"><?php echo $h['name']; ?></option>
<?php } ?>
</select>
</div>

<div class="col-md-2">
<label>Floor</label>
<select name="floor_id" class="form-control" required>
<option value="">Select</option>
<?php while($f=mysqli_fetch_assoc($floors)){ ?>
<option value="<?php echo $f['id']; ?>">
Floor <?php echo $f['floor_number']; ?>
</option>
<?php } ?>
</select>
</div>

<div class="col-md-2">
<label>Room No</label>
<input type="text" name="room_number" class="form-control" required>
</div>

<div class="col-md-2">
<label>Type</label>
<select name="room_type" class="form-control">
<option value="AC">AC</option>
<option value="NON-AC">NON-AC</option>
</select>
</div>

<div class="col-md-2">
<label>Total Beds</label>
<input type="number" name="total_beds" class="form-control" required>
</div>

<div class="col-md-2 d-flex align-items-end">
<button type="submit" name="save" class="btn btn-primary w-100">
Add
</button>
</div>

</form>

</div>

<!-- ===== TABLE CARD ===== -->
<div class="card allocation-card p-4">

<table class="table allocation-table">

<thead>
<tr>
<th>Hostel</th>
<th>Floor</th>
<th>Room</th>
<th>Type</th>
<th>Total Beds</th>
<th>Filled</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php while($row=mysqli_fetch_assoc($rooms)){ ?>
<tr>
<td><?php echo $row['hostel_name']; ?></td>
<td><?php echo $row['floor_number']; ?></td>
<td><?php echo $row['room_number']; ?></td>
<td><?php echo $row['room_type']; ?></td>
<td><?php echo $row['total_beds']; ?></td>
<td><?php echo $row['filled_beds']; ?></td>
<td>
<a href="?delete=<?php echo $row['id']; ?>"
class="btn btn-delete"
onclick="return confirm('Delete room?')">
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

</body>
</html>