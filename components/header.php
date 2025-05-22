<header class="header">
    <div class="flex">
        <a href="<?php echo isset($_SESSION['admin_id']) ? 'dashboard.php' : 'home.php'; ?>" class="logo">
            <img src="img/logoo.png" height="65" width="230">
        </a>

        <nav class="navbar">
            <?php if (isset($_SESSION['admin_id'])): ?>
                <!-- Menyja KUR ËSHTË ADMIN -->
                <a href="dashboard.php">Dashboard</a>
                <a href="add_product.php">Add Product</a>
                <a href="view_products.php">View Products</a>
                <a href="account.php">Account</a>
            <?php else: ?>
                <!-- Menyja KUR NUK ËSHTË ADMIN -->
                <a href="home.php">Home</a>
                
                <div class="dropdown">
                    <a href="view_products.php" class="dropbtn">Books <i class="bx bx-chevron-down"></i></a>
                    <div class="dropdown-content">
                        <?php
                        $categories = $conn->query("SELECT * FROM categories ORDER BY name");
                        while ($category = $categories->fetch(PDO::FETCH_ASSOC)):
                        ?>
                            <a href="view_products.php?category_id=<?= $category['id'] ?>">
                                <?= htmlspecialchars($category['name']) ?>
                            </a>
                        <?php endwhile; ?>
                    </div>
                </div>
                
                <a href="accessories.php">Accesories</a>
                <a href="order.php">Orders</a>
                <a href="about.php">About Us</a>
                <a href="contact.php">Contact Us</a>
            <?php endif; ?>
        </nav>

        <div class="icons">
            <i class="bx bxs-user" id="user-btn"></i>
            <?php if (!isset($_SESSION['admin_id'])): ?>
                <a href="wishlist.php" class="cart-btn"><i class="bx bx-heart"></i><sup><?= $total_wishlist_items ?></sup></a>
                <a href="cart.php" class="cart-btn"><i class="bx bx-cart-download"></i><sup><?= $total_cart_items ?></sup></a>
            <?php endif; ?>
            <i class="bx bx-list-plus" id="menu-btn" style="font-size: 2rem;"></i>
        </div>

        <div class="user-box">
            <?php if (isset($_SESSION['admin_id'])): ?>
                <p>Admin: <span><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span></p>
                <form method="post">
                    <button type="submit" name="logout" class="logout-btn">Log out</button>
                </form>
            <?php elseif (isset($_SESSION['user_id'])): ?>
                <p>User: <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span></p>
                <form method="post">
                    <button type="submit" name="logout" class="logout-btn">Log out</button>
                </form>
            <?php else: ?>
                <a href="login.php" class="btn">Login</a>
                <a href="register.php" class="btn">Register</a>
            <?php endif; ?>
        </div>
    </div>
</header>