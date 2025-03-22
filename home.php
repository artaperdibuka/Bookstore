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
        header('Location: home.php');
        exit; // Shtoni exit pas header për të ndaluar ekzekutimin e mëtejshëm
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
    <title>BookstoreBuzuku - Home Page</title>
</head>
<body>
    <?php  include 'components/header.php'; ?>
    <div class="main">
      <section class="home-section">
        <div class="slider">
                <div class="slider__slider slide1">
                    <div class="overlay"></div>
                    <div class="slide-detail">
                     <h1>Welcome to Library</h1>
                        <p>x</p>
                        <a href="view_products.php" class="btn">Shop now</a>
                    </div>
                    <div class="hero-dec-top"></div>
                    <div class="hero-dec-bottom"></div>
                </div>
            <!-- slide end -->
            <div class="slider__slider slide2">
                    <div class="overlay"></div>
                    <div class="slide-detail">
                    <h1>Welcome to Library</h1>
                    <p>x</p>
                        <a href="view_products.php" class="btn">Shop now</a>
                    </div>
                    <div class="hero-dec-top"></div>
                    <div class="hero-dec-bottom"></div>
                </div>
            <!-- slide end -->
            <div class="slider__slider slide3">
                    <div class="overlay"></div>
                    <div class="slide-detail">
                        <h1>Welcome to Library</h1>
                        <p>x</p>
                        <a href="view_products.php" class="btn">Shop now</a>
                    </div>
                    <div class="hero-dec-top"></div>
                    <div class="hero-dec-bottom"></div>
                </div>
            <!-- slide end -->
            <div class="slider__slider slide4">
                    <div class="overlay"></div>
                    <div class="slide-detail">
                        <h1>Welcome to Library</h1>
                        <p></p>
                        <a href="view_products.php" class="btn">Shop now</a>
                    </div>
                    <div class="hero-dec-top"></div>
                    <div class="hero-dec-bottom"></div>
                </div>
            <!-- slide end -->
            <div class="slider__slider slide5">
                    <div class="overlay"></div>
                    <div class="slide-detail">
                        <h1>Welcome to Library</h1>
                        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Id, cum? Corrupti placeat ex</p>
                        <a href="view_products.php" class="btn">Shop now</a>
                    </div>
                    <div class="hero-dec-top"></div>
                    <div class="hero-dec-bottom"></div>
                </div>
            <!-- slide end -->
            <div class="left-arrow"><i class="bx bxs-left-arrow"></i></div>
            <div class="right-arrow"><i class="bx bxs-right-arrow"></i></div>
            </div>
            
      </section>
      <!-- home slider end -->
       <section class="thumb">
        <div class="box-container">
            <div class="box">
                <img src="img/thumb11.png">
                <h3>Shkence</h3>
                <p>lorem ipsum dolar sit amer elit.</p>
                <i class="bx bx-chevron-right"></i>
            </div>
            <div class="box">
                <img src="img/thumb12.png">
                <h3>Poezi</h3>
                <p>lorem ipsum dolar sit amer elit.</p>
                <i class="bx bx-chevron-right"></i>
            </div>
            <div class="box">
                <img src="img/thumb13.png">
                <h3>Letersi</h3>
                <p>lorem ipsum dolar sit amer elit.</p>
                <i class="bx bx-chevron-right"></i>
            </div>
            <div class="box">
                <img src="img/thumb14.png">
                <h3>Klasik</h3>
                <p>lorem ipsum dolar sit amer elit.</p>
                <i class="bx bx-chevron-right"></i>
            </div>

        </div>
       </section>
       <section class="container">
        <div class="box-container">
            <div class="box">
                <img src="img/about-us.png"  id="sale-image" >
            </div>
            <div class="box">
                <img src="img/sale.png" >
                <span>Nxitoni</span>
                <h1>Zbritje deri ne 50%</h1>
                <p> Ne shume lloje te librave, nga me te shiturat e deri nga me te rejat...</p>
            </div>
        </div>
       </section>
       <section class="shop">
            <div class="title">
                    <!-- <img src="img/download.png"> -->
                <h1>Librat me te shitura</h1>
            </div>
            <div class="shop-slider">
                
                <!-- <div class="row">
                    <img src="img/about.jpg">
                    <div class="row-detail">
                        <img src="img/basil.png">
                        <div class="top-footer">
                            <h1>Librat me te shitura</h1>
                        </div>
                    </div>
                </div> -->
                <div class="box-container">
                    <div class="box">
                        <img src="img/b1.png" alt="book1">
                        <a href="view_product.php" class="btn">Shop now</a>
                    </div>
                    <div class="box">
                        <img src="img/b2.jpg">
                        <a href="view_product.php" class="btn">Shop now</a>
                    </div>
                    <div class="box">
                        <img src="img/b3.jpg">
                        <a href="view_product.php" class="btn">Shop now</a>
                    </div>
                    <div class="box">
                        <img src="img/b4.jpg">
                        <a href="view_product.php" class="btn">Shop now</a>
                    </div>
                    <div class="box">
                        <img src="img/b5.jpg">
                        <a href="view_product.php" class="btn">Shop now</a>
                    </div>
                    <div class="box">
                        <img src="img/b6.jpg">
                        <a href="view_product.php" class="btn">Shop now</a>
                    </div>
                    <div class="box">
                        <img src="img/b2.png">
                        <a href="view_product.php" class="btn">Shop now</a>
                    </div>
                    <div class="box">
                        <img src="img/b5.jpg">
                        <a href="view_product.php" class="btn">Shop now</a>
                    </div>
                    <div class="box">
                        <img src="img/b1.jpg">
                        <a href="view_product.php" class="btn">Shop now</a>
                    </div>
                    <div class="box">
                        <img src="img/b6.jpg">
                        <a href="view_product.php" class="btn">Shop now</a>
                    </div>
                    <div class="box">
                        <img src="img/b4.jpg">
                        <a href="view_product.php" class="btn">Shop now</a>
                    </div>
                    <div class="box">
                        <img src="img/b3.jpg">
                        <a href="view_product.php" class="btn">Shop now</a>
                    </div>
                
                 </div>

            <div class="shop-left-arrow"><i class="bx bxs-left-arrow"></i></div>
            <div class="shop-right-arrow"><i class="bx bxs-right-arrow"></i></div>
            </div>
        
       </section>
       <section class="services">
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js" defer></script>
    <script src="script.js" defer></script>
    <?php  include 'components/alert.php' ; ?>

</body>
</html>