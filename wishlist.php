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




if (isset($_POST['delete_item'])) {
    $wishlist_id = $_POST['wishlist_id'];
    $wishlist_id = filter_var($wishlist_id, FILTER_SANITIZE_STRING);

    $varify_delete_items = $conn->prepare("SELECT * FROM `wishlist` WHERE id = ?");
    $varify_delete_items->execute([$wishlist_id]);

    if ($varify_delete_items->rowCount() > 0) {
        $delete_item = $conn->prepare("DELETE FROM `wishlist` WHERE id = ?");
        $delete_item->execute([$wishlist_id]);
        $success_msg[] = 'Product deleted from wishlist successfully';
    } else {
        $warning_msg[] = 'Product in wishlist already deleted';
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
    <title>BookstoreBuzuku - Wishlist Page</title>
    <style type="text/css">
        <?php include 'style.css'; ?>
    </style>
</head>

<body>
    <?php include 'components/header.php'; ?>
    <div class="main">
        <div class="banner">
            <h1> My Wishlist</h1>
        </div>
        <div class="tittle2">
            <a href="home.php">Home</a><span>wishlist</span>
        </div>

        <section class="products">
            <h1 class="title">Products in your wishlist</h1>
            <div class="box-container">
                <?php
                $grand_total = 0;
                $select_wishlist = $conn->prepare("SELECT * FROM `wishlist` WHERE user_id = ?");
                $select_wishlist->execute([$user_id]);

                if ($select_wishlist->rowCount() > 0) {
                    while ($fetch_wishlist = $select_wishlist->fetch(PDO::FETCH_ASSOC)) {
                        $select_products = $conn->prepare("SELECT * FROM `products` WHERE id = ? LIMIT 1");
                        $select_products->execute([$fetch_wishlist['product_id']]);

                        if ($select_products->rowCount() > 0) {
                            $fetch_products = $select_products->fetch(PDO::FETCH_ASSOC);

                            $stock_class = ($fetch_products['quantity'] <= 0) ? 'out-of-stock' : '';
                ?>
                            <form method="post" action="" class="box <?= $stock_class ?>">
                                <input type="hidden" name="wishlist_id" value="<?= $fetch_wishlist['id']; ?>">
                                <input type="hidden" name="product_id" value="<?= $fetch_products['id']; ?>">

                                <img class="img" src="image/<?= $fetch_products['image'] ?>">

                                <div class="button">
                                    <?php if ($fetch_products['quantity'] > 0): ?>
                                        <button type="submit" name="add_to_cart">
                                            <i class="bx bx-cart"></i>
                                        </button>
                                    <?php else: ?>
                                        <button type="submit" name="add_to_cart" disabled>
                                            <i class="bx bx-cart"></i>
                                        </button>
                                    <?php endif; ?>


                                    <a href="view_page.php?pid=<?= $fetch_products['id']; ?>" class="bx bxs-show"></a>
                                    <button type="submit" name="delete_item" onclick="return confirm('Delete this item?')">
                                        <i class="bx bx-x"></i>
                                    </button>
                                </div>

                                <h3 class="name"><?= $fetch_products['name']; ?></h3>

                                <div class="flex">
                                    <p class="price">price $<?= $fetch_products['price']; ?>/-</p>

                                    <?php if ($fetch_products['quantity'] > 0): ?>
                                        <div class="quantity-control">
                                            <label for="qty_<?= $fetch_products['id'] ?>">Qty:</label>
                                            <input type="number" id="qty_<?= $fetch_products['id'] ?>"
                                                name="qty" min="1" max="<?= $fetch_products['quantity'] ?>"
                                                value="1" class="qty">
                                        </div>

                                    <?php endif; ?>
                                </div>

                                <a href="<?= $fetch_products['quantity'] > 0 ? 'checkout.php?get_id=' . $fetch_products['id'] : '#' ?>"
                                    class="btn"
                                    style="<?= $fetch_products['quantity'] <= 0 ? 'pointer-events: none; opacity: 0.5; cursor: not-allowed;' : '' ?>">
                                    buy now
                                </a>


                            </form>
                <?php
                            $grand_total += $fetch_wishlist['price'];
                        }
                    }
                } else {
                    echo '<p class="empty">No products in wishlist yet.</p>';
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
    <script src="contact-validation.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
    <script src="script.js"></script>
</body>

</html>