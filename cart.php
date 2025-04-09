<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


include_once 'components/connection.php';

if(isset($_SESSION['user_id'])){
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
}

$success_msg = [];
$warning_msg = [];


if(isset($_POST['logout'])){
    session_destroy();
    header('Location: login.php');
    exit();
}

//update product to cart
if(isset($_POST['update_cart'])){
    $cart_id = filter_var($_POST['cart_id'], FILTER_SANITIZE_STRING);
    $new_qty = filter_var($_POST['qty'], FILTER_SANITIZE_NUMBER_INT);

  
    $get_item = $conn->prepare("SELECT c.product_id, c.qty as current_qty, p.quantity as stock 
                              FROM cart c 
                              JOIN products p ON c.product_id = p.id 
                              WHERE c.id = ?");
    $get_item->execute([$cart_id]);
    $item = $get_item->fetch(PDO::FETCH_ASSOC);

    if(!$item) {
        $warning_msg[] = 'Product not found in cart!';
    } else {
        if($new_qty > $item['stock']) {
            $warning_msg[] = 'Only '.$item['stock'].' items available in stock!';
        } else {
        
            $update_cart = $conn->prepare("UPDATE `cart` SET qty = ? WHERE id = ?");
            if($update_cart->execute([$new_qty, $cart_id])) {
                $success_msg[] = 'Cart updated successfully';
            } else {
                $warning_msg[] = 'Failed to update cart';
            }
        }
    }
}


//delete item from wishlist
if(isset($_POST['delete_item'])){
    $cart_id = $_POST['cart_id'];
    $cart_id = filter_var($cart_id, FILTER_SANITIZE_STRING);

    $varify_delete_items = $conn->prepare("SELECT * FROM `cart` WHERE id = ?");
    $varify_delete_items->execute([$cart_id]);

    if($varify_delete_items->rowCount() > 0){
        $delete_item = $conn->prepare("DELETE FROM `cart` WHERE id = ?");
        $delete_item->execute([$cart_id]);
        $success_msg[] = 'Product deleted from cart successfully';
    } else {
        $warning_msg[] = 'Product in cart already deleted';
    }
}

//empty cart
if(isset($_POST['empty_cart'])){
    $varify_empty_item = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
    $varify_empty_item->execute([$user_id]);

    if($varify_empty_item -> rowCount() > 0){
        $delete_item = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
        $delete_item->execute([$user_id]);
        $success_msg[] = 'empty successfully';
    } else {
        $warning_msg[] = 'Product in cart already deleted';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>BookstoreBuzuku - Cart Page</title>
    <style type="text/css"> 
        <?php include 'style.css'; ?>
    </style>
</head>
<body>
    <?php include 'components/header.php'; ?>
    <div class="main">
        <div class="banner">
            <h1> My Cart</h1>
        </div>
        <div class="tittle2">
            <a href="home.php">Home</a><span>Cart</span>
        </div>

        <section class="products">
            <h1 class="title">products added in cart</h1>
            <div class="box-container">
            <?php
                 $grand_total = 0;
                 $all_in_stock = true; // Flag to check if all items are in stock
                 
                 $select_cart = $conn->prepare("SELECT c.*, p.quantity as product_stock FROM `cart` c 
                                              JOIN `products` p ON c.product_id = p.id 
                                              WHERE c.user_id = ?"); 
                 $select_cart->execute([$user_id]); 
                 
                 if($select_cart->rowCount() > 0){
                     while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
                         $select_products = $conn->prepare("SELECT * FROM `products` WHERE id = ? LIMIT 1"); 
                         $select_products->execute([$fetch_cart['product_id']]); 
                         
                         if($select_products->rowCount() > 0){
                             $fetch_products = $select_products->fetch(PDO::FETCH_ASSOC);
                             $is_out_of_stock = ($fetch_products['quantity'] < $fetch_cart['qty']);
                             
                             if($is_out_of_stock) {
                                 $all_in_stock = false;
                             }
                ?>
                <form method="post" action="" class="box">
                    <input type="hidden" name="cart_id" value="<?=$fetch_cart['id']; ?>">
                    <img class="img" src="image/<?= $fetch_products['image']?>">
                    <h3 class="name"><?= $fetch_products['name'];?></h3>
                    <div class="flex">
                        <p class="price">price $<?= $fetch_products['price']; ?>/-</p>
                        <?php if($is_out_of_stock): ?>
                            <span class="out-of-stock">Out of Stock</span>
                        <?php else: ?>
                            <input type="number" name="qty" required min="1" 
                                   value="<?= $fetch_cart['qty']; ?>" 
                                   max="<?= $fetch_products['quantity']; ?>" 
                                   class="qty">
                            <button type="submit" name="update_cart" class="bx bxs-edit fa-edit"></button>
                        <?php endif; ?>
                    </div>
                    <p class="sub-total">sub total:<span>$<?=$sub_total = ($fetch_cart['qty']* $fetch_cart['price'])?></span></p>
                    
                    <button type="submit" name="delete_item" class="btn" onclick="return confirm('delete this item')">
                        delete
                    </button>
                </form>
                <?php 
                             $grand_total += $sub_total;
                         } else {
                             echo '<p class="empty">product was not found.</p>';
                         }
                     }
                 } else {
                     echo '<p class="empty">No products added yet.</p>'; 
                 }
                ?>
            </div>
            <?php if($grand_total != 0): ?>
            <div class="cart-total">
                <p>total amount payable: <span>$<?=$grand_total?>/-</span></p>
                <div class="button">
                    <form method="post">
                        <button type="submit" name="empty_cart" class="btn" 
                                onclick="return confirm('are you sure to empty your cart')">
                            empty cart
                        </button>
                        <?php if($all_in_stock): ?>
                            <a href="checkout.php" class="btn">proceed to checkout</a>
                        <?php else: ?>
                            <button type="button" class="btn disabled">proceed to checkout</button>
                            <p class="stock-warning">Some items in your cart are out of stock</p>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            <?php endif; ?>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js" ></script>
    <script src="script.js" ></script>
</body>
</html>