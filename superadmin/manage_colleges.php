<?php
include("../config/config.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != "superadmin") {
    header("Location: ../login.php");
    exit;
}

// Add college
if(isset($_POST['add'])) {
    $name = $_POST['name'];

    $stmt = mysqli_prepare($conn, "INSERT INTO colleges (name) VALUES (?)");
    mysqli_stmt_bind_param($stmt, "s", $name);
    mysqli_stmt_execute($stmt);
}

// Delete college
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM colleges WHERE id=$id");
}

$colleges = mysqli_query($conn, "SELECT * FROM colleges");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Colleges</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="superadmin.css">
</head>
<body>

<div class="container-fluid">
<div class="row">

<?php include("sidebar.php"); ?>

<div class="col-md-10 content">
    <h3>Manage Colleges</h3>
    <hr>

    <form method="POST" class="mb-3">
        <div class="row">
            <div class="col-md-6">
                <input type="text" name="name" class="form-control" placeholder="College Name" required>
            </div>
            <div class="col-md-2">
                <button type="submit" name="add" class="btn btn-primary">Add</button>
            </div>
        </div>
    </form>

    <table class="table table-bordered">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Action</th>
        </tr>

        <?php while($row = mysqli_fetch_assoc($colleges)) { ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['name']; ?></td>
            <td>
                <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
            </td>
        </tr>
        <?php } ?>

    </table>

</div>
</div>
</div>

</body>
</html>
