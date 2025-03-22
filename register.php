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
    $id = uniqueid();
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $pass = filter_var($_POST['pass'], FILTER_SANITIZE_STRING);
    $cpass = filter_var($_POST['cpass'], FILTER_SANITIZE_STRING);

    // Kontrollo nëse email-i ekziston tashmë në bazën e të dhënave
    $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
    $select_user->execute([$email]);

    if ($select_user->rowCount() > 0) {
        $message[] = 'Email already exists';
    } else {
        if ($pass != $cpass) {
            $message[] = 'Confirm your password';
        } else {
            // Krijo një hash për fjalëkalimin për siguri
            $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
            $insert_user = $conn->prepare("INSERT INTO `users`(id, name, email, password) VALUES (?, ?, ?, ?)");
            $insert_user->execute([$id, $name, $email, $hashed_pass]);

            // Vendos të dhënat e përdoruesit në sesion për hyrjen e menjëhershme
            $_SESSION['user_id'] = $id;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;

            // Ridrejto në faqen kryesore
            header('Location: home.php');
            exit;
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
    <title>BookstoreBuzuku - register now </title>
</head>
<body>
    <div class="main-container">
        <section class="form-container">
            <div class="title">
                <h1>Register Now</h1>
                <p>Lorem ipsum lorem ispum haha</p>


            </div>
            <form action="" method="post">
                <div class="input-field">
                    <p>Your name<span class="required-star">*</span></p>
                    <input type="text" name="name"  required placeholder=" Enter your name" maxlength="50"
                    oninput="this.value=this.value.replace(/\s/g, '')">
                </div>
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
                <div class="input-field">
                    <p>Confirm password<span class="required-star">*</span></p>
                    <input type="password" name="cpass"  required placeholder=" Enter your password" maxlength="50"
                    oninput="this.value=this.value.replace(/\s/g, '')">
                </div>
                <input type="submit" name="submit" value="register now" class="btn">
                <p>already have an account?<a href="login.php">Login now</a></p>

            </form>

        </section>

    </div>

    
</body>
</html>