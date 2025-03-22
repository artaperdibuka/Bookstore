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




?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>BookstoreBuzuku - Order Page</title>
    <style type="text/css">
        <?php include 'style.css'; ?>
    </style>
</head>

<body>
    <?php include 'components/header.php'; ?>
    <div class="main">
        <div class="tittle2">
            <a href="home.php">Home</a><span>orders</span>
        </div>

        <section class="products">

            <div class="title">
                <img src="img/logoo.png" class="logo">
                <h1>my orders</h1>
                <p>Here you can see all your orders</p>

                <div class="box-container">
                    <?php
                    $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ? ORDER BY date DESC");
                    $select_orders->execute([$user_id]);
                    if ($select_orders->rowCount() > 0) {
                        while ($fetch_order = $select_orders->fetch(PDO::FETCH_ASSOC)) {
                            $select_products = $conn->prepare("SELECT * FROM `products` WHERE id = ? ");
                            $select_products->execute([$fetch_order['product_id']]);
                            if ($select_products->rowCount() > 0) {
                                while ($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)) {


                    ?>
                                    <div class="box" <?php if ($fetch_order['status'] == 'cancel') {
                                                            echo 'style="border:2px solid red"';
                                                        } ?>>
                                        <a href="view_order.php?get_id=<?= $fetch_order['id']; ?>">
                                            <p class="date"><i class="bi bi-calendar-fill"></i><span><?= $fetch_order['date']; ?>
                                                </span></p>
                                            <img src="image/<?= $fetch_product['image']; ?>" class="img">
                                            <div class="row">
                                                <h3 class="name"><?= $fetch_product['name']; ?></h3>
                                                <p class="price">Price : <?= $fetch_order['price']; ?> x <?= $fetch_order['qty']; ?></p>
                                                <p class="status" style="color: <?php if ($fetch_order['status'] == 'delivered') {
                                                                                    echo 'green';
                                                                                } elseif ($fetch_order['status'] == 'cancelled') {
                                                                                    echo 'red';
                                                                                } else {
                                                                                    echo 'orange';
                                                                                } ?>;"><?= $fetch_order['status']; ?></p>

                                            </div>
                                        </a>

                        <?php
                                }
                            }
                        }
                    } else {
                        echo '<p class="empty">No orders takes placed yet.</p>'; // Mesazhi nëse nuk ka porosi
                    }
                        ?>

                                    </div>

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