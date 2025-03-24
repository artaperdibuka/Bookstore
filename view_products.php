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

            $insert_wishlist = $conn->prepare("INSERT INTO `wishlist`(id, user_id, product_id, price) VALUES (?, ?, ?, ?)");
            $insert_wishlist->execute([$id, $user_id, $product_id, $fetch_price['price']]);
            $success_msg[] = 'Product added to wishlist successfully';
        }
    }
}

if (isset($_POST['add_to_cart'])) {
    $id = uniqueid();
    $product_id = filter_var($_POST['product_id'], FILTER_SANITIZE_NUMBER_INT);


    if (empty($user_id)) {
        $warning_msg[] = 'Please log in to add products to your wishlist.';
    } else {
        $qty = $_POST['qty'];
        $qty = filter_var($qty, FILTER_SANITIZE_STRING);

        $varify_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ? AND product_id = ?");
        $varify_cart->execute([$user_id, $product_id]);

        $max_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
        $max_cart_items->execute([$user_id]);


        if ($varify_cart->rowCount() > 0) {
            $warning_msg[] = 'Product already exists in your cart';
        } else if ($max_cart_items->rowCount() > 20) {
            $warning_msg[] = 'cart is full';
        } else {

            $select_price = $conn->prepare("SELECT * FROM `products` WHERE id = ? LIMIT 1");
            $select_price->execute([$product_id]);
            $fetch_price = $select_price->fetch(PDO::FETCH_ASSOC);


            $insert_cart = $conn->prepare("INSERT INTO `cart`(id, user_id, product_id, price, qty) VALUES (?, ?, ?, ? ,?)");
            $insert_cart->execute([$id, $user_id, $product_id, $fetch_price['price'], $qty]);
            $success_msg[] = 'Product added to cart successfully';
        }
    }
}


$limit = 8;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = max(0, ($page - 1) * $limit);



$total_products = $conn->query("SELECT COUNT(*) FROM `products`")->fetchColumn();
$total_pages = ($total_products > 0) ? ceil($total_products / $limit) : 1;

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


if (!empty($search)) {
    $whereConditions[] = "name LIKE :search";
}

$min_price = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float)$_GET['min_price'] : null;
$max_price = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float)$_GET['max_price'] : null;


$query = "SELECT *, 
         CASE 
             WHEN discount_price > 0 THEN discount_price 
             ELSE price 
         END AS final_price 
         FROM `products`";

$whereConditions = [];


if (!empty($search)) {
    $whereConditions[] = "name LIKE :search";
}

if ($min_price !== null && $max_price !== null) {
    $whereConditions[] = "(CASE WHEN discount_price > 0 THEN discount_price ELSE price END) BETWEEN :min_price AND :max_price";
} elseif ($min_price !== null) {
    $whereConditions[] = "(CASE WHEN discount_price > 0 THEN discount_price ELSE price END) >= :min_price";
} elseif ($max_price !== null) {
    $whereConditions[] = "(CASE WHEN discount_price > 0 THEN discount_price ELSE price END) <= :max_price";
}


