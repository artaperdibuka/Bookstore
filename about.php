<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'components/connection.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
}

if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: login.php');
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
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <title>BookstoreBuzuku - About Us Page</title>
</head>

<body>
    <?php include 'components/header.php'; ?>
    <div class="main">
        <div class="tittle2">
            <a href="home.php">Home</a><span>about</span>
        </div>
        
        <?php $xml = simplexml_load_file('about.xml');

        echo '<section id="about-us" class="about-section">';
        echo '<div class="container">';

        // Section title
        echo '<h2 class="section-title">About Us</h2>';

        // Basic information table
        echo '<div class="about-grid">';
        echo '<div class="about-card">';
        echo '<h3>Basic Information</h3>';
        echo '<table class="about-table">';
        echo '<tr><th>Name:</th><td>' . $xml->company->name . '</td></tr>';
        echo '<tr><th>Founded:</th><td>' . $xml->company->founded . '</td></tr>';
        echo '<tr><th>Mission:</th><td>' . $xml->company->mission . '</td></tr>';
        echo '<tr><th>Location:</th><td>' . $xml->company->location . '</td></tr>';
        echo '</table>';
        echo '</div>';

        // Team table
        echo '<div class="about-card">';
        echo '<h3>Our Team</h3>';
        echo '<table class="about-table">';
        echo '<tr><th>Name</th><th>Position</th><th>Experience</th></tr>';
        foreach ($xml->team->member as $member) {
            echo '<tr>';
            echo '<td>' . $member->name . '</td>';
            echo '<td>' . $member->position . '</td>';
            echo '<td>' . $member->experience . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        echo '</div>';

        // Statistics
        echo '<div class="about-card stats">';
        echo '<h3>Our Numbers</h3>';
        echo '<div class="stats-container">';
        echo '<div class="stat-item"><span class="stat-number">' . $xml->stats->books . '</span><span class="stat-label">Books</span></div>';
        echo '<div class="stat-item"><span class="stat-number">' . $xml->stats->customers . '</span><span class="stat-label">Customers</span></div>';
        echo '<div class="stat-item"><span class="stat-number">' . $xml->stats->branches . '</span><span class="stat-label">Branches</span></div>';
        echo '</div>';
        echo '</div>';

        echo '</div>'; // close about-grid
        echo '</div>'; // close container
        echo '</section>';

        ?>

<div class="about-category">
            <div class="box">
                <img src="img/1.png" alt="Newest Books">
                <div class="detail">
                    <span>Books</span>
                    <h1>Newest</h1>
                    <a href="view_products.php?sort=newest" class="btn">shop now</a>
                </div>
            </div>
            <div class="box">
                <img src="img/2.png" alt="Books on Sale">
                <div class="detail">
                    <span>Books</span>
                    <h1>On Sale</h1>
                    <a href="view_products.php?sort=discount" class="btn">shop now</a>
                </div>
            </div>
        </div>
        <div class="about">
            <div class="row">
                <div class="img-box">
                    <img src="img/events1.jpg">
                </div>
                <div class="detail">
                    <h1>Come and Host Your Events With Us</h1>
                    <p>Are you looking for a special place to host your next event?
                        With us, you will find the perfect setting for any type of activity—whether it's a seminar, a book launch, or a literary gathering.
                        With modern and comfortable spaces, we offer everything you need to make your event unforgettable.
                        Contact us and let's create a unique experience together for you and your guests!</p>


                </div>

            </div>

        </div>
        <section class="services">
            <div class="title">
                <h1>Why us?</h1>
                <p>We offer more than books — we offer a unique reading experience, personal service, and a passion for literature that sets us apart.</p>

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

        <?php include 'components/footer.php' ?>
    </div>
    <script scr="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
    <script src="script.js"></script>
    <?php include 'components/alert.php'; ?>

</body>

</html>