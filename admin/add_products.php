<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once(__DIR__ . '/../components/connection.php');

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$admin_id = $_SESSION['admin_id'];
$select_profile = $conn->prepare("SELECT * FROM `admin` WHERE id = ?");
$select_profile->execute([$admin_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

$success_msg = [];
$warning_msg = [];

if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: ../login.php');
    exit();
}


// Merr të gjitha kategoritë nga databaza
$select_categories = $conn->prepare("SELECT * FROM `categories`");
$select_categories->execute();
$categories = $select_categories->fetchAll(PDO::FETCH_ASSOC);

// Add product to database
if (isset($_POST['publish'])) {
    $category_id = isset($_POST['category_id']) ? $_POST['category_id'] : null;

    if (empty($category_id)) {
        $warning_msg[] = 'Please select a category!';
    } else {
        $id = uniqueid();
        $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
        $price = filter_var($_POST['price'], FILTER_SANITIZE_STRING);
        $content = filter_var($_POST['content'], FILTER_SANITIZE_STRING);
        $status = "active";
        $quantity = filter_var($_POST['quantity'], FILTER_SANITIZE_NUMBER_INT);
        if ($quantity < 0) {
            $warning_msg[] = 'Quantity cannot be negative!';
            $quantity = 0; // Set to zero if negative
        }

        $image = $_FILES['image']['name'] ?? '';
        $image = filter_var($image, FILTER_SANITIZE_STRING);
        $image_size = $_FILES['image']['size'] ?? 0;
        $image_tmp_name = $_FILES['image']['tmp_name'] ?? '';
        $image_folder = '../image/' . $image;

        // Kontrollo nëse foto ekziston
        $select_image = $conn->prepare("SELECT * FROM `products` WHERE image = ?");
        $select_image->execute([$image]);

        if (!empty($image)) {
            if ($select_image->rowCount() > 0) {
                $warning_msg[] = 'Image already exists!';
            } elseif ($image_size > 2000000) {
                $warning_msg[] = 'Image size is too large!';
            } else {
                move_uploaded_file($image_tmp_name, $image_folder);
            }
        }

        if ($select_image->rowCount() > 0 && $image != '') {
            $warning_msg[] = 'Please rename your image!';
        } else {
            $insert_product = $conn->prepare("INSERT INTO `products`(id, name, price, product_detail, image, status, category_id,quantity) VALUES(?,?,?,?,?,?,?,?)");
            $insert_product->execute([$id, $name, $price, $content, $image, $status, $category_id, $quantity ]);
            $success_msg[] = 'Product added successfully!';
        }
    }
}
//save as draft
$select_categories = $conn->prepare("SELECT * FROM `categories`");
$select_categories->execute();
$categories = $select_categories->fetchAll(PDO::FETCH_ASSOC);

// Add product to database
if (isset($_POST['draft'])) {
    $category_id = isset($_POST['category_id']) ? $_POST['category_id'] : null;

    if (empty($category_id)) {
        $warning_msg[] = 'Please select a category!';
    } else {
        $id = uniqueid();
        $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
        $price = filter_var($_POST['price'], FILTER_SANITIZE_STRING);
        $content = filter_var($_POST['content'], FILTER_SANITIZE_STRING);
        $status = "deactive";
        $quantity = filter_var($_POST['quantity'], FILTER_SANITIZE_NUMBER_INT);
        if ($quantity < 0) {
            $warning_msg[] = 'Quantity cannot be negative!';
            $quantity = 0; // Set to zero if negative
        }

        $image = $_FILES['image']['name'] ?? '';
        $image = filter_var($image, FILTER_SANITIZE_STRING);
        $image_size = $_FILES['image']['size'] ?? 0;
        $image_tmp_name = $_FILES['image']['tmp_name'] ?? '';
        $image_folder = '../image/' . $image;

        // Kontrollo nëse foto ekziston
        $select_image = $conn->prepare("SELECT * FROM `products` WHERE image = ?");
        $select_image->execute([$image]);

        if (!empty($image)) {
            if ($select_image->rowCount() > 0) {
                $warning_msg[] = 'Image already exists!';
            } elseif ($image_size > 2000000) {
                $warning_msg[] = 'Image size is too large!';
            } else {
                move_uploaded_file($image_tmp_name, $image_folder);
            }
        }

        if ($select_image->rowCount() > 0 && $image != '') {
            $warning_msg[] = 'Please rename your image!';
        } else {
            $insert_product = $conn->prepare("INSERT INTO `products`(id, name, price, product_detail, image, status, category_id) VALUES(?,?,?,?,?,?,?)");
            $insert_product->execute([$id, $name, $price, $content, $image, $status, $category_id]);
            $success_msg[] = 'Product added successfully!';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>BookstoreBuzuku Admin Panel - add products Page</title>
    <style type="text/css">
        <?php include '../style.css'; ?>
    </style>
</head>

<body>
    <?php include __DIR__ . '/../components/admin_header.php'; ?>


    <div class="main">

        <div class="tittle2">
            <a href="dashboard.php">dashboard</a><span>add products</span>
        </div>
        <section class="form-container">
            <h1 class="heading"> add products</h1>
            <form action="" method="post" enctype="multipart/form-data">
                <div class="input-field">
                    <label> porudct name</label>
                    <input type="text" name="name" maxlength="100" placeholder="Product Name" required>


                </div>
                <div class="input-field">
                    <label> porudct price</label>
                    <input type="number" name="price" maxlength="100" placeholder="Product Price" required>
                </div>
                <div class="input-field">
                    <label>Product quantity</label>
                    <input type="number" name="quantity" min="0" placeholder="Product Quantity" required>
                </div>
                <div class="input-field">
                    <label>category</label>
                    <br/>
                    <select name="category_id" required>
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id']; ?>"><?= htmlspecialchars($category['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-field">
                    <label> porudct detail</label>
                    <textarea name="content" required maxlength="1000" style="resize: none;" required placeholder="write Product description" required></textarea>
                </div>
                <div class="input-field">
                    <label> porudct image</label>
                    <input type="file" name="image" accept="image/*" required>
                </div>
                <div class="flex-btn">
                    <button type="submit" name="publish" class="btn">publish product</button>
                    <button type="submit" name="draft" class="btn">save as a draft</button>
                </div>
            </form>
            <h1 class="heading"> add products</h1>


        </section>
    </div>

    <script>
        function showAlert(type, message) {
            Swal.fire({
                icon: type,
                title: type === 'success' ? 'Success!' : 'Warning!',
                text: message,
                confirmButtonText: 'OK'
            });
        }

        <?php if (!empty($success_msg)): ?>
            <?php foreach ($success_msg as $msg): ?>
                showAlert('success', '<?php echo $msg; ?>');
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!empty($warning_msg)): ?>
            <?php foreach ($warning_msg as $msg): ?>
                showAlert('warning', '<?php echo $msg; ?>');
            <?php endforeach; ?>
        <?php endif; ?>
    </script>
    <script src="../script.js"></script>
</body>

</html>