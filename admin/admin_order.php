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

//delete order
if (isset($_POST['delete_order'])) {
    $order_id = $_POST['order_id'];
    $order_id = filter_var($order_id, FILTER_SANITIZE_STRING);

    $verify_delete = $conn->prepare("DELETE FROM `orders` WHERE id = ?");
    $verify_delete->execute([$order_id]);
    if ($verify_delete->rowCount() > 0) {

        $delete_order = $conn->prepare("DELETE FROM `orders` WHERE id = ?");
        $delete_order->execute([$order_id]);
        $success_msg[] = 'Order deleted successfully!';
    } else {
        $warning_msg[] = 'Order already deleted!';
    }
}

//update order
//update order
if (isset($_POST['update_order'])) {
    $order_id = $_POST['order_id'];
    $order_id = filter_var($order_id, FILTER_SANITIZE_STRING);

    // Check if update_payment exists and isn't empty
    if (!isset($_POST['update_payment']) || empty($_POST['update_payment'])) {
        $warning_msg[] = 'Please select a payment status!';
    } else {
        $update_payment = $_POST['update_payment'];
        $update_payment = filter_var($update_payment, FILTER_SANITIZE_STRING);

        $verify_update = $conn->prepare("UPDATE `orders` SET payment_status = ? WHERE id = ?");
        $verify_update->execute([$update_payment, $order_id]);

        if ($verify_update->rowCount() > 0) {
            $success_msg[] = 'Order updated successfully!';
        } else {
            $warning_msg[] = 'No changes made or order not found!';
        }
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
    <title>BookstoreBuzuku Admin Panel - order placed page</title>
    <style type="text/css">
        <?php include '../style.css'; ?>
    </style>
</head>

<body>
    <?php include __DIR__ . '/../components/admin_header.php'; ?>

    <div class="main">
        <div class="tittle2">
            <a href="dashboard.php">dashboard</a><span>order placed</span>
        </div>

        <section class="order-container">
            <h1 class="heading">Total Order Placed</h1>
            <div class="box-container">
                <div class="flex-btn">
                    <a href="add_order.php" class="btn">Add New Order</a>
                </div>

                <?php
                $select_orders = $conn->prepare("SELECT * FROM `orders`");
                $select_orders->execute();

                if ($select_orders->rowCount() > 0) {
                    while ($fetch_order = $select_orders->fetch(PDO::FETCH_ASSOC)) {
                        $order_id = $fetch_order['id'];
                        $user_id = $fetch_order['user_id'];

                        // Get user details
                        $select_user = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
                        $select_user->execute([$user_id]);
                        $fetch_user = $select_user->fetch(PDO::FETCH_ASSOC);

                ?>
                        <div class="box">
                            <div class="status" style="color: <?php if ($fetch_order['status'] == 'In Progress') {
                                                                    echo 'green';
                                                                } else {
                                                                    echo 'red';
                                                                } ?>">
                                <?= $fetch_order['status']; ?>
                            </div>
                            <div class="detail">
                                <p>User Name: <span> <?= $fetch_order['name']; ?></span></p>
                                <p>User Id: <span> <?= $fetch_order['id']; ?></span></p>
                                <p>Placed On: <span> <?= $fetch_order['date']; ?></span></p>
                                <p>User Number: <span> <?= $fetch_order['number']; ?></span></p>
                                <p>User Email: <span> <?= $fetch_order['email']; ?></span></p>
                                <p>method: <span> <?= $fetch_order['method']; ?></span></p>
                                <p>Address: <span> <?= $fetch_order['address']; ?></span></p>
                            </div>
                            <form action="" method="post">
                                <input type="hidden" name="order_id" value="<?= $order_id; ?>">
                                <select name="update_payment" required>
                                    <option value="" selected disabled><?= $fetch_order['payment_status']; ?></option>
                                    <option value="pending">Pending</option>
                                    <option value="complete">Complete</option>
                                </select>
                                <div class="flex-btn">
                                    

                                    <button type="submit" name="update_order" class="btn">update Order</button>
                                    <button type="submit" name="delete_order" class="btn">delete Order</button>
                                </div>
                            </form>

                        </div>
                <?php
                    }
                } else {
                    echo '<p class="empty">no orders takes placed </p>';
                }
                ?>

            </div>
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

        function toggleUserForm() {
            const form = document.getElementById('userForm');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
            // Hide edit form when showing add form
            document.getElementById('editUserForm').style.display = 'none';
        }

        function openEditModal(id, name, email, userType) {
            document.getElementById('edit_user_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_user_type').value = userType;

            // Hide add form if visible
            document.getElementById('userForm').style.display = 'none';

            // Show edit form
            document.getElementById('editUserForm').style.display = 'block';

            // Scroll to the form
            document.getElementById('editUserForm').scrollIntoView({
                behavior: 'smooth'
            });
        }
    </script>
    <script src="../script.js"></script>
</body>

</html>