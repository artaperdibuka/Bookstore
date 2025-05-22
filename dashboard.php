<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once 'components/connection.php';

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
    header('Location: login.php');
    exit();
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
    <title>BookstoreBuzuku Admin Panel - Dashboard Page</title>
    <style type="text/css">
        <?php include 'style.css'; ?>
    </style>
</head>

<body>
    <?php include 'components/admin_header.php'; ?>
    <div class="main">
       <div class="banner">
        <h1>Dashboard</h1>

       </div>
       <div class="title2">
        <a href="dashboard.php">Home</a><span>Dashboard</span>

       </div>
       <section class="dashboard">
        <h1 class="heading"> dashboard</h1>
        <div class="box-container">
            <div class="box">
                <h3>welcome</h3>
                <p><?php echo $fetch_profile['name']; ?></p>
                <a href="profile.php" class="btn">profile</a>
            </div>
            <div class="box">
               
                <?php 
                $select_product = $conn->prepare("SELECT * FROM `products`");
                $select_product->execute();
                $num_of_products = $select_product->rowCount();

                ?>
                <h3><?= $num_of_products; ?></h3>
                <p>products added</p>
                <a href="add_products.php" class="btn">add new products</a>
                
            </div> 
        </div>

       </section>
        
    </div>


    <script>
    
        function showAlert(type, message) {
            Swal.fire({
                icon: type,
                title: type === 'success' ? 'Sukses!' : 'Kujdes!',
                text: message,
                confirmButtonText: 'Mirë'
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
    </script>
    <script src="contact-validation.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
    <script src="script.js"></script>