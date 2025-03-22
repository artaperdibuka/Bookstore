<?php
// Filloni sesionin nëse nuk është aktiv
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Përfshini lidhjen me bazën e të dhënave
include_once 'components/connection.php';

// Kontrolloni nëse përdoruesi është i identifikuar
if(isset($_SESSION['user_id'])){
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
}

// Inicializoni mesazhet
$success_msg = [];
$warning_msg = [];

// Procesoni daljen
if(isset($_POST['logout'])){
    session_destroy();
    header('Location: login.php');
    exit();
}
//update product to cart
if(isset($_POST['update_cart'])){
    $cart_id = $_POST['cart_id'];
    $cart_id = filter_var($cart_id, FILTER_SANITIZE_STRING);

    $qty = $_POST['qty'];
    $qty = filter_var($qty, FILTER_SANITIZE_STRING);

    $update_cart = $conn->prepare("UPDATE `cart` SET qty = ? WHERE id = ?");
    $update_cart->execute([$qty, $cart_id]);
    
    $success_msg[] = 'cart updated successfully';
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
            <h1 class="title"> products added in cart</h1>
            <div class="box-container">
            <?php
                 $grand_total = 0;
                 $select_cart = $conn -> prepare ("SELECT * FROM `cart` WHERE user_id = ?"); 
                    $select_cart -> execute([$user_id]); 
                    if($select_cart -> rowCount() > 0){
                        while($fetch_cart = $select_cart -> fetch(PDO::FETCH_ASSOC)){
                            $select_products = $conn -> prepare ("SELECT * FROM `products` WHERE id = ? LIMIT 1"); 
                            $select_products -> execute([$fetch_cart['product_id']]); 
                            if($select_products -> rowCount() >0 ){
                                 $fetch_products = $select_products -> fetch(PDO::FETCH_ASSOC);   
                   
                ?>
                <form method="post" action=""  class="box">
                        <input type="hidden" name="cart_id" value="<?=$fetch_cart['id']; ?>" >
                        <img class="img" src="image/<?= $fetch_products['image']?>">
                        <h3 class="name"><?= $fetch_products['name'];?></h3>
                        <div class="flex">
                            <p class="price">price $<?= $fetch_products['price']; ?>/-</p>
                            <input type="hidden" name="qty" required min="1" value="<?= $fetch_cart['qty']; ?> "max="99" maxlength="2" class="qty">
                            <input type="number" name="qty" required min = "1" value="1" max="99" maxlength="2" class="qty">
                            <button type="submit" name="update_cart" class="bx bxs-edit fa-edit"></button>
                        </div>
                        <p class="sub-total">sub total:<span>$<?=$sub_total = ($fetch_cart['qty']* $fetch_cart['price'])?></span></p>
                        

                        <button type="submit" name="delete_item" class="btn" onclick="return  confirm('delete this item')">
                            delete
                        </button>                </form>
                <?php 
                $grand_total += $sub_total;
                         }else{
                            echo '<p class="empty">product was not found.</p>';
                         }
                        }
                    }else {
                        echo '<p class="empty">No products added yet.</p>'; // Mesazhi nëse nuk ka produkte
                    }
                ?>
            </div>
            <?php
                if($grand_total !=0){
                    
                
            ?>
            <div class="cart-total">
                <p>total amount payable : <span>$<?=$grand_total?>/-</span></p>
                <div class="button">
                    <form method="post">
                        <button type="submit" name="empty_cart" class="btn" onclick=" 
                        return confirm('are you sure to empty your cart ')">empty cart</button>
                        <a href="checkout.php" class="btn">proceed to checkout</a>
                    </form>
                </div>
            </div>
            <?php 
            }
            ?>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js" ></script>
    <script src="script.js" ></script>
</body>
</html>