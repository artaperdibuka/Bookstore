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

$get_id = $_GET['post_id'] ?? '';

//delete product

if(isset($_POST['delete'])){
    $p_id = $_POST['product_id'];
    $p_id = filter_var($p_id, FILTER_SANITIZE_STRING);
    
    try {
        // 1. Fillimisht fshi produktin nga shportat (cart)
        $delete_from_cart = $conn->prepare("DELETE FROM `cart` WHERE product_id = ?");
        $delete_from_cart->execute([$p_id]);
        
        // 2. Pastaj fshi produktin nga listat e dëshirave (wishlist)
        $delete_from_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE product_id = ?");
        $delete_from_wishlist->execute([$p_id]);
        
        // 3. Pastaj fshi imazhin nëse ekziston
        $delete_image = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
        $delete_image->execute([$p_id]);
        $fetch_delete_image = $delete_image->fetch(PDO::FETCH_ASSOC);
        
        if($fetch_delete_image['image'] != '' && file_exists('../image/' . $fetch_delete_image['image'])) {
            unlink('../image/' . $fetch_delete_image['image']);
        }

        // 4. Më në fund fshi produktin
        $delete_product = $conn->prepare("DELETE FROM `products` WHERE id = ?");
        $delete_product->execute([$p_id]);
        
        $success_msg[] = 'Product deleted successfully!';
        header('location: view_products.php');
        exit();
        
    } catch(PDOException $e) {
        $warning_msg[] = 'Error deleting product: ' . $e->getMessage();
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
    <title>BookstoreBuzuku Admin Panel - Read Product Page</title>
    <style type="text/css">
        <?php include '../style.css'; ?>
    </style>
</head>

<body>
    <?php include __DIR__ . '/../components/admin_header.php'; ?>


    <div class="main">

        <div class="tittle2">
            <a href="dashboard.php">Dashboard</a><span> read product</span>
        </div>
        <section class="read-post">
            <h1 class="heading">read product</h1>
           <?php 
           $select_product = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
           $select_product->execute([$get_id]);
           if($select_product->rowCount()>0){
             while($fetch_product = $select_product->fetch(PDO::FETCH_ASSOC)){

           
           ?>
           <form action="" method="post">
            <input type="hidden" name="product_id" value="<?= $fetch_product['id']; ?>">
            <div class="status" style="color: <?php if($fetch_product['status']=='active'){echo 'green';}else{ echo 'red';} ?>">
                <?= $fetch_product['status']; ?>
            </div>
            <?php if($fetch_product['image'] != ''){?>
            <img src="../image/<?= $fetch_product['image']; ?>" class="image" alt="">
            <?php } ?>
            <div class="price">$<?= $fetch_product['price']; ?>/-</div>
            <div class="title"><?= $fetch_product['name']; ?></div>
            <div class="title"><?= $fetch_product['product_detail']; ?></div>
            <div class="flex-btn">
                <a href="edit_product.php?id=<?= $fetch_product['id']; ?>" class="btn">edit</a>
                <button type="submit" name="delete" class="btn" onclick="return confirm('Are you sure you want to delete this product?');">delete</button>
                <a href="view_products.php?id=<?= $get_id; ?>" class="btn">go back</a>
            </div>
           </form>
           <?php 
           }
                } else {
                    echo '
                <div class="empty">
                    <p>no products added yet!<br> <a href="add_products.php" style="margin-top:1.5rem;" class="btn">add products</a></p>
                </div>
                ';
                }
           ?>
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