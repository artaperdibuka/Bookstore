<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once 'components/connection.php';

// Kontrollo nëse është admin i loguar
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Merr të dhënat e adminit
$admin_id = $_SESSION['admin_id'];
$select_profile = $conn->prepare("SELECT * FROM `admin` WHERE id = ?");
$select_profile->execute([$admin_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

$success_msg = [];
$warning_msg = [];

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
    <title>BookstoreBuzuku Admin Panel - Dashboard Page</title>
    <style type="text/css">
        <?php include 'style.css'; ?>
    </style>
</head>
<body>
    <?php include 'components/admin_header.php'; ?>
    <div class="main">
    
       <div class="tittle2">
            <a href="dashboard.php">Home</a><span>Dashboard</span>
       </div>
       <section class="dashboard">
            <h1 class="heading">Dashboard</h1>
            <div class="box-container">
                <div class="box">
                    <h3>Welcome</h3>
                    <p><?php echo htmlspecialchars($fetch_profile['name']); ?></p>
                    <a href="profile.php" class="btn">Profile</a>
                </div>
                
                <div class="box">
                    <?php 
                    $select_product = $conn->prepare("SELECT * FROM `products`");
                    $select_product->execute();
                    $num_of_products = $select_product->rowCount();
                    ?>
                    <h3><?= $num_of_products; ?></h3>
                    <p>Products added</p>
                    <a href="add_product.php" class="btn">Add new products</a>
                </div>
                
               
                <div class="box">
                    <?php 
                    $select_available_product = $conn->prepare("SELECT * FROM `products` WHERE quantity > 0");   
                    $select_available_product->execute();
                    $num_of_available_products = $select_available_product->rowCount();
                    ?>
                    <h3><?= $num_of_available_products; ?></h3>
                    <p>Available products</p>
                    <a href="view_products.php" class="btn">View products</a>
                </div>
                
                <div class="box">
                    <?php 
                    $select_out_of_stock = $conn->prepare("SELECT * FROM `products` WHERE quantity = 0");   
                    $select_out_of_stock->execute();
                    $num_of_out_of_stock = $select_out_of_stock->rowCount();
                    ?>
                    <h3><?= $num_of_out_of_stock; ?></h3>
                    <p>Out of stock</p>
                    <a href="view_products.php" class="btn">View products</a>
                </div>
                <div class="box">
                    <?php 
                    $select_users = $conn->prepare("SELECT * FROM `users`");
                    $select_users->execute();
                    $num_of_users = $select_users->rowCount();
                    ?>
                    <h3><?= $num_of_users; ?></h3>
                    <p>users added</p>
                    <a href="accounts.php" class="btn">Add new users</a>
                </div>
                <div class="box">
                    <?php 
                    $select_message = $conn->prepare("SELECT * FROM `messages`");
                    $select_message->execute();
                    $num_of_message = $select_message->rowCount();
                    ?>
                    <h3><?= $num_of_message; ?></h3>
                    <p>message added</p>
                    <a href="message.php" class="btn">view message</a>
                </div>
                <div class="box">
                    <?php 
                    $select_orders = $conn->prepare("SELECT * FROM `orders`");
                    $select_orders->execute();
                    $num_of_orders = $select_orders->rowCount();
                    ?>
                    <h3><?= $num_of_orders; ?></h3>
                    <p>total order placed</p>
                    <a href="order.php" class="btn">view orders</a>
                </div>
                <div class="box">
                    <?php 
                    $select_confirm_orders = $conn->prepare("SELECT * FROM `orders` WHERE status = ?");
                    $select_confirm_orders->execute(['in progress']);
                    $num_of_confirm_orders = $select_confirm_orders->rowCount();
                    ?>
                    <h3><?= $num_of_confirm_orders; ?></h3>
                    <p>total confirm orders</p>
                    <a href="order.php" class="btn">view  confirm orders</a>
                </div>
                <div class="box">
                    <?php 
                    $select_canceled_orders = $conn->prepare("SELECT * FROM `orders` WHERE status = ?");
                    $select_canceled_orders->execute(['canceled']);
                    $num_of_canceled_orders = $select_canceled_orders->rowCount();
                    ?>
                    <h3><?= $num_of_confirm_orders; ?></h3>
                    <p>total canceled orders</p>
                    <a href="order.php" class="btn">view  canceled orders</a>
                </div>

                
            </div>
       </section>
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
    </script>
    <script src="script.js"></script>
</body>
</html>