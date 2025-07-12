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

// Get Total Count of Medicines
$query = "SELECT COUNT(*) AS total FROM stock";
$result = $conn->query($query);
$row = $result->fetch_assoc();
$total_items = $row['total'];

$today = date('Y-m-d');
$query = "SELECT COUNT(*) AS expired FROM stock WHERE expiry_date < '$today'";
$result = $conn->query($query);
$row = $result->fetch_assoc();
$expired_items = $row['expired'];

$count_query = "SELECT COUNT(*) AS low_stock_count FROM stock WHERE quantity < 50";
$count_result = $conn->query($count_query);
$count_row = $count_result->fetch_assoc();
$low_stock_count = $count_row['low_stock_count'];

// Fetch total number of bills generated today
$date_today = date("Y-m-d");
$query = "SELECT COUNT(*) as total_bills FROM bill WHERE DATE(bill_date) = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $date_today);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_bills_today = $row['total_bills'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmaStock - Medical Stock Management</title>
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
            background: url('logofinal.png') no-repeat left center;
            background-size: 70px;
            padding-left: 70px;
        }
        .navbar-brand:hover {
            color: #2E7D32;
            transform: scale(1.05);
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
        .menu-toggle:hover .menu-line {
            background: #2E7D32;
        }
        .menu-toggle.active .menu-line:nth-child(1) {
            transform: rotate(45deg) translate(6px, 6px);
        }
        .menu-toggle.active .menu-line:nth-child(2) {
            opacity: 0;
        }
        .menu-toggle.active .menu-line:nth-child(3) {
            transform: rotate(-45deg) translate(6px, -6px);
        }
        .slide-menu-items {
            padding: 60px 20px;
        }
        .slide-menu-item {
            display: block;
            padding: 15px;
            color: #333;
            text-decoration: none;
            border-bottom: 1px solid #eee;
            transition: all 0.3s ease;
        }
        .slide-menu-item:hover {
            background: #f5f5f5;
            padding-left: 25px;
            color: #4CAF50;
        }
        .dashboard-card {
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            transition: all 0.3s ease;
            border: none;
            background: white;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.15);
            background: #fafafa;
        }
        .hero-section {
            background: linear-gradient(135deg, #4CAF50, #81C784);
            color: white;
            padding: 100px 0;
            text-align: center;
            transition: all 0.4s ease;
            /* background-image: url('https://img.freepik.com/free-vector/pharmacy-background-design_1300-71.jpg'); */
            background-size: cover;
            background-blend-mode: overlay;
        }
        .hero-section:hover {
            background: linear-gradient(135deg, #2E7D32, #66BB6A);
            transform: scale(1.01);
        }
        .hero-title {
            font-size: 3.5rem;
            font-weight: bold;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            color: #ffffff;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .hero-title:hover {
            transform: scale(1.05);
            text-shadow: 3px 3px 6px rgba(0,0,0,0.3);
        }
        .hero-subtitle {
            font-size: 1.5rem;
            margin-bottom: 30px;
            transition: all 0.3s ease;
            color: #E8F5E9;
        }
        .hero-subtitle:hover {
            transform: translateY(-3px);
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        .feature-card {
            padding: 30px;
            text-align: center;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
            margin: 20px 0;
            transition: all 0.3s ease;
            border: none;
        }
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.12);
            background: #fafafa;
        }
        .feature-icon {
            font-size: 2.5rem;
            color: #4CAF50;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            /* background: url('https://cdn-icons-png.flaticon.com/512/4006/4006511.png') no-repeat center; */
            background-size: contain;
            height: 60px;
        }
        .feature-icon:hover {
            transform: rotate(10deg) scale(1.1);
            color: #2E7D32;
        }
        .card-title {
            color: #333;
            font-weight: 600;
        }
        .card-text {
            color: #2E7D32;
            font-weight: bold;
        }
    </style>
</head>
<body>
    
        <!-- ... existing code ... -->
        <div class="slide-menu">
            <div class="slide-menu-items">
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
    
        <!-- ... existing code ... -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        
        <!-- Add this JavaScript for the slide menu functionality -->
        <script>
            document.querySelector('.menu-toggle').addEventListener('click', function() {
                this.classList.toggle('active');
                document.querySelector('.slide-menu').classList.toggle('active');
            });
        </script>
    
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="#">PharmaStock</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card dashboard-card">
                <div class="card-body" onclick="window.location.href='view_medicines.php';" style="cursor: pointer;">
    <h5 class="card-title">Total Items</h5>
    <h2 class="card-text"><?php echo $total_items; ?></h2>
</div>
                    
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                <div class="card-body" onclick="window.location.href='low_stock.php'" style="cursor:pointer;">
    <h5 class="card-title">Low Stock</h5>
    <h2 class="card-text"><?php echo $low_stock_count; ?></h2>
</div>
                    
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                <div class="card-body" onclick="window.location.href='expired_medicines.php'">
    <h5 class="card-title">Expired Items</h5>
    <h2 class="card-text"><?php echo $expired_items; ?></h2>
</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                <div class="card-body" onclick="window.location.href='view_bills.php'">
    <h5 class="card-title">Total Bills Today</h5>
    <h2 class="card-text"><?php echo $total_bills_today; ?></h2>
</div>
                    
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-8">
            <div class="card dashboard-card">
    <div class="card-body text-center">
        <h5 class="card-title">Date & Time</h5>
        <h3 id="current-time"></h3>
        <h4 id="current-date"></h4>
        <br>
        <div id="calendar"></div> <!-- Calendar will be generated here -->
    </div>
</div>

<script>
    function updateDateTime() {
        let now = new Date();
        let hours = now.getHours();
        let minutes = now.getMinutes();
        let seconds = now.getSeconds();
        let ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12 || 12;
        minutes = minutes < 10 ? '0' + minutes : minutes;
        seconds = seconds < 10 ? '0' + seconds : seconds;
        let timeString = `${hours}:${minutes}:${seconds} ${ampm}`;

        let options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        let dateString = now.toLocaleDateString('en-US', options);

        document.getElementById("current-time").innerText = timeString;
        document.getElementById("current-date").innerText = dateString;
    }

    setInterval(updateDateTime, 1000);
    updateDateTime();

    function generateCalendar() {
        let now = new Date();
        let monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        let daysInMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0).getDate();
        let firstDay = new Date(now.getFullYear(), now.getMonth(), 1).getDay();

        let calendarHTML = `<h6>${monthNames[now.getMonth()]} ${now.getFullYear()}</h6>`;
        calendarHTML += "<table class='table table-sm text-center'><tr>";

        let dayNames = ["S", "M", "T", "W", "T", "F", "S"];
        for (let day of dayNames) {
            calendarHTML += `<th>${day}</th>`;
        }
        calendarHTML += "</tr><tr>";

        for (let i = 0; i < firstDay; i++) {
            calendarHTML += "<td></td>";
        }

        for (let day = 1; day <= daysInMonth; day++) {
            let today = now.getDate() === day ? "style='background-color: #007bff; color: white; border-radius: 50%; padding: 5px;'" : "";
            calendarHTML += `<td ${today}>${day}</td>`;

            if ((day + firstDay) % 7 === 0) {
                calendarHTML += "</tr><tr>";
            }
        }

        calendarHTML += "</tr></table>";
        document.getElementById("calendar").innerHTML = calendarHTML;
    }

    generateCalendar();
