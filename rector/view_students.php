<?php
include("../config/config.php");

// Role Check
if(!isset($_SESSION['role']) || $_SESSION['role'] != "rector") {
    header("Location: ../login.php");
    exit;
}

$hostel_id = $_SESSION['hostel_id'];

// Filter logic
$status_filter = "";
if(isset($_GET['status']) && $_GET['status'] != "") {
    $status = $_GET['status'];
    $status_filter = "AND s.status='$status'";
}

// Fetch students
$students = mysqli_query($conn,"
    SELECT s.*, c.name as college_name, r.room_number
    FROM students s
    JOIN colleges c ON s.college_id = c.id
    LEFT JOIN rooms r ON s.room_id = r.id
    WHERE s.hostel_id=$hostel_id
    $status_filter
    ORDER BY s.status ASC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Students</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="rector.css">

</head>

<body>

<!-- ================= TOPBAR ================= -->

<div class="topbar d-flex justify-content-between align-items-center">

<div class="d-flex align-items-center gap-3">
<h5 class="mb-0">Students</h5>
<input type="text" id="searchInput" class="form-control search-box" placeholder="Search...">
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
<h5>Student Management</h5>
<p>View and manage hostel students</p>
</div>

<!-- FILTER BUTTONS -->
<div class="mb-3">
<a href="view_students.php" class="btn btn-outline-primary btn-sm">All</a>
<a href="view_students.php?status=reserved" class="btn btn-outline-warning btn-sm">Reserved</a>
<a href="view_students.php?status=allotted" class="btn btn-outline-success btn-sm">Allotted</a>
</div>

<!-- TABLE CARD -->
<div class="card p-3 main-card">

<div class="table-responsive">

<table class="table align-middle">

<thead>
<tr>
<th>Name</th>
<th>College</th>
<th>Room Type</th>
<th>Room</th>
<th>Status</th>
<th>Total</th>
<th>Paid</th>
<th>Pending</th>
<th>Action</th>
</tr>
</thead>

<tbody id="tableBody">

<?php
if($students && mysqli_num_rows($students) > 0){
    while($row = mysqli_fetch_assoc($students)){
        $pending = $row['total_fees'] - $row['paid_amount'];
?>

<tr>

<td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>

<td><?php echo htmlspecialchars($row['college_name']); ?></td>

<td>
<span class="badge bg-light text-dark">
<?php echo $row['room_type']; ?>
</span>
</td>

<td><?php echo $row['room_number'] ?? '-'; ?></td>

<td>
<?php if($row['status']=='reserved'){ ?>
<span class="badge bg-warning text-dark">Reserved</span>
<?php } else { ?>
<span class="badge bg-success">Allotted</span>
<?php } ?>
</td>

<td>₹ <?php echo number_format($row['total_fees'],2); ?></td>

<td class="text-success fw-bold">
₹ <?php echo number_format($row['paid_amount'],2); ?>
</td>

<td>
<?php if($pending>0){ ?>
<span class="text-danger fw-bold">
₹ <?php echo number_format($pending,2); ?>
</span>
<?php } else { ?>
<span class="text-success">0</span>
<?php } ?>
</td>

<td>
<td>

<?php if($row['status']=='reserved'){ ?>

<a href="allot_room.php?id=<?php echo $row['id']; ?>" 
class="btn btn-sm btn-primary">
Allot
</a>

<?php } else { ?>

<a href="allot_room.php?id=<?php echo $row['id']; ?>" 
class="btn btn-sm btn-warning">
Change Room
</a>

<?php } ?>

</td>

</tr>

<?php
    }
}else{
?>
<tr>
<td colspan="9" class="text-center text-muted">No Students Found</td>
</tr>
<?php } ?>

</tbody>
</table>

</div>

</div>

</div>
</div>
</div>

<!-- ================= JS ================= -->

<script>
// 🔍 SEARCH
document.getElementById("searchInput").addEventListener("keyup", function() {
    let value = this.value.toLowerCase();
    let rows = document.querySelectorAll("#tableBody tr");

    rows.forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(value) 
        ? "" : "none";
    });
});
</script>

</body>
</html>