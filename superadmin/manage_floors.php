<?php
include("../config/config.php");

// Role Check
if(!isset($_SESSION['role']) || $_SESSION['role'] != "superadmin") {
    header("Location: ../login.php");
    exit;
}

$edit_mode = false;
$edit_data = null;
$error = "";
$success = "";


/* DELETE */
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);

    mysqli_query($conn,"DELETE FROM floors WHERE id=$id");

    if(isset($_GET['hostel'])){
        header("Location: manage_floors.php?hostel=".$_GET['hostel']);
    } else {
        header("Location: manage_floors.php");
    }

    exit;
}


/* EDIT */
if(isset($_GET['edit'])){
    $edit_mode = true;
    $id = intval($_GET['edit']);

    $edit_data = mysqli_fetch_assoc(
        mysqli_query($conn,"
            SELECT * FROM floors
            WHERE id=$id
        ")
    );
}


/* SAVE */
if(isset($_POST['save'])){

    $hostel_id = $_POST['hostel_id'];
    $floor_number = $_POST['floor_number'];

    // Check seat allocation
    $allocation_query = mysqli_query($conn,"
        SELECT SUM(allocated_seats) as total_allocated
        FROM seat_allocation
        WHERE hostel_id='$hostel_id'
    ");

    $allocation_data = mysqli_fetch_assoc($allocation_query);
    $allocated_limit = $allocation_data['total_allocated'] ?? 0;

    // Count existing floors
    $used_query = mysqli_query($conn,"
        SELECT COUNT(*) as total_floors
        FROM floors
        WHERE hostel_id='$hostel_id'
    ");

    $used_data = mysqli_fetch_assoc($used_query);
    $used_floors = $used_data['total_floors'];

    if(isset($_POST['floor_id'])){
        $used_floors--;
    }

    $new_total = $used_floors + 1;

    if($allocated_limit == 0){
        $error = "No seat allocation found for this hostel.";
    }
    elseif($new_total > $allocated_limit){

        $remaining = $allocated_limit - $used_floors;
        $error = "Floor limit exceeded! Only $remaining entries remaining.";

    } else {

        // UPDATE
        if(isset($_POST['floor_id'])){

            $id = $_POST['floor_id'];

            $stmt = mysqli_prepare($conn,"
                UPDATE floors
                SET hostel_id=?, floor_number=?
                WHERE id=?
            ");

            mysqli_stmt_bind_param(
                $stmt,
                "iii",
                $hostel_id,
                $floor_number,
                $id
            );

            mysqli_stmt_execute($stmt);

            $success = "Floor updated successfully!";
        }

        // INSERT
        else{

            $stmt = mysqli_prepare($conn,"
                INSERT INTO floors(hostel_id,floor_number)
                VALUES(?,?)
            ");

            mysqli_stmt_bind_param(
                $stmt,
                "ii",
                $hostel_id,
                $floor_number
            );

            mysqli_stmt_execute($stmt);

            $remaining = $allocated_limit - ($used_floors + 1);

            $success = "Floor added successfully!";
        }
    }
}


/* HOSTELS FOR DROPDOWN */
$hostels = mysqli_query($conn,"
    SELECT DISTINCT h.*
    FROM hostels h
    JOIN seat_allocation sa
    ON h.id = sa.hostel_id
");


/* HOSTEL SUMMARY */
$hostel_summary = mysqli_query($conn,"
    SELECT DISTINCT h.id,h.name
    FROM floors f
    JOIN hostels h
    ON f.hostel_id = h.id
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Floors</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="superadmin.css">
</head>

<body>

<div class="container-fluid">
<div class="row">

<?php include("sidebar.php"); ?>

<div class="col-md-10 content">

<h3 class="mb-3">Manage Floors</h3>
<hr>


<!-- ADD FLOOR FORM -->
<div class="card allocation-card p-4 mb-4">

<?php if($error){ ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php } ?>

<?php if($success){ ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php } ?>

<form method="POST" class="row g-3">

<?php if($edit_mode){ ?>
<input type="hidden" 
       name="floor_id"
       value="<?php echo $edit_data['id']; ?>">
<?php } ?>

<div class="col-md-4">
    <label>Hostel</label>

    <select name="hostel_id" class="form-control" required>
        <option value="">Select</option>

        <?php while($h=mysqli_fetch_assoc($hostels)){ ?>
            <option value="<?php echo $h['id']; ?>"
            <?php if($edit_mode && $edit_data['hostel_id']==$h['id']) echo "selected"; ?>>
                <?php echo $h['name']; ?>
            </option>
        <?php } ?>
    </select>
</div>


<div class="col-md-4">
    <label>Floor Number</label>

    <input type="number"
           name="floor_number"
           class="form-control"
           value="<?php echo $edit_mode ? $edit_data['floor_number'] : ''; ?>"
           required>
</div>


<div class="col-md-2 d-flex align-items-end">
    <button type="submit"
            name="save"
            class="btn btn-primary w-100">

        <?php echo $edit_mode ? "Update" : "Add"; ?>
    </button>
</div>

</form>
</div>


<!-- HOSTEL LIST / FLOOR DETAILS -->
<div class="card allocation-card p-4">

<?php if(isset($_GET['hostel'])){ 

    $hostel_id = intval($_GET['hostel']);

    $floor_details = mysqli_query($conn,"
        SELECT f.*, h.name as hostel_name
        FROM floors f
        JOIN hostels h ON f.hostel_id=h.id
        WHERE f.hostel_id='$hostel_id'
        ORDER BY f.floor_number ASC
    ");
?>

<h4 class="mb-3">Hostel Floor Details</h4>

<a href="manage_floors.php" class="btn btn-secondary mb-3">
    Back
</a>

<table class="table allocation-table">
<thead>
<tr>
    <th>Floor Number</th>
    <th>Action</th>
</tr>
</thead>

<tbody>
<?php while($row=mysqli_fetch_assoc($floor_details)){ ?>
<tr>
    <td>Floor <?php echo $row['floor_number']; ?></td>

    <td>
        <a href="?edit=<?php echo $row['id']; ?>"
           class="btn btn-warning btn-sm">
            Edit
        </a>

        <a href="?delete=<?php echo $row['id']; ?>&hostel=<?php echo $hostel_id; ?>"
           class="btn btn-danger btn-sm"
           onclick="return confirm('Delete floor?')">
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
        <a href="manage_floors.php?hostel=<?php echo $hostel['id']; ?>"
           class="btn btn-primary btn-sm">
            View Floors
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