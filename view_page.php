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

if (isset($_POST['add_to_wishlist']) && !isset($_POST['add_to_cart'])) {
    $id = uniqueid();
    $product_id = $_POST['product_id'];

    if (empty($user_id)) {
        $warning_msg[] = 'Please log in to add products to your wishlist.';
    } else {
        $verify_wishlist = $conn->prepare("SELECT * FROM `wishlist` WHERE user_id = ? AND product_id = ?");
        $verify_wishlist->execute([$user_id, $product_id]);


        $cart_num = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ? AND product_id = ?");
        $cart_num->execute([$user_id, $product_id]);

        if ($verify_wishlist->rowCount() > 0) {
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

if (isset($_POST['add_to_cart']) && !isset($_POST['add_to_wishlist'])) {
    $id = uniqueid();
    $product_id = $_POST['product_id'];

    if (empty($user_id)) {
        $warning_msg[] = 'Please log in to add products to your cart.';
    } else {
        $qty = $_POST['qty'];
        $qty = filter_var($qty, FILTER_SANITIZE_STRING);

        $verify_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ? AND product_id = ?");
        $verify_cart->execute([$user_id, $product_id]);

        $max_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
        $max_cart_items->execute([$user_id]);



        if ($verify_cart->rowCount() > 0) {
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>BookstoreBuzuku - Product Detail Page</title>
    <style type="text/css">
        <?php include 'style.css'; ?>
        .button-group {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

    </style>
</head>

<body>
    <?php include 'components/header.php'; ?>
    <div class="main">
        <div class="tittle2">
            <a href="home.php">Home</a><span>product detail</span>
        </div>

        <section class="view_page">
            <?php
            if (isset($_GET['pid'])) {
                $pid = $_GET['pid'];
                $select_products = $conn->prepare("SELECT * FROM `products` WHERE id = '$pid'");
                $select_products->execute();
                if ($select_products->rowCount() > 0) {
                    while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) {
                        $outOfStock = $fetch_products['quantity'] <= 0;

            ?>
                        <form method="post">
                            <img src="image/<?php echo $fetch_products['image']; ?>" alt="product image">
                            <div class="detail">
                                <div class="price">$<?php echo $fetch_products['price']; ?>/-</div>
                                <div class="name"><?php echo $fetch_products['name']; ?></div>
                                <div class="detail">
                                    <p>
                                        Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.
                                    </p>
                                </div>
                                <div class="button-group">
                                    <!-- Butoni i Wishlist -->
                                    <button type="submit" name="add_to_wishlist" class="btn wishlist-btn">
                                        <i class="bx bx-heart"></i> Wishlist
                                    </button>

                                    <!-- Butoni i Cart -->
                                    <button type="submit" name="add_to_cart" class="btn cart-btn" <?= $outOfStock ? 'disabled' : '' ?>>
                                        <i class="bx bx-cart"></i> <?= $outOfStock ? 'Out of Stock' : 'Cart' ?>
                                    </button>
                                </div>
                            </div>

                        </form>
            <?php
                    }
                }
            }
            ?>

        </section>

        <?php include 'components/footer.php'; ?>
    </div>


    <script>
        function showAlert(type, message) {
            Swal.fire({
                icon: type,
                title: type === 'success' ? 'Success!' : 'Warning!',
                text: message,
                confirmButtonText: 'ok'
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
    <script src="contact-validation.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
    <script src="script.js"></script>