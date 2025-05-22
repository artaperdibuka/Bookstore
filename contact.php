<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once 'components/connection.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
}

if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: login.php');
}
$user_id = $_SESSION['user_id'] ?? 'guest'; // Ose '0' nëse është int
if (isset($_POST['submit_message'])) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';

    $errors = [];

    if (empty($name)) $errors[] = "Name is required";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
    if (empty($message)) $errors[] = "Message is required";

    if (empty($errors)) {
        try {
            $sql = "INSERT INTO messages (user_id, name, email, subject, message) 
                    VALUES (:user_id, :name, :email, :subject, :message)";

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':subject', $subject);
            $stmt->bindParam(':message', $message);

             
if($stmt->execute()) {
                $_SESSION['success_message'] = "Mesazhi u dërgua me sukses!";
            } else {
                $_SESSION['error_message'] = "Gabim në dërgimin e mesazhit!";
            }
        } catch(PDOException $e) {
            $_SESSION['error_message'] = "Gabim në databazë: " . $e->getMessage();
        }
        
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    } else {
        $_SESSION['error_message'] = implode("<br>", $errors);
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>BookstoreBuzuku - Contact Page</title>
    <style type="text/css">
        <?php include 'style.css'; ?>
    </style>
</head>

<body>
    <?php include 'components/header.php'; ?>
    <div class="main">
        <div class="tittle2">
            <a href="home.php">Home</a><span>Contact</span>
        </div>
        
        <div class="form-container">
            <form id="contactForm" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['error_message'];
                                                    unset($_SESSION['error_message']); ?></div>
                <?php endif; ?>

                <div class="input-field">
                    <div class="title">
                        <h1>leave a message</h1>
                    </div>
                    <p>Your name<span class="required-star">*</span></p>
                    <input type="text" name="name" id="name" required
                        value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                    <div id="nameError" class="error-message" style="color: red; font-size: 0.8em;"></div>
                </div>

                <div class="input-field">
                    <p>Your email<span class="required-star">*</span></p>
                    <input type="email" name="email" id="email" required
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    <div id="emailError" class="error-message" style="color: red; font-size: 0.8em;"></div>
                </div>
                <div class="input-field">
                    <p>Subject</p>
                    <input type="text" name="subject" id="subject" value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>">
                </div>

                <div class="input-field">
                    <p>Your message<span class="required-star">*</span></p>
                    <textarea name="message" id="message" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                    <div id="messageError" class="error-message" style="color: red; font-size: 0.8em;"></div>
                </div>

                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                <button type="submit" name="submit_message" class="btn">Send message</button>
            </form>
        </div>

        <div class="address">
            <div class="title">
                <h1>Contact Details</h1>
                <p>lorem ipsum bla bla lorem ipsum bla bla</p>
            </div>
            <div class="box-container">
                <div class="box">
                    <i class="bx bx-map-pin"></i>
                    <div>
                        <h4>Address</h4>
                        <p>Prishtina mall, Floor 1</p>
                    </div>
                </div>
                <div class="box">
                    <i class="bx bxs-phone-call"></i>
                    <div>
                        <h4>Phone Number</h4>
                        <p>+383 93 903 949</p>
                    </div>
                </div>
                <div class="box">
                    <i class="bx bxs-envelope"></i>
                    <div>
                        <h4>Email</h4>
                        <p>contact@bookstoreuzuku.com</p>
                    </div>
                </div>
            </div>
        </div>

        <section class="services">
            <div class="title">
                <h1>Pse ne?</h1>
                <p>ktu ni pargraf e marrim prej chatgbts</p>

            </div>
            <div class="box-container">
                <div class="box">
                    <img src="img/icon2.png">
                    <div class="detail">
                        <h3>great savings</h3>
                        <p>save big every order</p>
                    </div>
                </div>
                <div class="box">
                    <img src="img/icon1.png">
                    <div class="detail">
                        <h3>24*7</h3>
                        <p>one-on-one support</p>
                    </div>
                </div>
                <div class="box">
                    <img src="img/icon0.png">
                    <div class="detail">
                        <h3>gift vouchers</h3>
                        <p>vouchers on every festivals</p>
                    </div>
                </div>
                <div class="box">
                    <img src="img/icon.png">
                    <div class="detail">
                        <h3>worlwide delivery</h3>
                        <p>dropship worldwide</p>
                    </div>
                </div>
            </div>
        </section>


        <?php include 'components/footer.php'; ?>
    </div>
    <!-- <script>
        const number = document.getElementById('number').value.trim();
if(number === '') {
    document.getElementById('numberError').textContent = 'Phone number is required';
    isValid = false;
}
    </script> -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (isset($_SESSION['success_message'])): ?>
            Swal.fire({
                icon: 'success',
                title: 'Sukses!',
                text: '<?php echo $_SESSION['success_message']; ?>',
                confirmButtonText: 'Mirë'
            });
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            Swal.fire({
                icon: 'error',
                title: 'Gabim!',
                html: '<?php echo str_replace("'", "\\'", $_SESSION['error_message']); ?>',
                confirmButtonText: 'Kupto'
            });
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
    });
</script>

    <script src="contact-validation.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js" defer></script>
    <script src="script.js" defer></script>
    <?php include 'components/alert.php'; ?>
</body>

</html>