<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


include_once 'components/connection.php';


if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
}

$success_msg = [];
$warning_msg = [];


if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}

if (isset($_POST['add_to_wishlist'])) {
    $id = uniqid();
    $product_id = $_POST['product_id'];


    if (empty($user_id)) {
        $warning_msg[] = 'Please log in to add products to your wishlist.';
    } else {

        $varify_wishlist = $conn->prepare("SELECT * FROM `wishlist` WHERE user_id = ? AND product_id = ?");
        $varify_wishlist->execute([$user_id, $product_id]);


        $cart_num = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ? AND product_id = ?");
        $cart_num->execute([$user_id, $product_id]);

        if ($varify_wishlist->rowCount() > 0) {
            $warning_msg[] = 'Product already exists in your wishlist';
        } else if ($cart_num->rowCount() > 0) {
            $warning_msg[] = 'Product already exists in your cart';
        } else {

            $select_price = $conn->prepare("SELECT * FROM `products` WHERE id = ? LIMIT 1");
            $select_price->execute([$product_id]);
            $fetch_price = $select_price->fetch(PDO::FETCH_ASSOC);

            $insert_wishlist = $conn->prepare("INSERT INTO `wishlist`(user_id, product_id, price) VALUES (?, ?, ?)");
            $insert_wishlist->execute([$user_id, $product_id, $fetch_price['price']]);

            $success_msg[] = 'Product added to wishlist successfully';
        }
    }
}

if (isset($_POST['add_to_cart'])) {
    $id = uniqueid();
    $product_id = filter_var($_POST['product_id'], FILTER_SANITIZE_NUMBER_INT);
    $qty = filter_var($_POST['qty'], FILTER_SANITIZE_NUMBER_INT);

    if (empty($user_id)) {
        $warning_msg[] = 'Please log in to add products to your cart.';
    } else {
        $check_stock = $conn->prepare("SELECT quantity FROM products WHERE id = ?");
        $check_stock->execute([$product_id]);
        $product_stock = $check_stock->fetch(PDO::FETCH_ASSOC);

        if (!$product_stock) {
            $warning_msg[] = 'Product not found!';
        } elseif ($product_stock['quantity'] <= 0) {
            $warning_msg[] = 'This product is out of stock!';
        } elseif ($qty > $product_stock['quantity']) {
            $warning_msg[] = 'Only ' . $product_stock['quantity'] . ' items available!';
        } else {
            $verify_cart = $conn->prepare("SELECT id, qty FROM `cart` WHERE user_id = ? AND product_id = ?");
            $verify_cart->execute([$user_id, $product_id]);
            $existing_item = $verify_cart->fetch(PDO::FETCH_ASSOC);

            if ($existing_item) {

                $new_total_qty = $existing_item['qty'] + $qty;

                if ($new_total_qty > $product_stock['quantity']) {
                    $warning_msg[] = 'Cannot add more than available stock!';
                } else {
                    $update_cart = $conn->prepare("UPDATE `cart` SET qty = ? WHERE id = ?");
                    if ($update_cart->execute([$new_total_qty, $existing_item['id']])) {
                        $success_msg[] = 'Cart quantity updated successfully';
                    }
                }
            } else {

                $cart_count = $conn->prepare("SELECT COUNT(*) FROM `cart` WHERE user_id = ?");
                $cart_count->execute([$user_id]);

                if ($cart_count->fetchColumn() >= 20) {
                    $warning_msg[] = 'Your cart is full (max 20 items)';
                } else {

                    $select_price = $conn->prepare("SELECT price FROM `products` WHERE id = ? LIMIT 1");
                    $select_price->execute([$product_id]);
                    $product_price = $select_price->fetchColumn();

                    if ($product_price === false) {
                        $warning_msg[] = 'Could not retrieve product price';
                    } else {

                        $insert_cart = $conn->prepare("INSERT INTO `cart`(id, user_id, product_id, price, qty) VALUES (?, ?, ?, ?, ?)");
                        if ($insert_cart->execute([$id, $user_id, $product_id, $product_price, $qty])) {
                            $success_msg[] = 'Product added to cart successfully';
                        } else {
                            $warning_msg[] = 'Failed to add product to cart';
                        }
                    }
                }
            }
        }
    }
}

$limit = 8;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = max(0, ($page - 1) * $limit);

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';
$min_price = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float)$_GET['min_price'] : null;
$max_price = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float)$_GET['max_price'] : null;

$query = "SELECT *, 
          CASE 
              WHEN discount_price > 0 THEN discount_price 
              ELSE price 
          END AS final_price 
          FROM `products`";

