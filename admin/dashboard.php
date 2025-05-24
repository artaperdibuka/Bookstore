<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once(__DIR__ . '/../components/connection.php');


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
    header('Location: ../login.php');
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
        <?php include '../style.css'; ?>
    </style>
</head>

<body>
    <?php include __DIR__ . '/../components/admin_header.php'; ?>


    <div class="main">

        <div class="tittle2">
            <a >Dashboard</a><span> admin panel</span>
        </div>
        <section class="dashboard">
            <h1 class="heading">Dashboard</h1>
            <div class="box-container">
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
                    // Totali i të gjitha produkteve
                    $select_total = $conn->prepare("SELECT * FROM `products`");
                    $select_total->execute();
                    $num_of_total = $select_total->rowCount();
                    ?>
                    <h3><?= $num_of_total; ?></h3>
                    <p>Total products</p>
                    <a href="view_products.php" class="btn">View all</a>
                </div>
                <div class="box">
                    <?php
                    $select_product = $conn->prepare("SELECT * FROM `products`");
                    $select_product->execute();
                    $num_of_products = $select_product->rowCount();
                    ?>
                    <h3><?= $num_of_products; ?></h3>
                    <p>Products added</p>
                    <a href="add_products.php" class="btn">Add new products</a>
                </div>
                <div class="box">
                    <?php
                    // Të gjitha produktet e disponueshme (quantity > 0)
                    $select_available_product = $conn->prepare("SELECT * FROM `products` WHERE quantity > 0");
                    $select_available_product->execute();
                    $num_of_available_products = $select_available_product->rowCount();
                    ?>
                    <h3><?= $num_of_available_products; ?></h3>
                    <p>Available products</p>
                    <a href="view_products.php?filter=available" class="btn">View products</a>
                </div>

                <div class="box">
                    <?php
                    // Produktet që kanë mbaruar (quantity = 0)
                    $select_out_of_stock = $conn->prepare("SELECT * FROM `products` WHERE quantity = 0");
                    $select_out_of_stock->execute();
                    $num_of_out_of_stock = $select_out_of_stock->rowCount();
                    ?>
                    <h3><?= $num_of_out_of_stock; ?></h3>
                    <p>Out of stock</p>
                    <a href="view_products.php?filter=out_of_stock" class="btn">View products</a>
                </div>






                <div class="box">
                    <?php
                    $select_message = $conn->prepare("SELECT * FROM `messages`");
                    $select_message->execute();
                    $num_of_message = $select_message->rowCount();
                    ?>
                    <h3><?= $num_of_message; ?></h3>
                    <p>message added</p>
                    <a href="admin_message.php" class="btn">view message</a>
                </div>
                <div class="box">
                    <?php
                    $select_orders = $conn->prepare("SELECT * FROM `orders`");
                    $select_orders->execute();
                    $num_of_orders = $select_orders->rowCount();
                    ?>
                    <h3><?= $num_of_orders; ?></h3>
                    <p>total order placed</p>
                    <a href="admin_order.php" class="btn">view orders</a>
                </div>


                <div class="box">
                    <?php
                    $select_categories = $conn->prepare("SELECT * FROM `categories`");
                    $select_categories->execute();
                    $num_of_categories = $select_categories->rowCount();
                    ?>
                    <h3><?= $num_of_categories; ?></h3>
                    <p>categories added</p>
                    <a href="add_categories.php" class="btn">add new categories</a>
                </div>
                <div class="box">
                    <?php
                    $select_carts = $conn->prepare("SELECT * FROM `cart`");
                    $select_carts->execute();
                    $num_of_carts = $select_carts->rowCount();
                    ?>
                    <h3><?= $num_of_carts; ?></h3>
                    <p>active carts</p>
                    <a href="admin_cart.php" class="btn">view all carts</a>
                </div>
                   <div class="box">
                    <?php
                    $select_wishlists = $conn->prepare("SELECT * FROM `wishlist`");
                    $select_wishlists->execute();
                    $num_of_wishlists = $select_wishlists->rowCount();
                    ?>
                    <h3><?= $num_of_wishlists; ?></h3>
                    <p>wishlist items</p>
                    <a href="admin_wishlist.php" class="btn">view wishlists</a>
                </div>
                <div class="box">
                    <?php
                    $select_subscribers = $conn->prepare("SELECT * FROM `newsletter_subscribers`");
                    $select_subscribers->execute();
                    $num_of_subscribers = $select_subscribers->rowCount();
                    ?>
                    <h3><?= $num_of_subscribers; ?></h3>
                    <p>email subscribers</p>
                    <a href="admin_subscribers.php" class="btn">view all subscribers</a>
                </div>

                <!-- Admin Wishlist Box -->
             



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
    <script src="../script.js"></script>
</body>

</html>