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

// Shto mesazh të ri
if (isset($_POST['add_message'])) {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $subject = filter_var($_POST['subject'], FILTER_SANITIZE_STRING);
    $message = filter_var($_POST['message'], FILTER_SANITIZE_STRING);

    $insert_message = $conn->prepare("INSERT INTO `messages` (name, email, subject, message) VALUES (?, ?, ?, ?)");
    if ($insert_message->execute([$name, $email, $subject, $message])) {
        $success_msg[] = 'Message added successfully!';
    } else {
        $warning_msg[] = 'Failed to add message!';
    }
}

// Përditëso mesazhin
if (isset($_POST['update_message'])) {
    $message_id = $_POST['message_id'];
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $subject = filter_var($_POST['subject'], FILTER_SANITIZE_STRING);
    $message = filter_var($_POST['message'], FILTER_SANITIZE_STRING);

    $update_message = $conn->prepare("UPDATE `messages` SET name = ?, email = ?, subject = ?, message = ? WHERE id = ?");
    if ($update_message->execute([$name, $email, $subject, $message, $message_id])) {
        $success_msg[] = 'Message updated successfully!';
    } else {
        $warning_msg[] = 'Failed to update message!';
    }
}

// Fshi mesazhin
if (isset($_POST['delete'])) {
    $delete_id = $_POST['delete_id'];
    $delete_id = filter_var($delete_id, FILTER_SANITIZE_STRING);

    $verify_delete = $conn->prepare("SELECT * FROM `messages` WHERE id = ?");
    $verify_delete->execute([$delete_id]);
    if ($verify_delete->rowCount() > 0) {
        $delete_message = $conn->prepare("DELETE FROM `messages` WHERE id = ?");
        $delete_message->execute([$delete_id]);
        $success_msg[] = 'Message deleted successfully!';
    } else {
        $warning_msg[] = 'Message already deleted!';
    }
}

$select_message = $conn->prepare("SELECT * FROM `messages`");
$select_message->execute();
$messages = $select_message->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>BookstoreBuzuku Admin Panel - Messages</title>
    <style type="text/css">
        <?php include '../style.css'; ?>
        
        /* Stilizimi i ri */
        .message-form {
            background-color: #f5f5f5;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: none;
        }
        
        .edit-form {
            display: none;
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        .btn-edit {
            background-color: #4CAF50;
            color: white;
        }
        
        .btn-delete {
            background-color: #f44336;
            color: white;
        }
        
        .btn-add {
            background-color: #2196F3;
            color: white;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
   <?php include __DIR__ . '/../components/admin_header.php'; ?>

    <div class="main">
       <div class="tittle2">
            <a href="dashboard.php">dashboard</a><span> messages</span>
       </div>
       
       <section class="accounts">
            <h1 class="heading">Messages</h1>
            
            <!-- Butoni për të shtuar mesazh të ri -->
            <button type="button" class="btn btn-add" onclick="toggleMessageForm()">Add New Message</button>
            
            <!-- Forma për shtimin e mesazheve -->
            <div class="message-form" id="messageForm">
                <form action="" method="post">
                    <div class="input-field">
                        <label>Name <sup>*</sup></label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="input-field">
                        <label>Email <sup>*</sup></label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="input-field">
                        <label>Subject <sup>*</sup></label>
                        <input type="text" name="subject" required>
                    </div>
                    <div class="input-field">
                        <label>Message <sup>*</sup></label>
                        <textarea name="message" required></textarea>
                    </div>
                    <button type="submit" name="add_message" class="btn">Add Message</button>
                    <button type="button" class="btn" onclick="toggleMessageForm()">Cancel</button>
                </form>
            </div>
            
            <!-- Lista e mesazheve -->
            <div class="box-container">
                <?php if(count($messages) > 0): ?>
                    <?php foreach($messages as $message): ?>
                    <div class="box">
                        <h3 class="name"><?= htmlspecialchars($message['name']); ?></h3>
                        <h4><?= htmlspecialchars($message['subject']); ?></h4>
                        <p><?= htmlspecialchars($message['message']); ?></p>
                        <p><small><?= htmlspecialchars($message['email']); ?></small></p>
                        
                        <div class="action-buttons">
                            <button type="button" class="btn btn-edit" onclick="toggleEditForm(<?= $message['id']; ?>)">Edit</button>
                            
                            <form action="" method="post">
                                <input type="hidden" name="delete_id" value="<?= $message['id']; ?>">
                                <button type="submit" name="delete" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this message?')">Delete</button>
                            </form>
                        </div>
                        
                        <!-- Forma e fshehur për editim -->
                        <div class="edit-form" id="edit-form-<?= $message['id']; ?>">
                            <form action="" method="post">
                                <input type="hidden" name="message_id" value="<?= $message['id']; ?>">
                                <div class="input-field">
                                    <label>Name <sup>*</sup></label>
                                    <input type="text" name="name" value="<?= htmlspecialchars($message['name']); ?>" required>
                                </div>
                                <div class="input-field">
                                    <label>Email <sup>*</sup></label>
                                    <input type="email" name="email" value="<?= htmlspecialchars($message['email']); ?>" required>
                                </div>
                                <div class="input-field">
                                    <label>Subject <sup>*</sup></label>
                                    <input type="text" name="subject" value="<?= htmlspecialchars($message['subject']); ?>" required>
                                </div>
                                <div class="input-field">
                                    <label>Message <sup>*</sup></label>
                                    <textarea name="message" required><?= htmlspecialchars($message['message']); ?></textarea>
                                </div>
                                <button type="submit" name="update_message" class="btn">Update</button>
                                <button type="button" class="btn" onclick="toggleEditForm(<?= $message['id']; ?>)">Cancel</button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="empty">No messages yet!</p>
                <?php endif; ?>
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

        function toggleMessageForm() {
            const form = document.getElementById('messageForm');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }

        function toggleEditForm(messageId) {
            const editForm = document.getElementById(`edit-form-${messageId}`);
            editForm.style.display = editForm.style.display === 'none' ? 'block' : 'none';
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
    <script src="../script.js"></script>
</body>
</html>