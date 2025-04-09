<?php
session_start();
include 'connect.php';

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Đã cập nhật thêm cột image để lấy ảnh từ DB (nếu có)
$sql = "SELECT product_id, name, category_id, price, description FROM products";
$result = $conn->query($sql);

$selected_category = isset($_GET['category']) ? $_GET['category'] : 'all';

// Xây dựng câu truy vấn SQL
$sql = "SELECT product_id, name, category_id, price, description FROM products";
$params = [];
$types = "";

if ($selected_category !== 'all') {
    $sql .= " WHERE category_id = ?";
    $params[] = $selected_category;
    $types .= "i";
}

// Chuẩn bị truy vấn SQL
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Lỗi chuẩn bị truy vấn: " . $conn->error);
}

// Gán tham số nếu có
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

// Thực thi truy vấn
$stmt->execute();
$result = $stmt->get_result();



// Initialize cart in session if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Logout functionality
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Lấy danh mục (Categories)
$categories = [];
$sql_categories = "SELECT * FROM category";
$categoriesResult = $conn->query($sql_categories);
if ($categoriesResult && $categoriesResult->num_rows > 0) {
    while ($row = $categoriesResult->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Xử lý các thao tác với giỏ hàng
if (isset($_POST['remove_from_cart'])) {
    $product_id = $_POST['product_id'];
    $_SESSION['cart'] = array_filter($_SESSION['cart'], function($item) use ($product_id) {
        return $item['id'] != $product_id;
    });
}

if (isset($_POST['update_quantity'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $product_id) {
            $item['quantity'] = $quantity;
            break;
        }
    }
}

if (isset($_POST['clear_cart'])) {
    $_SESSION['cart'] = [];
}

// Hàm tìm kiếm và lọc (nếu cần)
$search_keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
// Xây dựng truy vấn SQL tìm kiếm
$sql = "SELECT product_id, name, category_id, price, description FROM products";
if (!empty($search_keyword)) {
    $sql .= " WHERE name LIKE ?";
}

// Chuẩn bị câu lệnh SQL
$stmt = $conn->prepare($sql);

// Gán giá trị tìm kiếm nếu có
if (!empty($search_keyword)) {
    $search_param = "%$search_keyword%";
    $stmt->bind_param("s", $search_param);
}

$stmt->execute();
$result = $stmt->get_result();

// Tính tổng tiền giỏ hàng
// $cart_total = array_reduce($_SESSION['cart'], function($total, $item) {
//     return $total + ($item['price'] * $item['quantity']);
// }, 0);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cửa hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="doboi.css">   
</head>
<body>
    <div class="top-bar">
        <div class="logo"><strong>BTEC</strong></div>
        <div class="search_bar">
            <form method="get" action="">
                <input type="text" name="search" id="searchInput" placeholder="Tìm kiếm sản phẩm" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Search</button>
            </form>
        </div>

        <div class="cart-icon">
            <button type="button" class="btn btn-primary position-relative" data-bs-toggle="modal" data-bs-target="#cartModal">
                <i class="bi bi-cart"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-count">
                    <?php echo array_sum(array_column($_SESSION['cart'], 'quantity')); ?>
                </span>
            </button>
        </div>
    </div>

    <nav class="navbar navbar-expand-md navbar-light bg-light">
        <div class="container">
            <div class="navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#">Home</a></li>
                    <li class="nav-item">
                        <a class="nav-link" href="?logout=1">Logout </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <!-- Sidebar: Danh mục sản phẩm -->
            <aside class="col-md-3">
                <h3>Danh mục</h3>
                <ul class="list-group">
                    <li class="list-group-item <?php echo ($selected_category == 'all') ? 'active' : ''; ?>">
                        <a href="?category=all" class="text-decoration-none">All products</a>
                    </li>
                    <?php foreach ($categories as $category): ?>
                        <li class="list-group-item <?php echo ($selected_category == $category['category_id']) ? 'active' : ''; ?>">
                            <a href="?category=<?php echo $category['category_id']; ?>" class="text-decoration-none">
                                <?php echo htmlspecialchars($category['category_name']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>

                </ul>
            </aside>


            <!-- Main Content: Carousel -->
            <div class="col-md-9">
                <div id="productBanner" class="carousel slide mb-4" data-bs-ride="carousel">
                    <div class="carousel-indicators">
                        <button type="button" data-bs-target="#productBanner" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                        <button type="button" data-bs-target="#productBanner" data-bs-slide-to="1" aria-label="Slide 2"></button>
                        <button type="button" data-bs-target="#productBanner" data-bs-slide-to="2" aria-label="Slide 3"></button>
                        <button type="button" data-bs-target="#productBanner" data-bs-slide-to="3" aria-label="Slide 4"></button>
                    </div>
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <img src="kinhboii.jpg" class="d-block w-100" alt="Kính bơi">
                        </div>
                        <div class="carousel-item">
                            <img src="phaotayy.jpg" class="d-block w-100" alt="Phao tay">
                        </div>
                        <div class="carousel-item">
                            <img src="phaoboii.jpg" class="d-block w-100" alt="Áo bơi">
                        </div>
                        <div class="carousel-item">
                            <img src="muboi.jpg" class="d-block w-100" alt="Quần bơi">
                        </div>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#productBanner" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Trước</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#productBanner" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Tiếp</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Danh sách sản phẩm -->
        <!-- Main Content -->
        <div class="col-md-9">
        <h2>Danh sách sản phẩm</h2>
        <div class="row">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $product_id = $row['product_id'];

                    // Lấy ảnh đại diện của sản phẩm từ bảng images
                    $image_sql = "SELECT image FROM images WHERE product_id = ? LIMIT 1";
                    $stmt_image = $conn->prepare($image_sql);
                    $stmt_image->bind_param("i", $product_id);
                    $stmt_image->execute();
                    $image_result = $stmt_image->get_result();
                    $image_row = $image_result->fetch_assoc();

                    // Kiểm tra nếu có ảnh, nếu không dùng ảnh mặc định
                    $imagePath = !empty($image_row['image']) ? 'uploads/' . htmlspecialchars($image_row['image']) : 'uploads/default.jpg';

                    echo '
                    <div class="col-12 col-sm-6 col-md-4 mb-4 product-item">
                        <div class="card">
                            <img src="' . $imagePath . '" class="card-img-top" alt="' . htmlspecialchars($row["name"]) . '">
                            <div class="card-body">
                                <h5 class="card-title">' . htmlspecialchars($row["name"]) . '</h5>
                                <p class="card-text">Giá: <span class="product-price">' . number_format($row["price"], 0, ',', '.') . '</span> VND</p>
                                <p class="card-text description">' . nl2br(htmlspecialchars($row["description"])) . '</p>
                                <a href="product_detail.php?id=' . $row["product_id"] . '" class="btn btn-primary">Chi tiết</a>
                                <form action="addtoCart.php" method="POST">
                                    <input type="hidden" name="user_id" value="' . ($_SESSION['user_id'] ?? '') . '">
                                    <input type="hidden" name="product_id" value="' . $row['product_id'] . '">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-success add-to-cart-btn">Thêm vào giỏ</button>
                                </form>
                            </div>
                        </div>
                    </div>';

                    $stmt_image->close();
                }
            } else {
                echo '<p class="text-center text-danger">Không tìm thấy sản phẩm nào trong danh mục này.</p>';
            }
            ?>
        </div>
    </div>


    <!-- Script JS: đảm bảo chạy sau khi DOM load -->
    <script>
        document.addEventListener("DOMContentLoaded", function(){
            // Nếu có phần tử cần gán sự kiện, hãy kiểm tra tồn tại trước
            const allProductsBtn = document.getElementById('allProducts');
            if (allProductsBtn) {
                allProductsBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.querySelectorAll('.category-item').forEach(cat => {
                        cat.classList.remove('active');
                    });
                    // Giả sử filterByCategory là một hàm định nghĩa ở đâu đó
                    if (typeof filterByCategory === 'function') {
                        filterByCategory('all');
                    }
                });
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
