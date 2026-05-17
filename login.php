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
    <title>HMS Login</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            width: 900px;
            height: 520px;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            display: flex;
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        }

        /* LEFT SIDE */
        .left-panel {
            width: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
        }

        .left-panel h2 {
            font-weight: 600;
        }

        .left-panel p {
            opacity: 0.9;
        }

        /* RIGHT SIDE */
        .right-panel {
            width: 50%;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-control {
            border-radius: 10px;
            padding: 10px;
        }

        .btn-login {
            background: #2a5298;
            border: none;
            border-radius: 10px;
            padding: 10px;
            transition: 0.3s;
        }

        .btn-login:hover {
            background: #1e3c72;
        }

        .logo {
            font-size: 40px;
            margin-bottom: 10px;
        }

        .title {
            font-weight: 600;
        }
        .logo img {
    width: 80px;   /* adjust size */
    height: auto;
    margin-bottom: 10px;
}

.logo {
    text-align: center;
}
.notice-box {
    margin-top: 15px;
    background: #fff3cd;
    color: #856404;
    padding: 12px;
    border-radius: 10px;
    font-size: 13px;
    text-align: center;
    border: 1px solid #ffeeba;
}
    </style>
</head>

<body>

<div class="login-container">

    <!-- LEFT SIDE -->
    <div class="left-panel">
        <div class="logo">
        <img src="images/images.png" alt="Hostel Logo">
    </div>
        <h2>Welcome Back!</h2>
        <p>Hostel Management System</p>
        <small>Manage hostels, students & staff efficiently</small>
    </div>

    <!-- RIGHT SIDE -->
    <div class="right-panel">

        <h3 class="title mb-4 text-center">Login</h3>

        <?php if(isset($error)) { ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php } ?>

        <form method="POST">

            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" placeholder="Enter email" required>
            </div>

            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter password" required>
            </div>

            <button type="submit" name="login" class="btn btn-login w-100 text-white">
                Login
            </button>

            <div class="notice-box mt-3">
    <small>
        ⚠️ If you forgot your ID/password or you're a new user, 
        please contact the Super Admin to get/update your credentials.
        <br>
        📧 Email: <b>superadmin@gmail.com</b>
    </small>
</div>

        </form>

    </div>

</div>

</body>
</html>