<?php 
    if (session_status() == PHP_SESSION_NONE) {
        session_start(); 
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
        exit; 
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
            <div class="left-arrow"><i class="bx bxs-left-arrow"></i></div>
            <div class="right-arrow"><i class="bx bxs-right-arrow"></i></div>
            </div>
            
      </section>
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
       <section id="recommendedBooks" class="shop">
            <div class="title">
           
                <h1>Recommended</h1>
            </div>
            <div class="shop-slider">
                <div class="box-container">
                    <div class="box">
                        <img src="img/book11.jpg" alt="book1">
                        <a href="view-book-details.html?book=ANGEL'S TRIBUTE" class="btn">Shop now</a>
                    </div>
                    <div class="box">
                        <img src="img/book1.jpg" alt="book2">
                        <a href="view-book-details.html?book=SOUL" class="btn">Shop now</a>
                    </div>
                    <div class="box">
                        <img src="img/book2.webp" alt="book3">
                        <a href="view-book-details.html?book=WALK INTO THE SHADOW" class="btn">Shop now</a>
                    </div>
                    <div class="box">
                        <img src="img/book3.webp" alt="book4">
                        <a href="view-book-details.html?book=HIDE AND SEEK" class="btn">Shop now</a>
                    </div>
                    <div class="box">
                        <img src="img/book4.jpg" alt="book5">
                        <a href="view-book-details.html?book=THE REGENERATION OF STELLA YIN" class="btn">Shop now</a>
                    </div>
                    <div class="box">
                        <img src="img/book5.jpg" alt="book6">
                        <a href="view-book-details.html?book=OMNI" class="btn">Shop now</a>
                    </div>
                    <div class="box">
                        <img src="img/book6.jpg" alt="book7">
                        <a href="view-book-details.html?book=EISIGES LAND" class="btn">Shop now</a>
                    </div>
                    <div class="box">
                        <img src="img/book7.jpg" alt="book8">
                        <a href="view-book-details.html?book=THE LONGEST REVOLUTION" class="btn">Shop now</a>
                    </div>
                    <div class="box">
                        <img src="img/book8.jpg" alt="book9">
                        <a href="view-book-details.html?book=BEIEANG" class="btn">Shop now</a>
                    </div>
                    <div class="box">
                        <img src="img/book9.jpg" alt="book10">
                        <a href="view-book-details.html?book=GENIE UND REBELL" class="btn">Shop now</a>
                    </div>
                    <div class="box">
                        <img src="img/book10.jpg" alt="book11">
                        <a href="view-book-details.html?book=KEEP THE MEMORIES" class="btn">Shop now</a>
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
    


      <?php include 'components/footer.php'?>


    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
    <script>
      

    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js" defer></script>
    <script src="script.js" defer></script>
    <?php  include 'components/alert.php' ; ?>

</body>
</html>