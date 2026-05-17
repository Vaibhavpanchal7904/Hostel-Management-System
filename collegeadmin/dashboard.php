<?php
include("../config/config.php");

// Role check
if(!isset($_SESSION['role']) || $_SESSION['role'] != "collegeadmin") {
    header("Location: ../login.php");
    exit;
}

$college_id = $_SESSION['college_id'];

/* Student count */
$query = mysqli_query($conn,"
SELECT COUNT(*) as total 
FROM students 
WHERE college_id=$college_id
");
$total_students = mysqli_fetch_assoc($query)['total'];

/* Notices */
$notice = mysqli_query($conn,"
SELECT * 
FROM notices 
ORDER BY id DESC 
LIMIT 5
");

/* Hostel Allocation */
$allocation = mysqli_query($conn,"
SELECT 
    h.name as hostel_name,
    sa.allocated_seats,
    sa.ac_seats,
    sa.non_ac_seats,
    sa.ac_fees,
    sa.non_ac_fees
FROM seat_allocation sa
JOIN hostels h ON sa.hostel_id = h.id
WHERE sa.college_id = $college_id
");
?>

<!DOCTYPE html>
<html>
<head>
<title>College Admin Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="collegeadmin.css">

<style>
.card{
    border-radius:12px;
}

.main-card{
    border-radius:12px;
}

.carousel-item{
    min-height:220px;
}

.slider-video{
    width:100%;
    max-height:140px;
    object-fit:cover;
    border-radius:10px;
}

.notice-box{
    max-height:280px;
    overflow-y:auto;
}
</style>
</head>

<body>

<!-- TOPBAR -->
<div class="topbar d-flex justify-content-between align-items-center">

    <div class="d-flex align-items-center gap-3">
        <h5 class="mb-0">College Dashboard</h5>
        <input type="text" class="form-control search-box" placeholder="Search...">
    </div>

    <div>
        👤 <?php echo $_SESSION['name']; ?>
    </div>
</div>


<div class="container-fluid">
<div class="row">

<?php include("sidebar.php"); ?>

<div class="col-md-10 p-4">

<!-- TOP INFO CARDS -->
<div class="row g-3 mb-4">

    <div class="col-md-4">
        <div class="card info-card blue p-3">
            <h6>Date</h6>
            <h4 id="date"></h4>
            <p id="day"></p>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card info-card green p-3">
            <h5>Welcome 👋</h5>
            <p>Manage your college efficiently</p>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card info-card blue p-3">
            <h6>Time</h6>
            <h4 id="time"></h4>
        </div>
    </div>

</div>


<!-- STUDENT CARD -->
<div class="row g-4 mb-4">

    <div class="col-md-4">
        <div class="card stat-card primary text-center p-3">
            <h6>Total Students</h6>
            <h2><?php echo $total_students; ?></h2>
        </div>
    </div>

</div>


<!-- MAIN SECTION -->
<div class="row mt-4">

    <!-- LEFT SIDE -->
    <div class="col-md-8">

        <div class="card p-4 main-card">

            <h5 class="mb-3">🏨 Hostel Allocation</h5>

            <?php if(mysqli_num_rows($allocation) > 0){ ?>

            <div class="table-responsive">
                <table class="table table-hover align-middle">

                    <thead class="table-light">
                    <tr>
                        <th>Hostel</th>
                        <th>Total</th>
                        <th>AC</th>
                        <th>Non-AC</th>
                        <th>AC Fees</th>
                        <th>Non-AC Fees</th>
                    </tr>
                    </thead>

                    <tbody>

                    <?php while($row=mysqli_fetch_assoc($allocation)){ ?>

                    <tr>
                        <td><b><?php echo $row['hostel_name']; ?></b></td>
                        <td><?php echo $row['allocated_seats']; ?></td>

                        <td>
                            <span class="badge bg-success">
                                <?php echo $row['ac_seats']; ?>
                            </span>
                        </td>

                        <td>
                            <span class="badge bg-secondary">
                                <?php echo $row['non_ac_seats']; ?>
                            </span>
                        </td>

                        <td>₹<?php echo $row['ac_fees']; ?></td>
                        <td>₹<?php echo $row['non_ac_fees']; ?></td>
                    </tr>

                    <?php } ?>

                    </tbody>
                </table>
            </div>

            <?php } else { ?>
                <p class="text-muted">No hostel allocated yet</p>
            <?php } ?>
<!-- Slider -->
            
                <div class="card p-3 h-100">

                    <h6 class="text-center mb-3">
                        System Guide
                    </h6>

                    <div id="dashboardSlider" 
                         class="carousel slide" 
                         data-bs-ride="false">

                        <div class="carousel-inner">

                            <!-- Slide 1 -->
                            <div class="carousel-item active text-center">
                                <img src="../images/images.png"
                                     width="80"
                                     class="mb-3">

                                <h6 class="text-primary fw-bold">
                                    HMS
                                </h6>

                                <p class="small text-muted">
                                    Manage hostels easily
                                </p>
                            </div>

                            <!-- Slide 2 -->
                  <div class="carousel-item text-center">
                        <video width="100%"
                               controls
                               class="rounded shadow">
                            <source src="../images/demo_collegeadmin.mp4"
                                    type="video/mp4">
                        </video>
                    </div>

                            <!-- Slide 3 -->
                            <div class="carousel-item text-center">

                                <img src="../images/images.png"
                                     width="70"
                                     class="mb-3">

                                <p class="small text-muted">
                                    Need help? Check system guide.
                                </p>

                            </div>

                        </div>

                        <!-- Controls -->
                        <button class="carousel-control-prev"
                                type="button"
                                data-bs-target="#dashboardSlider"
                                data-bs-slide="prev">

                            <span class="carousel-control-prev-icon bg-dark rounded-circle p-2"></span>
                        </button>

                        <button class="carousel-control-next"
                                type="button"
                                data-bs-target="#dashboardSlider"
                                data-bs-slide="next">

                            <span class="carousel-control-next-icon bg-dark rounded-circle p-2"></span>
                        </button>

                    </div>

                </div>
            

        </div>

    </div>


    <!-- RIGHT SIDE -->
    <div class="col-md-4">

        <!-- Quick Actions -->
        <div class="card p-3 mb-4">
            <h6>Quick Actions</h6>
            <hr>

            <a href="add_student.php" class="btn btn-primary w-100 mb-2">
                Add Student
            </a>

            <a href="view_students.php" class="btn btn-outline-primary w-100">
                View Students
            </a>
        </div>


       

            

  <h5 class="mb-3 text-primary fw-bold">
    📢 Latest Announcements
</h5>

<?php 
if(mysqli_num_rows($notice) > 0){

    $count = 0;

    while($row=mysqli_fetch_assoc($notice)){ 
        $count++;
?>

<div class="card shadow-sm border-0 mb-3 p-3 notice-card">

    <div class="d-flex justify-content-between align-items-center">

        <h6 class="mb-1 fw-bold text-dark">
            <?php echo $row['title']; ?>
        </h6>

        <?php if($count == 1){ ?>
            <span class="badge bg-danger blink-badge">
                NEW
            </span>
        <?php } ?>

    </div>

    <p class="small text-muted mb-2">
        <?php echo substr($row['description'],0,50); ?>...
    </p>

    <div class="d-flex gap-2">

        <!-- Read More -->
        <button 
            class="btn btn-sm btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#noticeModal<?php echo $row['id']; ?>">
            Read Full Notice
        </button>

        <!-- File -->
        <?php if(!empty($row['file'])) { ?>
            <a href="../uploads/<?php echo $row['file']; ?>"
               target="_blank"
               class="btn btn-sm btn-outline-success">
               📄 View File
            </a>
        <?php } ?>

    </div>

</div>


<!-- Modal -->
<div class="modal fade"
     id="noticeModal<?php echo $row['id']; ?>"
     tabindex="-1">

    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">

                <h5 class="modal-title">
                    📢 <?php echo $row['title']; ?>
                </h5>

                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <p class="text-dark">
                    <?php echo nl2br($row['description']); ?>
                </p>

            </div>

            <div class="modal-footer">

                <button class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    Close
                </button>

            </div>

        </div>
    </div>
</div>

<?php 
    }
} 
else { 
?>

<div class="alert alert-info text-center">
    No notices available
</div>

<?php } ?>
       

    </div>

</div>

</div>
</div>
</div>


<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
function updateTime(){
    let now = new Date();

    document.getElementById("date").innerHTML =
        now.toLocaleDateString();

    document.getElementById("day").innerHTML =
        now.toLocaleDateString('en-US',{
            weekday:'long'
        });

    document.getElementById("time").innerHTML =
        now.toLocaleTimeString();
}

setInterval(updateTime,1000);
updateTime();
</script>

</body>
</html>