$whereConditions = [];
$params = [];
$category_name = '';

if (isset($_GET['category_id']) && is_numeric($_GET['category_id'])) {
    $category_id = (int)$_GET['category_id'];
    $whereConditions[] = "category_id = :category_id";
    $params[':category_id'] = $category_id;

    $stmt = $conn->prepare("SELECT name FROM categories WHERE id = ?");
    $stmt->execute([$category_id]);
    $category_data = $stmt->fetch(PDO::FETCH_ASSOC);
    $category_name = $category_data ? $category_data['name'] : 'Unknown Category';
}

if (!empty($search)) {
    $whereConditions[] = "name LIKE :search";
    $params[':search'] = "%$search%";
}

if ($min_price !== null && $max_price !== null) {
    $whereConditions[] = "(CASE WHEN discount_price > 0 THEN discount_price ELSE price END) BETWEEN :min_price AND :max_price";
    $params[':min_price'] = $min_price;
    $params[':max_price'] = $max_price;
} elseif ($min_price !== null) {
    $whereConditions[] = "(CASE WHEN discount_price > 0 THEN discount_price ELSE price END) >= :min_price";
    $params[':min_price'] = $min_price;
} elseif ($max_price !== null) {
    $whereConditions[] = "(CASE WHEN discount_price > 0 THEN discount_price ELSE price END) <= :max_price";
    $params[':max_price'] = $max_price;
}

switch ($sort) {
    case 'newest':
        $orderBy = "ORDER BY created_at DESC";
        break;
    case 'low_to_high':
        $orderBy = "ORDER BY final_price ASC";
        break;
    case 'high_to_low':
        $orderBy = "ORDER BY final_price DESC";
        break;
    case 'discount':
        $whereConditions[] = "discount_price > 0";
        $orderBy = "ORDER BY (price - discount_price) DESC";
        break;
    default:
        $orderBy = "ORDER BY id DESC";
        break;
}

if (!empty($whereConditions)) {
    $query .= " WHERE " . implode(" AND ", $whereConditions);
}

$countQuery = "SELECT COUNT(*) FROM `products`";
if (!empty($whereConditions)) {
    $countQuery .= " WHERE " . implode(" AND ", $whereConditions);
}

$stmt = $conn->prepare($countQuery);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$total_products = $stmt->fetchColumn();
$total_pages = ($total_products > 0) ? ceil($total_products / $limit) : 1;

$query .= " $orderBy LIMIT :limit OFFSET :offset";
$select_products = $conn->prepare($query);

foreach ($params as $key => $value) {
    $select_products->bindValue($key, $value);
}

$select_products->bindValue(':limit', $limit, PDO::PARAM_INT);
$select_products->bindValue(':offset', $offset, PDO::PARAM_INT);
$select_products->execute();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>BookstoreBuzuku - Books Page</title>
    <style type="text/css">
        <?php include 'style.css'; ?>
    </style>
</head>

