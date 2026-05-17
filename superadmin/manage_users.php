<?php
include("../config/config.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != "superadmin") {
    header("Location: ../login.php");
    exit;
}

/* FETCH */
$colleges = mysqli_query($conn,"SELECT * FROM colleges");
$hostels  = mysqli_query($conn,"SELECT * FROM hostels");

/* ADD USER */
if(isset($_POST['add_user'])){

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    //$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $password = $_POST['password'];
    $role = $_POST['role'];

    $college_id = !empty($_POST['college_id']) ? $_POST['college_id'] : NULL;
    $hostel_id  = !empty($_POST['hostel_id']) ? $_POST['hostel_id'] : NULL;

    $stmt = mysqli_prepare($conn,"
        INSERT INTO users (name,email,password,role,college_id,hostel_id)
        VALUES (?,?,?,?,?,?)
    ");

    mysqli_stmt_bind_param($stmt,"ssssii",
        $name,$email,$password,$role,$college_id,$hostel_id
    );

    mysqli_stmt_execute($stmt);

    $success = "✅ User Created Successfully!";
}

/* DELETE */
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    mysqli_query($conn,"DELETE FROM users WHERE id=$id AND role!='superadmin'");
    header("Location: manage_users.php");
    exit;
}

/* USERS */
$users = mysqli_query($conn,"
SELECT u.*, c.name as college_name, h.name as hostel_name
FROM users u
LEFT JOIN colleges c ON u.college_id=c.id
LEFT JOIN hostels h ON u.hostel_id=h.id
ORDER BY u.id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Users</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="superadmin.css">

<style>
.card-blue{background:linear-gradient(135deg,#dbeafe,#bfdbfe);}
.role-badge{padding:4px 10px;border-radius:10px;font-size:12px;}
</style>

</head>

<body>

<div class="container-fluid">
<div class="row">

<?php include("sidebar.php"); ?>

<div class="col-md-10 content">

<h3>👤 Manage Users</h3>
<hr>

<?php if(isset($success)){ ?>
<div class="alert alert-success"><?php echo $success; ?></div>
<?php } ?>

<!-- 🔵 FORM -->
<div class="card p-4 mb-4 card-blue">

<h5>Create New User</h5>

<form method="POST" class="row g-3">

<div class="col-md-3">
<label>Name</label>
<input type="text" name="name" class="form-control" required>
</div>

<div class="col-md-3">
<label>Email</label>
<input type="email" name="email" class="form-control" required>
</div>

<div class="col-md-2">
<label>Password</label>
<input type="password" name="password" class="form-control" required>
</div>

<div class="col-md-2">
<label>Role</label>
<select name="role" id="role" class="form-control" required onchange="toggleFields()">
<option value="collegeadmin">College Admin</option>
<option value="rector">Rector</option>
</select>
</div>

<div class="col-md-2" id="collegeDiv">
<label>College</label>
<select name="college_id" class="form-control">
<option value="">Select</option>
<?php while($c = mysqli_fetch_assoc($colleges)){ ?>
<option value="<?php echo $c['id']; ?>">
<?php echo $c['name']; ?>
</option>
<?php } ?>
</select>
</div>

<div class="col-md-2" id="hostelDiv" style="display:none;">
<label>Hostel</label>
<select name="hostel_id" class="form-control">
<option value="">Select</option>
<?php while($h = mysqli_fetch_assoc($hostels)){ ?>
<option value="<?php echo $h['id']; ?>">
<?php echo $h['name']; ?>
</option>
<?php } ?>
</select>
</div>

<div class="col-md-2 d-flex align-items-end">
<button type="submit" name="add_user" class="btn btn-primary w-100">
Create
</button>
</div>

</form>

</div>

<!-- 🔍 SEARCH -->
<input type="text" id="search" class="form-control mb-3" placeholder="Search user...">

<!-- 📋 TABLE -->
<div class="card p-3">

<table class="table" id="userTable">

<thead>
<tr>
<th>Name</th>
<th>Email</th>
<th>Role</th>
<th>College</th>
<th>Hostel</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php while($u = mysqli_fetch_assoc($users)){ ?>

<tr>
<td><?php echo $u['name']; ?></td>
<td><?php echo $u['email']; ?></td>

<td>
<?php 
if($u['role']=="collegeadmin"){
echo '<span class="badge bg-primary">College Admin</span>';
}elseif($u['role']=="rector"){
echo '<span class="badge bg-success">Rector</span>';
}else{
echo '<span class="badge bg-dark">Super Admin</span>';
}
?>
</td>

<td><?php echo $u['college_name'] ?? '-'; ?></td>
<td><?php echo $u['hostel_name'] ?? '-'; ?></td>

<td>
<?php if($u['role'] != 'superadmin'){ ?>
<a href="?delete=<?php echo $u['id']; ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Delete this user?')">
Delete
</a>
<?php } ?>
</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>
</div>
</div>

<!-- JS -->
<script>

/* ROLE BASED FIELD SHOW */
function toggleFields(){
let role = document.getElementById("role").value;

document.getElementById("collegeDiv").style.display = 
(role === "collegeadmin") ? "block" : "none";

document.getElementById("hostelDiv").style.display = 
(role === "rector") ? "block" : "none";
}

/* SEARCH */
document.getElementById("search").addEventListener("keyup", function(){
let val = this.value.toLowerCase();
document.querySelectorAll("#userTable tbody tr").forEach(row=>{
row.style.display = row.innerText.toLowerCase().includes(val) ? "" : "none";
});
});

</script>

</body>
</html>