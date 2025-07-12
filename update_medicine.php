<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Database Connection
$conn = new mysqli("localhost", "root", "", "pharmastock");

// Check for Connection Error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$medicine = null;

// Search Medicine
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    $search_value = $_POST['search_value'];

    // Search by Medicine ID or Batch No
    $stmt = $conn->prepare("SELECT * FROM stock WHERE medicine_id = ? OR batch_no = ?");
    $stmt->bind_param("ss", $search_value, $search_value);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $medicine = $result->fetch_assoc();
    } else {
        echo "<script>alert('Medicine not found!');</script>";
    }
}

// Update Medicine
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $medicine_id = $_POST['medicine_id'];
    $name = $_POST['medicine_name'];
    $batch_no = $_POST['batch_no'];
    $manufacture_date = $_POST['manufacture_date'];
    $expiry_date = $_POST['expiry_date'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price_per_unit'];
    $supplier_name = $_POST['supplier_name'];
    $supplier_contact = $_POST['supplier_contact'];

    $check_duplicate = $conn->prepare("SELECT medicine_id FROM stock WHERE medicine_name = ? AND medicine_id != ?");
    $check_duplicate->bind_param("si", $name, $medicine_id);
    $check_duplicate->execute();
    $check_duplicate->store_result();

    if ($check_duplicate->num_rows > 0) {
        echo "<script>
            alert('Error: Medicine with this name already exists.');
            window.location.href = 'update_medicine.php?medicine_id=$medicine_id'; // Redirect to the update page
        </script>";
        exit();
    }

    if (strtotime($expiry_date) < strtotime(date('Y-m-d'))) {
        echo "<script>alert('Error: Cannot update medicine with an expired date.'); window.history.back();</script>";
        exit();
    }
    
    $check_batch = $conn->prepare("SELECT batch_no FROM stock WHERE batch_no = ? AND medicine_id != ?");
    $check_batch->bind_param("si", $batch_no, $medicine_id);
    $check_batch->execute();
    $check_batch->store_result();

    if ($check_batch->num_rows > 0) {
    echo "<script>alert('Error: Batch number already exists.');
      window.location.href = 'update_medicine.php?medicine_id=$medicine_id'; </script>";
    exit();
    }

    if ($manufacture_date >= $expiry_date) {
        echo "<script>
            alert('Error: Manufacture Date should be earlier than Expiry Date.');
            window.location.href = 'update_medicine.php?medicine_id=$medicine_id';
        </script>";
        exit();
    }
    if (!preg_match("/^[A-Za-z\s\-0-9]+$/", $name)) {
        echo "<script>alert('Error: Medicine name can only contain letters ,nums and spaces.'); window.history.back();</script>";
        exit();
    }
    
    if (!preg_match("/^[0-9]+$/", $batch_no)) {
        echo "<script>alert('Error: Batch number must be numeric.'); window.history.back();</script>";
        exit();
    }
    
    if ($quantity < 0 || $price < 0) {
        echo "<script>alert('Error: Quantity and price cannot be negative.'); window.history.back();</script>";
        exit();
    }
    
    if (!preg_match("/^[A-Za-z\s]+$/", $supplier_name)) {
        echo "<script>alert('Error: Supplier name can only contain letters.'); window.history.back();</script>";
        exit();
    }
    
    if (!preg_match("/^[0-9]{10}$/", $supplier_contact)) {
        echo "<script>alert('Error: Supplier contact must be 10 digits.'); window.history.back();</script>";
        exit();
    }
    
    // Validate Expiry Date
    if ($expiry_date < date('Y-m-d')) {
        echo "<script>alert('Error: Cannot update medicine with an expired date.');</script>";
    } elseif ($quantity < 0 || $price < 0) {
        echo "<script>alert('Error: Quantity and price cannot be negative.');</script>";
    } else {
        // Proceed with update
        $stmt = $conn->prepare("UPDATE stock SET medicine_name=?, batch_no=?, expiry_date=?, manufacture_date=?, quantity=?, price_per_unit=?, supplier_name=?, supplier_contact=? WHERE medicine_id=?");
        $stmt->bind_param("ssssidsis", $name, $batch_no, $expiry_date, $manufacture_date, $quantity, $price, $supplier_name, $supplier_contact, $medicine_id);

        if ($stmt->execute()) {
            echo "<script>
                alert('Medicine Updated Successfully!');
                window.location.href = 'update_medicine.php';
            </script>";
            exit();
        } else {
            echo "<script>alert('Error updating medicine: " . $conn->error . "');</script>";
        }
    }
}

// Delete Medicine
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    $medicine_id = $_POST['medicine_id'];

    $stmt = $conn->prepare("DELETE FROM stock WHERE medicine_id=?");
    $stmt->bind_param("s", $medicine_id);
    
    if ($stmt->execute()) {
        echo "<script>
            alert('Medicine Deleted Successfully!');
            window.location.href = 'dashboard.php';
        </script>";
        exit();
    } else {
        echo "<script>alert('Error deleting medicine: " . $conn->error . "');</script>";
    }
}



