<?php
session_start();
include("../config/config.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != "collegeadmin") {
    header("Location: ../login.php");
    exit;
}

$college_id = $_SESSION['college_id'];

$edit_id = "";
$edit_mode = false;

/* ---------------- FETCH HOSTELS ---------------- */
$hostel_query = mysqli_query($conn,"
SELECT 
    sa.*, 
    h.name,

    (
        SELECT COUNT(*) 
        FROM students s 
        WHERE s.hostel_id=sa.hostel_id 
        AND s.college_id=sa.college_id
    ) as used_seats,

    (
        SELECT COUNT(*) 
        FROM students s 
        WHERE s.hostel_id=sa.hostel_id 
        AND s.college_id=sa.college_id
        AND s.room_type='AC'
    ) as used_ac,

    (
        SELECT COUNT(*) 
        FROM students s 
        WHERE s.hostel_id=sa.hostel_id 
        AND s.college_id=sa.college_id
        AND s.room_type='NON-AC'
    ) as used_nonac

FROM seat_allocation sa
JOIN hostels h ON sa.hostel_id=h.id
WHERE sa.college_id=$college_id
");

$hostel_data = [];

while($h=mysqli_fetch_assoc($hostel_query)){
    $hostel_data[$h['hostel_id']] = [
        "name"=>$h['name'],
        "total"=>$h['allocated_seats'],
        "used"=>$h['used_seats'],
        "ac_total"=>$h['ac_seats'],
        "nonac_total"=>$h['non_ac_seats'],
        "ac_remaining"=>$h['ac_seats']-$h['used_ac'],
        "nonac_remaining"=>$h['non_ac_seats']-$h['used_nonac'],
        "ac_fee"=>$h['ac_fees'],
        "nonac_fee"=>$h['non_ac_fees']
    ];
}

/* DEFAULT VALUES */
$name = "";
$hostel_id = "";
$room_type = "";
$paid_amount = "";


/* ---------------- EDIT MODE ---------------- */
if(isset($_GET['edit'])){

    $edit_id = intval($_GET['edit']);
    $edit_mode = true;

    $studentQuery = mysqli_query($conn,"
        SELECT *
        FROM students
        WHERE id='$edit_id'
        AND college_id='$college_id'
    ");

    if(mysqli_num_rows($studentQuery) > 0){

        $student = mysqli_fetch_assoc($studentQuery);

        $name = $student['name'];
        $hostel_id = $student['hostel_id'];
        $room_type = $student['room_type'];
        $paid_amount = $student['paid_amount'];
    }
}


/* ---------------- SAVE / UPDATE ---------------- */
if(isset($_POST['submit'])){

    $name = $_POST['name'];
    $hostel_id = $_POST['hostel_id'];
    $room_type = $_POST['room_type'];
    $paid_amount = $_POST['paid_amount'];

    $alloc = mysqli_fetch_assoc(mysqli_query($conn,"
        SELECT * 
        FROM seat_allocation
        WHERE college_id=$college_id 
        AND hostel_id=$hostel_id
    "));

    $total_fees = ($room_type=="AC")
        ? $alloc['ac_fees']
        : $alloc['non_ac_fees'];

    if($paid_amount > $total_fees){
        $error = "Paid amount cannot exceed total fees!";
    }

    if(!isset($error)){

        if($edit_mode){

            // UPDATE STUDENT
            mysqli_query($conn,"
                UPDATE students SET
                    name='$name',
                    hostel_id='$hostel_id',
                    room_type='$room_type',
                    total_fees='$total_fees',
                    paid_amount='$paid_amount'
                WHERE id='$edit_id'
                AND college_id='$college_id'
            ");

            $success = "Student Updated Successfully!";

        } else {

            // INSERT STUDENT
            mysqli_query($conn,"
                INSERT INTO students(
                    name,
                    college_id,
                    hostel_id,
                    room_type,
                    total_fees,
                    paid_amount,
                    status
                )
                VALUES(
                    '$name',
                    '$college_id',
                    '$hostel_id',
                    '$room_type',
                    '$total_fees',
                    '$paid_amount',
                    'reserved'
                )
            ");

            $success = "Student Added Successfully!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title><?php echo $edit_mode ? "Edit Student" : "Add Student"; ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="collegeadmin.css">
</head>
<body>

<div class="container-fluid">
<div class="row">

<?php include("sidebar.php"); ?>

<div class="col-md-10 p-4">

<h4><?php echo $edit_mode ? "Edit Student" : "Add Student"; ?></h4>

<?php if(isset($error)){ ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php } ?>

<?php if(isset($success)){ ?>
<div class="alert alert-success"><?php echo $success; ?></div>
<?php } ?>

<div class="card p-4">

<form method="POST">
<div class="row g-3">

<div class="col-md-4">
<label>Name</label>
<input type="text"
       name="name"
       class="form-control"
       value="<?php echo $name; ?>"
       required>
</div>

<div class="col-md-4">
<label>Hostel</label>
<select name="hostel_id"
        class="form-control"
        id="hostelSelect"
        required>

<option value="">Select Hostel</option>

<?php foreach($hostel_data as $id=>$h){ ?>
<option value="<?= $id ?>"
    <?php if($hostel_id==$id) echo "selected"; ?>>
    <?= $h['name'] ?>
</option>
<?php } ?>

</select>
</div>

<div class="col-md-4">
<label>Room Type</label>
<select name="room_type"
        class="form-control"
        id="roomType">
</select>
</div>

<div class="col-md-4">
<label>Payment Type</label>
<select class="form-control" id="paymentType">
    <option value="full">Full Payment</option>
    <option value="half">Half Payment</option>
    <option value="custom">Custom Payment</option>
</select>
</div>

<div class="col-md-4">
<label>Paid Amount</label>
<input type="number"
       name="paid_amount"
       id="paidAmount"
       class="form-control"
       value="<?php echo $paid_amount; ?>"
       required>
</div>

<div class="col-md-12">
    <div id="seatInfo" class="alert alert-info d-none"></div>
</div>

<div class="col-md-3">
<button class="btn btn-primary w-100" name="submit">
    <?php echo $edit_mode ? "Update Student" : "Add Student"; ?>
</button>
</div>

</div>
</form>

</div>
</div>
</div>
</div>

<script>
const hostelData = <?php echo json_encode($hostel_data); ?>;
const selectedRoomType = "<?= $room_type ?>";

const hostelSelect = document.getElementById("hostelSelect");
const roomType = document.getElementById("roomType");
const paymentType = document.getElementById("paymentType");
const paidAmount = document.getElementById("paidAmount");
const seatInfo = document.getElementById("seatInfo");

function updateRoomTypes(){

    let h = hostelData[hostelSelect.value];
    roomType.innerHTML = "";

    if(!h) return;

    if(h.ac_remaining > 0 || selectedRoomType==="AC"){
        roomType.innerHTML += `
            <option value="AC" ${selectedRoomType==="AC" ? "selected" : ""}>
                AC (${h.ac_remaining} left)
            </option>
        `;
    }

    if(h.nonac_remaining > 0 || selectedRoomType==="NON-AC"){
        roomType.innerHTML += `
            <option value="NON-AC" ${selectedRoomType==="NON-AC" ? "selected" : ""}>
                NON-AC (${h.nonac_remaining} left)
            </option>
        `;
    }

    updateFees();
}

function updateFees(){

    let h = hostelData[hostelSelect.value];
    if(!h) return;

    let fee = roomType.value === "AC"
        ? parseFloat(h.ac_fee)
        : parseFloat(h.nonac_fee);

    if(paymentType.value === "full"){
        paidAmount.value = fee;
        paidAmount.readOnly = true;
    }
    else if(paymentType.value === "half"){
        paidAmount.value = fee/2;
        paidAmount.readOnly = true;
    }
    else{
        paidAmount.readOnly = false;
    }
}

function updateSeatInfo(){

    let h = hostelData[hostelSelect.value];

    if(!h){
        seatInfo.classList.add("d-none");
        return;
    }

    let remaining = h.total - h.used;

    seatInfo.classList.remove("d-none");

    seatInfo.innerHTML = `
        Total: ${h.total} |
        Used: ${h.used} |
        Remaining: <b>${remaining}</b><br>
        AC Left: <b>${h.ac_remaining}</b> |
        NON-AC Left: <b>${h.nonac_remaining}</b>
    `;

    updateRoomTypes();
}

hostelSelect.addEventListener("change", updateSeatInfo);
roomType.addEventListener("change", updateFees);
paymentType.addEventListener("change", updateFees);

// Auto load existing values in edit mode
if(hostelSelect.value){
    updateSeatInfo();
}
</script>

</body>
</html>