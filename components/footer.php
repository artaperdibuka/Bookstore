<?php
require_once 'components/connection.php';


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['subscribe'])) {
    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email address!');</script>";
    } else {
        // Kontrollo nëse ekziston emaili
        $check = $conn->prepare("SELECT * FROM newsletter_subscribers WHERE email = ?");
        $check->execute([$email]);

        if ($check->rowCount() > 0) {
            echo "<script>alert('You are already subscribed!');</script>";
        } else {
            $insert = $conn->prepare("INSERT INTO newsletter_subscribers (email) VALUES (?)");
            $insert->execute([$email]);
            echo "<script>alert('Thank you for subscribing!');</script>";
        }
    }
}
?>

<div class="top-footer">
    <h2><i class="bx bx-envelope"></i> Sign Up For Newsletter</h2>
    <form method="POST" action="">
        <div class="input-field">
            <input type="text" name="email" placeholder="email address..." required>
            <button type="submit" name="subscribe" class="btn">Subscribe</button>
        </div>
    </form>
</div>

<footer>
    <div  class="overlay"></div>
    <div class="footer-content">
        <div class="img-box">
        <img src="img/logoo.png">
    </div>
    <div class="inner-footer">
        <div class="card">
            <h3>about us</h3>
            <ul>
                <li>about us</li>
                <li>our difference</li>
                <li>community matters</li>
                <li>press</li>
                <li>blog</li>
            </ul>
        </div> 
        <div class="card">
            <h3>services</h3>
            <ul>
                <li>order</li>
                <li>help center</li>
                <li>shipping</li>
                <li>term of use</li>
                <li>account detail</li>
                <li>my account</li>
            </ul>
        </div>
        <div class="card">
            <h3>local</h3>
            <ul>
                <li>Sheshi Skenderbeu</li>
                <li>Albi Mall</li>
                <li>Prishtina Mall</li>
            </ul>
        </div>
        <div class="card">
            <h3>newsletter</h3>
             <p>Sign Up For Newsletter</p>
             <div class="social-links">
                <i class="bx bxl-instagram"></i>
                <i class="bx bxl-facebook"></i>
                <i class="bx bxl-whatsapp"></i>
             </div>
            </div>
        </div>
        
        <div class="bottom-footer">
            <p>© 1990 - 2025 Libraria Buzuku. All Rights Reserved.</p>

        </div>
    </div>
</footer>