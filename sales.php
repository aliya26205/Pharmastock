<?php
session_start();
include('db_connect.php'); // Database connection file
require('tcpdf/tcpdf.php'); // Include TCPDF library for PDF generation
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
        body {
            background-color: #f5f5f5;
            font-family: Arial, sans-serif;
            padding: 20px;
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

        .openbtn {
            font-size: 20px;
            cursor: pointer;
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            position: fixed;
            right: 0;
            top: 0;
            z-index: 2;
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
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            margin: 0 5px;
        }
        .btn:hover {
            background-color: #45a049;
            color: white;
        }
        #salesChartDaily, #salesChartMonthly {
            width: 80%; /* Make the chart wider */
            max-width: 800px; /* Set a maximum width if needed */
            height: 400px; /* Increase the height */
            margin: 20px auto;
        }
        .report-options {
            text-align: center;
            margin-bottom: 20px;
        }
        .report-section {
            margin-top: 30px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        .chart-container {
            display: flex;
            justify-content: center;
            align-items: center;
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
            <a href="view_bills.php" class="slide-menu-item">View bills</a>
            <a href="logout.php" class="slide-menu-item">Logout</a>
        </div>
    </div>

    <div class="menu-toggle">
        <div class="menu-line"></div>
        <div class="menu-line"></div>
        <div class="menu-line"></div>
    </div>

<script>
    document.querySelector('.menu-toggle').addEventListener('click', function() {
        this.classList.toggle('active');
        document.querySelector('.slide-menu').classList.toggle('active');
    });
</script>

<div class="container">
    <h2>Sales Report</h2>

    <div class="text-center mb-4">
        <a href="dashboard.php" class="btn">Back to Dashboard</a>
    </div>

    <div class="report-options">
    <button class="btn btn-primary" onclick="showDailyReport()">Daily Sales</button>
    <button class="btn btn-info" onclick="showMonthlyReport()">Monthly Sales</button>
</div>

<div id="dailyReportSection" class="report-section">
    <h3>Daily Sales Report</h3>
    <?php
    $today = date('Y-m-d');
    $queryDaily = "SELECT s.medicine_name, s.price_per_unit, sa.quantity_sold, 
                          (s.price_per_unit * sa.quantity_sold) AS total
                   FROM sales sa
                   JOIN stock s ON sa.medicine_id = s.medicine_id
                   JOIN bill b ON sa.bill_id = b.bill_id
                   WHERE DATE(b.bill_date) = '$today'";

    $resultDaily = mysqli_query($conn, $queryDaily);
    if (!$resultDaily) {
        die("Query Failed: " . mysqli_error($conn));
    }

    $totalSalesDaily = 0;
    $chartDataDaily = [];
    while ($row = mysqli_fetch_assoc($resultDaily)) {
        $totalSalesDaily += $row['total'];
        $chartDataDaily[] = $row;
    }
    ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-success">
                <tr>
                    <th>Medicine Name</th>
                    <th>Price per Unit</th>
                    <th>Quantity Sold</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($resultDaily) > 0): ?>
                    <?php foreach ($chartDataDaily as $row): ?>
                        <tr>
                            <td><?php echo $row['medicine_name']; ?></td>
                            <td><?php echo $row['price_per_unit']; ?></td>
                            <td><?php echo $row['quantity_sold']; ?></td>
                            <td><?php echo $row['total']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center">No sales recorded for today.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <h4 class="text-center mt-4">Total Sales for Today: <?php echo number_format($totalSalesDaily, 2); ?> INR</h4>

    <?php if (!empty($chartDataDaily)): ?>
        <div class="chart-container">
            <canvas id="salesChartDaily"></canvas>
        </div>
    <?php else: ?>
        <p class="text-center">No chart available for today's sales.</p>
    <?php endif; ?>

    <div class="text-center mt-4">
        <form method="post" action="generate_sales_report.php" class="d-inline">
            <input type="hidden" name="report_type" value="daily">
            <button type="submit" name="generate_pdf" class="btn btn-danger">Generate Daily Sales Report (PDF)</button>
        </form>
    </div>
</div>

<div id="monthlyReportSection" class="report-section" style="display: none;">
    <h3>Monthly Sales Report</h3>
    <?php
    $currentMonth = date('Y-m');
    $queryMonthly = "SELECT s.medicine_name, s.price_per_unit, SUM(sa.quantity_sold) AS quantity_sold, 
                            SUM(s.price_per_unit * sa.quantity_sold) AS total_sales
                     FROM sales sa
                     JOIN stock s ON sa.medicine_id = s.medicine_id
                     JOIN bill b ON sa.bill_id = b.bill_id
                     WHERE DATE_FORMAT(b.bill_date, '%Y-%m') = '$currentMonth'
                     GROUP BY s.medicine_name, s.price_per_unit
                     ORDER BY total_sales DESC";

    $resultMonthly = mysqli_query($conn, $queryMonthly);
    if (!$resultMonthly) {
        die("Query Failed: " . mysqli_error($conn));
    }

    $totalSalesMonthly = 0;
    $chartDataMonthly = [];
    while ($row = mysqli_fetch_assoc($resultMonthly)) {
        $totalSalesMonthly += $row['total_sales'];
        $chartDataMonthly[] = $row;
    }
    ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover" id="monthlySalesTable">
            <thead class="table-info">
                <tr>
                    <th>Medicine Name</th>
                    <th>Price per Unit</th>
                    <th>Total Quantity Sold</th>
                    <th>Total Sales</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($resultMonthly) > 0): ?>
                    <?php foreach ($chartDataMonthly as $row): ?>
                        <tr>
                            <td><?php echo $row['medicine_name']; ?></td>
                            <td><?php echo $row['price_per_unit']; ?></td>
                            <td><?php echo $row['quantity_sold']; ?></td>
                            <td><?php echo $row['total_sales']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center">No sales recorded for the current month.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <h4 class="text-center mt-4">Total Sales for Current Month: <?php echo number_format($totalSalesMonthly, 2); ?> INR</h4>

    <?php if (!empty($chartDataMonthly)): ?>
        <div class="chart-container">
            <canvas id="salesChartMonthly"></canvas>
        </div>
    <?php else: ?>
        <p class="text-center">No chart available for this month's sales.</p>
    <?php endif; ?>

    <div class="text-center mt-4">
        <form method="post" action="generate_monthly_sales_report_pdf.php" class="d-inline">
            <input type="hidden" name="report_type" value="monthly">
            <button type="submit" name="generate_pdf" class="btn btn-warning">Generate Monthly Sales Report (PDF)</button>
        </form>
    </div>
