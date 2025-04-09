<?php
include 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Kiểm tra xem các khóa có tồn tại không trước khi truy cập
    $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : ''; // Bỏ comment và thêm kiểm tra
   
    // Kiểm tra định dạng mật khẩu
    if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\W).{6,}$/', $password)) {
        $message = "Password must be at least 6 characters, including uppercase, lowercase and special characters!";
    } elseif ($password !== $confirm_password) {
        $message = "Re-entered password does not match!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Kiểm tra xem username hoặc email đã tồn tại chưa
        $checkUser = $conn->prepare("SELECT user_id FROM users WHERE full_name = ? OR email = ?");
        $checkUser->bind_param("ss", $full_name, $email);
        $checkUser->execute();
        $result = $checkUser->get_result();

        if ($result->num_rows > 0) {
            $message = "Username or Email already exists!";
        } else {
            // Chèn dữ liệu vào database
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $full_name, $email, $password, $phone, $address);

            if ($stmt->execute()) {
                header("Location: login.php?success=1");
                exit();
            } else {
                $message = "Error while registering!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
    background: #E3F2FD; /* Màu xanh nhạt */
    font-family: Arial, sans-serif;
    }

    .container {
    max-width: 450px;
    margin: auto;
    padding: 30px;
    }

    .form-box {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    h2 {
    color: #0277BD; /* Màu xanh đậm */
    font-weight: bold;
    }

    .btn-primary {
    background: #0288D1;
    border: none;
    transition: 0.3s;
    }

    .btn-primary:hover {
    background: #01579B;
    }

    .form-control {
    border-radius: 5px;
    border: 1px solid #90CAF9;
    padding: 10px;
    }

    .form-label {
    font-weight: bold;
    color: #0277BD;
    }

    .text-danger {
    font-size: 14px;
    }

    a {
    color: #0277BD;
    text-decoration: none;
    }

    a:hover {
    color: #01579B;
    text-decoration: underline;
    }

    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="form-box">
            <h2 class="text-center">Register</h2>
            <?php if (isset($message)) echo "<p class='text-danger text-center'>$message</p>"; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">User name</label>
                    <input type="text" name="full_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone number</label>
                    <input type="text" name="phone" class="form-control" required>
                </div>
                <div class="mb-3">
                <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Re-enter password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Register</button>
            </form>
            <p class="text-center mt-3">Already have an account? <a href="Login.php">Login</a></p>
        </div>
    </div>
</body>
</html>