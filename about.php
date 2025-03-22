<?php 
    if (session_status() == PHP_SESSION_NONE) {
        session_start(); // Vetëm nëse sesioni nuk është aktiv
    }
    include 'components/connection.php';

    if(isset($_SESSION['user_id'])){
        $user_id = $_SESSION['user_id'];
    } else {
        $user_id = '';
    }

    if(isset ($_POST['logout'])){
        session_destroy();
        header('Location: login.php');
       
    }
?>

<style type="text/css"> 
    <?php  include 'style.css' ; ?>
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
    <?php  include 'components/header.php'; ?>
    <div class="main">
        <div class="tittle2">
            <a href="home.php">Home</a><span>about</span>
        </div>
        <div class="about-category">
            <div class="box">
                <img src="img/1.png">
                <div class="detail">
                    <span>Libra</span>
                    <h1> Libra shqip</h1>
                    <a href="view_products.php" class="btn">shop now</a>
                </div>
            </div>
            <div class="box">
                <img src="img/2.png">
                <div class="detail">
                    <span>Libra</span>
                    <h1> Libra te huaj</h1>
                    <a href="view_products.php" class="btn">shop now</a>
                </div>
            </div>
            <div class="box">
                <img src="img/5.png">
                <div class="detail">
                    <span>Cards</span>
                    <h1>Loyalty Card</h1>
                    <a href="view_products.php" class="btn">shop now</a>
                </div>
            </div>
            <div class="box">
                <img src="img/6.png">
                <div class="detail">
                    <span>Cards</span>
                    <h1> E-Gift Card</h1>
                    <a href="view_products.php" class="btn">shop now</a>
                </div>
            </div>
        </div>
        
       <div class="about">
        <div class="row">
            <div class="img-box">
                <img src="img/events1.jpg">
            </div>
            <div class="detail">
                <h1>Ejani dhe mbani eventet tuaja tek ne</h1>
                    <p>Kërkoni një vend të veçantë për të mbajtur eventin tuaj të ardhshëm? 
                        Tek ne do të gjeni ambientin perfekt për çdo lloj aktiviteti: qoftë një seminar, një lansim libri, apo një takim letrar.
                         Me hapësira moderne dhe komode, ne ofrojmë gjithçka që ju nevojitet për ta bërë ngjarjen tuaj të paharrueshme.
                          Na kontaktoni dhe le të krijojmë së bashku një përvojë të veçantë për ju dhe të ftuarit tuaj!</p>

                    <a href="events.php" class="btn">Reserve event</a>
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

      <?php include 'components/footer.php'?> <!-- Vendosim footer-in këtu -->
    </div>
    <script scr="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
    <script src="script.js"></script>
    <?php  include 'components/alert.php' ; ?>

</body>
</html>