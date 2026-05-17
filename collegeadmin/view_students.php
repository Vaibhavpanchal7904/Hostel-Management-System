<?php
include("../config/config.php");

// Role check
if(!isset($_SESSION['role']) || $_SESSION['role'] != "collegeadmin") {
    header("Location: ../login.php");
    exit;
}

$college_id = $_SESSION['college_id'];

/* DELETE STUDENT */
if(isset($_GET['delete'])) {

    $delete_id = intval($_GET['delete']);

    mysqli_query($conn,"
        DELETE FROM students
        WHERE id=$delete_id 
        AND college_id=$college_id
    ");

    exit;
}


/* FETCH STUDENTS WITH ROOM + FLOOR */
$students = mysqli_query($conn,"
    SELECT 
        s.*,
        h.name as hostel_name,
        r.room_number,
        f.floor_number

    FROM students s

    JOIN hostels h 
        ON s.hostel_id = h.id

    LEFT JOIN rooms r
        ON s.room_id = r.id

    LEFT JOIN floors f
        ON r.floor_id = f.id

    WHERE s.college_id = $college_id

    ORDER BY s.id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>View Students</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="collegeadmin.css">

<style>
.room-badge{
    padding:8px 12px;
    border-radius:20px;
    font-size:13px;
}
</style>
</head>

<body>

<!-- TOPBAR -->
<div class="topbar d-flex justify-content-between align-items-center">

    <div class="d-flex align-items-center gap-3">
        <h5 class="mb-0">Students</h5>

        <input type="text"
               id="searchInput"
               class="form-control search-box"
               placeholder="Search student...">
    </div>

    <div>
        👤 <?php echo $_SESSION['name']; ?>
    </div>

</div>


<div class="container-fluid">
<div class="row">

<?php include("sidebar.php"); ?>


<div class="col-md-10 p-4">

<!-- HEADER -->
<div class="card info-card blue mb-4">
    <h5 class="mb-1">All Students</h5>
    <p class="mb-0">
        Manage and track student records
    </p>
</div>


<!-- TABLE CARD -->
<div class="card p-3 main-card">

    <!-- SEARCH + EXPORT -->
    <div class="d-flex justify-content-between mb-3">

        <input type="text"
               id="searchInput2"
               class="form-control w-50"
               placeholder="Search by name or hostel...">

        <button onclick="exportTable()"
                class="btn btn-success">
            📥 Export
        </button>

    </div>


    <div class="table-responsive">

        <table class="table table-hover table-bordered align-middle">

            <thead>
            <tr>
                <th>Name</th>
                <th>Hostel</th>
                <th>Room Type</th>
                <th>Allocated Room</th>
                <th>Total</th>
                <th>Paid</th>
                <th>Pending</th>
                <th>Status</th>
                <th class="text-center">Action</th>
            </tr>
            </thead>

            <tbody id="tableBody">

            <?php 
            if($students && mysqli_num_rows($students) > 0){

                while($row=mysqli_fetch_assoc($students)){

                    $pending = $row['total_fees'] - $row['paid_amount'];
            ?>

            <tr>

                <!-- Name -->
                <td>
                    <strong>
                        <?php echo htmlspecialchars($row['name']); ?>
                    </strong>
                </td>

                <!-- Hostel -->
                <td>
                    <?php echo htmlspecialchars($row['hostel_name']); ?>
                </td>

                <!-- Room Type -->
                <td>
                    <span class="badge bg-light text-dark">
                        <?php echo $row['room_type']; ?>
                    </span>
                </td>

                <!-- Allocated Room -->
                <td>

                    <?php if($row['room_number']){ ?>

                        <span class="badge bg-info text-dark room-badge">
                            Floor <?php echo $row['floor_number']; ?>
                            -
                            Room <?php echo $row['room_number']; ?>
                        </span>

                    <?php } else { ?>

                        <span class="text-muted">
                            Not Allotted
                        </span>

                    <?php } ?>

                </td>

                <!-- Total -->
                <td>
                    ₹ <?php echo number_format($row['total_fees'],2); ?>
                </td>

                <!-- Paid -->
                <td class="text-success fw-bold">
                    ₹ <?php echo number_format($row['paid_amount'],2); ?>
                </td>

                <!-- Pending -->
                <td>

                    <?php if($pending > 0){ ?>

                        <span class="text-danger fw-bold">
                            ₹ <?php echo number_format($pending,2); ?>
                        </span>

                    <?php } else { ?>

                        <span class="text-success fw-bold">
                            ₹ 0
                        </span>

                    <?php } ?>

                </td>

                <!-- Status -->
                <td>

                    <?php if($row['status']=="reserved"){ ?>

                        <span class="badge bg-warning text-dark">
                            Reserved
                        </span>

                    <?php } else { ?>

                        <span class="badge bg-success">
                            Allotted
                        </span>

                    <?php } ?>

                </td>

                <!-- Action -->
                <td class="text-center">

                    <a href="add_student.php?edit=<?php echo $row['id']; ?>"
                       class="btn btn-sm btn-outline-warning me-1">
                       ✏️
                    </a>

                    <button onclick="deleteStudent(<?php echo $row['id']; ?>)"
                            class="btn btn-sm btn-outline-danger">
                        🗑️
                    </button>

                </td>

            </tr>

            <?php 
                }
            } 
            else { 
            ?>

            <tr>
                <td colspan="9"
                    class="text-center text-muted">
                    No Students Found
                </td>
            </tr>

            <?php } ?>

            </tbody>
        </table>

    </div>


    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-3">
        <button class="btn btn-outline-primary me-2"
                onclick="prevPage()">
            Prev
        </button>

        <button class="btn btn-outline-primary"
                onclick="nextPage()">
            Next
        </button>
    </div>

</div>

</div>
</div>
</div>


<script>
// SEARCH
document.getElementById("searchInput2")
.addEventListener("keyup", function(){

    let value = this.value.toLowerCase();
    let rows = document.querySelectorAll("#tableBody tr");

    rows.forEach(row=>{
        row.style.display =
            row.innerText.toLowerCase().includes(value)
            ? ""
            : "none";
    });
});


// PAGINATION
let currentPage = 1;
let rowsPerPage = 5;

function showPage(page){
    let rows = document.querySelectorAll("#tableBody tr");

    let start = (page-1)*rowsPerPage;
    let end = start + rowsPerPage;

    rows.forEach((row,i)=>{
        row.style.display =
            (i >= start && i < end)
            ? ""
            : "none";
    });
}

function nextPage(){
    currentPage++;
    showPage(currentPage);
}

function prevPage(){
    if(currentPage > 1){
        currentPage--;
        showPage(currentPage);
    }
}

showPage(1);


// DELETE
function deleteStudent(id){
    if(confirm("Delete this student?")){
        fetch("view_students.php?delete=" + id)
        .then(() => location.reload());
    }
}


// EXPORT
function exportTable(){
    let table = document.querySelector("table");
    let html = table.outerHTML;

    let url = 'data:application/vnd.ms-excel,' 
        + encodeURIComponent(html);

    let link = document.createElement("a");

    link.href = url;
    link.download = "students.xls";
    link.click();
}
</script>

</body>
</html>