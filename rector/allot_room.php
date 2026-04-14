<?php
include("../config/config.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != "rector") {
    header("Location: ../login.php");
    exit;
}

$hostel_id = $_SESSION['hostel_id'];

/* =============================
   SAFE GET ID
============================= */
if(!isset($_GET['id']) || empty($_GET['id'])){
    echo "❌ Student ID missing!";
    exit;
}

$student_id = intval($_GET['id']);

/* =============================
   FETCH STUDENT
============================= */
$query = mysqli_query($conn, 
    "SELECT * FROM students 
     WHERE id=$student_id 
     AND hostel_id=$hostel_id
     LIMIT 1"
);

$student = mysqli_fetch_assoc($query);

if(!$student){
    echo "❌ Invalid student!";
    exit;
}

$room_type = $student['room_type'];

/* =============================
   FETCH ROOMS
============================= */
$rooms = mysqli_query($conn, "
    SELECT * FROM rooms 
    WHERE hostel_id=$hostel_id 
    AND room_type='$room_type'
    AND filled_beds < total_beds
");

/* =============================
   ALLOT ROOM
============================= */
if(isset($_POST['allot'])){

    $room_id = intval($_POST['room_id']);

    // GET OLD ROOM
    $old_room = $student['room_id'];

    // अगर पहले से room allotted है
    if($old_room){

        // decrease old room count
        mysqli_query($conn, "
            UPDATE rooms 
            SET filled_beds = filled_beds - 1
            WHERE id=$old_room
        ");
    }

    // assign new room
    mysqli_query($conn, "
        UPDATE students 
        SET room_id=$room_id, status='allotted'
        WHERE id=$student_id
    ");

    // increase new room count
    mysqli_query($conn, "
        UPDATE rooms 
        SET filled_beds = filled_beds + 1
        WHERE id=$room_id
    ");

    header("Location: view_students.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Allot Room</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="rector.css">

</head>

<body>

<!-- ================= TOPBAR ================= -->

<div class="topbar d-flex justify-content-between align-items-center">

<div class="d-flex align-items-center gap-3">
<h5 class="mb-0">Allot Room</h5>
</div>

<div>
👤 <?php echo $_SESSION['name']; ?>
</div>

</div>


<div class="container-fluid">
<div class="row">

<!-- ================= SIDEBAR ================= -->
<?php include("sidebar.php"); ?>


<!-- ================= MAIN ================= -->

<div class="col-md-10 p-4">

<!-- HEADER -->
<div class="card info-card blue mb-4">
<h5>Room Allocation</h5>
<p>Assign a room to student</p>
</div>

<!-- FORM CARD -->
<div class="card p-4 main-card">

<h5 class="mb-3">
👤 Student: <strong><?php echo htmlspecialchars($student['name']); ?></strong>
</h5>

<p class="text-muted">
Room Type: <b><?php echo $room_type; ?></b>
</p>

<form method="POST">

<div class="row g-3">

<!-- ROOM SELECT -->
<div class="col-md-6">
<label class="form-label">Select Available Room</label>

<select name="room_id" class="form-control" required>
<option value="">Choose Room...</option>

<?php while($r = mysqli_fetch_assoc($rooms)) { ?>
<option value="<?php echo $r['id']; ?>">
Room <?php echo $r['room_number']; ?> 
(Available: <?php echo $r['total_beds'] - $r['filled_beds']; ?>)
</option>
<?php } ?>

</select>

</div>

<!-- BUTTON -->
<div class="col-md-6 d-flex align-items-end">
<button type="submit" name="allot" class="btn btn-success w-100">
✅ Confirm Allotment
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