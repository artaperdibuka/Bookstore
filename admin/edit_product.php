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

//update product
if(isset($_POST['update'])){
    $post_id = $_GET['id'];
    
    $name = $_POST['name'];
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);

    $price = $_POST['price'];
    $price = filter_var($_POST['price'], FILTER_SANITIZE_STRING);

    $content = $_POST['content'];
    $content = filter_var($_POST['content'], FILTER_SANITIZE_STRING);

  $status = $_POST['status'] ?? 'active'; // Default to 'active' if status not provided
    $status = filter_var($status, FILTER_SANITIZE_STRING);

    $update_product = $conn->prepare("UPDATE `products` SET name = ?, price = ?, product_detail = ?, status = ? WHERE id = ?");
    $update_product->execute([$name, $price, $content, $status, $post_id]); 

    $success_msg[] = 'Product updated successfully!';

    $old_image = $_POST['old_image'];
    $image = $_FILES['image']['name'];
    $image = filter_var($image, FILTER_SANITIZE_STRING);
    $image_size = $_FILES['image']['size'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = '../image/' . $image;

    $select_image = $conn->prepare("SELECT * FROM `products` WHERE image = ?"); 
    $select_image->execute([$image]);


    if(!empty($image)) {
        if($image_size > 2000000) {
            $warning_msg[] = 'Image size is too large!';
        }elseif($select_image->rowCount() > 0 AND $image != '') {
            $warning_msg[] = 'please rename your Image ';
        }
         else {
            $update_image = $conn->prepare("UPDATE `products` SET image = ? WHERE id = ?");
            $update_image->execute([$image, $post_id]);
            move_uploaded_file($image_tmp_name, $image_folder);

            if($old_image != $image AND $old_image != '') {
                unlink('../image/' . $old_image);
            }
            $success_msg[] = 'Product image updated successfully!';
        }
    } 
}

//delete product
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
    <title>BookstoreBuzuku Admin Panel - edit Product Page</title>
    <style type="text/css">
        <?php include '../style.css'; ?>
    </style>
</head>

<body>
    <?php include __DIR__ . '/../components/admin_header.php'; ?>


    <div class="main">

        <div class="tittle2">
            <a href="dashboard.php">Dashboard</a><span> edit product</span>
        </div>
        <section class="edit-post">
            <h1 class="heading">edit post</h1>
            <?php 
            $post_id = $_GET['id'];

            $select_product = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
            $select_product->execute([$post_id]);

            if($select_product->rowCount() > 0) {
                while ($fetch_product = $select_product->fetch(PDO::FETCH_ASSOC)) {
               
            ?>
            <div class="form-container">
                <form action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="old_image" value="<?= $fetch_product['image']; ?>">
                    <input type="hidden" name="product_id" value="<?= $fetch_product['id']; ?>">
                    <div class="input-field">
                        <label >update status</label>
                        <select name="status">
                            <option selected disabled value=" <?= $fetch_product['status'] ?>"><?= $fetch_product['status'] ?></option>
                            <option value="active"> active</option>
                            <option value="deactive"> deactive</option>
                        </select>
                    </div>
                     <div class="input-field">
                        <label >product name</label>
                        <input type="text" name="name" maxlength="100" placeholder="Product Name" required class="box" value="<?= $fetch_product['name']; ?>">
                    </div>
                     <div class="input-field">
                        <label >product price</label>
                        <input type="number" name="price" maxlength="100" placeholder="Product price" required class="box" value="<?= $fetch_product['price']; ?>">
                    </div>
                    <div class="input-field">
                        <label >product description</label>
                        <textarea name="content"><?= $fetch_product['product_detail']; ?></textarea>
                    </div>
                    <div class="input-field">
                        <label >product image</label>
                        <input type="file" name="image" accept="image/*">
                        <img src="../image/<?= $fetch_product['image']; ?>" class="image" alt="">
                    </div>
                    <div class="flex-btn">
                        <button type="submit" name="update" class="btn">update product</button>
                        <a href="view_products.php" class="btn">cancel</a>
                        <button type="submit" name="delete" class="btn">delete product</button>
                    </div>

                </form>
            </div>
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