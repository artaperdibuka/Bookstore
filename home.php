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
    header('Location: home.php');
    exit;
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
    <title>BookstoreBuzuku - Home Page</title>
</head>

<body>
    
    <?php include 'components/header.php'; ?>
    <div class="main">
        <section class="home-section">
            <div class="slider">
                <div class="slider__slider">
                    <img src="image/BuzukuBookstore.jpeg" alt="Slide 1">
                    <div class="overlay"></div>
                    <div class="slide-detail">
                        <p class="text-slide">Discover amazing books at Buzuku Bookstore and explore our events!</p>
                        <a href="about.php" class="btn">About us</a>
                    </div>
                    <div class="hero-dec-top"></div>
                    <div class="hero-dec-bottom"></div>
                </div>

                <div class="slider__slider">
                    <img src="image/recomended1.png" alt="Slide 3">
                    <div class="overlay"></div>
                    <div class="slide-detail">
                        <a href="#recommendedBooks" class="btn">View Recommended Books</a>
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
        <div class="box" onclick="window.location.href='view_products.php?category_id=3'">
            <img src="image/thumb11.png" alt="Biography">
            <h3>Biography</h3>
            <p>True stories of remarkable lives that inspire and motivate.</p>
            <i class="bx bx-chevron-right"></i>
        </div>
        <div class="box" onclick="window.location.href='view_products.php?category_id=6'">
            <img src="image/thumb12.png" alt="Fantasy">
            <h3>Fantasy</h3>
            <p>Enter magical worlds filled with adventure and wonder.</p>
            <i class="bx bx-chevron-right"></i>
        </div>
        <div class="box" onclick="window.location.href='view_products.php?category_id=8'">
            <img src="image/thumb13.png" alt="Novel">
            <h3>Novel</h3>
            <p>Engaging stories that capture life’s emotions.</p>
            <i class="bx bx-chevron-right"></i>
        </div>
        <div class="box" onclick="window.location.href='view_products.php?category_id=10'">
            <img src="image/thumb14.png" alt="Poetry">
            <h3>Poetry</h3>
            <p>Expressive verses that evoke deep feelings and reflections.</p>
            <i class="bx bx-chevron-right"></i>
        </div>
    </div>
        </section>
        <section class="container">
            <div class="box-container">
                <div class="box">
                    <img src="img/about-us.png" id="sale-image">
                </div>
                <div class="box">
                    <img src="img/sale.png">
                    <span>Hurry up</span>
                    <h1>Discounts up to 50%</h1>
                    <p>On many types of books, from bestsellers to the newest releases...</p>

                </div>
            </div>
        </section>

        <section id="recommendedBooks" class="shop">
            <div class="title">
                <h1>Recommended</h1>
            </div>
            <div class="shop-slider">
                <div class="box-container">
                    <?php

                    // Query 1: Get low quantity products (quantity < 5 for example)
                    $lowQuantityQuery = $conn->prepare("
                SELECT p.id, p.name, p.price, p.discount_price, p.image 
                FROM products p 
                WHERE p.quantity < 5 
                ORDER BY p.quantity ASC 
                LIMIT 6
            ");
                    $lowQuantityQuery->execute();
                    $lowQuantityBooks = $lowQuantityQuery->fetchAll(PDO::FETCH_ASSOC);

                    // Query 2: Get best selling products
                    $bestSellingQuery = $conn->prepare("
                SELECT p.id, p.name, p.price, p.discount_price, p.image, SUM(o.qty) as total_sold 
                FROM products p 
                JOIN orders o ON p.id = o.product_id 
                WHERE o.status = 'completed' 
                GROUP BY p.id 
                ORDER BY total_sold DESC 
                LIMIT 6
            ");
                    $bestSellingQuery->execute();
                    $bestSellingBooks = $bestSellingQuery->fetchAll(PDO::FETCH_ASSOC);

                    // Combine results, removing duplicates
                    $recommendedBooks = [];
                    $addedIds = [];

                    foreach ($bestSellingBooks as $book) {
                        if (!in_array($book['id'], $addedIds)) {
                            $recommendedBooks[] = $book;
                            $addedIds[] = $book['id'];
                        }
                    }

                    foreach ($lowQuantityBooks as $book) {
                        if (!in_array($book['id'], $addedIds) && count($recommendedBooks) < 12) {
                            $recommendedBooks[] = $book;
                            $addedIds[] = $book['id'];
                        }
                    }

                    foreach ($recommendedBooks as $book) {
                        $finalPrice = $book['discount_price'] > 0 ? $book['discount_price'] : $book['price'];
                        echo '
                <div class="box">
                    <img src="image/' . $book['image'] . '" alt="' . $book['name'] . '">
                    

                    <div class="book-info">
                        <h3>' . $book['name'] . '</h3>
                        <p class="price">' . ($book['discount_price'] > 0 ?
                            '<span class="original-price">$' . $book['price'] . '</span> $' . $book['discount_price'] :
                            '$' . $book['price']) . '</p>
                    </div>
                    <a href="view_page.php?pid=' . $book['id'] . '" class="btn">Shop now</a>
                </div>';
                    }

                    // If no recommendations, show some default books
                    if (empty($recommendedBooks)) {
                        $defaultBooks = [
                            ['name' => 'ANGEL\'S TRIBUTE', 'image' => 'image/book11.jpg'],
                            ['name' => 'SOUL', 'image' => 'image/book1.jpg'],
                            // Add more default books as needed
                        ];

                        foreach ($defaultBooks as $book) {
                            echo '
                    <div class="box">
                        <img src="' . $book['image'] . '" alt="' . $book['name'] . '">
                      <a href="view_page.php?pid=' . $book['id'] . '" class="btn">Shop now</a>
                    </div>';
                        }
                    }
                    ?>
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



        <?php include 'components/footer.php' ?>


    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
    <script>


    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js" defer></script>
    <script src="script.js" defer></script>
    <?php include 'components/alert.php'; ?>

</body>

</html>