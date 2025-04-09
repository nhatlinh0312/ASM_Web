<?php
include 'connect.php';

$message = isset($_GET['message']) ? urldecode($_GET['message']) : "";
$categories = $conn->query("SELECT * FROM category");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $price = str_replace(".", "", $_POST['price']);
    $category_id = (int)$_POST['category_id'];
    $description = trim($_POST['description']);

    if (empty($name) || empty($price) || !is_numeric($price) || $category_id <= 0 || empty($description)) {
        echo "<div class='alert alert-danger'>Please fill in all information!</div>";
    } elseif (empty($_FILES['image']['name'])) {
        echo "<div class='alert alert-danger'>Please select product image!</div>";
    } else {
        $upload_dir = "uploads/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $image_upload_success = false;
        $image_name = "";

        if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
            if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                echo "<div class='alert alert-danger'>Image is too large! Maximum size is 5MB.</div>";
            } else {
                $image_name = time() . '_' . basename($_FILES['image']['name']);
                $target_path = $upload_dir . $image_name;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                    $image_upload_success = true;
                }
            }
        }

        if ($image_upload_success) {
            // Thêm sản phẩm vào bảng `products`
            $sql = "INSERT INTO products (name, category_id, price, description) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sids", $name, $category_id, $price, $description);

            if ($stmt->execute()) {
                $product_id = $stmt->insert_id; // Lấy ID sản phẩm vừa thêm
                $stmt->close();

                // Thêm ảnh vào bảng `images`
                $sql_image = "INSERT INTO images (product_id, image) VALUES (?, ?)";
                $stmt_image = $conn->prepare($sql_image);
                $stmt_image->bind_param("is", $product_id, $image_name);

                if ($stmt_image->execute()) {
                    header("Location: themsanpham.php?message=" . urlencode("<div class='alert alert-success'>Product added successfully!</div>"));
                    exit();
                } else {
                    echo "<div class='alert alert-danger'>Error adding photo!</div>";
                }
                $stmt_image->close();
            } else {
                echo "<div class='alert alert-danger'>Error adding product!</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Error uploading image!</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Products </title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #EAF4FC;
        color: #333;
    }
    .container {
        background: white;
        border-radius: 8px;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        padding: 20px;
        margin-top: 30px;
        max-width: 600px;
    }
    h2 {
        color: #0072B1;
        text-align: center;
    }
    .form-group label {
        font-weight: bold;
        color: #005A9E;
    }
    .form-control {
        border: 1px solid #0096D6;
        border-radius: 5px;
        padding: 8px;
    }
    .btn-primary {
        background-color: #0072B1;
        border: none;
        padding: 10px 15px;
        font-size: 16px;
        width: 100%;
        transition: 0.3s;
    }
    .btn-primary:hover {
        background-color: #005A9E;
    }
    .btn-secondary {
        background-color: #EAF4FC;
        color: #0072B1;
        border: 1px solid #0072B1;
        padding: 10px 15px;
        display: block;
        text-align: center;
        text-decoration: none;
        width: 100%;
        margin-top: 10px;
        font-weight: bold;
    }
    .btn-secondary:hover {
        background-color: #C7E3FA;
    }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mt-5">Add product</h2>
        <form method="POST" enctype="multipart/form-data" class="mt-3">
            <div class="form-group">
                <label>Product Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category_id" class="form-control" required>
                    <option value="">Select category</option>
                    <?php while ($category = $categories->fetch_assoc()) { ?>
                    <option value="<?php echo $category['category_id']; ?>">
                        <?php echo htmlspecialchars($category['category_name']); ?>
                    </option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <label>Price </label>
                <input type="number" step="0.01" name="price" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Describe</label>
                <textarea name="description" class="form-control" required></textarea>
            </div>
            <div class="form-group">
                <label>Product photo</label>
                <input type="file" name="image" class="form-control" required>
            </div>
            <button type="submit" name="add_product" class="btn btn-primary">Add product</button>
        </form>
        <a href="quanlisp.php" class="btn btn-secondary mt-3">View product</a>
    </div>
</body>
</html>
