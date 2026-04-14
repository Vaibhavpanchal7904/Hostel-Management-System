<?php
include("config/config.php");

if(isset($_POST['login'])) {

    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepared statement
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email=? AND password=?");
    mysqli_stmt_bind_param($stmt, "ss", $email, $password);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if(mysqli_num_rows($result) == 1) {

        $user = mysqli_fetch_assoc($result);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['college_id'] = $user['college_id'];
        $_SESSION['hostel_id'] = $user['hostel_id'];

        // Role-based redirection
        if($user['role'] == "superadmin") {
            header("Location: superadmin/dashboard.php");
        }
        elseif($user['role'] == "collegeadmin") {
            header("Location: collegeadmin/dashboard.php");
        }
        elseif($user['role'] == "rector") {
            header("Location: rector/dashboard.php");
        }

        exit;
    } else {
        $error = "Invalid Email or Password!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Hostel Management Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/login.css">
</head>
<body>

<div class="container">
    <div class="login-box">
        <h3 class="text-center mb-4">Hostel Management System</h3>

        <?php if(isset($error)) { ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php } ?>

        <form method="POST">
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Password</label>
                <input type="text" name="password" class="form-control" required>
            </div>

            <button type="submit" name="login" class="btn btn-primary w-100">
                Login
            </button>
        </form>
    </div>
</div>

</body>
</html>
