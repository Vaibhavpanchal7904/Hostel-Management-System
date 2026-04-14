<?php

include("../config/config.php");

// Role Check
if(!isset($_SESSION['role']) || $_SESSION['role'] != "superadmin") {
    header("Location: ../login.php");
    exit;
}

$edit_mode = false;
$edit_data = null;

/* DELETE */
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    mysqli_query($conn,"DELETE FROM floors WHERE id=$id");
    header("Location: manage_floors.php");
    exit;
}

/* EDIT */
if(isset($_GET['edit'])){
    $edit_mode = true;
    $id = intval($_GET['edit']);
    $edit_data = mysqli_fetch_assoc(
        mysqli_query($conn,"SELECT * FROM floors WHERE id=$id")
    );
}

/* SAVE */
if(isset($_POST['save'])){
    $hostel_id = $_POST['hostel_id'];
    $floor_number = $_POST['floor_number'];

    if(isset($_POST['floor_id'])){
        $id = $_POST['floor_id'];
        $stmt = mysqli_prepare($conn,
            "UPDATE floors SET hostel_id=?, floor_number=? WHERE id=?");
        mysqli_stmt_bind_param($stmt,"iii",
            $hostel_id,$floor_number,$id);
        mysqli_stmt_execute($stmt);
    } else {
        $stmt = mysqli_prepare($conn,
            "INSERT INTO floors (hostel_id,floor_number) VALUES (?,?)");
        mysqli_stmt_bind_param($stmt,"ii",
            $hostel_id,$floor_number);
        mysqli_stmt_execute($stmt);
    }

    header("Location: manage_floors.php");
    exit;
}

$hostels = mysqli_query($conn,"SELECT * FROM hostels");

$floors = mysqli_query($conn,"
    SELECT f.*, h.name as hostel_name
    FROM floors f
    JOIN hostels h ON f.hostel_id=h.id
    ORDER BY h.name,f.floor_number
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

<!-- ✅ SIDEBAR -->
<?php include("sidebar.php"); ?>

<!-- ✅ MAIN CONTENT -->
<div class="col-md-10 content">

<h3 class="mb-3">Manage Floors</h3>
<hr>

<!-- FORM -->
<div class="card allocation-card p-4 mb-4">

<form method="POST" class="row g-3">

<?php if($edit_mode){ ?>
<input type="hidden" name="floor_id" value="<?php echo $edit_data['id']; ?>">
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
<input type="number" name="floor_number"
class="form-control"
value="<?php echo $edit_mode ? $edit_data['floor_number'] : ''; ?>"
required>
</div>

<div class="col-md-2 d-flex align-items-end">
<button type="submit" name="save" class="btn btn-primary w-100">
<?php echo $edit_mode ? "Update" : "Add"; ?>
</button>
</div>

</form>

</div>

<!-- TABLE -->
<div class="card allocation-card p-4">

<table class="table allocation-table">

<thead>
<tr>
<th>Hostel</th>
<th>Floor</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php while($row=mysqli_fetch_assoc($floors)){ ?>
<tr>
<td><?php echo $row['hostel_name']; ?></td>
<td><?php echo $row['floor_number']; ?></td>
<td>
<a href="?edit=<?php echo $row['id']; ?>" class="btn btn-edit">Edit</a>
<a href="?delete=<?php echo $row['id']; ?>" class="btn btn-delete"
onclick="return confirm('Delete floor?')">Delete</a>
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