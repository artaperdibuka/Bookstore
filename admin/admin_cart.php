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

// Delete cart item
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE id = ?");
    $delete_cart->execute([$delete_id]);
    $success_msg[] = 'Cart item deleted successfully!';
}

// Update cart item
if (isset($_POST['update_cart'])) {
    $cart_id = $_POST['cart_id'];
    $qty = $_POST['qty'];
    
    if ($qty < 1) {
        $warning_msg[] = 'Quantity must be at least 1!';
    } else {
        $update_cart = $conn->prepare("UPDATE `cart` SET qty = ? WHERE id = ?");
        $update_cart->execute([$qty, $cart_id]);
        $success_msg[] = 'Cart updated successfully!';
    }
}

// Add new cart item
if (isset($_POST['add_cart'])) {
    $user_id = $_POST['user_id'];
    $product_id = $_POST['product_id'];
    $qty = $_POST['qty'];
    
    // Get product price
    $select_product = $conn->prepare("SELECT price FROM `products` WHERE id = ?");
    $select_product->execute([$product_id]);
    $product = $select_product->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        $warning_msg[] = 'Product not found!';
    } else {
        $price = $product['price'];
        
        // Check if item already exists in cart
        $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ? AND product_id = ?");
        $check_cart->execute([$user_id, $product_id]);
        
        if ($check_cart->rowCount() > 0) {
            $warning_msg[] = 'Product already exists in cart!';
        } else {
            $insert_cart = $conn->prepare("INSERT INTO `cart` (user_id, product_id, price, qty) VALUES (?, ?, ?, ?)");
            $insert_cart->execute([$user_id, $product_id, $price, $qty]);
            $success_msg[] = 'Product added to cart successfully!';
        }
    }
}

// Get all cart items with user and product details
$select_carts = $conn->prepare("
    SELECT c.*, u.name as user_name, u.email, p.name as product_name 
    FROM `cart` c
    JOIN `users` u ON c.user_id = u.id
    JOIN `products` p ON c.product_id = p.id
    ORDER BY c.created_at DESC
");
$select_carts->execute();
$carts = $select_carts->fetchAll(PDO::FETCH_ASSOC);

// Get all users for dropdown
$select_users = $conn->prepare("SELECT id, name, email FROM `users`");
$select_users->execute();
$users = $select_users->fetchAll(PDO::FETCH_ASSOC);

// Get all products for dropdown
$select_products = $conn->prepare("SELECT id, name, price FROM `products` WHERE status = 'active' AND quantity > 0");
$select_products->execute();
$products = $select_products->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Admin Cart Management</title>
    <style type="text/css">
        <?php include '../style.css'; ?>
        /* Additional styles for cart management */
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .cart-table th, .cart-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        .cart-table th {
            background-color: #f8fafc;
            font-weight: 600;
            color: #4a5568;
        }
        .cart-table tr:hover {
            background-color: #f8fafc;
        }
        .qty-input {
            width: 60px;
            padding: 8px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
        }
        .action-buttons {
            display: flex;
            gap: 8px;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../components/admin_header.php'; ?>

    <div class="main">
        <div class="tittle2">
            <a href="dashboard.php">Dashboard</a><span>Cart Management</span>
        </div>

        <section class="cart-management">
            <h1 class="heading">Manage Carts</h1>
            
            <!-- Add New Cart Item Form -->
            <button class="toggle-form-btn" onclick="toggleCartForm()">Add New Cart Item</button>
            
            <div class="cart-form" id="cartForm" style="display: none;">
                <form action="" method="post">
                    <div class="input-field">
                        <label>Select User</label>
                        <select name="user_id" required>
                            <option value="">Select a user</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user['id']; ?>">
                                    <?= htmlspecialchars($user['name']) ?> (<?= htmlspecialchars($user['email']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="input-field">
                        <label>Select Product</label>
                        <select name="product_id" required>
                            <option value="">Select a product</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?= $product['id']; ?>">
                                    <?= htmlspecialchars($product['name']) ?> ($<?= number_format($product['price'], 2) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="input-field">
                        <label>Quantity</label>
                        <input type="number" name="qty" min="1" value="1" required>
                    </div>
                    
                    <div class="flex-btn">
                        <button type="submit" name="add_cart" class="btn">Add to Cart</button>
                        <button type="button" onclick="document.getElementById('cartForm').style.display='none'" class="btn cancel-btn">Cancel</button>
                    </div>
                </form>
            </div>
            
            <!-- Cart Items Table -->
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Date Added</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($carts as $cart): ?>
                    <tr>
                        <td><?= $cart['id']; ?></td>
                        <td><?= htmlspecialchars($cart['user_name']); ?><br><?= htmlspecialchars($cart['email']); ?></td>
                        <td><?= htmlspecialchars($cart['product_name']); ?></td>
                        <td>$<?= number_format($cart['price'], 2); ?></td>
                        <td>
                            <form action="" method="post" style="display: inline;">
                                <input type="hidden" name="cart_id" value="<?= $cart['id']; ?>">
                                <input type="number" name="qty" min="1" value="<?= $cart['qty']; ?>" class="qty-input">
                                <button type="submit" name="update_cart" class="edit-btn">Update</button>
                            </form>
                        </td>
                        <td>$<?= number_format($cart['price'] * $cart['qty'], 2); ?></td>
                        <td><?= $cart['created_at']; ?></td>
                        <td class="action-buttons">
                            <a href="admin_cart.php?delete=<?= $cart['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this cart item?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </div>

    <script>
    function toggleCartForm() {
        const form = document.getElementById('cartForm');
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    }
    </script>
</body>
</html>