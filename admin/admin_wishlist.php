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

// Delete wishlist item
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE id = ?");
    $delete_wishlist->execute([$delete_id]);
    $success_msg[] = 'Wishlist item deleted successfully!';
}

// Update wishlist item (though typically you'd just delete and re-add)
if (isset($_POST['update_wishlist'])) {
    $wishlist_id = $_POST['wishlist_id'];
    $product_id = $_POST['product_id'];
    
    // Get new product price
    $select_product = $conn->prepare("SELECT price FROM `products` WHERE id = ?");
    $select_product->execute([$product_id]);
    $product = $select_product->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        $warning_msg[] = 'Product not found!';
    } else {
        $update_wishlist = $conn->prepare("UPDATE `wishlist` SET product_id = ?, price = ? WHERE id = ?");
        $update_wishlist->execute([$product_id, $product['price'], $wishlist_id]);
        $success_msg[] = 'Wishlist updated successfully!';
    }
}

// Add new wishlist item
if (isset($_POST['add_wishlist'])) {
    $user_id = $_POST['user_id'];
    $product_id = $_POST['product_id'];
    
    // Get product price
    $select_product = $conn->prepare("SELECT price FROM `products` WHERE id = ?");
    $select_product->execute([$product_id]);
    $product = $select_product->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        $warning_msg[] = 'Product not found!';
    } else {
        $price = $product['price'];
        
        // Check if item already exists in wishlist
        $check_wishlist = $conn->prepare("SELECT * FROM `wishlist` WHERE user_id = ? AND product_id = ?");
        $check_wishlist->execute([$user_id, $product_id]);
        
        if ($check_wishlist->rowCount() > 0) {
            $warning_msg[] = 'Product already exists in wishlist!';
        } else {
            $insert_wishlist = $conn->prepare("INSERT INTO `wishlist` (user_id, product_id, price) VALUES (?, ?, ?)");
            $insert_wishlist->execute([$user_id, $product_id, $price]);
            $success_msg[] = 'Product added to wishlist successfully!';
        }
    }
}

// Get all wishlist items with user and product details
$select_wishlists = $conn->prepare("
    SELECT w.*, u.name as user_name, u.email, p.name as product_name, p.id as product_id 
    FROM `wishlist` w
    JOIN `users` u ON w.user_id = u.id
    JOIN `products` p ON w.product_id = p.id
    ORDER BY w.created_at DESC
");
$select_wishlists->execute();
$wishlists = $select_wishlists->fetchAll(PDO::FETCH_ASSOC);

// Get all users for dropdown
$select_users = $conn->prepare("SELECT id, name, email FROM `users`");
$select_users->execute();
$users = $select_users->fetchAll(PDO::FETCH_ASSOC);

// Get all products for dropdown
$select_products = $conn->prepare("SELECT id, name, price FROM `products` WHERE status = 'active'");
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

    <title>Admin Wishlist Management</title>
    <style type="text/css">
        <?php include '../style.css'; ?>
        /* Enhanced styles for wishlist management */
        .wishlist-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        .wishlist-table th {
            background-color: #f8fafc;
            font-weight: 600;
            color: #4a5568;
            padding: 15px;
            text-align: left;
            border-bottom: 2px solid #e2e8f0;
        }
        .wishlist-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .wishlist-table tr:hover {
            background-color: #f8fafc;
        }
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        .edit-form {
            background-color: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            border-left: 4px solid #4299e1;
        }
        .product-select {
            min-width: 250px;
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../components/admin_header.php'; ?>

    <div class="main">
        <div class="tittle2">
            <a href="dashboard.php">Dashboard</a><span>Wishlist Management</span>
        </div>

        <section class="wishlist-management">
            <h1 class="heading">Manage Wishlists</h1>
            
            <!-- Add New Wishlist Item Form -->
            <button class="toggle-form-btn" onclick="toggleWishlistForm()">Add New Wishlist Item</button>
            
            <div class="wishlist-form" id="wishlistForm" style="display: none;">
                <form action="" method="post">
                    <div class="input-field">
                        <label>Select User</label>
                        <select name="user_id" required class="product-select">
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
                        <select name="product_id" required class="product-select">
                            <option value="">Select a product</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?= $product['id']; ?>">
                                    <?= htmlspecialchars($product['name']) ?> ($<?= number_format($product['price'], 2) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="flex-btn">
                        <button type="submit" name="add_wishlist" class="btn">Add to Wishlist</button>
                        <button type="button" onclick="document.getElementById('wishlistForm').style.display='none'" class="btn cancel-btn">Cancel</button>
                    </div>
                </form>
            </div>
            
            <!-- Wishlist Items Table -->
            <table class="wishlist-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Current Product</th>
                        <th>Price</th>
                        <th>Date Added</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($wishlists as $wishlist): ?>
                    <tr>
                        <td><?= $wishlist['id']; ?></td>
                        <td><?= htmlspecialchars($wishlist['user_name']); ?><br><?= htmlspecialchars($wishlist['email']); ?></td>
                        <td>
                            <?php if (isset($_GET['edit']) && $_GET['edit'] == $wishlist['id']): ?>
                                <div class="edit-form">
                                    <form action="" method="post">
                                        <input type="hidden" name="wishlist_id" value="<?= $wishlist['id']; ?>">
                                        <select name="product_id" class="product-select" required>
                                            <?php foreach ($products as $product): ?>
                                                <option value="<?= $product['id']; ?>" <?= $product['id'] == $wishlist['product_id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($product['name']) ?> ($<?= number_format($product['price'], 2) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="flex-btn" style="margin-top: 10px;">
                                            <button type="submit" name="update_wishlist" class="btn">Update</button>
                                            <a href="admin_wishlist.php" class="btn cancel-btn">Cancel</a>
                                        </div>
                                    </form>
                                </div>
                            <?php else: ?>
                                <?= htmlspecialchars($wishlist['product_name']); ?>
                            <?php endif; ?>
                        </td>
                        <td>$<?= number_format($wishlist['price'], 2); ?></td>
                        <td><?= $wishlist['created_at']; ?></td>
                        <td class="action-buttons">
                            <?php if (!isset($_GET['edit'])): ?>
                                <a href="admin_wishlist.php?edit=<?= $wishlist['id']; ?>" class="edit-btn">Edit</a>
                                <a href="admin_wishlist.php?delete=<?= $wishlist['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this wishlist item?')">Delete</a>
                            <?php else: ?>
                                <span style="color: #a0aec0;">Actions disabled</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </div>

    <script>
    function toggleWishlistForm() {
        const form = document.getElementById('wishlistForm');
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
        // If editing, cancel edit when opening add form
        if (window.location.href.includes('edit=')) {
            window.location.href = 'admin_wishlist.php';
        }
    }
    
    // If page loaded with edit parameter, scroll to that item
    document.addEventListener('DOMContentLoaded', function() {
        if (window.location.href.includes('edit=')) {
            const editId = new URL(window.location.href).searchParams.get('edit');
            const element = document.querySelector(`[href*="edit=${editId}"]`);
            if (element) {
                element.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
    </script>
</body>
</html>