<?php
include 'connect.php';
session_start();

if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    header("Location: user.php");
    exit();
}

$result = $conn->query("SELECT * FROM users");

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<style>
    body {
    background: #E3F2FD; /* Màu nền xanh nhạt */
    font-family: Arial, sans-serif;
    }

    .container {
        max-width: 900px;
        margin: auto;
        padding: 30px;
    }

    h2 {
        color: #0277BD; /* Màu xanh đậm */
        font-weight: bold;
        text-align: center;
    }

    .table {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .table th {
        background: #0288D1;
        color: white;
        text-align: center;
    }

    .table td {
        text-align: center;
        vertical-align: middle;
    }

    .btn-danger {
        background: #0077b6;
        border: none;
        transition: 0.3s;
    }

    .btn-danger:hover {
        background: rgb(77, 98, 173);
    }

    .btn-success {
        background: #0077b6;
        border: none;
    }

    .btn-success:hover {
        background:rgb(77, 98, 173);
    }

    a {
        text-decoration: none;
        color: white;
    }

    a:hover {
        opacity: 0.8;
    }

</style>
<body>
<div class="container mt-5">
    <h2 class="text-center">User Management</h2>
    <!-- Bảng danh sách users -->
    <table class="table table-bordered">
        <thead>
            <tr><th>ID</th>
                <th>Full name</th>
                <th>Email</th>
                <th>Password</th>
                <th>Phone number</th>
                <th>Address</th>
                <th>Rights</th>
                <th>Operation</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= $row['user_id']; ?></td>
                <td><?= htmlspecialchars($row['full_name']); ?></td>
                <td><?= htmlspecialchars($row['email']); ?></td>
                <td><?= htmlspecialchars($row['password']); ?></td>
                <td><?= htmlspecialchars($row['phone']); ?></td>
                <td><?= htmlspecialchars($row['address']); ?></td>
                <td><?= htmlspecialchars($row['role']); ?></td>
                <td>
                    <a href="?delete_id=<?= $row['user_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete?')">Delete</a>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    <a href="quanlisp.php" class="btn btn-success mb-3">Product Management</a>
</div>
</body>
</html>