</div>

<script>
function showDailyReport() {
    document.getElementById('dailyReportSection').style.display = 'block';
    document.getElementById('monthlyReportSection').style.display = 'none';
    generateDailyChart();
}

function showMonthlyReport() {
    document.getElementById('dailyReportSection').style.display = 'none';
    document.getElementById('monthlyReportSection').style.display = 'block';
    generateMonthlyChart();
}

function generateDailyChart() {
    <?php if (!empty($chartDataDaily)): ?>
        var ctxDaily = document.getElementById('salesChartDaily').getContext('2d');
        new Chart(ctxDaily, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($chartDataDaily, 'medicine_name')); ?>,
                datasets: [{
                    label: 'Sales Amount',
                    data: <?php echo json_encode(array_column($chartDataDaily, 'total')); ?>,
                    backgroundColor: 'rgba(76, 175, 80, 0.6)',
                    borderColor: 'rgba(76, 175, 80, 1)',
                    borderWidth: 1
                }]
            },
            options: { responsive: true }
        });
    <?php endif; ?>
}

function generateMonthlyChart() {
    var ctxMonthly = document.getElementById('salesChartMonthly').getContext('2d');
    new Chart(ctxMonthly, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($chartDataMonthly, 'medicine_name')); ?>,
            datasets: [{
                label: 'Total Sales',
                data: <?php echo json_encode(array_column($chartDataMonthly, 'total_sales')); ?>,
                backgroundColor: 'rgba(76, 175, 80, 0.6)',
                borderColor: 'rgba(76, 175, 80, 1)',
                borderWidth: 1
            }]
        },
        options: { responsive: true }
    });
}

showDailyReport();
</script>
