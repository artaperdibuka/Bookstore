<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once(__DIR__ . '/../components/connection.php');

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Add new subscriber
if (isset($_POST['add_subscriber'])) {
    $email = $_POST['email'];
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $warning_msg[] = 'Invalid email format!';
    } else {
        // Check if email already exists
        $check_subscriber = $conn->prepare("SELECT * FROM `newsletter_subscribers` WHERE email = ?");
        $check_subscriber->execute([$email]);
        
        if ($check_subscriber->rowCount() > 0) {
            $warning_msg[] = 'Email already subscribed!';
        } else {
            $insert_subscriber = $conn->prepare("INSERT INTO `newsletter_subscribers` (email) VALUES (?)");
            $insert_subscriber->execute([$email]);
            $success_msg[] = 'Subscriber added successfully!';
        }
    }
}

// Delete subscriber
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $delete_subscriber = $conn->prepare("DELETE FROM `newsletter_subscribers` WHERE id = ?");
    $delete_subscriber->execute([$delete_id]);
    $success_msg[] = 'Subscriber deleted successfully!';
}

// Update subscriber
if (isset($_POST['update_subscriber'])) {
    $subscriber_id = $_POST['subscriber_id'];
    $email = $_POST['email'];
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $warning_msg[] = 'Invalid email format!';
    } else {
        // Check if new email already exists (excluding current record)
        $check_subscriber = $conn->prepare("SELECT * FROM `newsletter_subscribers` WHERE email = ? AND id != ?");
        $check_subscriber->execute([$email, $subscriber_id]);
        
        if ($check_subscriber->rowCount() > 0) {
            $warning_msg[] = 'Email already exists!';
        } else {
            $update_subscriber = $conn->prepare("UPDATE `newsletter_subscribers` SET email = ? WHERE id = ?");
            $update_subscriber->execute([$email, $subscriber_id]);
            $success_msg[] = 'Subscriber updated successfully!';
        }
    }
}

// Get all subscribers
$select_subscribers = $conn->prepare("SELECT * FROM `newsletter_subscribers` ORDER BY subscribed_at DESC");
$select_subscribers->execute();
$subscribers = $select_subscribers->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscribers Management</title>
    <style type="text/css">
        <?php include '../style.css'; ?>
        /* Subscribers table styles */
        .subscribers-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .subscribers-table th, .subscribers-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        .subscribers-table th {
            background-color: #f8fafc;
            font-weight: 600;
            color: #4a5568;
        }
        .subscribers-table tr:hover {
            background-color: #f8fafc;
        }
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        .edit-btn, .delete-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        .edit-btn {
            background-color: #4299e1;
            color: white;
        }
        .edit-btn:hover {
            background-color: #3182ce;
        }
        .delete-btn {
            background-color: #f56565;
            color: white;
        }
        .delete-btn:hover {
            background-color: #e53e3e;
        }
        .subscriber-form {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../components/admin_header.php'; ?>

    <div class="main">
        <div class="tittle2">
            <a href="dashboard.php">Dashboard</a><span>Subscribers Management</span>
        </div>

        <section class="subscribers-management">
            <h1 class="heading">Manage Subscribers</h1>
            
            <!-- Add New Subscriber Form -->
            <button class="toggle-form-btn" onclick="toggleSubscriberForm()">Add New Subscriber</button>
            
            <div class="subscriber-form" id="subscriberForm" style="display: none;">
                <form action="" method="post">
                    <div class="input-field">
                        <label>Email Address</label>
                        <input type="email" name="email" placeholder="Enter email address" required>
                    </div>
                    
                    <div class="flex-btn">
                        <button type="submit" name="add_subscriber" class="btn">Add Subscriber</button>
                        <button type="button" onclick="document.getElementById('subscriberForm').style.display='none'" class="btn cancel-btn">Cancel</button>
                    </div>
                </form>
            </div>
            
            <!-- Edit Subscriber Form (hidden by default) -->
            <div class="subscriber-form" id="editSubscriberForm" style="display: none;">
                <form action="" method="post">
                    <input type="hidden" name="subscriber_id" id="edit_subscriber_id">
                    <div class="input-field">
                        <label>Email Address</label>
                        <input type="email" name="email" id="edit_subscriber_email" placeholder="Enter email address" required>
                    </div>
                    
                    <div class="flex-btn">
                        <button type="submit" name="update_subscriber" class="btn">Update Subscriber</button>
                        <button type="button" onclick="document.getElementById('editSubscriberForm').style.display='none'" class="btn cancel-btn">Cancel</button>
                    </div>
                </form>
            </div>
            
            <!-- Subscribers Table -->
            <table class="subscribers-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Email</th>
                        <th>Subscribed At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subscribers as $subscriber): ?>
                    <tr>
                        <td><?= $subscriber['id']; ?></td>
                        <td><?= htmlspecialchars($subscriber['email']); ?></td>
                        <td><?= $subscriber['subscribed_at']; ?></td>
                        <td class="action-buttons">
                            <button class="edit-btn" onclick="openEditModal(<?= $subscriber['id']; ?>, '<?= htmlspecialchars($subscriber['email']); ?>')">Edit</button>
                            <a href="admin_subscribers.php?delete=<?= $subscriber['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this subscriber?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </div>

    <script>
    function toggleSubscriberForm() {
        const form = document.getElementById('subscriberForm');
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
        // Hide edit form if it's open
        document.getElementById('editSubscriberForm').style.display = 'none';
    }
    
    function openEditModal(id, email) {
        document.getElementById('edit_subscriber_id').value = id;
        document.getElementById('edit_subscriber_email').value = email;
        document.getElementById('editSubscriberForm').style.display = 'block';
        // Hide add form if it's open
        document.getElementById('subscriberForm').style.display = 'none';
        
        // Scroll to the edit form
        document.getElementById('editSubscriberForm').scrollIntoView({ behavior: 'smooth' });
    }
    </script>
</body>
</html>