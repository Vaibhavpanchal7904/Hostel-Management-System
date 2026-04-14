<?php
include("../config/config.php");

// Role Check
if(!isset($_SESSION['role']) || $_SESSION['role'] != "superadmin") {
    header("Location: ../login.php");
    exit;
}

$edit_mode = false;
$edit_id = null;

/* =============================
   DELETE ALLOCATION
============================= */
if(isset($_GET['delete'])) {

    $delete_id = intval($_GET['delete']);

    $stmt = mysqli_prepare($conn, "DELETE FROM seat_allocation WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $delete_id);
    mysqli_stmt_execute($stmt);

    header("Location: allocate_seats.php");
    exit;
}

/* =============================
   EDIT MODE
============================= */
if(isset($_GET['edit'])) {

    $edit_id = intval($_GET['edit']);
    $edit_mode = true;

    $edit_query = mysqli_query($conn,
        "SELECT * FROM seat_allocation WHERE id=$edit_id"
    );

    if(mysqli_num_rows($edit_query) > 0){
        $edit_data = mysqli_fetch_assoc($edit_query);
    } else {
        header("Location: allocate_seats.php");
        exit;
    }
}

/* =============================
   FETCH COLLEGES & HOSTELS
============================= */
$colleges = mysqli_query($conn, "SELECT * FROM colleges");
$hostels = mysqli_query($conn, "SELECT * FROM hostels");

/* =============================
   ADD OR UPDATE
============================= */
if(isset($_POST['submit'])) {

    $hostel_id = $_POST['hostel_id'];
    $college_id = $_POST['college_id'];
    $allocated = $_POST['allocated_seats'];
    $ac_seats = $_POST['ac_seats'];
    $non_ac_seats = $_POST['non_ac_seats'];
    $ac_fees = $_POST['ac_fees'];
    $non_ac_fees = $_POST['non_ac_fees'];

    // Validation 1: AC + NON-AC = Total
    if(($ac_seats + $non_ac_seats) != $allocated) {
        $error = "AC + Non-AC seats must equal Total Allocated Seats!";
    } else {

        // Get hostel capacity
        $hostel_data = mysqli_fetch_assoc(
            mysqli_query($conn,
                "SELECT total_seats, maintenance_seats 
                 FROM hostels WHERE id=$hostel_id"
            )
        );

        $available = $hostel_data['total_seats'] - $hostel_data['maintenance_seats'];

        // Sum already allocated seats
        $sum_query = mysqli_query($conn,
            "SELECT SUM(allocated_seats) as total 
             FROM seat_allocation WHERE hostel_id=$hostel_id"
        );

        $already_allocated = mysqli_fetch_assoc($sum_query)['total'] ?? 0;

        // If editing subtract old value
        if($edit_mode){
            $old = mysqli_fetch_assoc(
                mysqli_query($conn,
                    "SELECT allocated_seats 
                     FROM seat_allocation WHERE id=$edit_id"
                )
            );
            $already_allocated -= $old['allocated_seats'];
        }

        $new_total = $already_allocated + $allocated;

        if($new_total > $available){
            $error = "Total allocation exceeds hostel capacity!";
        }
        else {

            if($edit_mode){

                $stmt = mysqli_prepare($conn,
                    "UPDATE seat_allocation 
                     SET hostel_id=?, college_id=?, allocated_seats=?, 
                         ac_seats=?, non_ac_seats=?, ac_fees=?, non_ac_fees=? 
                     WHERE id=?"
                );

                mysqli_stmt_bind_param($stmt, "iiiiiiii",
                    $hostel_id,
                    $college_id,
                    $allocated,
                    $ac_seats,
                    $non_ac_seats,
                    $ac_fees,
                    $non_ac_fees,
                    $edit_id
                );

                mysqli_stmt_execute($stmt);

                $success = "Allocation updated successfully!";

            } else {

                $stmt = mysqli_prepare($conn,
                    "INSERT INTO seat_allocation
                     (hostel_id, college_id, allocated_seats, 
                      ac_seats, non_ac_seats, ac_fees, non_ac_fees)
                     VALUES (?, ?, ?, ?, ?, ?, ?)"
                );

                mysqli_stmt_bind_param($stmt, "iiiiiii",
                    $hostel_id,
                    $college_id,
                    $allocated,
                    $ac_seats,
                    $non_ac_seats,
                    $ac_fees,
                    $non_ac_fees
                );

                mysqli_stmt_execute($stmt);

                $success = "Allocation added successfully!";
            }
        }
    }
}

/* =============================
   FETCH ALLOCATIONS
============================= */
$allocations = mysqli_query($conn, "
    SELECT sa.*, c.name as college_name, h.name as hostel_name
    FROM seat_allocation sa
    JOIN colleges c ON sa.college_id = c.id
    JOIN hostels h ON sa.hostel_id = h.id
    ORDER BY sa.id DESC
");
?>

<!DOCTYPE html>
<html>
<head>

<title>Seat Allocation</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="../css/superadmin.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="superadmin.css">
</head>

<body>

<div class="container-fluid">
<div class="row">

<?php include("sidebar.php"); ?>


<div class="col-md-10 content">

<div class="allocation-card">

<div class="card-header d-flex justify-content-between align-items-center">

<h4><i class="fa fa-chair text-primary"></i>
<?php echo $edit_mode ? "Edit Seat Allocation" : "Seat Allocation"; ?>
</h4>

</div>


<div class="card-body">

<?php if(isset($error)) { ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php } ?>

<?php if(isset($success)) { ?>
<div class="alert alert-success"><?php echo $success; ?></div>
<?php } ?>


<!-- FORM -->

<form method="POST" class="row g-3 mb-4">

<div class="col-md-2">

<label>Hostel</label>

<select name="hostel_id" class="form-select" required>

<option value="">Select</option>

<?php while($h = mysqli_fetch_assoc($hostels)) { ?>

<option value="<?php echo $h['id']; ?>"
<?php if($edit_mode && $edit_data['hostel_id']==$h['id']) echo "selected"; ?>>

<?php echo $h['name']; ?>

</option>

<?php } ?>

</select>

</div>



<div class="col-md-2">

<label>College</label>

<select name="college_id" class="form-select" required>

<option value="">Select</option>

<?php while($c = mysqli_fetch_assoc($colleges)) { ?>

<option value="<?php echo $c['id']; ?>"
<?php if($edit_mode && $edit_data['college_id']==$c['id']) echo "selected"; ?>>

<?php echo $c['name']; ?>

</option>

<?php } ?>

</select>

</div>



<div class="col-md-1">

<label>Total</label>

<input type="number" name="allocated_seats"
class="form-control"

value="<?php echo $edit_mode ? $edit_data['allocated_seats'] : ''; ?>"

required>

</div>


<div class="col-md-1">

<label>AC</label>

<input type="number" name="ac_seats"

class="form-control"

value="<?php echo $edit_mode ? $edit_data['ac_seats'] : ''; ?>"

required>

</div>


<div class="col-md-1">

<label>Non-AC</label>

<input type="number" name="non_ac_seats"

class="form-control"

value="<?php echo $edit_mode ? $edit_data['non_ac_seats'] : ''; ?>"

required>

</div>


<div class="col-md-2">

<label>AC Fees</label>

<input type="number" name="ac_fees"

class="form-control"

value="<?php echo $edit_mode ? $edit_data['ac_fees'] : ''; ?>"

required>

</div>


<div class="col-md-2">

<label>Non-AC Fees</label>

<input type="number" name="non_ac_fees"

class="form-control"

value="<?php echo $edit_mode ? $edit_data['non_ac_fees'] : ''; ?>"

required>

</div>


<div class="col-md-1 d-flex align-items-end">

<button type="submit" name="submit"

class="btn btn-primary w-100">

<?php echo $edit_mode ? "Update" : "Add"; ?>

</button>

</div>

</form>



<!-- TABLE -->

<div class="table-responsive">

<table class="table allocation-table">

<thead>

<tr>

<th>Hostel</th>

<th>College</th>

<th>Total</th>

<th>AC</th>

<th>Non-AC</th>

<th>AC Fees</th>

<th>Non-AC Fees</th>

<th>Action</th>

</tr>

</thead>


<tbody>

<?php while($row = mysqli_fetch_assoc($allocations)) { ?>

<tr>

<td><?php echo $row['hostel_name']; ?></td>

<td><?php echo $row['college_name']; ?></td>

<td><?php echo $row['allocated_seats']; ?></td>

<td><?php echo $row['ac_seats']; ?></td>

<td><?php echo $row['non_ac_seats']; ?></td>

<td>₹<?php echo number_format($row['ac_fees']); ?></td>

<td>₹<?php echo number_format($row['non_ac_fees']); ?></td>


<td>

<a href="allocate_seats.php?edit=<?php echo $row['id']; ?>"

class="btn btn-edit">

Edit

</a>


<a href="allocate_seats.php?delete=<?php echo $row['id']; ?>"

class="btn btn-delete"

onclick="return confirm('Are you sure?')">

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

</div>

</div>

</body>
</html>