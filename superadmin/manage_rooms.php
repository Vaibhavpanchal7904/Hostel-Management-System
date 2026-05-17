<?php
include("../config/config.php");

if(!isset($_SESSION['role']) || $_SESSION['role']!="superadmin"){
    header("Location: ../login.php");
    exit;
}

$error="";
$success="";

/* DELETE */
if(isset($_GET['delete'])){
    $id=intval($_GET['delete']);

    mysqli_query($conn,"DELETE FROM rooms WHERE id=$id");

    if(isset($_GET['hostel'])){
        header("Location: manage_rooms.php?hostel=".$_GET['hostel']);
    }else{
        header("Location: manage_rooms.php");
    }
    
    exit;
}


/* ADD ROOM */
if(isset($_POST['save'])){

    $hostel_id=$_POST['hostel_id'];
    $floor_id=$_POST['floor_id'];
    $room_number=$_POST['room_number'];
    $room_type=$_POST['room_type'];
    $total_beds=$_POST['total_beds'];

    /* Check allocated seats */
    $allocation_query=mysqli_query($conn,"
        SELECT SUM(allocated_seats) as total_allocated
        FROM seat_allocation
        WHERE hostel_id='$hostel_id'
    ");

    $allocation_data=mysqli_fetch_assoc($allocation_query);
    $allocated_limit=$allocation_data['total_allocated'] ?? 0;

    /* Used beds */
    $used_query=mysqli_query($conn,"
        SELECT SUM(total_beds) as total_used
        FROM rooms
        WHERE hostel_id='$hostel_id'
    ");

    $used_data=mysqli_fetch_assoc($used_query);
    $used_beds=$used_data['total_used'] ?? 0;

    $new_total=$used_beds+$total_beds;

    if($allocated_limit==0){
        $error="No seat allocation found for this hostel.";
    }
    elseif($new_total>$allocated_limit){

        $remaining=$allocated_limit-$used_beds;
        $error="Seat limit exceeded! Only $remaining beds remaining.";
    }
    else{

        $stmt=mysqli_prepare($conn,"
            INSERT INTO rooms
            (hostel_id,floor_id,room_number,room_type,total_beds)
            VALUES (?,?,?,?,?)
        ");

        mysqli_stmt_bind_param(
            $stmt,
            "iissi",
            $hostel_id,
            $floor_id,
            $room_number,
            $room_type,
            $total_beds
        );

        mysqli_stmt_execute($stmt);

        $remaining=$allocated_limit-($used_beds+$total_beds);

        $success="Room added successfully! Remaining seats: $remaining";
    }
}


/* Only allocated hostels */
$hostels=mysqli_query($conn,"
    SELECT DISTINCT h.*
    FROM hostels h
    JOIN seat_allocation sa
    ON h.id=sa.hostel_id
");


/* Floors based on selected hostel */
$selected_hostel = isset($_GET['hostel_id']) ? $_GET['hostel_id'] : "";

$floors = null;

if($selected_hostel != ""){
    $floors = mysqli_query($conn,"
        SELECT *
        FROM floors
        WHERE hostel_id='$selected_hostel'
        ORDER BY floor_number ASC
    ");
}


/* Hostel Summary */
$hostel_summary=mysqli_query($conn,"
    SELECT DISTINCT h.id,h.name
    FROM rooms r
    JOIN hostels h ON r.hostel_id=h.id
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

<?php include("sidebar.php"); ?>

<div class="col-md-10 content">

<h3 class="mb-3">Manage Rooms</h3>
<hr>


<!-- ADD ROOM FORM -->
<div class="card allocation-card p-4 mb-4">

<?php if($error){ ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php } ?>

<?php if($success){ ?>
<div class="alert alert-success"><?php echo $success; ?></div>
<?php } ?>

<form method="POST" class="row g-3">

<div class="col-md-2">
<label>Hostel</label>
<select name="hostel_id" class="form-control" required 
        onchange="window.location='manage_rooms.php?hostel_id='+this.value">

<option value="">Select</option>

<?php while($h=mysqli_fetch_assoc($hostels)){ ?>
<option value="<?php echo $h['id']; ?>"
    <?php if($selected_hostel == $h['id']) echo "selected"; ?>>
    
    <?php echo $h['name']; ?>
</option>
<?php } ?>

</select>
</div>


<div class="col-md-2">
<label>Floor</label>
<select name="floor_id" class="form-control" required>

<option value="">Select Floor</option>

<?php 
if($floors){
    while($f=mysqli_fetch_assoc($floors)){ 
?>
    <option value="<?php echo $f['id']; ?>">
        Floor <?php echo $f['floor_number']; ?>
    </option>
<?php 
    }
}
?>

</select>
</div>


<div class="col-md-2">
<label>Room No</label>
<input type="text"
name="room_number"
class="form-control"
required>
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
<input type="number"
name="total_beds"
class="form-control"
required>
</div>


<div class="col-md-2 d-flex align-items-end">
<button type="submit"
name="save"
class="btn btn-primary w-100">
Add
</button>
</div>

</form>

</div>



<!-- HOSTEL LIST / DETAILS -->
<div class="card allocation-card p-4">

<?php if(isset($_GET['hostel'])){ 

    $hostel_id=intval($_GET['hostel']);

    $room_details=mysqli_query($conn,"
        SELECT r.*, h.name as hostel_name, f.floor_number
        FROM rooms r
        JOIN hostels h ON r.hostel_id=h.id
        JOIN floors f ON r.floor_id=f.id
        WHERE r.hostel_id='$hostel_id'
        ORDER BY f.floor_number,r.room_number
    ");

?>

<h4 class="mb-3">Hostel Room Details</h4>

<a href="manage_rooms.php" class="btn btn-secondary mb-3">
Back
</a>

<table class="table allocation-table">

<thead>
<tr>
<th>Floor</th>
<th>Room</th>
<th>Type</th>
<th>Total Beds</th>
<th>Filled</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php while($row=mysqli_fetch_assoc($room_details)){ ?>
<tr>

<td><?php echo $row['floor_number']; ?></td>
<td><?php echo $row['room_number']; ?></td>
<td><?php echo $row['room_type']; ?></td>
<td><?php echo $row['total_beds']; ?></td>
<td><?php echo $row['filled_beds']; ?></td>

<td>
<a href="?delete=<?php echo $row['id']; ?>&hostel=<?php echo $hostel_id; ?>"
class="btn btn-delete"
onclick="return confirm('Delete room?')">
Delete
</a>
</td>

</tr>
<?php } ?>

</tbody>
</table>

<?php } else { ?>

<h4 class="mb-3">Hostel List</h4>

<table class="table allocation-table">

<thead>
<tr>
<th>Hostel Name</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php while($hostel=mysqli_fetch_assoc($hostel_summary)){ ?>
<tr>

<td><?php echo $hostel['name']; ?></td>

<td>
<a href="manage_rooms.php?hostel=<?php echo $hostel['id']; ?>"
class="btn btn-primary">
View Details
</a>
</td>

</tr>
<?php } ?>

</tbody>

</table>

<?php } ?>

</div>

</div>
</div>
</div>

</body>
</html>