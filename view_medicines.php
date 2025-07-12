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

// Delete Medicine Logic
if (isset($_POST['delete_id'])) {
    $medicine_id = $_POST['delete_id'];
    $query = "DELETE FROM stock WHERE medicine_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $medicine_id);
    
    if ($stmt->execute()) {
        echo "<script>alert('Medicine deleted successfully!'); window.location.href='view_medicines.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error deleting medicine!');</script>";
    }
}

// Search Logic
$search = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    // Search query with exact and partial matches
    $query = "SELECT *, 
              CASE 
                WHEN medicine_name = ? OR batch_no = ? OR medicine_id = ? THEN 0
                ELSE 1
              END as match_order 
              FROM stock 
              WHERE medicine_name LIKE ? OR batch_no LIKE ? OR medicine_id LIKE ?
              ORDER BY match_order, medicine_name";
    $stmt = $conn->prepare($query);
    $exactTerm = $search;
    $searchTerm = "%$search%";
    $stmt->bind_param("ssssss", $exactTerm, $exactTerm, $exactTerm, $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Fetch All Medicines if no search
    $query = "SELECT * FROM stock ORDER BY medicine_name";
    $result = $conn->query($query);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Medicines</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .navbar-brand {
            font-weight: bold;
            color: #4CAF50;
            font-size: 2.5rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
            background: url('https://cdn-icons-png.flaticon.com/512/2037/2037338.png') no-repeat left center;
            background-size: 40px;
            padding-left: 50px;
        }
        .navbar-brand:hover {
            color: #2E7D32;
            transform: scale(1.05);
        }
        .table-container {
            max-height: 500px;
            overflow-y: auto;
            margin-top: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            background: white;
            border-radius: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            position: sticky;
            top: 0;
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
            z-index: 1;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        tr:hover {
            background-color: #E8F5E9;
        }
        .container {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        h2 {
            color: #4CAF50;
            margin-bottom: 1.5rem;
            font-weight: bold;
        }
        body {
            background-color: #f5f5f5;
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
        .btn-dashboard {
            background: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            margin-top: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-dashboard:hover {
            background: #2E7D32;
            transform: translateY(-2px);
        }
        .search-form {
            margin-bottom: 20px;
        }
        .search-form input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 300px;
            margin-right: 10px;
        }
        .search-form button {
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .search-form button:hover {
            background: #2E7D32;
        }
    </style>
</head>
<body>
    <div class="slide-menu">
        <div class="slide-menu-items">
            <a href="dashboard.php" class="slide-menu-item">Dashboard</a>
            <a href="add_medicine.php" class="slide-menu-item">Add Medicines</a>
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

    <div class="container mt-5">
        <h2>All Medicines</h2>
        
        <form class="search-form" method="GET">
            <input type="text" name="search" placeholder="Search by name, batch no, or ID" value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
            <?php if($search): ?>
                <a href="view_medicines.php" class="btn btn-secondary">Clear Search</a>
            <?php endif; ?>
        </form>

        <div class="table-container">
            <table>
                <tr>
                <th>Medicine ID</th>
                    <th>Name</th>
                    <th>Batch No</th>
                    <th>Expiry  Date</th>
                    <th>Manufact Date</th>
                    <th>Added Date</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Supplier Name</th>
                    <th>Supplier Contact</th>
                    <th>Action</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                    <td><?php echo htmlspecialchars($row['medicine_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['medicine_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['batch_no']); ?></td>
                        <td><?php echo htmlspecialchars($row['expiry_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['manufacture_date']); ?></td>
                       
                        <td><?php echo htmlspecialchars($row['added_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                        <td><?php echo htmlspecialchars($row['price_per_unit']); ?></td>
                        <td><?php echo htmlspecialchars($row['supplier_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['supplier_contact']); ?></td>
                        <td>
                            <form method="post" onsubmit="return confirm('Are you sure you want to delete this medicine?');">
                                <input type="hidden" name="delete_id" value="<?php echo $row['medicine_id']; ?>">
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>

        <a href="dashboard.php" class="btn btn-dashboard">Back to Dashboard</a>
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
<?php
// Close Connection
$conn->close();
?>