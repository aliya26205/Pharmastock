<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Database Connection
$conn = new mysqli("localhost", "root", "", "pharmastock");

// Check Connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch low stock medicines (quantity < 50)
$query = "SELECT * FROM stock WHERE quantity < 50 ORDER BY quantity ASC";
$result = $conn->query($query);

// Handle Medicine Deletion
if (isset($_POST['delete_medicine'])) {
    $medicine_id = $_POST['medicine_id'];
    $delete_query = "DELETE FROM stock WHERE medicine_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $medicine_id);
    
    if ($stmt->execute()) {
        echo "<script>alert('Medicine deleted successfully!'); window.location.href='low_stock.php';</script>";
    } else {
        echo "<script>alert('Error deleting medicine.');</script>";
    }
    $stmt->close();
}

// Get low stock count for dashboard
$count_query = "SELECT COUNT(*) AS low_stock_count FROM stock WHERE quantity < 50";
$count_result = $conn->query($count_query);
$count_row = $count_result->fetch_assoc();
$low_stock_count = $count_row['low_stock_count'];


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Low Stock Medicines</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
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
        .table {
            margin-top: 20px;
            background: white;
        }
        .table th {
            background-color: #4CAF50;
            color: white;
        }
        .btn-warning {
            background-color: #ffc107;
            border: none;
            color: black;
            margin-right: 5px;
        }
        .btn-danger {
            background-color: #dc3545;
            border: none;
        }
        .btn-back {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
        }
        .btn-back:hover {
            background-color: #45a049;
        }

        .search-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        #searchInput {
            padding: 10px;
            width: 250px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        .search-btn {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 14px;
            cursor: pointer;
        }

        .search-btn.green {
            background-color: #28a745;
        }

        .search-btn.green:hover {
            background-color: #218838;
        }

        .search-btn.gray {
            background-color: #6c757d;
        }

        .search-btn.gray:hover {
            background-color: #5a6268;
        }
        .low-stock-count {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
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
        <h2>Low Stock Medicines</h2>
        <div class="low-stock-count">
            Total Low Stock Medicines: <strong><?php echo $low_stock_count; ?></strong>
        </div>

        <div class="search-container">
    <input type="text" id="searchInput" placeholder="Search by name, batch no, or ID">
    <button class="search-btn green" onclick="searchMedicine()">Search</button>
    <button class="search-btn gray" onclick="clearSearch()">Clear Search</button>
    <button class="search-btn green" onclick="generateLowStockReport()">Generate Report</button>
    
</div>
        <!-- <input type="text" id="searchBar" placeholder="Search Medicine name" onkeyup="searchMedicine()"> -->
        <!-- <input type="text" id="searchInput" placeholder="Enter Medicine ID, Name, or Batch No">
        <button onclick="searchMedicine()">Search</button>
        <button onclick="clearSearch()">Clear</button> -->


        <table class="table table-bordered">
            <thead>
                <tr>
                <th>Medicine ID</th>
    <th>Batch No</th>
    <th>Medicine Name</th>
    <th>Quantity</th>
    <th>Expiry Date</th>
    <th>Supplier Name</th>
    <th>Supplier Contact</th>
    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="medicineTable">
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['medicine_id']; ?></td>
                        <td><?php echo $row['batch_no']; ?></td>
                        <td><?php echo $row['medicine_name']; ?></td>
                        <td style="color: red;"><?php echo $row['quantity']; ?></td>
                        <td><?php echo $row['expiry_date']; ?></td>
                        <td><?php echo htmlspecialchars($row['supplier_name']); ?></td>
            <td><?php echo htmlspecialchars($row['supplier_contact']); ?></td>
                        <td>
                            <a href="update_medicine.php?medicine_id=<?php echo $row['medicine_id']; ?>" class="btn btn-warning">Update</a>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="medicine_id" value="<?php echo $row['medicine_id']; ?>">
                                <button type="submit" name="delete_medicine" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this medicine?');">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <button class="btn-back" onclick="window.location.href='dashboard.php'">Back to Dashboard</button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelector('.menu-toggle').addEventListener('click', function() {
    this.classList.toggle('active');
    document.querySelector('.slide-menu').classList.toggle('active');
});


function generateLowStockReport() {
    window.location.href = "generate_low_stock_report.php";
}

function searchMedicine() {
    let input = document.getElementById("searchInput").value.toLowerCase();
    let table = document.getElementById("medicineTable");
    let rows = table.getElementsByTagName("tr");

    for (let i = 0; i < rows.length; i++) {  
        let columns = rows[i].getElementsByTagName("td");
        if (columns.length > 0) {
            let medicineId = columns[0].textContent.toLowerCase();  // Assuming Medicine ID is in column 0
            let medicineName = columns[2].textContent.toLowerCase();  // Assuming Medicine Name is in column 2
            let batchNo = columns[3].textContent.toLowerCase();  // Assuming Batch No is in column 3

            if (medicineId.includes(input) || medicineName.includes(input) || batchNo.includes(input)) {
                rows[i].style.display = "";
            } else {
                rows[i].style.display = "none";
            }
        }
    }
}
function clearSearch() {
    document.getElementById("searchInput").value = "";
    let table = document.getElementById("medicineTable");
    let rows = table.getElementsByTagName("tr");

    for (let i = 0; i < rows.length; i++) { // Start from 1 to skip table header
        rows[i].style.display = ""; // Show all rows
    }
}
    </script>
</body>
</html>
