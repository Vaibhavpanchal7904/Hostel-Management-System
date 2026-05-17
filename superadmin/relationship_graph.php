<?php
include("../config/config.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != "superadmin"){
    header("Location: ../login.php");
    exit;
}

/* Stats */
$total_colleges = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM colleges"));
$total_hostels = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM hostels"));
$total_links = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM seat_allocation"));

/* Colleges */
$college_data = mysqli_query($conn,"
SELECT DISTINCT c.id,c.name
FROM colleges c
JOIN seat_allocation sa ON c.id=sa.college_id
");
?>

<!DOCTYPE html>
<html>
<head>
<title>College Hostel Graph</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="superadmin.css">

<style>
body{
    background:#f8fafc;
}

/* Header */
.graph-header{
    background:linear-gradient(135deg,#1e3c72,#2a5298);
    color:white;
    padding:20px;
    border-radius:12px;
    margin-bottom:20px;
}

/* Stats */
.stat-card{
    background:white;
    padding:20px;
    border-radius:12px;
    text-align:center;
    box-shadow:0 6px 16px rgba(0,0,0,0.08);
}

.stat-card h4{
    color:#2a5298;
    font-weight:700;
}

/* Graph */
.graph-container{
    background:white;
    border-radius:15px;
    min-height:450px;
    padding:40px;
    box-shadow:0 8px 20px rgba(0,0,0,0.08);
}

.college-node{
    width:220px;
    height:80px;
    background:#2563eb;
    color:white;
    border-radius:12px;
    display:flex;
    justify-content:center;
    align-items:center;
    font-weight:600;
    margin:auto;
    font-size:18px;
}

.line{
    width:4px;
    height:60px;
    background:#94a3b8;
    margin:auto;
}

.hostel-node{
    background:#10b981;
    color:white;
    padding:20px;
    border-radius:12px;
    text-align:center;
    font-weight:600;
    min-height:80px;
    display:flex;
    justify-content:center;
    align-items:center;
    box-shadow:0 5px 15px rgba(0,0,0,0.1);
}

.carousel-control-prev-icon,
.carousel-control-next-icon{
    background-color:#1e3c72;
    border-radius:50%;
    padding:20px;
}
</style>

</head>

<body>

<div class="container-fluid">
<div class="row">

<?php include("sidebar.php"); ?>

<div class="col-md-10 content">

<!-- Header -->
<div class="graph-header">
    <h3>📊 College → Hostel Graph View</h3>
    <small>One college graph per slide</small>
</div>

<!-- Stats -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <h4><?php echo $total_colleges; ?></h4>
            <p>Total Colleges</p>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card">
            <h4><?php echo $total_hostels; ?></h4>
            <p>Total Hostels</p>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card">
            <h4><?php echo $total_links; ?></h4>
            <p>Total Connections</p>
        </div>
    </div>
</div>


<!-- Graph Slider -->
<div class="card border-0 shadow-sm p-4">

<div id="collegeCarousel" class="carousel slide" data-bs-ride="false">

<div class="carousel-inner">

<?php
$first=true;

while($college=mysqli_fetch_assoc($college_data)){

    $college_id=$college['id'];

    $hostels=mysqli_query($conn,"
    SELECT h.name
    FROM seat_allocation sa
    JOIN hostels h ON sa.hostel_id=h.id
    WHERE sa.college_id='$college_id'
    ");
?>

<div class="carousel-item <?php if($first) echo 'active'; ?>">

    <div class="graph-container text-center">

        <!-- College Node -->
        <div class="college-node">
            🎓 <?php echo $college['name']; ?>
        </div>

        <div class="line"></div>

        <!-- Hostel Nodes -->
        <div class="row justify-content-center">

            <?php while($hostel=mysqli_fetch_assoc($hostels)){ ?>
            
            <div class="col-md-3 mb-3">
                <div class="hostel-node">
                    🏨 <?php echo $hostel['name']; ?>
                </div>
            </div>

            <?php } ?>

        </div>

    </div>

</div>

<?php
$first=false;
}
?>

</div>

<!-- Buttons -->
<button class="carousel-control-prev"
type="button"
data-bs-target="#collegeCarousel"
data-bs-slide="prev">
<span class="carousel-control-prev-icon"></span>
</button>

<button class="carousel-control-next"
type="button"
data-bs-target="#collegeCarousel"
data-bs-slide="next">
<span class="carousel-control-next-icon"></span>
</button>

</div>

</div>

</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>