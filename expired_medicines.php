<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "pharmastock");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$today = date('Y-m-d');

// **Delete Medicine Logic**
if (isset($_POST['delete_id'])) {
    $medicine_id = $_POST['delete_id'];
    $query = "DELETE FROM stock WHERE medicine_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $medicine_id);
    
    if ($stmt->execute()) {
        echo "<script>alert('Medicine deleted successfully!'); window.location.href='expired_medicines.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error deleting medicine!');</script>";
    }
}

// Search Logic
$search = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    // Modified query to show exact matches first, then partial matches
    $query = "SELECT *, DATEDIFF(expiry_date, '$today') AS days_left,
              CASE 
                WHEN medicine_name = ? OR batch_no = ? OR medicine_id = ? THEN 0
                ELSE 1
              END as match_order 
              FROM stock 
              WHERE (medicine_name LIKE ? OR batch_no LIKE ? OR medicine_id LIKE ?)
              ORDER BY match_order, expiry_date ASC";
    $stmt = $conn->prepare($query);
    $exactTerm = $search;
    $searchTerm = "%$search%";
    $stmt->bind_param("ssssss", $exactTerm, $exactTerm, $exactTerm, $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Fetch All Medicines if no search
    $query = "SELECT *, DATEDIFF(expiry_date, '$today') AS days_left FROM stock ORDER BY expiry_date ASC";
    $result = $conn->query($query);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Expired Medicines</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .expired { 
            color: red; 
            font-weight: bold; 
        }
        
        body {
            background-color: #f5f5f5;
        }

        .container {
            padding: 2rem;
        }

        h2 {
            color: #4CAF50;
            margin-bottom: 1.5rem;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            background: white;
            border-radius: 8px;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
        }

        tr:hover {
            background-color: #E8F5E9;
        }

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

    <div class="container">
        <h2>Expired Medicines</h2>

        <form class="search-form" method="GET">
            <input type="text" name="search" placeholder="Search by name, batch no, or ID" value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
            <?php if($search): ?>
                <a href="expired_medicines.php" class="btn btn-secondary">Clear Search</a>
            <?php endif; ?>
        </form>

        <table>
            <tr>
                <th>Medicine ID</th>
                <th>Name</th>
                <th>Batch No</th>
                <th>Quantity</th>
                <th>Expiry Date</th>
                <th>Days Left</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()) { 
                $days_left = $row['days_left'];
                $class = ($days_left < 0) ? 'expired' : '';
            ?>
                <tr class="<?php echo $class; ?>">
                    <td><?php echo $row['medicine_id']; ?></td>
                    <td><?php echo $row['medicine_name']; ?></td>
                    <td><?php echo $row['batch_no']; ?></td>
                    <td><?php echo $row['quantity']; ?></td>
                    <td><?php echo $row['expiry_date']; ?></td>
                    <td><?php echo ($days_left < 0) ? "Expired" : "$days_left days"; ?></td>
                    <td>
                        <form method="post" onsubmit="return confirm('Are you sure you want to delete this medicine?');">
                            <input type="hidden" name="delete_id" value="<?php echo $row['medicine_id']; ?>">
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>

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
