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

        /* Stilizimi bazë */
.orders-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    padding: 15px;
}

.order-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.08);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.order-card.canceled {
    position: relative;
    opacity: 0.8;
}

.order-card.canceled::after {
    content: 'Canceled';
    position: absolute;
    top: 10px;
    right: 10px;
    background: #f44336;
    color: white;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
}

.order-info {
    padding: 15px;
    background: #f8f9fa;
    border-bottom: 1px solid #eee;
}

.order-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 10px;
}

.order-meta > span {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 14px;
}

.books-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
    padding: 15px;
}

.book-card {
    border: 1px solid #eee;
    border-radius: 8px;
    overflow: hidden;
    transition: transform 0.2s;
}

.book-card:hover {
    transform: translateY(-3px);
}

.book-image {
    width: 100%;
    height: 120px;
    object-fit: contain;
    background: #f8f9fa;
    padding: 10px;
}

.book-info {
    padding: 10px;
}

.book-title {
    font-size: 14px;
    margin: 0 0 5px 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.book-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    font-size: 13px;
}

.book-price {
    font-weight: bold;
    color: #2e7d32;
}

.book-status {
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 11px;
}

.book-status.delivered {
    background: #e8f5e9;
    color: #2e7d32;
}

.book-status.canceled {
    background: #ffebee;
    color: #c62828;
}

.book-status.in-progress {
    background: #fff3e0;
    color: #e65100;
}

.order-actions {
    margin-top: 10px;
}

.cancel-btn {
    background: #f44336;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.cancel-btn:hover {
    background: #d32f2f;
}

.payment-status {
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 13px;
}

.payment-status.pending {
    background: #fff3e0;
    color: #e65100;
}

.payment-status.complete {
    background: #e8f5e9;
    color: #2e7d32;
}

.no-orders {
    text-align: center;
    grid-column: 1 / -1;
    padding: 40px;
    color: #666;
    font-size: 18px;
}

/* Responsive */
@media (max-width: 768px) {
    .orders-grid {
        grid-template-columns: 1fr;
    }
    
    .books-grid {
        grid-template-columns: repeat(2, 1fr);
    }
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
   <div class="orders-grid">
    <?php
    $select_orders = $conn->prepare("
        SELECT 
            DATE_FORMAT(order_date, '%Y-%m-%d %H:%i:%s') as order_date_formatted,
            GROUP_CONCAT(id) as order_ids,
            GROUP_CONCAT(product_id) as product_ids,
            GROUP_CONCAT(price) as prices,
            GROUP_CONCAT(qty) as quantities,
            GROUP_CONCAT(status SEPARATOR '|') as statuses,
            payment_status,
            SUM(price * qty) as total_amount,
            COUNT(*) as item_count
        FROM `orders` 
        WHERE user_id = ? 
        GROUP BY DATE_FORMAT(order_date, '%Y-%m-%d %H:%i:%s'), payment_status
        ORDER BY order_date DESC
    ");
    $select_orders->execute([$user_id]);

    if ($select_orders->rowCount() > 0) {
        while ($fetch_order = $select_orders->fetch(PDO::FETCH_ASSOC)) {
            $product_ids = explode(',', $fetch_order['product_ids']);
            $prices = explode(',', $fetch_order['prices']);
            $quantities = explode(',', $fetch_order['quantities']);
            $statuses = explode('|', $fetch_order['statuses']);
            
            $all_canceled = in_array('Canceled', $statuses);
    ?>
    <div class="order-card <?= $all_canceled ? 'canceled' : '' ?>">
        <div class="order-info">
            <div class="order-meta">
                <span class="order-date">
                    <i class="bi bi-calendar-fill"></i>
                    <?= date('d M Y H:i', strtotime($fetch_order['order_date_formatted'])) ?>
                </span>
                <span class="order-total">
                    <i class="bi bi-cash-stack"></i>
                    <?= number_format($fetch_order['total_amount'], 2) ?>€
                </span>
                <span class="payment-status <?= strtolower($fetch_order['payment_status']) ?>">
                    <i class="bi bi-credit-card"></i>
                    <?= $fetch_order['payment_status'] ?>
                </span>
            </div>
            
            <?php if ($fetch_order['payment_status'] == 'Pending' && !$all_canceled) { ?>
            <form method="post" class="order-actions">
                <input type="hidden" name="order_date" value="<?= $fetch_order['order_date_formatted'] ?>">
                <button type="submit" name="cancel_order" class="cancel-btn">
                    <i class="bi bi-x-circle"></i> Cancel
                </button>
            </form>
            <?php } ?>
        </div>

        <div class="books-grid">
            <?php
            for ($i = 0; $i < count($product_ids); $i++) {
                $select_product = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
                $select_product->execute([$product_ids[$i]]);
                $fetch_product = $select_product->fetch(PDO::FETCH_ASSOC);
            ?>
            <div class="book-card">
                <img src="image/<?= $fetch_product['image'] ?>" alt="<?= $fetch_product['name'] ?>" class="book-image">
                <div class="book-info">
                    <h3 class="book-title"><?= $fetch_product['name'] ?></h3>
                    <div class="book-meta">
                        <span class="book-price"><?= $prices[$i] ?>€</span>
                        <span class="book-qty">× <?= $quantities[$i] ?></span>
                        <span class="book-status <?= strtolower($statuses[$i]) ?>"><?= $statuses[$i] ?></span>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
    <?php
        }
    } else {
        echo '<p class="no-orders">Nuk keni asnjë porosi.</p>';
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