$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Medicine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #4CAF50;
            text-align: center;
            margin-bottom: 30px;
        }
        .form-control {
            margin-bottom: 15px;
        }
        .btn-primary {
            background-color: #4CAF50;
            border: none;
            width: 100%;
            padding: 10px;
        }
        .btn-primary:hover {
            background-color: #45a049;
        }
        .btn-danger {
            background-color: #dc3545;
            border: none;
            width: 100%;
            padding: 10px;
            margin-top: 10px;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #4CAF50;
            text-decoration: none;
        }
        .back-link:hover {
            color: #45a049;
        }
        /* Slide Menu Styles */
        .slide-menu {
            position: fixed;
            top: 0;
            right: -250px;
            width: 250px;
            height: 100vh;
            background: #fff;
            box-shadow: -2px 0 5px rgba(0,0,0,0.1);
            transition: right 0.3s ease;
            z-index: 1000;
        }
        .slide-menu.active {
            right: 0;
        }
        .menu-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            cursor: pointer;
            z-index: 1001;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .menu-line {
            width: 25px;
            height: 3px;
            background: #4CAF50;
            transition: all 0.3s ease;
        }
        .slide-menu-items {
            padding: 20px;
            margin-top: 60px;
        }
        .slide-menu-item {
            display: block;
            padding: 15px;
            color: #333;
            text-decoration: none;
            transition: all 0.3s ease;
            border-bottom: 1px solid #eee;
        }
        .slide-menu-item:hover {
            background: #E8F5E9;
            color: #4CAF50;
            padding-left: 25px;
        }
        .form-label {
            font-weight: bold;
            color: #4CAF50;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="slide-menu">
        <div class="slide-menu-items">
            <a href="dashboard.php" class="slide-menu-item">Dashboard</a>
            <a href="add_medicine.php" class="slide-menu-item">Add Medicines</a>
            <a href="view_medicines.php" class="slide-menu-item">View Medicines</a>
            <a href="generate_bill.php" class="slide-menu-item">Bill</a>
            <a href="sales.php" class="slide-menu-item">Reports</a>
            <a href="expired_medicines.php" class="slide-menu-item">Expired medicines</a>
            <a href="manage_pharmacist.php" class="slide-menu-item">Profiles</a>
            <a href="logout.php" class="slide-menu-item">Logout</a>
        </div>
    </div>

    <div class="menu-toggle">
        <div class="menu-line"></div>
        <div class="menu-line"></div>
        <div class="menu-line"></div>
    </div>

    <div class="container">
        <h2>Search Medicine</h2>
        <form method="post">
    <div class="form-group">
        <label class="form-label">Medicine ID or Batch No:</label>
        <input type="text" class="form-control" name="search_value" 
               placeholder="Enter Medicine ID or Batch No" 
               value="<?php echo isset($_GET['medicine_id']) ? htmlspecialchars($_GET['medicine_id']) : ''; ?>" 
               required>
    </div>
    <button type="submit" class="btn btn-primary" name="search">Search</button>
</form>
        <?php if ($medicine): ?>
        <h2>Update Medicine</h2>
        <form method="post">
            
            <input type="hidden" name="medicine_id" value="<?= htmlspecialchars($medicine['medicine_id']) ?>">
            
            <div class="form-group">
                <label class="form-label">Medicine Name:</label>
                <input type="text" class="form-control" name="medicine_name" required pattern="[A-Za-z\s\-0-9]+" 
       title="Only alphabets and numbers are allowed" value="<?= htmlspecialchars($medicine['medicine_name']) ?>">

                </div>

            <div class="form-group">
                <label class="form-label">Batch Number:</label>
                <input type="text" class="form-control" name="batch_no" placeholder="Batch No" value="<?= htmlspecialchars($medicine['batch_no']) ?>" required>
            </div>
            <div class="form-group">
    <label class="form-label">Manufacture Date:</label>
    <input type="date" class="form-control" name="manufacture_date" 
           value="<?= htmlspecialchars($medicine['manufacture_date']) ?>" required>
</div>
            <div class="form-group">
                <label class="form-label">Expiry Date:</label>
                <input type="date" class="form-control" name="expiry_date" id="expiry_date" required 
       value="<?= htmlspecialchars($medicine['expiry_date']) ?>">
<script>
    document.getElementById('expiry_date').setAttribute('min', new Date().toISOString().split('T')[0]);
</script>

                 </div>

            <div class="form-group">
                <label class="form-label">Quantity:</label>
                <input type="number" class="form-control" name="quantity" required min="1" 
       title="Quantity must be a positive number" value="<?= htmlspecialchars($medicine['quantity']) ?>">

               </div>

            <div class="form-group">
                <label class="form-label">Price per Unit:</label>
                <input type="number" class="form-control" name="price_per_unit" required min="0.01" step="0.01"
       title="Price must be a positive number" value="<?= htmlspecialchars($medicine['price_per_unit']) ?>">

                </div>
            <div class="form-group">
    <label class="form-label">Supplier Name:</label>
    <input type="text" class="form-control" name="supplier_name" placeholder="Supplier Name" 
           value="<?= htmlspecialchars($medicine['supplier_name']) ?>" required pattern="[A-Za-z\s]+" 
           title="Only alphabets are allowed">
</div>

<div class="form-group">
    <label class="form-label">Supplier Contact:</label>
    <input type="text" class="form-control" name="supplier_contact" placeholder="Supplier Contact" 
           value="<?= htmlspecialchars($medicine['supplier_contact']) ?>" required pattern="[0-9]{10}" 
           title="Only numbers are allowed (10 digits)">
</div>
            <button type="submit" class="btn btn-primary" name="update">Update Medicine</button>
            <button type="submit" class="btn btn-danger" name="delete" onclick="return confirm('Are you sure you want to delete this medicine?');">Delete Medicine</button>
        </form>
        <?php endif; ?>

        <a href="dashboard.php" class="back-link">Back to Dashboard</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelector('.menu-toggle').addEventListener('click', function() {
            this.classList.toggle('active');
            document.querySelector('.slide-menu').classList.toggle('active');
        });
    </script>
</body>
</html>
