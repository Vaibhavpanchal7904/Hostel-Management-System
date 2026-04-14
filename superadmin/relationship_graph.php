<?php
include("../config/config.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != "superadmin"){
    header("Location: ../login.php");
    exit;
}

/* FETCH RELATION */
$data = mysqli_query($conn,"
SELECT 
c.name as college,
h.name as hostel
FROM seat_allocation sa
JOIN colleges c ON sa.college_id=c.id
JOIN hostels h ON sa.hostel_id=h.id
");
?>

<!DOCTYPE html>
<html>
<head>

<title>College-Hostel Graph</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="superadmin.css">

<script src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>

<style>
#network{
height:600px;
border-radius:12px;
background:#f1f5f9;
box-shadow:0 8px 20px rgba(0,0,0,0.08);
}
</style>

</head>

<body>

<div class="container-fluid">
<div class="row">

<?php include("sidebar.php"); ?>

<div class="col-md-10 content">

<h3>🔗 College ↔ Hostel Relationship</h3>
<hr>

<div id="network"></div>

</div>
</div>
</div>

<script>

/* ================= DATA ================= */
let nodes = [];
let edges = [];
let nodeMap = {};
let id = 1;

let relations = <?php
$data_arr = [];
mysqli_data_seek($data,0);
while($row = mysqli_fetch_assoc($data)){
    $data_arr[] = $row;
}
echo json_encode($data_arr);
?>;

/* ================= BUILD GRAPH ================= */
relations.forEach(row => {

    let college = row.college;
    let hostel = row.hostel;

    // COLLEGE NODE (LEFT)
    if(!nodeMap[college]){
        nodeMap[college] = id;
        nodes.push({
            id:id,
            label:college,
            color:"#3b82f6",
            shape:"box",
            level:1,
            font:{color:"#fff"},
            margin:10
        });
        id++;
    }

    // HOSTEL NODE (RIGHT)
    if(!nodeMap[hostel]){
        nodeMap[hostel] = id;
        nodes.push({
            id:id,
            label:hostel,
            color:"#10b981",
            shape:"ellipse",
            level:2,
            font:{color:"#fff"},
            margin:10
        });
        id++;
    }

    // EDGE
    edges.push({
        from: nodeMap[college],
        to: nodeMap[hostel],
        arrows:"to"
    });

});

/* ================= GRAPH ================= */
let container = document.getElementById("network");

let data = {
    nodes: new vis.DataSet(nodes),
    edges: new vis.DataSet(edges)
};

let options = {

    layout: {
        hierarchical: {
            direction: "LR",   // Left → Right
            sortMethod: "directed",
            levelSeparation: 150,
            nodeSpacing: 120
        }
    },

    nodes:{
        borderWidth:1,
        size:20
    },

    edges:{
        width:2,
        color:"#94a3b8",
        smooth:true
    },

    physics:false   // IMPORTANT (no random movement)
};

new vis.Network(container,data,options);

</script>

</body>
</html>