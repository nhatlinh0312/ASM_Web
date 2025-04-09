<?php
session_start();
include 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Kiểm tra nếu các trường đã được gửi từ form
    $name = isset($_POST['full_name']) ? trim($_POST['full_name']) : "";
    $password = isset($_POST['password']) ? trim($_POST['password']) : "";
    $errors = [];
    }


    if (empty($name) || empty($password)) {
        $errors[] = "Please enter your username and password!";
    }

    if (empty($errors)) {
        
        
        // Truy vấn database để lấy thông tin user
        $stmt = $conn->prepare("SELECT user_id, full_name, password, role FROM users WHERE full_name = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();

            if ($password === $user['password']) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
            
                if ($user['role'] == 'admin') {
                    header("Location: quanlisp.php");
                } else {
                    header("Location: doboi.php");
                }
                exit();
            }
            
        } else {
            $errors[] = "Username does not exist!";
        }
        $stmt->close();
    }
    $conn->close();

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    body {
        background: #E3F2FD; /* Màu xanh nhạt */
        font-family: Arial, sans-serif;
    }
    .container {
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        margin-top: 50px;
    }
    h2 {
        color: #0277BD; /* Màu xanh đậm */
    }
    .btn-primary {
        background: #0288D1;
        border: none;
    }
    .btn-primary:hover {
        background: #01579B;
    }
    .form-control {
        border-radius: 5px;
    }
    .text-danger {
        font-size: 14px;
    }
    a {
        color: #0277BD;
    }
    a:hover {
        color: #01579B;
        text-decoration: none;
    }
</style>

</head>
<body>
    <div class="container mt-5">
        <div class="col-md-6 mx-auto">
            <h2 class="text-center">Login </h2>
            <?php 
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    echo "<p class='text-danger text-center'>$error</p>";
                }
            }
            ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">UsernameUsername</label>
                    <input type="text" name="full_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password </label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login </button>
            </form>
            <p class="text-center mt-3">No account yet? <a href="Regester.php">Sign up now</a></p>
        </div>
</div>
</body>
</html>