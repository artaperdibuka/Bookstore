<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once(__DIR__ . '/../components/connection.php');

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$admin_id = $_SESSION['admin_id'];
$select_profile = $conn->prepare("SELECT * FROM `admin` WHERE id = ?");
$select_profile->execute([$admin_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

$success_msg = [];
$warning_msg = [];

// Get all products
$select_products = $conn->prepare("SELECT * FROM `products` WHERE status = 'active' AND quantity > 0");
$select_products->execute();
$products = $select_products->fetchAll(PDO::FETCH_ASSOC);

// Get all users
$select_users = $conn->prepare("SELECT * FROM `users`");
$select_users->execute();
$users = $select_users->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['add_order'])) {
    $user_id = $_POST['user_id'];
    $product_id = $_POST['product_id'];
    $qty = $_POST['qty'];
    $address = $_POST['address'];
    $address_type = $_POST['address_type'];
    $method = $_POST['method'];
    $number = $_POST['number'];
    
    // Validate inputs
    if (empty($user_id) || empty($product_id) || empty($qty) || empty($address)) {
        $warning_msg[] = 'Please fill all required fields!';
    } else {
        // Get product price
        $select_product = $conn->prepare("SELECT price FROM `products` WHERE id = ?");
        $select_product->execute([$product_id]);
        $product = $select_product->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            $warning_msg[] = 'Product not found!';
        } else {
            $price = $product['price'];
            $total = $price * $qty;
            
            // Get user details including number
            $select_user = $conn->prepare("SELECT name, email FROM `users` WHERE id = ?");
            $select_user->execute([$user_id]);
            $user = $select_user->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                $warning_msg[] = 'User not found!';
            } else {
                // Insert order with all required fields
                $insert_order = $conn->prepare("INSERT INTO `orders` 
                    (user_id, name, number, email, address, address_type, method, product_id, price, qty, status, payment_status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'In Progress', 'Pending')");
                $insert_order->execute([
                    $user_id,
                    $user['name'],
                    $number,
                    $user['email'],
                    $address,
                    $address_type,
                    $method,
                    $product_id,
                    $price,
                    $qty
                ]);
                
                if ($insert_order->rowCount() > 0) {
                    $success_msg[] = 'Order added successfully!';
                    
                    // Update product quantity
                    $update_qty = $conn->prepare("UPDATE `products` SET quantity = quantity - ? WHERE id = ?");
                    $update_qty->execute([$qty, $product_id]);
                } else {
                    $warning_msg[] = 'Failed to add order!';
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Order</title>
    <style type="text/css">
        <?php include '../style.css'; ?>
    </style>
</head>
<body>
    <?php include __DIR__ . '/../components/admin_header.php'; ?>

    <div class="main">
        <div class="tittle2">
            <a href="orders.php">Orders</a><span>Add New Order</span>
        </div>

        <section class="form-container">
            <h1 class="heading">Add New Order</h1>
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
                    <select name="product_id" required id="product-select">
                        <option value="">Select a product</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= $product['id']; ?>" data-price="<?= $product['price']; ?>">
                                <?= htmlspecialchars($product['name']) ?> ($<?= number_format($product['price'], 2) ?>) 
                                (Qty: <?= $product['quantity'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="input-field">
                    <label>Quantity</label>
                    <input type="number" name="qty" min="1" value="1" required id="quantity">
                </div>
                
                <div class="input-field">
                    <label>Total Price</label>
                    <input type="text" id="total-price" readonly value="$0.00">
                </div>
                
                <div class="input-field">
                    <label>Address Type</label>
                    <select name="address_type" required>
                        <option value="Home">Home</option>
                        <option value="Office">Office</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                  
                <div class="input-field">
                    <label>Number</label>
                    <input type="number" name="number" min="6"  placeholder="Enter your number">
                </div>
                <div class="input-field">
                    <label>Address</label>
                    <textarea name="address" required style="resize: none;" required placeholder="Enter full address"></textarea>
                </div>
                
                <div class="input-field">
                    <label>Payment Method</label>
                    <select name="method" required>
                        <option value="Credit Card">Credit Card</option>
                        <option value="PayPal">PayPal</option>
                        <option value="Cash on Delivery">Cash on Delivery</option>
                    </select>
                </div>
                
                <div class="flex-btn">
                    <button type="submit" name="add_order" class="btn">Add Order</button>
                    <a href="admin_order.php" class="btn cancel-btn">Cancel</a>
                </div>
            </form>
        </section>
    </div>

    <script>
        // Calculate total price when product or quantity changes
        document.getElementById('product-select').addEventListener('change', calculateTotal);
        document.getElementById('quantity').addEventListener('input', calculateTotal);
        
        function calculateTotal() {
            const productSelect = document.getElementById('product-select');
            const quantity = document.getElementById('quantity').value;
            const selectedOption = productSelect.options[productSelect.selectedIndex];
            
            if (selectedOption.value && quantity > 0) {
                const price = selectedOption.getAttribute('data-price');
                const total = price * quantity;
                document.getElementById('total-price').value = '$' + total.toFixed(2);
            } else {
                document.getElementById('total-price').value = '$0.00';
            }
        }
        
        // Show alerts for success/error messages
        <?php if (!empty($success_msg)): ?>
            <?php foreach ($success_msg as $msg): ?>
                alert('<?= $msg ?>');
            <?php endforeach; ?>
        <?php endif; ?>
        
        <?php if (!empty($warning_msg)): ?>
            <?php foreach ($warning_msg as $msg): ?>
                alert('<?= $msg ?>');
            <?php endforeach; ?>
        <?php endif; ?>
    </script>
</body>
</html>