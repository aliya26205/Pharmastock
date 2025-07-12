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

// Get latest medicine_id and increment it
$result = $conn->query("SELECT MAX(medicine_id) AS last_id FROM stock");
$row = $result->fetch_assoc();
$medicine_id = $row['last_id'] ? $row['last_id'] + 1 : 1; // If no ID exists, start from 1

// Initialize variables to retain form values
$batch_no = $medicine_name = $price_per_unit = $quantity = $expiry_date = $manufacture_date = $supplier_name = $supplier_contact = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $batch_no = isset($_POST['batch_no']) ? $_POST['batch_no'] : "";
    $medicine_name = isset($_POST['medicine_name']) ? $_POST['medicine_name'] : "";
    $price_per_unit = isset($_POST['price_per_unit']) ? $_POST['price_per_unit'] : "";
    $quantity = isset($_POST['quantity']) ? $_POST['quantity'] : "";
    $expiry_date = isset($_POST['expiry_date']) ? $_POST['expiry_date'] : "";
    $manufacture_date = isset($_POST['manufacture_date']) ? $_POST['manufacture_date'] : "";
    $supplier_name = isset($_POST['supplier_name']) ? $_POST['supplier_name'] : "";
    $supplier_contact = isset($_POST['supplier_contact']) ? $_POST['supplier_contact'] : "";
    $added_date = date("Y-m-d H:i:s"); // ✅ Fix: Define added date

    $errors = [];

    // Validate Medicine Name (Only Letters)
    if (!preg_match("/^[a-zA-Z\s\-0-9]+$/", $medicine_name)) {
        $errors[] = "Medicine Name should contain only characters and hyphens and numbers!!";
    }

    // Validate supplier contact (Only numbers with staring from 6-9)
    if (!preg_match("/^[6-9][0-9]{9}$/", $supplier_contact)) {
        $errors[] = "Supplier Contact must be a valid 10-digit number starting with 6-9!";
    }
    
    // Validate batch no (Only Letters and numbers)
    if (!preg_match("/^[a-zA-Z0-9]+$/", $batch_no)) {
        $errors[] = "Batch Number should contain only letters and numbers!";
    }

    // Validate Price and Quantity (No Negative Values)
    if ($price_per_unit < 0 || $quantity < 0) {
        $errors[] = "Price and Quantity cannot be negative!";
    }

    // price should be at least 0.01
    if ($price_per_unit <= 0) {
        $errors[] = "Price must be greater than 0!";
    }
    
    // Ensure quantity is an integer.
    if (!filter_var($quantity, FILTER_VALIDATE_INT) || $quantity <= 0) {
        $errors[] = "Quantity must be a positive whole number!";
    }
    

    // Validate Expiry Date (Should Not Be Expired)
    if (strtotime($expiry_date) < time()) {
        $errors[] = "Cannot add expired medicines!";
    }

    //  Ensure manufacture date is today or earlier.
    if (strtotime($manufacture_date) > time()) {
        $errors[] = "Manufacture date cannot be in the future!";
    }
    

    // Validate manufacture date is before expiry date
    if (strtotime($manufacture_date) >= strtotime($expiry_date)) { // ✅ Fix: Use strtotime()
        $errors[] = "Manufacture date must be earlier than expiry date!";
    }

    // Validate expiry date is at least one week from today
    if (strtotime($expiry_date) < strtotime("+7 days")) { // ✅ Fix: Use strtotime() for comparison
        $errors[] = "You cannot add medicine expiring in less than 7 days!";
    }


    // Check if the batch number already exists
    $stmt = $conn->prepare("SELECT * FROM stock WHERE batch_no = ?");
    $stmt->bind_param("s", $batch_no);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $errors[] = "Batch number already exists! Please enter a unique batch number.";
    }
    $stmt->close();

    // If there are errors, show alert messages
    if (!empty($errors)) {
        echo "<script>alert('" . implode("\\n", $errors) . "');</script>";
    } else {
        // ✅ Insert data only if validation passes
        $stmt = $conn->prepare("INSERT INTO stock (medicine_id, medicine_name, price_per_unit, quantity, manufacture_date, expiry_date, batch_no, added_date, supplier_name, supplier_contact) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isdissssss", $medicine_id, $medicine_name, $price_per_unit, $quantity, $manufacture_date, $expiry_date, $batch_no, $added_date, $supplier_name, $supplier_contact);

        if ($stmt->execute()) {
            echo "<script>alert('Medicine added successfully!'); window.location.href='add_medicine.php';</script>";
            exit();
        } else {
            echo "<script>alert('Error: Could not add medicine.');</script>";
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Medicine</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script>
        function showDuplicateAlert() {
            alert("⚠️ Medicine ID already exists! Please use a different ID.");
        }

        function showSuccessPopup() {
            let popup = document.getElementById("successPopup");
            let overlay = document.getElementById("popupOverlay");
            popup.style.display = "block";
            overlay.style.display = "block";
        }

        function closeSuccessPopup() {
            window.location.href = "dashboard.php"; // Redirect to dashboard
        }
    </script>
    <style>
        body {
            background-color: #f0f2f5;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }

        .medicine-form-container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .form-title {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
            font-size: 24px;
            font-weight: bold;
        }

        .form-control {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 15px;
            width: 100%;
        }

        .form-control:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 5px rgba(76,175,80,0.3);
        }

        .form-label {
            color: #555;
            font-weight: 500;
            margin-bottom: 5px;
        }

        .btn-submit {
            background-color: #4CAF50;
            color: white;
            padding: 12px;
            width: 100%;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-submit:hover {
            background-color: #45a049;
        }

        .back-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
            transition: background-color 0.3s;
            position: fixed;
            bottom: 20px;
            left: 20px;
            z-index: 100; /* Keep button above table but below menu */
        }

        .back-btn:hover {
            background-color: #45a049;
            color: white;
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-column {
            flex: 1;
        }

        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }

        .popup-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 8px;
            z-index: 1001;
            text-align: center;
        }

        .popup button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }

        .popup button:hover {
            background-color: #45a049;
        }

        /* Added styles for slide menu */
        .slide-menu {
            position: fixed;
            top: 0;
            right: -250px;
            width: 250px;
            height: 100vh;
            background: #fff;
            box-shadow: -2px 0 5px rgba(0,0,0,0.1);
            transition: right 0.3s ease;
            z-index: 2000; /* Ensure menu stays on top */
        }

        .slide-menu.active {
            right: 0;
        }

        .menu-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            cursor: pointer;
            z-index: 2001; /* Ensure toggle stays on top */
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

        /* Ensure table stays below form */
        table {
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body>
    <div class="slide-menu">
        <div class="slide-menu-items">
            <a href="dashboard.php" class="slide-menu-item">Dashboard</a>
            <a href="update_medicine.php" class="slide-menu-item">Update Medicines</a>
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
    <div class="medicine-form-container">
        <h2 class="form-title">Add New Medicine</h2>

        <form method="POST" action="">
            <div class="form-row">
                <div class="form-column">
                    <div class="mb-3">
                        <label for="medicine_id" class="form-label">Medicine ID</label>
                        <input type="text" class="form-control" name="medicine_id" value="<?php 
                            $conn = new mysqli("localhost", "root", "", "pharmastock");
                            $result = $conn->query("SELECT MAX(medicine_id) AS last_id FROM stock");
                            $row = $result->fetch_assoc();
                            echo $row['last_id'] ? $row['last_id'] + 1 : 1;
                        ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="medicine_name" class="form-label">Medicine Name</label>
                        <input type="text" class="form-control" name="medicine_name" required pattern="[A-Za-z\s\-0-9]+" title="Only letters and numbers are allowed" value="<?php echo htmlspecialchars($medicine_name); ?>" >
                    </div>

                    <div class="mb-3">
                        <label for="price_per_unit" class="form-label">Price Per Unit</label>
                        <input type="number" class="form-control" name="price_per_unit" step="0.01" required min="0.01" title="Price must be greater than 0" value="<?php echo htmlspecialchars($price_per_unit); ?>" >
                    </div>

                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" name="quantity" required min="1" title="Quantity must be at least 1" value="<?php echo htmlspecialchars($quantity); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="supplier_name" class="form-label">Supplier Name</label>
                        <input type="text" class="form-control" name="supplier_name" required pattern="[A-Za-z\s]+" title="Only characters are allowed" value="<?php echo htmlspecialchars($supplier_name); ?>">
                    </div>
                </div>

                <div class="form-column">
                    <div class="mb-3">
                        <label for="manufacture_date" class="form-label">Manufacture Date</label>
                        <input type="date" class="form-control" name="manufacture_date" required value="<?php echo htmlspecialchars($manufacture_date); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="expiry_date" class="form-label">Expiry Date</label>
                        <input type="date" class="form-control" name="expiry_date" required value="<?php echo htmlspecialchars($expiry_date); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="batch_no" class="form-label">Batch Number</label>
                        <input type="text" class="form-control" name="batch_no" required value="<?php echo htmlspecialchars($batch_no); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="added_date" class="form-label">Added Date</label>
                        <input type="date" class="form-control" name="added_date" value="<?php echo date('Y-m-d'); ?>" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="supplier_contact" class="form-label">Supplier Contact</label>
                        <input type="tel" class="form-control" name="supplier_contact" required pattern="[0-9]{10}" title="Only 10 numbers are allowed" value="<?php echo htmlspecialchars($supplier_contact); ?>" >
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-submit">Add Medicine</button>
            <a href="dashboard.php" class="btn btn-secondary mt-3 w-100">Back to Dashboard</a>
        </form>
    </div>
</div>

<!-- Success Popup -->
<div id="popupOverlay" class="popup-overlay"></div>
<div id="successPopup" class="popup">
    <p style="color: black;">✅ Medicine added successfully!</p>
    <button onclick="closeSuccessPopup()">OK</button>
</div>

<!-- PHP Trigger for Duplicate Check -->
<?php if (isset($duplicateError) && $duplicateError): ?>
    <script>showDuplicateAlert();</script>
<?php endif; ?>

<!-- PHP Trigger for Success Popup -->
<?php if (isset($medicineAdded) && $medicineAdded): ?>
    <script>showSuccessPopup();</script>
<?php endif; ?>

<script>
    document.querySelector('.menu-toggle').addEventListener('click', function() {
        this.classList.toggle('active');
        document.querySelector('.slide-menu').classList.toggle('active');
    });
</script>
</body>
</html>