<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

$admin_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : null;
?>

  <header class="header">
        <div class="flex">
            <a href="../admin/dashboard.php" class="logo"><img src="../img/logoo.png" height="65" width="230"></a>

            <nav class="navbar">
                <a href="dashboard.php">Dashboard</a>
                <a href="add_products.php">Add Product</a>
                <a href="view_products.php">View Products</a>
                <a href="account.php">Account</a>
            </nav>

            <div class="icons">
                <i class="bx bxs-user" id="user-btn"></i>
                <i class="bx bx-list-plus" id="menu-btn" style="font-size: 2rem;"></i>
            </div>

            <div class="user-box">
                <p>Admin: <span><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span></p>
                <p>Email: <span><?php echo htmlspecialchars($_SESSION['admin_email']); ?></span></p>
                <form method="post">
                    <button type="submit" name="logout" class="logout-btn">Log out</button>
                </form>
            </div>
        </div>
    </header>