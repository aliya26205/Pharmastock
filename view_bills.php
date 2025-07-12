<?php
require_once('db_connect.php');

$date_today = date("Y-m-d");
$search_bill_id = "";
$search_date = $date_today;

// Initialize $result
$result = null;

// Search by Bill ID
if (isset($_POST['search_bill'])) {
    $search_bill_id = $_POST['bill_id'];
    $query = "SELECT * FROM bill WHERE bill_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $search_bill_id);
    $stmt->execute();
    $result = $stmt->get_result();
} 
// Search by Date (including future dates, but will return no records)
elseif (isset($_POST['search_date'])) {
    $search_date = $_POST['date'];
    $query = "SELECT * FROM bill WHERE DATE(bill_date) = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $search_date);
    $stmt->execute();
    $result = $stmt->get_result();
} 
// Default: Show today's bills
else {
    $query = "SELECT * FROM bill WHERE DATE(bill_date) = ? ORDER BY bill_date DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $date_today);
    $stmt->execute();
    $result = $stmt->get_result();
}
date_default_timezone_set('Asia/Kolkata');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Bills</title>
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
        .search-forms {
            background: #E8F5E9;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .table {
            margin-top: 20px;
            background: white;
        }
        .table th {
            background-color: #4CAF50;
            color: white;
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
        <h2>View Bills</h2>
        
        <div class="search-forms">
            <div class="row">
                <div class="col-md-6">
                    <form method="post" class="mb-3">
                        <div class="input-group">
                            <input type="text" class="form-control" name="bill_id" placeholder="Enter Bill ID" required>
                            <button type="submit" class="btn btn-primary" name="search_bill">Search by Bill ID</button>
                        </div>
                    </form>
                </div>
                <div class="col-md-6">
                    <form method="post">
                        <div class="input-group">
                            <input type="date" class="form-control" name="date" value="<?php echo $search_date; ?>" required>
                            <button type="submit" class="btn btn-primary" name="search_date">Search by Date</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Bill ID</th>
                        <th>Customer Name</th>
                        <th>Contact</th>
                        <th>Bill Date And Time</th>
                        <th>Payment Method</th>
                        <th>Total Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['bill_id']; ?></td>
                                <td><?php echo $row['customer_name']; ?></td>
                                <td><?php echo $row['customer_contact']; ?></td>
                                <td><?php echo $row['bill_date']; ?></td>
                                <td><?php echo $row['payment_method']; ?></td>
                                <td>₹<?php echo $row['total_amount']; ?></td>
                                <td>
                                    <a href="generate_pdf.php?bill_id=<?php echo $row['bill_id']; ?>" class="btn btn-primary btn-sm" target="_blank">View Bill</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">No records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <button class="btn-back" onclick="window.location.href='dashboard.php'">Back to Dashboard</button>
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
