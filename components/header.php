<?php
// Nis sesionin vetëm nëse nuk është nisur më parë
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kontrollo nëse përdoruesi ka bërë log out
if (isset($_POST['logout'])) {
    session_destroy(); // Shkatërron sesionin
    header("Location: login.php"); // Riinicializon tek faqja e logimit
    exit; // Ndërpret ekzekutimin
}

// Sigurohuni që $user_id është i vendosur
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Ikonat
$total_wishlist_items = 0;
$total_cart_items = 0;

if ($user_id !== null) {
    // Fetch wishlist items count
    try {
        $count_wishlist_items = $conn->prepare("SELECT * FROM `wishlist` WHERE user_id = ?");
        $count_wishlist_items->execute([$user_id]);
        $total_wishlist_items = $count_wishlist_items->rowCount();
    } catch (Exception $e) {
        $total_wishlist_items = 0; // Default value in case of error
    }

    // Fetch cart items count
    try {
        $count_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
        $count_cart_items->execute([$user_id]);
        $total_cart_items = $count_cart_items->rowCount();
    } catch (Exception $e) {
        $total_cart_items = 0; // Default value in case of error
    }
}
?>

<header class="header">
    <div class="flex">
        <!-- Logo -->
        <a href="home.php" class="logo"><img src="img/logoo.png" height="65" width="230"></a>

        <!-- Navigimi -->
        <nav class="navbar">
            <a href="home.php">Home</a>
            <a href="view_products.php">Products</a>
            <a href="order.php">Orders</a>
            <a href="events.php">Events</a>
            <a href="about.php">About Us</a>
            <a href="contact.php">Contact Us</a>
        </nav>

        <!-- Ikonat -->
        <div class="icons">
            <i class="bx bxs-user" id="user-btn"></i>
            <a href="wishlist.php" class="cart-btn"><i class="bx bx-heart"></i><sup><?= $total_wishlist_items ?></sup></a>
            <a href="cart.php" class="cart-btn"><i class="bx bx-cart-download"></i><sup><?= $total_cart_items ?></sup></a>
            <i class="bx bx-list-plus" id="menu-btn" style="font-size: 2rem;"></i>
        </div>

        <!-- Informacioni i përdoruesit -->
        <div class="user-box">
            <p>Username: <span><?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Guest'; ?></span></p>
            <p>Email: <span><?php echo isset($_SESSION['user_email']) ? $_SESSION['user_email'] : 'Not Available'; ?></span></p>

            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Butoni i log out nëse përdoruesi është i loguar -->
                <form method="post">
                    <button type="submit" name="logout" class="logout-btn">Log out</button>
                </form>
            <?php else: ?>
                <!-- Butonat e logimit dhe regjistrimit për përdoruesit jo të loguar -->
                <a href="login.php" class="btn">Login</a>
                <a href="register.php" class="btn">Register</a>
            <?php endif; ?>
        </div>
    </div>
</header>