<?php
include("../config/config.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != "rector") {
    header("Location: ../login.php");
    exit;
}

$hostel_id = $_SESSION['hostel_id'];

/* ================= BASIC COUNTS ================= */
$reserved = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) as total 
FROM students 
WHERE hostel_id=$hostel_id 
AND status='reserved'"
))['total'];

$allotted = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) as total 
FROM students 
WHERE hostel_id=$hostel_id 
AND status='allotted'"
))['total'];

/* ================= SMART ANALYTICS ================= */

// Total beds
$totalBeds = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT SUM(total_beds) as total 
FROM rooms 
WHERE hostel_id=$hostel_id"
))['total'] ?? 0;

// Filled beds
$filledBeds = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT SUM(filled_beds) as total 
FROM rooms 
WHERE hostel_id=$hostel_id"
))['total'] ?? 0;

// Pending fees students
$pendingFees = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) as total 
FROM students 
WHERE hostel_id=$hostel_id 
AND (total_fees > paid_amount)"
))['total'] ?? 0;

// Occupancy %
$occupancy = ($totalBeds > 0)
    ? round(($filledBeds / $totalBeds) * 100)
    : 0;

/* ================= COLLEGE WISE ================= */
$collegeWise = mysqli_query($conn,"
SELECT 
    c.name as college_name,
    
    SUM(CASE 
        WHEN s.status='reserved' THEN 1 
        ELSE 0 
    END) as reserved,

    SUM(CASE 
        WHEN s.status='allotted' THEN 1 
        ELSE 0 
    END) as allotted,

    COUNT(*) as total

FROM students s
JOIN colleges c 
ON s.college_id = c.id

WHERE s.hostel_id = $hostel_id

GROUP BY s.college_id
");

/* Notices */
$notice = mysqli_query($conn,"
SELECT * 
FROM notices 
ORDER BY id DESC 
LIMIT 5
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Rector Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="rector.css">

<style>
.notice-card{
    border-left:4px solid #0d6efd;
    transition:0.3s;
}

.notice-card:hover{
    transform:translateY(-4px);
    box-shadow:0 10px 25px rgba(0,0,0,0.08);
}

.blink-badge{
    animation: blink 1s infinite;
}

@keyframes blink{
    0%{opacity:1;}
    50%{opacity:0.3;}
    100%{opacity:1;}
}

.carousel-item{
    min-height:280px;
}

.stat-card{
    border-radius:15px;
    padding:20px;
}

.main-card{
    border-radius:15px;
}
</style>

</head>

<body>

<!-- TOPBAR -->
<div class="topbar d-flex justify-content-between align-items-center">

    <div class="d-flex align-items-center gap-3">
        <h5 class="mb-0">Smart Rector Dashboard</h5>

        <input type="text"
               class="form-control search-box"
               placeholder="Search...">
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
    <h5>🏨 Hostel Intelligence Panel</h5>
    <p>Real-time analytics & smart monitoring</p>
</div>


<!-- STATS -->
<div class="row g-4">

    <div class="col-md-3">
        <div class="card stat-card primary text-center">
            <h6>Total Beds</h6>
            <h2><?php echo $totalBeds; ?></h2>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card success text-center">
            <h6>Filled Beds</h6>
            <h2><?php echo $filledBeds; ?></h2>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card danger text-center">
            <h6>Pending Fees</h6>
            <h2><?php echo $pendingFees; ?></h2>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card text-center bg-dark text-white">
            <h6>Occupancy</h6>
            <h2><?php echo $occupancy; ?>%</h2>
        </div>
    </div>

</div>


<!-- OCCUPANCY -->
<div class="card p-4 mt-4 main-card">

    <h6>📊 Hostel Occupancy</h6>

    <div class="progress mt-3" style="height:25px;">
        <div class="progress-bar bg-success"
             style="width: <?php echo $occupancy; ?>%">
            <?php echo $occupancy; ?>%
        </div>
    </div>

    <?php if($occupancy >= 90){ ?>
        <div class="alert alert-danger mt-3">
            ⚠️ Hostel almost full!
        </div>
    <?php } ?>

</div>


<!-- QUICK ACTIONS -->
<div class="row mt-4">

    <div class="col-md-6">
        <div class="card p-4 main-card">

            <h6>⚡ Quick Actions</h6>

            <a href="view_students.php?status=reserved"
               class="btn btn-warning w-100 mb-2">
                View Reserved Students
            </a>

            <a href="view_students.php?status=allotted"
               class="btn btn-success w-100">
                View Allotted Students
            </a>

        </div>
    </div>


    <div class="col-md-6">
        <div class="card p-4 main-card">

            <h6>📌 Insights</h6>

            <p>🛏️ Available Beds: 
                <b><?php echo $totalBeds-$filledBeds; ?></b>
            </p>

            <p>⚠️ Pending Fees: 
                <b><?php echo $pendingFees; ?></b>
            </p>

            <p>📋 Reserved: 
                <b><?php echo $reserved; ?></b>
            </p>

            <p>✅ Allotted: 
                <b><?php echo $allotted; ?></b>
            </p>

        </div>
    </div>

</div>


<!-- COLLEGE TABLE -->
<div class="card p-3 main-card mt-4">

    <h6 class="mb-3">🏫 College-wise Distribution</h6>

    <div class="table-responsive">

        <table class="table align-middle">

            <thead>
            <tr>
                <th>College</th>
                <th>Reserved</th>
                <th>Allotted</th>
                <th>Total</th>
            </tr>
            </thead>

            <tbody>

            <?php 
            if($collegeWise && mysqli_num_rows($collegeWise) > 0){

                while($row=mysqli_fetch_assoc($collegeWise)){
            ?>

            <tr>
                <td>
                    <strong>
                        <?php echo $row['college_name']; ?>
                    </strong>
                </td>

                <td><?php echo $row['reserved']; ?></td>
                <td><?php echo $row['allotted']; ?></td>
                <td><?php echo $row['total']; ?></td>
            </tr>

            <?php 
                }
            } else { 
            ?>

            <tr>
                <td colspan="4" class="text-center text-muted">
                    No Students Found
                </td>
            </tr>

            <?php } ?>

            </tbody>

        </table>

    </div>

</div>


<!-- SLIDER + NOTICE -->
<div class="row mt-4">

    <!-- Slider -->
    <div class="col-md-6">

        <div class="card p-4 main-card h-100">

            <h5 class="text-primary fw-bold mb-3">
                🎥 Smart Hostel Guide
            </h5>

            <div id="rectorSlider"
                 class="carousel slide"
                 data-bs-ride="false">

                <div class="carousel-inner">

                    <div class="carousel-item active text-center">
                        <img src="../images/images.png"
                             width="120"
                             class="mb-3">

                        <h5 class="fw-bold text-primary">
                            Hostel Management System
                        </h5>

                        <p class="text-muted">
                            Manage rooms, fees,
                            allocations & reports.
                        </p>
                    </div>

                    <div class="carousel-item text-center">
                        <video width="100%"
                               controls
                               class="rounded shadow">
                            <source src="../images/demo_rector.mp4"
                                    type="video/mp4">
                        </video>
                    </div>

                    <div class="carousel-item text-center">
                        <img src="../images/images.png"
                             width="100"
                             class="mb-3">

                        <h5>Need Help?</h5>

                        <p class="text-muted">
                            Check full documentation.
                        </p>
                    </div>

                </div>

                <button class="carousel-control-prev"
                        type="button"
                        data-bs-target="#rectorSlider"
                        data-bs-slide="prev">

                    <span class="carousel-control-prev-icon 
                                 bg-dark rounded-circle p-3">
                    </span>
                </button>

                <button class="carousel-control-next"
                        type="button"
                        data-bs-target="#rectorSlider"
                        data-bs-slide="next">

                    <span class="carousel-control-next-icon 
                                 bg-dark rounded-circle p-3">
                    </span>
                </button>

            </div>

        </div>

    </div>


    <!-- Notices -->
    <div class="col-md-6">

        <div class="card p-4 main-card h-100">

            <h5 class="text-danger fw-bold mb-3">
                📢 Latest Announcements
            </h5>

            <?php 
            if(mysqli_num_rows($notice) > 0){

                $count = 0;

                while($row=mysqli_fetch_assoc($notice)){
                    $count++;
            ?>

            <div class="card notice-card p-3 mb-3">

                <div class="d-flex justify-content-between">

                    <h6>
                        <?php echo $row['title']; ?>
                    </h6>

                    <?php if($count==1){ ?>
                        <span class="badge bg-danger blink-badge">
                            NEW
                        </span>
                    <?php } ?>

                </div>

                <p class="small text-muted">
                    <?php echo substr($row['description'],0,60); ?>...
                </p>

                <div class="d-flex gap-2">

                    <button class="btn btn-sm btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#noticeModal<?php echo $row['id']; ?>">
                        Read More
                    </button>

                    <?php if(!empty($row['file'])){ ?>
                        <a href="../uploads/<?php echo $row['file']; ?>"
                           target="_blank"
                           class="btn btn-sm btn-outline-success">
                           File
                        </a>
                    <?php } ?>

                </div>

            </div>


            <!-- Modal -->
            <div class="modal fade"
                 id="noticeModal<?php echo $row['id']; ?>"
                 tabindex="-1">

                <div class="modal-dialog modal-lg modal-dialog-centered">

                    <div class="modal-content">

                        <div class="modal-header bg-primary text-white">

                            <h5 class="modal-title">
                                <?php echo $row['title']; ?>
                            </h5>

                            <button type="button"
                                    class="btn-close btn-close-white"
                                    data-bs-dismiss="modal">
                            </button>

                        </div>

                        <div class="modal-body">
                            <?php echo nl2br($row['description']); ?>
                        </div>

                    </div>

                </div>

            </div>

            <?php 
                }
            } else { 
            ?>

            <div class="alert alert-info">
                No notices available
            </div>

            <?php } ?>

        </div>

    </div>

</div>

</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>