<body>
    <?php include 'components/header.php'; ?>
    <div class="main">
        <div class="tittle2">
            <a href="home.php">Home</a>
            <span>
                <?php if (isset($_GET['category_id'])): ?>
                    <a href="view_products.php">Books</a>
                <?php else: ?>
                    Books
                <?php endif; ?>
            </span>
            <?php if (isset($_GET['category_id']) && !empty($category_name)): ?>
                <span><?= htmlspecialchars($category_name) ?></span>
            <?php endif; ?>
        </div>


        <div class="filter-container">

            <form action="" method="GET" class="sort-form">

                <?php if (isset($_GET['category_id']) && is_numeric($_GET['category_id'])): ?>
                    <input type="hidden" name="category_id" value="<?= (int)$_GET['category_id'] ?>">
                <?php endif; ?>

                <?php if (!empty($_GET['search'])): ?>
                    <input type="hidden" name="search" value="<?= htmlspecialchars($_GET['search']) ?>">
                <?php endif; ?>
                <?php if (isset($_GET['min_price'])): ?>
                    <input type="hidden" name="min_price" value="<?= htmlspecialchars($_GET['min_price']) ?>">
                <?php endif; ?>
                <?php if (isset($_GET['max_price'])): ?>
                    <input type="hidden" name="max_price" value="<?= htmlspecialchars($_GET['max_price']) ?>">
                <?php endif; ?>

                <select name="sort" onchange="this.form.submit()">
                    <option value="">Sort by</option>
                    <option value="newest" <?= (isset($_GET['sort']) && $_GET['sort'] == 'newest') ? 'selected' : ''; ?>>Newest</option>
                    <option value="low_to_high" <?= (isset($_GET['sort']) && $_GET['sort'] == 'low_to_high') ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="high_to_low" <?= (isset($_GET['sort']) && $_GET['sort'] == 'high_to_low') ? 'selected' : ''; ?>>Price: High to Low</option>
                    <option value="discount" <?= (isset($_GET['sort']) && $_GET['sort'] == 'discount') ? 'selected' : ''; ?>>Biggest Discounts</option>
                </select>
            </form>


            <form action="" method="GET" class="price-filter-form">

                <?php if (isset($_GET['category_id']) && is_numeric($_GET['category_id'])): ?>
                    <input type="hidden" name="category_id" value="<?= (int)$_GET['category_id'] ?>">
                <?php endif; ?>

                <?php if (!empty($_GET['search'])): ?>
                    <input type="hidden" name="search" value="<?= htmlspecialchars($_GET['search']) ?>">
                <?php endif; ?>

                <?php if (!empty($_GET['sort'])): ?>
                    <input type="hidden" name="sort" value="<?= htmlspecialchars($_GET['sort']) ?>">
                <?php endif; ?>

                <input type="number" name="min_price" placeholder="Min" value="<?= isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : '' ?>">
                <input type="number" name="max_price" placeholder="Max" value="<?= isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : '' ?>">
                <button type="submit">Filter</button>
            </form>

            <form action="" method="GET" class="search-form">
                <?php if (isset($_GET['category_id']) && is_numeric($_GET['category_id'])): ?>
                    <input type="hidden" name="category_id" value="<?= (int)$_GET['category_id'] ?>">
                <?php endif; ?>

                <?php if (!empty($_GET['sort'])): ?>
                    <input type="hidden" name="sort" value="<?= htmlspecialchars($_GET['sort']) ?>">
                <?php endif; ?>

                <?php if (isset($_GET['min_price']) && $_GET['min_price'] !== ''): ?>
                    <input type="hidden" name="min_price" value="<?= htmlspecialchars($_GET['min_price']) ?>">
                <?php endif; ?>
                <?php if (isset($_GET['max_price']) && $_GET['max_price'] !== ''): ?>
                    <input type="hidden" name="max_price" value="<?= htmlspecialchars($_GET['max_price']) ?>">
                <?php endif; ?>

                <input type="text" name="search" placeholder="Search books..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            </form>
        </div>


        <section class="products">
            <div class="box-container">
                <?php
                if ($select_products && $select_products->rowCount() > 0) {
                    while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) {
                        $quantity = $fetch_products['quantity'];
                        $fewItemsLeft = $quantity <= 3 && $quantity > 0;
                        $outOfStock = $quantity <= 0;
                ?>
                        <form action="" method="post" class="box">
                            <?php if ($outOfStock): ?>
                                <div class="out-of-stock-label">OUT OF STOCK</div>
                            <?php elseif ($fewItemsLeft): ?>
                                <div class="few-items">Only <?= $quantity ?> left!</div>
                            <?php endif; ?>

                            <img src="image/<?= $fetch_products['image']; ?>" class="img" alt="Product Image">

                            <div class="button">
                                <button type="submit" name="add_to_cart" <?= $outOfStock ? 'disabled' : '' ?>>
                                    <i class="bx bx-cart"></i>
                                </button>
                                <button type="submit" name="add_to_wishlist">
                                    <i class="bx bx-heart"></i>
                                </button>
                                <a href="view_page.php?pid=<?= $fetch_products['id']; ?>" class="bx bxs-show"></a>
                            </div>

                            <h3 class="name"><?= $fetch_products['name']; ?></h3>
                            <input type="hidden" name="product_id" value="<?= $fetch_products['id']; ?>">

                            <div class="flex">
                                <p class="price">
                                    <?php if ($fetch_products['discount_price'] > 0): ?>
                                        <span class="old-price">$<?= number_format($fetch_products['price'], 2); ?></span>
                                        <span class="new-price">$<?= number_format($fetch_products['discount_price'], 2); ?></span>
                                    <?php else: ?>
                                        <span>$<?= number_format($fetch_products['price'], 2); ?></span>
                                    <?php endif; ?>
                                </p>

                                <?php if (!$outOfStock): ?>
                                    <input type="number" name="qty" required min="1"
                                        value="1" max="<?= $quantity ?>"
                                        maxlength="2" class="qty">
                                <?php endif; ?>
                            </div>

                            <a href="checkout.php?get_id=<?= $fetch_products['id']; ?>" class="btn" <?= $outOfStock ? 'style="pointer-events: none; opacity: 0.5;"' : '' ?>>buy now</a>
                        </form>
                <?php
                    }
                }
                ?>
            </div>
            <?php

            $countQuery = "SELECT COUNT(*) FROM `products`";
            $whereConditions = [];
            $params = [];

            if (!empty($search)) {
                $whereConditions[] = "name LIKE :search";
                $params[':search'] = "%$search%";
            }

            if (isset($_GET['category_id']) && is_numeric($_GET['category_id'])) {
                $whereConditions[] = "category_id = :category_id";
                $params[':category_id'] = (int)$_GET['category_id'];
            }

            if ($min_price !== null && $max_price !== null) {
                $whereConditions[] = "(CASE WHEN discount_price > 0 THEN discount_price ELSE price END) BETWEEN :min_price AND :max_price";
                $params[':min_price'] = $min_price;
                $params[':max_price'] = $max_price;
            } elseif ($min_price !== null) {
                $whereConditions[] = "(CASE WHEN discount_price > 0 THEN discount_price ELSE price END) >= :min_price";
                $params[':min_price'] = $min_price;
            } elseif ($max_price !== null) {
                $whereConditions[] = "(CASE WHEN discount_price > 0 THEN discount_price ELSE price END) <= :max_price";
                $params[':max_price'] = $max_price;
            }

            if (!empty($whereConditions)) {
                $countQuery .= " WHERE " . implode(" AND ", $whereConditions);
            }

            $countStmt = $conn->prepare($countQuery);
            foreach ($params as $key => $value) {
                $countStmt->bindValue($key, $value);
            }
            $countStmt->execute();
            $total_products = $countStmt->fetchColumn();


            $total_pages = max(1, ceil($total_products / $limit));
            if ($page > $total_pages) {
                $page = 1;
            }
            $offset = max(0, ($page - 1) * $limit);
            ?>

            <div class="pagination">
                <?php
                $queryString = '';
                if (!empty($search)) $queryString .= '&search=' . urlencode($search);
                if (!empty($sort)) $queryString .= '&sort=' . urlencode($sort);
                if ($min_price !== null) $queryString .= '&min_price=' . urlencode($min_price);
                if ($max_price !== null) $queryString .= '&max_price=' . urlencode($max_price);
                if (!empty($_GET['category_id'])) $queryString .= '&category_id=' . (int)$_GET['category_id'];


                if ($total_pages > 1) {

                    if ($page > 1) {
                        echo '<a href="?page=' . ($page - 1) . $queryString . '">Previous</a>';
                    }


                    $start = max(1, $page - 2);
                    $end = min($total_pages, $page + 2);


                    if ($page <= 3) {
                        $end = min(5, $total_pages);
                    } elseif ($page >= $total_pages - 2) {
                        $start = max(1, $total_pages - 4);
                    }


                    if ($start > 1) {
                        echo '<a href="?page=1' . $queryString . '">1</a>';
                        if ($start > 2) echo '<span>...</span>';
                    }


                    for ($i = $start; $i <= $end; $i++) {
                        echo '<a href="?page=' . $i . $queryString . '"' . ($page == $i ? ' class="active"' : '') . '>' . $i . '</a>';
                    }


                    if ($end < $total_pages) {
                        if ($end < $total_pages - 1) echo '<span>...</span>';
                        echo '<a href="?page=' . $total_pages . $queryString . '">' . $total_pages . '</a>';
                    }


                    if ($page < $total_pages) {
                        echo '<a href="?page=' . ($page + 1) . $queryString . '">Next</a>';
                    }
                }
                ?>
            </div>
        </section>

        <?php include 'components/footer.php'; ?>
    </div>


    <script>
        function showAlert(type, message) {
            Swal.fire({
                icon: type,
                title: type === 'success' ? 'Sukses!' : 'Kujdes!',
                text: message,
                confirmButtonText: 'Mirë'
            });
        }

        <?php if (!empty($success_msg)): ?>
            <?php foreach ($success_msg as $msg): ?>
                showAlert('success', '<?php echo $msg; ?>');
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!empty($warning_msg)): ?>
            <?php foreach ($warning_msg as $msg): ?>
                showAlert('warning', '<?php echo $msg; ?>');
            <?php endforeach; ?>
        <?php endif; ?>
    </script>
    <script>
        document.querySelector('.search-form input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.form.submit();
            }
        });
    </script>
    <script src="contact-validation.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
    <script src="script.js"></script>
</body>

</html>