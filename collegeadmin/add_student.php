<?php
include("../config/config.php");

// Role check
if(!isset($_SESSION['role']) || $_SESSION['role'] != "collegeadmin") {
    header("Location: ../login.php");
    exit;
}

$college_id = $_SESSION['college_id'];
$edit_mode = false;
$student_id = null;

/* =============================
   EDIT MODE
============================= */
if(isset($_GET['edit'])) {

    $student_id = intval($_GET['edit']);
    $edit_mode = true;

    $edit_query = mysqli_query($conn, "
        SELECT * FROM students 
        WHERE id=$student_id AND college_id=$college_id
    ");

    if(mysqli_num_rows($edit_query) > 0){
        $edit_data = mysqli_fetch_assoc($edit_query);
    } else {
        header("Location: view_students.php");
        exit;
    }
}

/* =============================
   FETCH HOSTELS
============================= */
$hostel_query = mysqli_query($conn, "
    SELECT sa.*, h.name 
    FROM seat_allocation sa
    JOIN hostels h ON sa.hostel_id = h.id
    WHERE sa.college_id = $college_id
");

/* =============================
   ADD OR UPDATE STUDENT
============================= */
if(isset($_POST['submit'])) {

    $name = $_POST['name'];
    $hostel_id = $_POST['hostel_id'];
    $room_type = $_POST['room_type'];
    $payment_type = $_POST['payment_type'];
    $custom_amount = $_POST['custom_amount'];

    // Allocation check
    $alloc_query = mysqli_query($conn, "
        SELECT * FROM seat_allocation 
        WHERE college_id=$college_id 
        AND hostel_id=$hostel_id
    ");
    $alloc_data = mysqli_fetch_assoc($alloc_query);

    if(!$alloc_data){
        $error = "Invalid hostel selection!";
    } else {

        $allocated = $alloc_data['allocated_seats'];
        $ac_fees = $alloc_data['ac_fees'];
        $non_ac_fees = $alloc_data['non_ac_fees'];

        // Seat validation only when adding new student
        if(!$edit_mode){
            $count_query = mysqli_query($conn, "
                SELECT COUNT(*) as total 
                FROM students 
                WHERE college_id=$college_id 
                AND hostel_id=$hostel_id
            ");
            $admitted = mysqli_fetch_assoc($count_query)['total'];

            if($admitted >= $allocated){
                $error = "Seat limit reached!";
            }
        }

        if(!isset($error)){

            // Fee calculation
            $total_fees = ($room_type == "AC") ? $ac_fees : $non_ac_fees;

            if($payment_type == "full"){
                $paid_amount = $total_fees;
            }
            elseif($payment_type == "half"){
                $paid_amount = $total_fees / 2;
            }
            else{
                $paid_amount = $custom_amount;
            }

            if($edit_mode){

                $stmt = mysqli_prepare($conn, "
                    UPDATE students 
                    SET name=?, hostel_id=?, room_type=?, total_fees=?, paid_amount=? 
                    WHERE id=? AND college_id=?
                ");

                mysqli_stmt_bind_param($stmt, "sisddii",
                    $name,
                    $hostel_id,
                    $room_type,
                    $total_fees,
                    $paid_amount,
                    $student_id,
                    $college_id
                );

                mysqli_stmt_execute($stmt);

                $success = "Student updated successfully!";

            } else {

                $stmt = mysqli_prepare($conn, "
                    INSERT INTO students 
                    (name, college_id, hostel_id, room_type, total_fees, paid_amount, status)
                    VALUES (?, ?, ?, ?, ?, ?, 'reserved')
                ");

                mysqli_stmt_bind_param($stmt, "siisdd",
                    $name,
                    $college_id,
                    $hostel_id,
                    $room_type,
                    $total_fees,
                    $paid_amount
                );

                mysqli_stmt_execute($stmt);

                $success = "Student added successfully!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $edit_mode ? "Edit Student" : "Add Student"; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/collegeadmin.css">
</head>
<body>

<!-- ================= TOPBAR ================= -->

<div class="topbar d-flex justify-content-between align-items-center">

<div class="d-flex align-items-center gap-3">
<h5 class="mb-0"><?php echo $edit_mode ? "Edit Student" : "Add Student"; ?></h5>
<input type="text" class="form-control search-box" placeholder="Search...">
</div>

<div>
👤 <?php echo $_SESSION['name']; ?>
</div>

</div>


<div class="container-fluid">
<div class="row">

<!-- ================= SIDEBAR ================= -->

<div class="col-md-2 sidebar">

<h5 class="sidebar-title text-center mb-3">College Admin</h5>

<div class="menu-heading">MAIN</div>

<a href="dashboard.php" class="menu-link">
<span>🏠</span> Dashboard
</a>

<a href="add_student.php" class="menu-link active">
<span>➕</span> Add Student
</a>

<a href="view_students.php" class="menu-link">
<span>👨‍🎓</span> View Students
</a>

<div class="menu-heading">ACCOUNT</div>

<a href="../logout.php" class="menu-link danger">
<span>🚪</span> Logout
</a>

</div>


<!-- ================= MAIN ================= -->

<div class="col-md-10 p-4">

<!-- ===== HEADER CARD ===== -->

<div class="card info-card blue mb-4">
<h5 class="mb-1"><?php echo $edit_mode ? "Update Student Details" : "Add New Student"; ?></h5>
<p class="mb-0">Fill the form carefully</p>
</div>


<!-- ===== ALERTS ===== -->

<?php if(isset($error)) { ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php } ?>

<?php if(isset($success)) { ?>
<div class="alert alert-success"><?php echo $success; ?></div>
<?php } ?>


<!-- ===== FORM CARD ===== -->

<div class="card p-4 main-card">

<form method="POST">
<div class="row g-3">

<!-- NAME -->
<div class="col-md-4">
<label class="form-label">Student Name</label>
<input type="text" name="name" class="form-control"
value="<?php echo $edit_mode ? $edit_data['name'] : ''; ?>" required>
</div>

<!-- HOSTEL -->
<div class="col-md-4">
<label class="form-label">Select Hostel</label>
<select name="hostel_id" class="form-control" required>
<option value="">Choose...</option>

<?php 
mysqli_data_seek($hostel_query, 0);
while($h = mysqli_fetch_assoc($hostel_query)) { ?>
<option value="<?php echo $h['hostel_id']; ?>"
<?php if($edit_mode && $edit_data['hostel_id']==$h['hostel_id']) echo "selected"; ?>>
<?php echo $h['name']; ?>
</option>
<?php } ?>

</select>
</div>

<!-- ROOM TYPE -->
<div class="col-md-2">
<label class="form-label">Room Type</label>
<select name="room_type" class="form-control" required>
<option value="AC"
<?php if($edit_mode && $edit_data['room_type']=="AC") echo "selected"; ?>>
AC</option>

<option value="NON-AC"
<?php if($edit_mode && $edit_data['room_type']=="NON-AC") echo "selected"; ?>>
Non-AC</option>
</select>
</div>

<!-- PAYMENT TYPE -->
<div class="col-md-2">
<label class="form-label">Payment</label>
<select name="payment_type" class="form-control" required>
<option value="full">Full</option>
<option value="half">Half</option>
<option value="custom">Custom</option>
</select>
</div>

<!-- CUSTOM AMOUNT -->
<div class="col-md-3">
<label class="form-label">Custom Amount</label>
<input type="number" name="custom_amount" class="form-control" value="0">
</div>

<!-- BUTTON -->
<div class="col-md-3 d-flex align-items-end">
<button type="submit" name="submit" class="btn btn-primary w-100">
<?php echo $edit_mode ? "Update Student" : "Add Student"; ?>
</button>
</div>

</div>
</form>

</div>

</div>
</div>
</div>

</body>
</html>
