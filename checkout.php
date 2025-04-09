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

if (isset($_POST['place_order'])) {
    $name = $_POST['name'];
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $number = $_POST['number'];
    $number = filter_var($number, FILTER_SANITIZE_STRING);
    $email = $_POST['email'];
    $email = filter_var($email, FILTER_SANITIZE_STRING);
    $address = $_POST['flat'] . ', ' . $_POST['street'] . ', ' . $_POST['city'] . ', ' . $_POST['country'] . ', ' . $_POST['pincode'];
    $address = filter_var($address, FILTER_SANITIZE_STRING);
    $address_type = $_POST['address_type'];
    $address_type = filter_var($address_type, FILTER_SANITIZE_STRING);
    $method = $_POST['method'];
    $method = filter_var($method, FILTER_SANITIZE_STRING);

    $varify_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
    $varify_cart->execute([$user_id]);

    if (isset($_GET['get_id'])) {
        $get_id = $_GET['get_id'];
        $get_product = $conn->prepare("SELECT * FROM `products` WHERE id = ? LIMIT 1");
        $get_product->execute([$get_id]);

        if ($get_product->rowCount() > 0) {
            $fetch_p = $get_product->fetch(PDO::FETCH_ASSOC);

            $insert_order = $conn->prepare("INSERT INTO `orders` (user_id, name, number, email, address, address_type, method, product_id, price, qty) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $insert_order->execute([$user_id, $name, $number, $email, $address, $address_type, $method, $fetch_p['id'], $fetch_p['price'], 1]);

            if ($insert_order) {
                $success_msg[] = 'Order placed successfully!';
                header('Location: order.php');
                exit();
            } else {
                $warning_msg[] = 'Failed to place order.';
            }
        } else {
            $warning_msg[] = 'Product not found.';
        }
    } elseif ($varify_cart->rowCount() > 0) {
        while ($f_cart = $varify_cart->fetch(PDO::FETCH_ASSOC)) {
    
            $get_product = $conn->prepare("SELECT * FROM `products` WHERE id = ? LIMIT 1");
            $get_product->execute([$f_cart['product_id']]);
            
            if ($get_product->rowCount() > 0) {
                $fetch_p = $get_product->fetch(PDO::FETCH_ASSOC);
    
                $insert_order = $conn->prepare("INSERT INTO `orders` (user_id, name, number, email, address, address_type, method, product_id, price, qty) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $insert_order->execute([$user_id, $name, $number, $email, $address, $address_type, $method, $fetch_p['id'], $fetch_p['price'], $f_cart['qty']]);
    
                if ($insert_order) {
                
                    $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ? AND product_id = ?");
                    $delete_cart->execute([$user_id, $f_cart['product_id']]);
                } else {
                    $warning_msg[] = 'Failed to place order for product ID ' . $f_cart['product_id'] . '.';
                }
            } else {
                $warning_msg[] = 'Product not found for cart item.';
            }
        }
    
        if (empty($warning_msg)) {
            $success_msg[] = 'Order placed successfully!';
            header('Location: order.php');
            exit();
        }
    }
    
}

$get_cart_items = $conn->prepare("SELECT product_id, qty FROM cart WHERE user_id = ?");
$get_cart_items->execute([$user_id]);

while($item = $get_cart_items->fetch(PDO::FETCH_ASSOC)) {
    $update_stock = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
    $update_stock->execute([$item['qty'], $item['product_id']]);
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>BookstoreBuzuku - checkout Page</title>
    <style type="text/css">
        <?php include 'style.css'; ?>
    </style>
</head>

<body>
    <?php include 'components/header.php'; ?>
    <div class="main">
        <div class="tittle2">
            <a href="home.php">Home</a><span>checkout summary</span>
        </div>
        <section class="checkout">
            <div class="box-container">
                <div class="title">
                    <img src="img/logoo.png" class="logo">
                    <h1>Checkout Summary</h1>
                    <p>Fill out the form below to complete your order.</p>
                </div>
            </div>
            <div class="row">
                <form method="post">
                    <h3>billing details</h3>
                    <div class="flex">
                        <div class="box">
                            <div class="input-field">
                                <p>your name <span>*</span></p>
                                <input type="text" name="name" required maxlength="50" placeholder="Enter Your name" class="input">
                            </div>
                            <div class="input-field">
                                <p>your number <span>*</span></p>
                                <input type="number" name="number" required maxlength="10" placeholder="Enter Your number" class="input">
                            </div>
                            <div class="input-field">
                                <p>your email <span>*</span></p>
                                <input type="text" name="email" required maxlength="50" placeholder="Enter Your email" class="input">
                            </div>
                            <div class="input-field">
                                <p>payment method <span>*</span></p>
                                <select name="method" required class="input">
                                    <option value="">Select Payment Method</option>
                                    <option value="cash on delivery">Cash on delivery</option>
                                    <option value="credit or debit card">credit or debit Card</option>
                                    <option value="net banking">net banking</option>
                                    <option value="UPI or RuPay">UPI or RuPay</option>
                                    <option value="paytm">paytm</option>
                                </select>
                            </div>
                            <div class="input-field">
                                <p>address type<span>*</span></p>
                                <select name="address_type" required class="input">
                                    <option value="">Select Payment Method</option>
                                    <option value="home">home</option>
                                    <option value="office">office</option>

                                </select>
                            </div>
                        </div>
                        <div class="box">
                            <div class="input-field">
                                <p>address line 01 <span>*</span></p>
                                <input type="text" name="flat" required maxlength="50" placeholder="e.g flat & building number" class="input">
                            </div>
                            <div class="input-field">
                                <p>address line 02 <span>*</span></p>
                                <input type="text" name="street" required maxlength="50" placeholder="e.g street name" class="input">
                            </div>
                            <div class="input-field">
                                <p>city name <span>*</span></p>
                                <input type="text" name="city" required maxlength="50" placeholder="Enter your city name" class="input">
                            </div>
                            <div class="input-field">
                                <p>country name <span>*</span></p>
                                <input type="text" name="country" required maxlength="50" placeholder="Enter your country name" class="input">
                            </div>
                            <div class="input-field">
                                <p>pincode <span>*</span></p>
                                <input type="text" name="pincode" required maxlength="6" placeholder="110022" min="0" max="999999" class="input">
                            </div>
                        </div>
                    </div>
                    <button type="submit" name="place_order" class="btn">place order</button>
                </form>
                <div class="summary">
                    <h3>my bag</h3>
                    <div class="box-container">
                        <?php
                        $grand_total = 0;
                        if (isset($_GET['get_id'])) {
                            $select_get = $conn->prepare("SELECT * FROM `products` WHERE id = ? ");
                            $select_get->execute([$_GET['get_id']]);
                            while ($fetch_get = $select_get->fetch(PDO::FETCH_ASSOC)) {
                                $sub_total = $fetch_get['price'];
                                $grand_total += $sub_total;

                        ?>
                                <div class="flex">
                                    <img src="image/<?= $fetch_get['image']; ?>" class="img">
                                    <div>
                                        <h3 class="name"><?= $fetch_get['name']; ?></h3>
                                        <p class="price"><?= $fetch_get['price']; ?>/-</p>
                                    </div>
                                </div>
                                <?php

                            }
                        } else {
                            $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ? ");
                            $select_cart->execute([$user_id]);
                            if ($select_cart->rowCount() > 0) {
                                while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {
                                    $select_products = $conn->prepare("SELECT * FROM `products` WHERE id = ? ");
                                    $select_products->execute([$fetch_cart['product_id']]);
                                    $fetch_products = $select_products->fetch(PDO::FETCH_ASSOC);
                                    $sub_total = $fetch_cart['qty'] * $fetch_products['price'];
                                    $grand_total += $sub_total;

                                ?>
                                    <div class="flex">
                                        <img src="image/<?= $fetch_products['image']; ?>" class="img">
                                        <div>
                                            <h3 class="name"><?= $fetch_products['name']; ?></h3>
                                            <p class="price"><?= $fetch_products['price']; ?> X <?= $fetch_cart['qty']; ?>/-</p>

                                        </div>

                                    </div>
                        <?php
                                }
                            } else {
                                echo '<p class="empty" >your cart is empty</p>';
                            }
                        }
                        ?>
                    </div>
                    <div class="grand-total"><span>total amount payable:</span>$<?= $grand_total ?>/-</div>

                </div>
            </div>
        </section>

        <?php include 'components/footer.php'; ?>
    </div>

    <!-- JavaScript files -->
    <script>
       
        function showAlert(type, message) {
            Swal.fire({
                icon: type,
                title: type === 'success' ? 'Success!' : 'Warning!',
                text: message,
                confirmButtonText: 'OK'
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