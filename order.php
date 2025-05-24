<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once 'components/connection.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
    header('Location: login.php');
    exit();
}

$success_msg = [];
$warning_msg = [];

if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Përpunimi i anulimit të porosisë VETËM nëse payment_status është Pending
if (isset($_POST['cancel_order'])) {
    $order_id = $_POST['order_id'];

    // Kontrollo nëse porosia i përket përdoruesit dhe ka payment_status = Pending
    $check_order = $conn->prepare("SELECT * FROM `orders` WHERE id = ? AND user_id = ? AND payment_status = 'Pending'");
    $check_order->execute([$order_id, $user_id]);

    if ($check_order->rowCount() > 0) {
        $update_order = $conn->prepare("UPDATE `orders` SET status = 'Canceled' WHERE id = ?");
        if ($update_order->execute([$order_id])) {
            $success_msg[] = 'Order canceled successfully!';
            // Rifresko faqen për të shfaqur ndryshimet
            echo "<script>window.location.href = window.location.href;</script>";
        } else {
            $warning_msg[] = 'Failed to cancel order!';
        }
    } else {
        $warning_msg[] = 'Order cannot be canceled (either already completed or does not belong to you).';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>BookstoreBuzuku - My Orders</title>
    <style type="text/css">
        <?php include 'style.css'; ?>

        /* Shtojmë stilizim për payment_status */
        .payment-status-pending {
            color: #ff9800;
            font-weight: bold;
        }
        .payment-status-complete {
            color: #4caf50;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <?php include 'components/header.php'; ?>
    <div class="main">
        <div class="tittle2">
            <a href="home.php">Home</a><span> / My Orders</span>
        </div>

        <section class="products">
            <div class="title">
                <img src="img/logoo.png" class="logo">
                <h1>My Orders</h1>
                <p>Here you can see all your orders</p>

                <div class="box-container">
                    <?php
                    $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ? ORDER BY order_date DESC");
                    $select_orders->execute([$user_id]);

                    if ($select_orders->rowCount() > 0) {
                        while ($fetch_order = $select_orders->fetch(PDO::FETCH_ASSOC)) {
                            $select_product = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
                            $select_product->execute([$fetch_order['product_id']]);
                            $fetch_product = $select_product->fetch(PDO::FETCH_ASSOC);

                            // Përcakto klasën e statusit
                            $status_class = 'status-' . strtolower(str_replace(' ', '-', $fetch_order['status']));
                            $payment_status_class = 'payment-status-' . strtolower($fetch_order['payment_status']);
                    ?>
                            <div class="box" <?php if ($fetch_order['status'] == 'Canceled') {
                                                    echo 'style="border:2px solid red"';
                                                } ?>>
                                <a href="view_order.php?get_id=<?= $fetch_order['id']; ?>">
                                    <p class="date"><i class="bi bi-calendar-fill"></i><span><?= date('d M Y H:i', strtotime($fetch_order['order_date'])); ?></span></p>
                                    <img src="image/<?= $fetch_product['image']; ?>" class="img">
                                    <div class="row">
                                        <h3 class="name"><?= $fetch_product['name']; ?></h3>
                                        <p class="price">Price: <?= $fetch_order['price']; ?>€ x <?= $fetch_order['qty']; ?></p>
                                        <p class="status <?= $status_class; ?>">Status: <?= $fetch_order['status']; ?></p>
                                        <p class="<?= $payment_status_class; ?>">Payment: <?= $fetch_order['payment_status']; ?></p>
                                        <p class="total">Total: <?= number_format($fetch_order['price'] * $fetch_order['qty'], 2); ?>€</p>
                                    </div>
                                </a>

                                <!-- Shfaq butonin Cancel VETËM nëse payment_status është Pending dhe statusi nuk është Delivered -->
                                <?php if ($fetch_order['payment_status'] == 'Pending' && $fetch_order['status'] != 'Delivered') { ?>
                                    <form method="post" style="margin-top: 10px;" onsubmit="return confirmCancel()">
                                        <input type="hidden" name="order_id" value="<?= $fetch_order['id']; ?>">
                                        <button type="submit" name="cancel_order" class="cancel-btn">
                                            Cancel Order
                                        </button>
                                    </form>
                                <?php } ?>
                            </div>
                    <?php
                        }
                    } else {
                        echo '<p class="empty">No orders placed yet.</p>';
                    }
                    ?>
                </div>
            </div>
        </section>

        <?php include 'components/footer.php'; ?>
    </div>

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

        function confirmCancel() {
            return confirm('Are you sure you want to cancel this order?');
        }
    </script>
    <script src="script.js"></script>
</body>

</html>