switch ($sort) {
    case 'newest':
        $orderBy = "ORDER BY created_at DESC";
        break;
    case 'low_to_high':
        $orderBy = "ORDER BY (CASE WHEN discount_price > 0 THEN discount_price ELSE price END) ASC";
        break;
    case 'high_to_low':
        $orderBy = "ORDER BY (CASE WHEN discount_price > 0 THEN discount_price ELSE price END) DESC";
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


$query .= " $orderBy LIMIT :limit OFFSET :offset";
$select_products = $conn->prepare($query);

if (!empty($search)) {
    $searchTerm = "%$search%";
    $select_products->bindParam(':search', $searchTerm, PDO::PARAM_STR);
}

if ($min_price !== null) {
    $select_products->bindParam(':min_price', $min_price, PDO::PARAM_STR);
}

if ($max_price !== null) {
    $select_products->bindParam(':max_price', $max_price, PDO::PARAM_STR);
}

$select_products->bindParam(':limit', $limit, PDO::PARAM_INT);
$select_products->bindParam(':offset', $offset, PDO::PARAM_INT);
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
            <a href="home.php">Home</a><span>Books</span>
        </div>

        <div class="filter-container">
           
        <form action="" method="GET" class="sort-form">
    <select name="sort" onchange="this.form.submit()">
        <option value="">Sort by</option>
        <option value="newest" <?= (isset($_GET['sort']) && $_GET['sort'] == 'newest') ? 'selected' : ''; ?>>Newest</option>
        <option value="low_to_high" <?= (isset($_GET['sort']) && $_GET['sort'] == 'low_to_high') ? 'selected' : ''; ?>>Price: Low to High</option>
        <option value="high_to_low" <?= (isset($_GET['sort']) && $_GET['sort'] == 'high_to_low') ? 'selected' : ''; ?>>Price: High to Low</option>
        <option value="discount" <?= (isset($_GET['sort']) && $_GET['sort'] == 'discount') ? 'selected' : ''; ?>>Biggest Discounts</option>
    </select>
</form>


            <form action="" method="GET" class="price-filter-form">
                <input type="number" name="min_price" placeholder="Min" value="<?= isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : '' ?>">
                <input type="number" name="max_price" placeholder="Max" value="<?= isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : '' ?>">
                <button type="submit">Filter</button>
            </form>

           
            <form action="" method="GET" class="search-form">
                <input type="text" name="search" placeholder="Search books..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            </form>
        </div>


        <section class="products">
            <div class="box-container">
                <?php

                if ($select_products && $select_products->rowCount() > 0) {
                    while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) {
                ?>
                        <form action="" method="post" class="box">
                            <img src="image/<?= $fetch_products['image']; ?>" class="img" alt="Product Image">
                            <div class="button">
                                <button type="submit" name="add_to_cart"><i class="bx bx-cart"></i></button>
                                <button type="submit" name="add_to_wishlist"><i class="bx bx-heart"></i></button>
                                <a href="view_page.php?pid=<?php echo $fetch_products['id']; ?>" class="bx bxs-show"></a>
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

                                <input type="number" name="qty" required min="1" value="1" max="99" maxlength="2" class="qty">
                            </div>
                            <a href="checkout.php?get_id=<?= $fetch_products['id']; ?>" class="btn">buy now</a>
                        </form>
                <?php
                    }
                } else {
                    echo '<p class="empty">No products added yet.</p>';
                }
                ?>
            </div>
            <?php
            $countQuery = "SELECT COUNT(*) FROM `products`";
            $whereConditions = [];

          
            if (!empty($search)) {
                $whereConditions[] = "name LIKE :search";
            }

            if ($min_price !== null && $max_price !== null) {
                $whereConditions[] = "(CASE WHEN discount_price > 0 THEN discount_price ELSE price END) BETWEEN :min_price AND :max_price";
            } elseif ($min_price !== null) {
                $whereConditions[] = "(CASE WHEN discount_price > 0 THEN discount_price ELSE price END) >= :min_price";
            } elseif ($max_price !== null) {
                $whereConditions[] = "(CASE WHEN discount_price > 0 THEN discount_price ELSE price END) <= :max_price";
            }

         
            if (!empty($whereConditions)) {
                $countQuery .= " WHERE " . implode(" AND ", $whereConditions);
            }

            $countStmt = $conn->prepare($countQuery);

            if (!empty($search)) {
                $searchTerm = "%$search%";
                $countStmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);
            }

            if ($min_price !== null) {
                $countStmt->bindParam(':min_price', $min_price, PDO::PARAM_STR);
            }

            if ($max_price !== null) {
                $countStmt->bindParam(':max_price', $max_price, PDO::PARAM_STR);
            }

            $countStmt->execute();
            $total_products = $countStmt->fetchColumn();

            $total_pages = ($total_products > 0) ? ceil($total_products / $limit) : 1;
            if ($page > $total_pages) {
                $page = 1;
            }
            $offset = max(0, ($page - 1) * $limit);


            ?>

            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1; ?><?= !empty($search) ? '&search=' . htmlspecialchars($search) : '' ?><?= !empty($sort) ? '&sort=' . htmlspecialchars($sort) : '' ?><?= $min_price !== null ? '&min_price=' . htmlspecialchars($min_price) : '' ?><?= $max_price !== null ? '&max_price=' . htmlspecialchars($max_price) : '' ?>">Previous</a>
                <?php endif; ?>

                <?php
                
                $start = max(1, $page - 1);
                $end = min($total_pages, $page + 1);

                
                if ($page == 1) {
                    $start = 1;
                    $end = min(3, $total_pages);
                }

          
                if ($page == $total_pages) {
                    $start = max(1, $total_pages - 2);
                    $end = $total_pages;
                }

                for ($i = $start; $i <= $end; $i++): ?>
                    <a href="?page=<?= $i; ?><?= !empty($search) ? '&search=' . htmlspecialchars($search) : '' ?><?= !empty($sort) ? '&sort=' . htmlspecialchars($sort) : '' ?><?= $min_price !== null ? '&min_price=' . htmlspecialchars($min_price) : '' ?><?= $max_price !== null ? '&max_price=' . htmlspecialchars($max_price) : '' ?>" class="<?= ($page == $i) ? 'active' : ''; ?>"><?= $i; ?></a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1; ?><?= !empty($search) ? '&search=' . htmlspecialchars($search) : '' ?><?= !empty($sort) ? '&sort=' . htmlspecialchars($sort) : '' ?><?= $min_price !== null ? '&min_price=' . htmlspecialchars($min_price) : '' ?><?= $max_price !== null ? '&max_price=' . htmlspecialchars($max_price) : '' ?>">Next</a>
                <?php endif; ?>
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