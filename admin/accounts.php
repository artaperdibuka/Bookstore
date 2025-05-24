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

// Shto përdorues të ri
if (isset($_POST['add_user'])) {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);


    // Kontrollo nëse emaili ekziston
    $check_email = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
    $check_email->execute([$email]);

    if ($check_email->rowCount() > 0) {
        $warning_msg[] = 'Email already exists!';
    } else {
        $insert_user = $conn->prepare("INSERT INTO `users` (name, email, password, user_type) VALUES (?, ?, ?, ?)");
        $insert_user->execute([$name, $email, $password, $user_type]);
        $success_msg[] = 'User added successfully!';
    }
}

// Përditëso përdoruesin
// Përditëso përdoruesin
if (isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'];
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
  
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Kontrollo nëse emaili ekziston për një përdorues tjetër
    $check_email = $conn->prepare("SELECT * FROM `users` WHERE email = ? AND id != ?");
    $check_email->execute([$email, $user_id]);

    if ($check_email->rowCount() > 0) {
        $warning_msg[] = 'Email already exists for another user!';
    } else {
        // Nëse fjalëkalimi është dhënë
        if (!empty($new_password)) {
            if ($new_password !== $confirm_password) {
                $warning_msg[] = 'Passwords do not match!';
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_user = $conn->prepare("UPDATE `users` SET name = ?, email = ?, password = ? WHERE id = ?");
                $update_user->execute([$name, $email, $hashed_password, $user_id]);
                $success_msg[] = 'User (including password) updated successfully!';
            }
        } else {
            // Përditëso pa ndryshuar fjalëkalimin
            $update_user = $conn->prepare("UPDATE `users` SET name = ?, email = ?, user_type = ? WHERE id = ?");
            $update_user->execute([$name, $email, $user_type, $user_id]);
            $success_msg[] = 'User updated successfully!';
        }
    }
}
// Fshi përdoruesin
if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];

    // Mos lejo fshirjen e adminit aktual
    if ($user_id == $admin_id) {
        $warning_msg[] = 'You cannot delete your own account while logged in!';
    } else {
        $delete_user = $conn->prepare("DELETE FROM `users` WHERE id = ?");
        $delete_user->execute([$user_id]);
        $success_msg[] = 'User deleted successfully!';
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
    <title>BookstoreBuzuku Admin Panel - Manage Users</title>
    <style type="text/css">
        <?php include '../style.css'; ?>
    </style>
</head>

<body>
    <?php include __DIR__ . '/../components/admin_header.php'; ?>

    <div class="main">
        <div class="tittle2">
            <a href="dashboard.php">dashboard</a><span>manage users</span>
        </div>

        <section class="accounts">
            <h1 class="heading">manage users</h1>

            <!-- Forma për shtimin e përdoruesve -->
            <button class="toggle-form-btn" onclick="toggleUserForm()">Add New User</button>

            <div class="user-form" id="userForm" style="display: none;">
                <form action="" method="post">
                    <div class="input-field">
                        <label>Name </label>
                        <input type="text" name="name" maxlength="50" required>
                    </div>
                    <div class="input-field">
                        <label>Email </label>
                        <input type="email" name="email" maxlength="50" required>
                    </div>
                    <div class="input-field">
                        <label>Password </label>
                        <input type="password" name="password" required>
                    </div>
                    <div class="flex-btn">
                    <button type="submit" name="add_user" class="btn">Add User</button>
                <button type="button" class="btn" onclick="toggleUserForm()">Cancel</button>
                </div>
                </form>
            </div>


            <!-- Forma për editimin e përdoruesve -->
            <div class="user-form" id="editUserForm" style="display: none;">
                <form action="" method="post">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    <div class="input-field">
                        <label>Name </label>
                        <input type="text" name="name" id="edit_name" maxlength="50" required>
                    </div>
                    <div class="input-field">
                        <label>Email </label>
                        <input type="email" name="email" id="edit_email" maxlength="50" required>
                    </div>
                   
                    <div class="input-field">
                        <label>New Password (leave blank to keep current)</label>
                        <input type="password" name="new_password" id="edit_password">
                    </div>
                    <div class="input-field">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" id="edit_confirm_password">
                    </div>
                   <div class="flex-btn">
                        <button type="submit" name="update_user" class="btn">Update User</button>
                        <button type="button" class="btn" onclick="toggleUserForm()">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- Lista e përdoruesve -->
            <div class="box-container">
                <?php
                $select_user = $conn->prepare("SELECT * FROM `users`");
                $select_user->execute();
                if ($select_user->rowCount() > 0) {
                    while ($fetch_user = $select_user->fetch(PDO::FETCH_ASSOC)) {
                        $user_id = $fetch_user['id'];
                ?>
                        <div class="box">
                            <p>user id: <span><?= $fetch_user['id']; ?></span></p>
                            <p>user name: <span><?= htmlspecialchars($fetch_user['name']); ?></span></p>
                            <p>user email: <span><?= htmlspecialchars($fetch_user['email']); ?></span></p>
                           
                            <div class="action-buttons">
                                <button class="btn" onclick="openEditModal(
                            '<?= $fetch_user['id']; ?>',
                            '<?= htmlspecialchars($fetch_user['name']); ?>',
                            '<?= htmlspecialchars($fetch_user['email']); ?>',
                           
                        )">Edit</button>

                                <form action="" method="post" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?= $fetch_user['id']; ?>">
                                    <button type="submit" name="delete_user" class="btn" onclick="return confirm('Are you sure you want to delete this user?')">Delete</button>
                                </form>
                            </div>
                        </div>
                <?php
                    }
                } else {
                    echo '<p class="empty">no users registered yet!</p>';
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