</script>

<style>
    #calendar table {
        width: 100%;
        border-collapse: collapse;
    }
    #calendar th, #calendar td {
        font-size: 12px; /* Smaller text */
        padding: 4px; /* Reduced padding */
        text-align: center;
    }
    #calendar h6 {
        font-size: 14px; /* Smaller month title */
        margin-bottom: 5px;
    }
</style>

            </div>
            <div class="col-md-4">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Quick Actions</h5>
                        <div class="d-grid gap-2">
    <button class="btn btn-primary" onclick="window.location.href='add_medicine.php'">Add New Item</button>
    <button class="btn btn-secondary" onclick="window.location.href='update_medicine.php'">Update Medicine</button>
    <button class="btn btn-success" onclick="window.location.href='generate_bill.php'">Generate Bill</button>
    <button class="btn btn-info" onclick="window.location.href='sales.php'">Sales Report</button>
    <button class="btn btn-warning" onclick="window.location.href='manage_pharmacist.php'">Manage Logins</button>

</div>

                        
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container mt-5">
        <div class="row">
            <!-- Notes Section -->
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Quick Notes</h5>
                    </div>
                    <div class="card-body">
                        <div class="notes-container" style="max-height: 300px; overflow-y: auto;">
                            <div id="notes-list">
                                <!-- Notes will be added here dynamically -->
                            </div>
                        </div>
                        <div class="mt-3">
                            <textarea class="form-control mb-2" id="new-note" rows="2" placeholder="Write a new note..."></textarea>
                            <button class="btn btn-success btn-sm" onclick="addNote()">Add Note</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Quick Stats -->
            <div class="col-md-8">
    <div class="row">
        <!-- Total Medicines in Stock -->
        <div class="col-md-6 mb-4">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Total Medicines in Stock</h5>
                    <?php
                    $queryStock = "SELECT SUM(quantity) AS total_stock FROM stock";
                    $resultStock = mysqli_query($conn, $queryStock);
                    $rowStock = mysqli_fetch_assoc($resultStock);
                    $totalStock = $rowStock['total_stock'] ?? 0;
                    ?>
                    <h2 class="card-text"><?php echo $totalStock; ?></h2>
                    
                </div>
            </div>
        </div>

        <!-- Total Expired Medicines -->
        <div class="col-md-6 mb-4">
            <div class="card bg-danger text-white shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Expired Medicines</h5>
                    <?php
                    $today = date('Y-m-d');
                    $queryExpired = "SELECT COUNT(*) AS expired_count FROM stock WHERE expiry_date < '$today'";
                    $resultExpired = mysqli_query($conn, $queryExpired);
                    $rowExpired = mysqli_fetch_assoc($resultExpired);
                    $expiredCount = $rowExpired['expired_count'] ?? 0;
                    ?>
                    <h2 class="card-text"><?php echo $expiredCount; ?></h2>
                    
                </div>
            </div>
        </div>

        <!-- Today's Total Sales -->
        <div class="col-md-6 mb-4">
            <div class="card bg-warning  text-white shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Today's Sales</h5>
                    <?php
                    $querySales = "SELECT SUM(total_amount) AS total_sales FROM bill WHERE DATE(bill_date) = '$today'";
                    $resultSales = mysqli_query($conn, $querySales);
                    $rowSales = mysqli_fetch_assoc($resultSales);
                    $totalSales = $rowSales['total_sales'] ?? 0;
                    ?>
                    <h2 class="card-text">₹<?php echo number_format($totalSales, 2); ?></h2>
                    
                </div>
            </div>
        </div>

        <!-- Total Bills Generated Today -->
        <div class="col-md-6 mb-4">
            <div class="card bg-info text-white shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Total Bills Generated Today</h5>
                    <?php
                    $queryBills = "SELECT COUNT(*) AS total_bills FROM bill WHERE DATE(bill_date) = '$today'";
                    $resultBills = mysqli_query($conn, $queryBills);
                    $rowBills = mysqli_fetch_assoc($resultBills);
                    $totalBills = $rowBills['total_bills'] ?? 0;
                    ?>
                    <h2 class="card-text"><?php echo $totalBills; ?></h2>
                    
                </div>
            </div>
        </div>
    </div>
