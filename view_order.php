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


if (isset($_GET['get_id']) && is_numeric($_GET['get_id'])) {
    $get_id = intval($_GET['get_id']);
} else {
    header('Location: order.php');
    exit();
}

if (isset($_POST['cancel'])) {
    $update_order = $conn->prepare("UPDATE `orders` SET status = ? WHERE id = ?");
    $update_order->execute(['cancelled', $get_id]);

    $_SESSION['success_msg'] = 'Order cancelled successfully';


    header('Location: order.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <title>BookstoreBuzuku - Order Detail Page</title>
    <style type="text/css">
        <?php include 'style.css'; ?>
    </style>
</head>

<body>
    <?php include 'components/header.php'; ?>
    <div class="main">
        <div class="tittle2">
            <a href="home.php">Home</a><span>order Detail</span>
        </div>

        <section class="order-detail">
            <div class="title">
                <img src="img/logoo.png" class="logo">
                <h1>order detail</h1>
                <p>Here you can see all your orders</p>
            </div>
            <div class="box-container">
                <?php
                $grand_total = 0;
                $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE id = ? LIMIT 1");
                $select_orders->execute([$get_id]);

                if ($select_orders->rowCount() > 0) {
                    while ($fetch_order = $select_orders->fetch(PDO::FETCH_ASSOC)) {
                        $select_product = $conn->prepare("SELECT * FROM `products` WHERE id = ? LIMIT 1");
                        $select_product->execute([$fetch_order['product_id']]);
                        if ($select_product->rowCount() > 0) {
                            while ($fetch_product = $select_product->fetch(PDO::FETCH_ASSOC)) {
                                $sub_total = ($fetch_order['price'] * $fetch_order['qty']);
                                $grand_total += $sub_total;
                ?>
                                <div class="box">
                                    <div class="col">
                                        <p><i class="bi bi-calendar-fill"></i><?= $fetch_order['date']; ?></p>
                                        <img src="image/<?= $fetch_product['image']; ?>" class="img">
                                        <p class="price"><?= $fetch_product['price']; ?> x <?= $fetch_order['qty']; ?></p>
                                        <h3 class="name"><?= $fetch_product['name']; ?></h3>
                                        <p class="grand-total">Total amount payable : <span><?= $grand_total; ?></span></p>
                                    </div>
                                    <div class="col">
                                        <p class="title">Billing Address</p>
                                        <p class="user"><i class="bi bi-person-bounding-box"></i><?= $fetch_order['name']; ?></p>
                                        <p class="user"><i class="bi bi-phone"></i><?= $fetch_order['number']; ?></p>
                                        <p class="user"><i class="bi bi-envelope"></i><?= $fetch_order['email']; ?></p>
                                        <p class="user"><i class="bi bi-pin-map-fill"></i><?= $fetch_order['address']; ?></p>
                                        <p class="title">status</p>
                                        <p class="status" style="color: <?php if ($fetch_order['status'] == 'delivered') {
                                                                            echo 'green';
                                                                        } elseif ($fetch_order['status'] == 'cancelled') {
                                                                            echo 'red';
                                                                        } else {
                                                                            echo 'orange';
                                                                        } ?>;"><?= $fetch_order['status'] ?></p>
                                        <?php
                                        if ($fetch_order['status'] == 'cancelled') { ?>
                                            <a href="checkout.php?get_id=<?= $fetch_product['id']; ?>" class="btn">order again</a>
                                        <?php } else { ?>
                                            <form method="post">
                                                <button type="submit" name="cancel" class="btn" onclick="return confirm('Do you want to cancel this order?')">Cancel order</button>
                                            </form>
                                        <?php } ?>
                                    </div>
                                </div>
                <?php
                            }
                        } else {
                            echo '<p class="empty">Product not found.</p>';
                        }
                    }
                } else {
                    echo '<p class="empty">Order not found.</p>';
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