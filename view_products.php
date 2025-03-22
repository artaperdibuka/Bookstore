<?php
// Filloni sesionin nëse nuk është aktiv
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Përfshini lidhjen me bazën e të dhënave
include_once 'components/connection.php';

// Kontrolloni nëse përdoruesi është i identifikuar
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
}

// Inicializoni mesazhet
$success_msg = [];
$warning_msg = [];

// Procesoni daljen
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Shtimi i produkteve në wishlist
if (isset($_POST['add_to_wishlist'])) {
    $id = uniqueid(); // Krijoni një ID unike për wishlist
    $product_id = $_POST['product_id'];

    // Kontrolloni nëse përdoruesi është i identifikuar
    if (empty($user_id)) {
        $warning_msg[] = 'Please log in to add products to your wishlist.';
    } else {
        // Kontrolloni nëse produkti është tashmë në wishlist
        $varify_wishlist = $conn->prepare("SELECT * FROM `wishlist` WHERE user_id = ? AND product_id = ?");
        $varify_wishlist->execute([$user_id, $product_id]);

        // Kontrolloni nëse produkti është në karrocë
        $cart_num = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ? AND product_id = ?");
        $cart_num->execute([$user_id, $product_id]);

        if ($varify_wishlist->rowCount() > 0) {
            $warning_msg[] = 'Product already exists in your wishlist';
        } else if ($cart_num->rowCount() > 0) {
            $warning_msg[] = 'Product already exists in your cart';
        } else {
            // Merrni çmimin e produktit
            $select_price = $conn->prepare("SELECT * FROM `products` WHERE id = ? LIMIT 1");
            $select_price->execute([$product_id]);
            $fetch_price = $select_price->fetch(PDO::FETCH_ASSOC);

            // Shtoni produktin në wishlist
            $insert_wishlist = $conn->prepare("INSERT INTO `wishlist`(id, user_id, product_id, price) VALUES (?, ?, ?, ?)");
            $insert_wishlist->execute([$id, $user_id, $product_id, $fetch_price['price']]);
            $success_msg[] = 'Product added to wishlist successfully';
        }
    }
}

if (isset($_POST['add_to_cart'])) {
    $id = uniqueid(); // Krijoni një ID unike për wishlist
    $product_id = $_POST['product_id'];

    // Kontrolloni nëse përdoruesi është i identifikuar
    if (empty($user_id)) {
        $warning_msg[] = 'Please log in to add products to your wishlist.';
    } else {
        $qty = $_POST['qty'];
        $qty = filter_var($qty, FILTER_SANITIZE_STRING);
        // Kontrolloni nëse produkti është tashmë në wishlist
        $varify_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ? AND product_id = ?");
        $varify_cart->execute([$user_id, $product_id]);

        $max_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
        $max_cart_items->execute([$user_id]);
        // Kontrolloni nëse produkti është në karrocë

        if ($varify_cart->rowCount() > 0) {
            $warning_msg[] = 'Product already exists in your wishlist';
        } else if ($max_cart_items->rowCount() > 20) {
            $warning_msg[] = 'cart is full';
        } else {
            // Merrni çmimin e produktit
            $select_price = $conn->prepare("SELECT * FROM `products` WHERE id = ? LIMIT 1");
            $select_price->execute([$product_id]);
            $fetch_price = $select_price->fetch(PDO::FETCH_ASSOC);

            // Shtoni produktin në wishlist
            $insert_cart = $conn->prepare("INSERT INTO `cart`(id, user_id, product_id, price, qty) VALUES (?, ?, ?, ? ,?)");
            $insert_cart->execute([$id, $user_id, $product_id, $fetch_price['price'], $qty]);
            $success_msg[] = 'Product added to cart successfully';
        }
    }
}

// Pagination
$limit = 8; // Numri i produkteve për faqe
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Merrni numrin e faqes nga URL
$offset = ($page - 1) * $limit; // Llogaritni offset për kërkesën SQL

// Merrni numrin total të produkteve
$total_products = $conn->query("SELECT COUNT(*) FROM `products`")->fetchColumn();
$total_pages = ceil($total_products / $limit); // Llogaritni numrin e faqeve

// Merrni produktet për faqen aktuale
$select_products = $conn->prepare("SELECT * FROM `products` LIMIT :limit OFFSET :offset");
$select_products->bindParam(':limit', $limit, PDO::PARAM_INT);
$select_products->bindParam(':offset', $offset, PDO::PARAM_INT);
$select_products->execute(); // Ekzekutoni kërkesën
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>BookstoreBuzuku - Products Page</title>
    <style type="text/css">
        <?php include 'style.css'; ?>
    </style>
</head>

<body>
    <?php include 'components/header.php'; ?>
    <div class="main">
        <div class="tittle2">
            <a href="home.php">Home</a><span>products</span>
        </div>

        <section class="products">
            <div class="box-container">
                <?php
                // Kontrolloni nëse $select_products është inicializuar dhe ekzekutuar
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
                                <p class="price">price $<?= $fetch_products['price']; ?></p>
                                <input type="number" name="qty" required min="1" value="1" max="99" maxlength="2" class="qty">
                            </div>
                            <a href="checkout.php?get_id=<?= $fetch_products['id']; ?>" class="btn">buy now</a>
                        </form>
                <?php
                    }
                } else {
                    echo '<p class="empty">No products added yet.</p>'; // Mesazhi nëse nuk ka produkte
                }
                ?>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1; ?>">Previous</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i; ?>" class="<?= ($page == $i) ? 'active' : ''; ?>"><?= $i; ?></a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1; ?>">Next</a>
                <?php endif; ?>
            </div>
        </section>

        <?php include 'components/footer.php'; ?>
    </div>

    <!-- JavaScript files -->
    <script>
        // Funksioni për të shfaqur mesazhet
        function showAlert(type, message) {
            Swal.fire({
                icon: type,
                title: type === 'success' ? 'Sukses!' : 'Kujdes!',
                text: message,
                confirmButtonText: 'Mirë'
            });
        }

        // Kontrolloni nëse ka mesazhe suksesi ose paralajmërimi
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
    <script src="contact-validation.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
    <script src="script.js"></script>
</body>

</html>