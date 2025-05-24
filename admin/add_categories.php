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

// Funksione për menaxhimin e kategorive
if (isset($_POST['add_category'])) {
    $category_name = filter_var($_POST['category_name'], FILTER_SANITIZE_STRING);
    $category_description = filter_var($_POST['category_description'], FILTER_SANITIZE_STRING);
    
    // Kontrollo nëse kategoria ekziston
    $check_category = $conn->prepare("SELECT * FROM `categories` WHERE name = ?");
    $check_category->execute([$category_name]);
    
    if ($check_category->rowCount() > 0) {
        $warning_msg[] = 'Category already exists!';
    } else {
        $insert_category = $conn->prepare("INSERT INTO `categories` (name, description) VALUES (?, ?)");
        $insert_category->execute([$category_name, $category_description]);
        $success_msg[] = 'Category added successfully!';
    }
}

if (isset($_POST['update_category'])) {
    $category_id = $_POST['category_id'];
    $category_name = filter_var($_POST['category_name'], FILTER_SANITIZE_STRING);
    $category_description = filter_var($_POST['category_description'], FILTER_SANITIZE_STRING);
    
    $update_category = $conn->prepare("UPDATE `categories` SET name = ?, description = ? WHERE id = ?");
    $update_category->execute([$category_name, $category_description, $category_id]);
    $success_msg[] = 'Category updated successfully!';
}

if (isset($_GET['delete_category'])) {
    $category_id = $_GET['delete_category'];
    
    // Kontrollo nëse ka produkte në këtë kategori
    $check_products = $conn->prepare("SELECT * FROM `products` WHERE category_id = ?");
    $check_products->execute([$category_id]);
    
    if ($check_products->rowCount() > 0) {
        $warning_msg[] = 'Cannot delete category with existing products!';
    } else {
        $delete_category = $conn->prepare("DELETE FROM `categories` WHERE id = ?");
        $delete_category->execute([$category_id]);
        $success_msg[] = 'Category deleted successfully!';
    }
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
        $id = uniqid();
        $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
        $price = filter_var($_POST['price'], FILTER_SANITIZE_STRING);
        $content = filter_var($_POST['content'], FILTER_SANITIZE_STRING);
        $status = "active";

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

//save as draft
if (isset($_POST['draft'])) {
    $category_id = isset($_POST['category_id']) ? $_POST['category_id'] : null;

    if (empty($category_id)) {
        $warning_msg[] = 'Please select a category!';
    } else {
        $id = uniqid();
        $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
        $price = filter_var($_POST['price'], FILTER_SANITIZE_STRING);
        $content = filter_var($_POST['content'], FILTER_SANITIZE_STRING);
        $status = "deactive";

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
    <title>BookstoreBuzuku Admin Panel - Categories Page</title>
    <style type="text/css">
        <?php include '../style.css'; ?>
    </style>
</head>

<body>
    <?php include __DIR__ . '/../components/admin_header.php'; ?>

    <div class="main">
        <div class="tittle2">
            <a href="dashboard.php">dashboard</a><span>add categories</span>
        </div>

        <!-- Seksioni për menaxhimin e kategorive -->
        <section class="category-management">
            <h1 class="heading">Manage Categories</h1>
            
            <!-- Tabela e kategorive -->
            <table class="categories-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?= $category['id']; ?></td>
                        <td><?= htmlspecialchars($category['name']); ?></td>
                        <td><?= htmlspecialchars($category['description'] ?? 'N/A'); ?></td>
                        <td><?= $category['created_at']; ?></td>
                        <td class="action-buttons">
                            <button class="edit-btn" onclick="openEditModal(<?= $category['id']; ?>, '<?= htmlspecialchars($category['name']); ?>', '<?= htmlspecialchars($category['description'] ?? ''); ?>')">Edit</button>
                            <button class="delete-btn" onclick="confirmDelete(<?= $category['id']; ?>)">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <!-- Forma për shtimin e kategorive -->
            <button class="toggle-form-btn" onclick="toggleCategoryForm()">Add New Category</button>
            
            <div class="category-form" id="categoryForm" style="display: none;">
                <form action="" method="post">
                    <div class="input-field">
                        <label>Category Name </label>
                        <input type="text" name="category_name" maxlength="100" placeholder="Category Name" required>
                    </div>
                    <div class="input-field">
                        <label>Description</label>
                        <textarea name="category_description" style="resize: none;" maxlength="500" placeholder="Category Description"></textarea>
                    </div>
                     <div class="flex-btn">
            <button type="submit" name="add_category" class="btn">Add Category</button>
            <button type="button" onclick="document.getElementById('categoryForm').style.display='none'" class="btn cancel-btn">Cancel</button>
        </div>
                </form>
            </div>
            
            <!-- Forma për editimin e kategorive (e fshehur fillimisht) -->
            <div class="category-form" id="editCategoryForm" style="display: none;">
                <form action="" method="post">
                    <input type="hidden" name="category_id" id="edit_category_id">
                    <div class="input-field">
                        <label>Category Name</label>
                        <input type="text" name="category_name" id="edit_category_name" maxlength="100" placeholder="Category Name" required>
                    </div>
                    <div class="input-field">
                        <label>Description</label>
                        <textarea name="category_description" id="edit_category_description" style="resize: none;" maxlength="500" placeholder="Category Description"></textarea>
                    </div>
                   <div class="flex-btn">
            <button type="submit" name="update_category" class="btn">Update Category</button>
            <button type="button" onclick="document.getElementById('editCategoryForm').style.display='none'" class="btn cancel-btn">Cancel</button>
        </div>
                </form>
            </div>
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

        function toggleCategoryForm() {
            const form = document.getElementById('categoryForm');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }

        function openEditModal(id, name, description) {
            document.getElementById('edit_category_id').value = id;
            document.getElementById('edit_category_name').value = name;
            document.getElementById('edit_category_description').value = description || '';
            
            // Hide add form if visible
            document.getElementById('categoryForm').style.display = 'none';
            
            // Show edit form
            document.getElementById('editCategoryForm').style.display = 'block';
            
            // Scroll to the form
            document.getElementById('editCategoryForm').scrollIntoView({ behavior: 'smooth' });
        }

        function confirmDelete(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '?delete_category=' + id;
                }
            });
        }
    </script>
    <script src="../script.js"></script>
</body>

</html>