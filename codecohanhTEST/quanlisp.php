<?php
include 'connect.php';
session_start();

// Xử lý xóa sản phẩm
if (isset($_GET['delete'])) {
    $product_id = intval($_GET['delete']); // Ép kiểu để tránh SQL Injection

    // Xóa tất cả ảnh liên quan đến sản phẩm trong bảng images trước
    $stmt_images = $conn->prepare("DELETE FROM images WHERE product_id = ?");
    $stmt_images->bind_param("i", $product_id);
    $stmt_images->execute();
    $stmt_images->close();

    // Sau khi xóa ảnh, tiếp tục xóa sản phẩm trong bảng products
    $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        header("Location: quanlisp.php"); // Chuyển hướng về trang quản lý sản phẩm sau khi xóa
        exit();
    } else {
        echo "<div class='alert alert-danger'>Error while deleting product!</div>";
    }
}

// Lấy danh sách sản phẩm
$sql = "SELECT * FROM products";
$result = $conn->query($sql);
?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">
    <style>
    body {
    background-color: #E3F2FD; 
    font-family: Arial, sans-serif;
    }

    .container {
        max-width: 1100px;
        margin: auto;
        padding: 30px;
    }

    h2 {
        color: #0277BD; 
        font-weight: bold;
        text-align: center;
    }

    .table {
        background-color: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .table th {
        background-color: #0277BD;
        color: white;
        text-align: center;
    }

    .table td {
        text-align: center;
        vertical-align: middle;
    }

    .table img {
        border-radius: 5px;
        transition: transform 0.3s;
    }

    .table img:hover {
        transform: scale(1.1);
    }

    .btn-success, .btn-primary {
        border-radius: 5px;
        transition: 0.3s;
        background-color: #0277BD;
    }

    .btn-success:hover {
        background-color:rgb(85, 141, 173);
    }

    .btn-primary:hover {
        background-color:rgb(207, 44, 88);
    }

    .btn-danger:hover {
        background-color:rgb(41, 107, 212);
    }

    a {
        text-decoration: none;
        color: white;
    }

    a:hover {
        opacity: 0.8;
    }
    

    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Product List</h2>
        <a href="themsanpham.php" class="btn btn-success mb-3">Add product</a>
        <a href="doboi.php" class="btn btn-success mb-3">Go to sales page</a>
        <a href="user.php" class="btn btn-success mb-3">User Management</a>
        <table class="table table-bordered text-center">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Describe</th>
                    <th>Image</th>
                    <th>Select</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['product_id']; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo $row['category_id']; ?></td>
                    <td>$<?php echo number_format($row['price'], 2); ?></td>
                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                    <td>
                        <?php
                        $product_id = $row['product_id']; // Đã sửa lỗi
                        $images = $conn->query("SELECT image FROM images WHERE product_id = $product_id");

                        if ($images->num_rows > 0) {
                            while ($img = $images->fetch_assoc()) {
                                echo "<img src='uploads/" . htmlspecialchars($img['image']) . "' width='50' height='50' class='rounded'>";
                            }
                        } else {
                            echo "<img src='uploads/default.jpg' width='50' height='50' class='rounded'>";
                        }
                        ?>
                    </td>
                    <td>
                        <a href="themsanpham.php?id=<?php echo $row['product_id']; ?>" class="btn btn-primary btn-sm">Edit </a>
                        <a href="quanlisp.php?delete=<?php echo $row['product_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete??');">Delete </a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php $conn->close(); ?>
