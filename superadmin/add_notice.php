<?php
include("../config/config.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != "superadmin") {
    header("Location: ../login.php");
    exit;
}

$success = "";
$error = "";

/* ================= ADD NOTICE ================= */
if(isset($_POST['submit']))
{
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);

    $file_name = $_FILES['file']['name'];
    $temp_name = $_FILES['file']['tmp_name'];

    $allowed = ['pdf','jpg','jpeg','png'];
    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    if(!empty($file_name) && !in_array($ext,$allowed)){
        $error = "❌ Only PDF, JPG, PNG allowed!";
    } else {

        $new_file = NULL;

        if(!empty($file_name)){
            $new_file = time()."_".$file_name;
            move_uploaded_file($temp_name,"../uploads/".$new_file);
        }

        $stmt = mysqli_prepare($conn,"
            INSERT INTO notices(title,description,file)
            VALUES (?,?,?)
        ");

        mysqli_stmt_bind_param($stmt,"sss",
            $title,$description,$new_file
        );

        mysqli_stmt_execute($stmt);

        $success = "✅ Notice Added Successfully!";
    }
}

/* ================= DELETE NOTICE ================= */
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);

    // get file name
    $file_data = mysqli_fetch_assoc(
        mysqli_query($conn,"SELECT file FROM notices WHERE id=$id")
    );

    if($file_data && $file_data['file']){
        $path = "../uploads/".$file_data['file'];
        if(file_exists($path)){
            unlink($path); // delete file
        }
    }

    mysqli_query($conn,"DELETE FROM notices WHERE id=$id");

    header("Location: add_notice.php");
    exit;
}

/* ================= FETCH NOTICES ================= */
$notices = mysqli_query($conn,"SELECT * FROM notices ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Notices</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="superadmin.css">

<style>
.card-soft{
background:linear-gradient(135deg,#e0f2fe,#bae6fd);
}
</style>

</head>

<body>

<div class="container-fluid">
<div class="row">

<?php include("sidebar.php"); ?>

<div class="col-md-10 content">

<h3>📢 Manage Notices</h3>
<hr>

<?php if($success){ ?>
<div class="alert alert-success"><?php echo $success; ?></div>
<?php } ?>

<?php if($error){ ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php } ?>

<!-- ================= ADD FORM ================= -->
<div class="card p-4 mb-4 card-soft">

<h5>Add New Notice</h5>

<form method="POST" enctype="multipart/form-data">

<div class="mb-3">
<label>Title</label>
<input type="text" name="title" class="form-control" required>
</div>

<div class="mb-3">
<label>Description</label>
<textarea name="description" class="form-control" rows="4"></textarea>
</div>

<div class="mb-3">
<label>Upload File (PDF/Image)</label>
<input type="file" name="file" class="form-control">
</div>

<button class="btn btn-primary" name="submit">
Add Notice
</button>

</form>

</div>

<!-- ================= NOTICE LIST ================= -->
<div class="card p-4">

<h5>All Notices</h5>

<table class="table">

<thead>
<tr>
<th>Title</th>
<th>Description</th>
<th>File</th>
<th>Action</th>
</tr>
</thead>

<tbody>

<?php while($n = mysqli_fetch_assoc($notices)){ ?>

<tr>

<td><?php echo htmlspecialchars($n['title']); ?></td>

<td><?php echo substr($n['description'],0,60); ?>...</td>

<td>
<?php if($n['file']){ ?>
<a href="../uploads/<?php echo $n['file']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
View
</a>
<?php } else { echo "-"; } ?>
</td>

<td>
<a href="?delete=<?php echo $n['id']; ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Delete this notice?')">
Delete
</a>
</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>
</div>
</div>

</body>
</html>