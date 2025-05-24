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

if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: ../login.php');
    exit();
}

// Funksione për menaxhimin e mesazheve
if (isset($_POST['add_message'])) {
    $stmt = $conn->prepare("INSERT INTO `messages` (name, email, subject, message) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([
        filter_var($_POST['name'], FILTER_SANITIZE_STRING),
        filter_var($_POST['email'], FILTER_SANITIZE_EMAIL),
        filter_var($_POST['subject'], FILTER_SANITIZE_STRING),
        filter_var($_POST['message'], FILTER_SANITIZE_STRING)
    ])) {
        echo "<script>Swal.fire('Success!', 'Message added successfully!', 'success');</script>";
    } else {
        echo "<script>Swal.fire('Error!', 'Failed to add message!', 'error');</script>";
    }
}

if (isset($_POST['update_message'])) {
    $stmt = $conn->prepare("UPDATE `messages` SET name=?, email=?, subject=?, message=? WHERE id=?");
    if ($stmt->execute([
        filter_var($_POST['name'], FILTER_SANITIZE_STRING),
        filter_var($_POST['email'], FILTER_SANITIZE_EMAIL),
        filter_var($_POST['subject'], FILTER_SANITIZE_STRING),
        filter_var($_POST['message'], FILTER_SANITIZE_STRING),
        $_POST['message_id']
    ])) {
        echo "<script>Swal.fire('Success!', 'Message updated successfully!', 'success');</script>";
    } else {
        echo "<script>Swal.fire('Error!', 'Failed to update message!', 'error');</script>";
    }
}

if (isset($_POST['delete'])) {
    $stmt = $conn->prepare("DELETE FROM `messages` WHERE id=?");
    if ($stmt->execute([$_POST['delete_id']])) {
        echo "<script>Swal.fire('Success!', 'Message deleted successfully!', 'success');</script>";
    } else {
        echo "<script>Swal.fire('Error!', 'Failed to delete message!', 'error');</script>";
    }
}

// Merr të gjitha mesazhet
$messages = $conn->query("SELECT * FROM `messages`")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>BookstoreBuzuku Admin Panel -Manage Messages</title>
    <style type="text/css">
        <?php include '../style.css'; ?>

    </style>
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="dashboard">
    <div class="main">
         <div class="tittle2">
            <a href="dashboard.php">dashboard</a><span>manage messages</span>
        </div>
    <div class="heading" style="text-align: center;">
        <h1>MANAGE Messages</h1>
        <div class="flex-btn">
        <button class="btn" onclick="toggleMsgForm('addMsgForm')">Add New Message</button>
    </div>
    </div>

    <div class="messages-container">
        <!-- Forma për shtim -->
        <form id="addMsgForm" class="msg-hidden message-card" method="post">
            <div class="msg-form-group">
                <label>Name</label>
                <input type="text" name="name" required>
            </div>
            <div class="msg-form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="msg-form-group">
                <label>Subject</label>
                <input type="text" name="subject" required>
            </div>
            <div class="msg-form-group">
                <label>Message</label>
                <textarea name="message" required style="resize: none;"></textarea>
            </div>
            <div class="flex-btn">
            <button type="submit" name="add_message" class="btn">Save</button>
            <button type="button" class="btn" onclick="toggleMsgForm('addMsgForm')">Cancel</button></div>
        </form>
        
        <!-- Lista e mesazheve -->
        <?php foreach ($messages as $message): ?>
        <div class="message-card">
            <h3><?= htmlspecialchars($message['name']) ?> - <?= htmlspecialchars($message['subject']) ?></h3>
            <p><?= htmlspecialchars($message['message']) ?></p>
            <p><small><?= htmlspecialchars($message['email']) ?></small></p>
            
           <div class="flex-btn">
             <button class="btn" onclick="toggleMsgForm('editMsgForm<?= $message['id'] ?>')">Edit</button>
            <form style="display:inline" method="post">
                <input type="hidden" name="delete_id" value="<?= $message['id'] ?>">
                <button type="submit" name="delete" class="btn" onclick="return confirmDelete()">Delete</button>
            </form>
           </div>
            
            <!-- Forma për editim -->
            <form id="editMsgForm<?= $message['id'] ?>" class="msg-hidden" method="post">
                <input type="hidden" name="message_id" value="<?= $message['id'] ?>">
                <div class="msg-form-group">
                    <label>Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($message['name']) ?>" required>
                </div>
                <div class="msg-form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($message['email']) ?>" required>
                </div>
                <div class="msg-form-group">
                    <label>Subject</label>
                    <input type="text" name="subject" value="<?= htmlspecialchars($message['subject']) ?>" required>
                </div>
                <div class="msg-form-group">
                    <label>Message</label>
                    <textarea name="message" required style="resize: none;"><?= htmlspecialchars($message['message']) ?></textarea>
                </div>
                <div class="flex-btn">
                <button type="submit" name="update_message" class="btn">Update</button>
                <button type="button" class="btn" onclick="toggleMsgForm('editMsgForm<?= $message['id'] ?>')">Cancel</button>
            </div>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
</section>
     

<script src="../js/admin_script.js"></script>
<script>
    function toggleMsgForm(id) {
        document.getElementById(id).classList.toggle('msg-hidden');
    }
    
    function confirmDelete() {
        return Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            return result.isConfirmed;
        });
    }
</script>
<script src="../script.js"></script>
</body>
</html>