<?php 
// Fillo sesionin nëse nuk është aktiv
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Përfshij lidhjen me bazën e të dhënave
include 'components/connection.php';

// Deklaro një array për mbledhjen e mesazheve të gabimeve
$message = [];

// Kontrollo nëse forma është dorëzuar
if (isset($_POST['submit'])) {
    // Gjenero një ID unike për përdoruesin
    $email = $_POST['email'];
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $pass = $_POST['pass'];
    $pass = filter_var($_POST['pass'], FILTER_SANITIZE_STRING);
   

    // Kontrollo nëse email-i ekziston tashmë në bazën e të dhënave
    $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ? AND password = ?");
    $select_user->execute([$email, $pass]);
    $row = $select_user->fetch(PDO::FETCH_ASSOC);

    if($select_user->rowCount() > 0){
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_name'] = $row['name'];
        $_SESSION['user_email'] = $row['email'];
        header('location: home.php');

    }else{
        $message[] = 'Incorret username or passwoerd';
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

            </form>

        </section>

    </div>

    
</body>
</html>