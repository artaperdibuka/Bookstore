<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'components/connection.php';

$message = [];

if (isset($_POST['submit'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $pass = filter_var($_POST['pass'], FILTER_SANITIZE_STRING);

    // Së pari kontrollo nëse është admin
    $select_admin = $conn->prepare("SELECT * FROM `admin` WHERE email = ? AND password = ?");
    $select_admin->execute([$email, $pass]);
    
    if($select_admin->rowCount() > 0){
        $admin_row = $select_admin->fetch(PDO::FETCH_ASSOC);
        $_SESSION['admin_id'] = $admin_row['id'];
        $_SESSION['admin_name'] = $admin_row['name'];
        $_SESSION['admin_email'] = $admin_row['email'];
        
        // Pastro çdo session të userit nëse ekziston
        unset($_SESSION['user_id']);
        unset($_SESSION['user_name']);
        unset($_SESSION['user_email']);
        
        header('location: admin/dashboard.php');
        exit;
    }
    else {
        // Nëse nuk është admin, kontrollo nëse është user i rregullt
        $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ? AND password = ?");
        $select_user->execute([$email, $pass]);
        
        if($select_user->rowCount() > 0){
            $row = $select_user->fetch(PDO::FETCH_ASSOC);
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['name'];
            $_SESSION['user_email'] = $row['email'];
            
            // Pastro çdo session të adminit nëse ekziston
            unset($_SESSION['admin_id']);
            unset($_SESSION['admin_name']);
            unset($_SESSION['admin_email']);
            
            header('location: home.php');
            exit;
        }
        else {
            $message[] = 'Email ose password i gabuar!';
        }
    }
}
?>
<style type="text/css"> 
        <?php include 'style.css'; ?>
</style>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookstoreBuzuku - login now </title>
</head>
<body>
    <div class="main-container">
        <section class="form-container">
            <div class="title">
                <h1>login Now</h1>
                <p>Lorem ipsum lorem ispum haha</p>


            </div>
            <form action="" method="post">
                
                <div class="input-field">
                    <p>Your email<span class="required-star">*</span></p>
                    <input type="email" name="email"  required placeholder=" Enter your email" maxlength="50"
                    oninput="this.value=this.value.replace(/\s/g, '')">
                </div>
                <div class="input-field">
                    <p>Your password<span class="required-star">*</span></p>
                    <input type="password" name="pass"  required placeholder=" Enter your password" maxlength="50"
                    oninput="this.value=this.value.replace(/\s/g, '')">
                </div>
        
                <input type="submit" name="submit" value="login now" class="btn">
                <p>do not have an account?<a href="register.php">register now</a></p>
<?php
if (!empty($message)) {
    foreach ($message as $msg) {
        echo '<div class="error-message">'.$msg.'</div>';
    }
}
?>
            </form>


        </section>

    </div>

    
</body>
</html>