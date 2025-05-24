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

//delete product
if(isset($_POST['delete'])){
    $p_id = $_POST['product_id'];
    $p_id = filter_var($p_id, FILTER_SANITIZE_STRING);
    $delete_product = $conn->prepare("DELETE FROM `products` WHERE id = ?");
    $delete_product->execute([$p_id]);
    $success_msg[] = 'Product deleted successfully!';
}

// Merr parametrin e filtrit
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';

// Përcakto titullin bazuar në filtrin
$page_title = "All Products";
if ($filter === 'out_of_stock') {
    $page_title = "Out of Stock Products";
} elseif ($filter === 'available') {
    $page_title = "Available Products";
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
    <title>BookstoreBuzuku Admin Panel - <?= $page_title ?></title>
    <style type="text/css">
        <?php include '../style.css'; ?>
    </style>
</head>
<body>
    <?php include __DIR__ . '/../components/admin_header.php'; ?>

    <div class="main">
        <div class="tittle2">
            <a href="dashboard.php">Dashboard</a><span><?= $page_title ?></span>
        </div>
        
        <section class="show-post">
            <h1 class="heading"><?= $page_title ?></h1>
            <div class="box-container">
                <?php
                // Krijoni query bazë
                $sql = "SELECT * FROM `products`";
                
                // Shto kushtet e filtrit
                if ($filter === 'out_of_stock') {
                    $sql .= " WHERE quantity = 0";
                } elseif ($filter === 'available') {
                    $sql .= " WHERE quantity > 0";
                }
                
                $select_products = $conn->prepare($sql);
                $select_products->execute();

                if ($select_products->rowCount() > 0) {
                    while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) {
                ?>
                        <form action="" method="post" class="box">
                            <input type="hidden" name="product_id" value="<?= $fetch_products['id']; ?>">
                            <?php if ($fetch_products['image'] != '') { ?>
                                <img src="../image/<?= $fetch_products['image']; ?>" class="image" alt="">
                            <?php } ?>
                            <div class="status" style="color: <?= ($fetch_products['status'] == 'active') ? 'green' : 'red'; ?>;">
                                <?= $fetch_products['status']; ?>
                            </div>
                            <div class="price">$<?= $fetch_products['price']; ?>/-</div>
                            <div class="title"><?= $fetch_products['name']; ?></div>
                            <div class="quantity">Quantity: <?= $fetch_products['quantity']; ?></div>
                            <div class="flex-btn">
                                <a href="edit_product.php?id=<?= $fetch_products['id']; ?>" class="btn">edit</a>
                                <button type="submit" name="delete" class="btn" onclick="return confirm('delete this product');">delete</button>
                                <a href="read_product.php?post_id=<?= $fetch_products['id']; ?>" class="btn">view</a>
                            </div>
                        </form>
                <?php
                    }
                } else {
                    echo '
                    <div class="empty">
                        <p>no products found!<br> <a href="add_products.php" style="margin-top:1.5rem;" class="btn">add products</a></p>
                    </div>
                    ';
                }
                ?>
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