</div>

        </div>
    </div>

    <script>
        function addNote() {
            const noteText = document.getElementById('new-note').value;
            if (noteText.trim() === '') return;

            const notesList = document.getElementById('notes-list');
            const noteElement = document.createElement('div');
            noteElement.className = 'alert alert-light alert-dismissible fade show mb-2';
            noteElement.innerHTML = `
                ${noteText}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            notesList.appendChild(noteElement);
            document.getElementById('new-note').value = '';
        }
    // Add hover animations
    document.addEventListener('DOMContentLoaded', () => {
        // Add hover effect to cards
        const cards = document.querySelectorAll('.card');
        cards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-5px)';
                card.style.transition = 'transform 0.3s ease';
            });
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0)';
            });
        });

        // Add hover effect to notes
        const notesList = document.getElementById('notes-list');
        notesList.addEventListener('mouseover', (e) => {
            if(e.target.classList.contains('alert')) {
                e.target.style.transform = 'scale(1.02)';
                e.target.style.transition = 'all 0.3s ease';
                e.target.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
            }
        });
        notesList.addEventListener('mouseout', (e) => {
            if(e.target.classList.contains('alert')) {
                e.target.style.transform = 'scale(1)';
                e.target.style.boxShadow = 'none';
            }
        });

        // Add hover effect to buttons
        const buttons = document.querySelectorAll('.btn');
        buttons.forEach(button => {
            button.addEventListener('mouseenter', () => {
                button.style.transform = 'scale(1.05)';
                button.style.transition = 'all 0.3s ease';
            });
            button.addEventListener('mouseleave', () => {
                button.style.transform = 'scale(1)';
            });
        });
    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
