<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}
// Database Connection
$conn = new mysqli("localhost", "root", "", "pharmastock");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch latest Bill ID
$result = $conn->query("SELECT MAX(bill_id) AS last_id FROM bill");
$row = $result->fetch_assoc();
$bill_id = $row['last_id'] + 1;


// Generate Bill
if (isset($_POST['generate_bill'])) {
    // Get customer details and payment method
    $customer_name = $_POST['customer_name'];
    $customer_contact = $_POST['customer_contact'];
    $payment_method = $_POST['payment_method'];
    $total_amount = $_POST['grand_total'];
    $bill_date = date("Y-m-d H:i:s");

    // Validation: Ensure customer name contains only letters and spaces
    if (!preg_match("/^[a-zA-Z ]+$/", $customer_name)) {
        echo "<script>alert('Error: Customer name should contain only letters.'); window.history.back();</script>";
        exit();
    }

    // Validation: Ensure contact number is exactly 10 digits
    if (!preg_match("/^[0-9]{10}$/", $customer_contact)) {
        echo "<script>alert('Error: Contact number must be 10 digits.'); window.history.back();</script>";
        exit();
    }

    // Validation: Ensure at least one medicine is selected before proceeding
    if (empty($_POST['medicine_id'])) {
        echo "<script>alert('Error: No medicines added to the bill. Please add medicines before generating a bill.'); window.history.back();</script>";
        exit();
    }

    // Check if any expired medicine is in the list
    foreach ($_POST['medicine_id'] as $index => $medicine_id) {
        $batch_no = $_POST['batch_no'][$index];

        // Query to check expiry date of selected medicine batch
        $check_expiry = $conn->query("SELECT expiry_date FROM stock WHERE medicine_id = '$medicine_id' AND batch_no = '$batch_no'");
        $expiry_data = $check_expiry->fetch_assoc();

        // If any medicine is expired, show an error and stop the process
        if ($expiry_data['expiry_date'] < date("Y-m-d")) {
            echo "<script>alert('Error: Cannot bill expired medicine!'); window.history.back();</script>";
            exit();
        }
    }

    // Insert the bill into the 'bill' table
    $stmt = $conn->prepare("INSERT INTO bill (customer_name, customer_contact, payment_method, total_amount, bill_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssds", $customer_name, $customer_contact, $payment_method, $total_amount, $bill_date);
    $stmt->execute();

    // Get the last inserted bill ID
    $bill_id = $conn->insert_id;

    // Insert each medicine into the 'billdetails' table and update stock
    foreach ($_POST['medicine_id'] as $index => $medicine_id) {
        $batch_no = $_POST['batch_no'][$index];
        $quantity = $_POST['quantity'][$index];
        $price_per_unit = $_POST['price_per_unit'][$index];
        $total_price = $_POST['total_price'][$index];

        // Check if there is enough stock available before proceeding
        $stock_check = $conn->query("SELECT quantity FROM stock WHERE medicine_id = '$medicine_id' AND batch_no = '$batch_no'");
        $stock_data = $stock_check->fetch_assoc();

        if ($stock_data['quantity'] < $quantity) {
            echo "<script>alert('Error: Not enough stock available!'); window.history.back();</script>";
            exit();
        }
        // Check if batch_no is set and not empty
        if (!isset($_POST['batch_no'][$index]) || empty($batch_no)) {
            echo "<script>alert('Error: Batch Number is required for Medicine ID " . $medicine_id . ". Please ensure medicine details are fetched correctly.'); window.history.back();</script>";
            exit();
        }
        // Insert medicine details into 'billdetails' table (Corrected batch_no as VARCHAR)
        $stmt = $conn->prepare("INSERT INTO billdetails (bill_no, medicine_id, batch_no, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissdd", $bill_id, $medicine_id, $batch_no, $quantity, $price_per_unit, $total_price);
        $stmt->execute();

        // Deduct stock after ensuring sufficient quantity
        $conn->query("UPDATE stock SET quantity = quantity - $quantity WHERE medicine_id = '$medicine_id' AND batch_no = '$batch_no'");
    }
    // Insert the sale into the 'sales' table
    $sale_date = date("Y-m-d H:i:s");
    $sql_sale = "INSERT INTO sales (bill_id, medicine_id, quantity_sold, total_price, sale_date) VALUES (?, ?, ?, ?, ?)";
    $stmt_sale = $conn->prepare($sql_sale);
    $stmt_sale->bind_param("iiids", $bill_id, $medicine_id, $quantity, $total_price, $sale_date);
    $stmt_sale->execute();
    $stmt_sale->close();
    // Show success message and option to download the bill
    echo "<script>
        if (confirm('Bill Generated Successfully! Do you want to download the bill?')) {
            window.open('generate_pdf.php?bill_id=$bill_id', '_blank');
        } else {
            window.location.href='generate_bill.php';
        }
    </script>";
}

if (isset($_POST['fetch_medicine'])) {
    $value = $_POST['value'];
    // Determine column based on the 'type' sent from frontend
    if ($_POST['type'] == 'id') {
        $column = 'medicine_id';
    } elseif ($_POST['type'] == 'batch') {
        $column = 'batch_no';
    } elseif ($_POST['type'] == 'name') {
        $column = 'medicine_name';
    }

    // Query based on the column selected
    $query = $conn->query("SELECT medicine_id, batch_no, medicine_name, price_per_unit, quantity FROM stock WHERE $column LIKE '%$value%'");
    $data = $query->fetch_assoc();
    echo json_encode($data);
    exit();
}


date_default_timezone_set('Asia/Kolkata');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Bill</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background-color: #f5f5f5;
            font-family: Arial, sans-serif;
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
        .form-control {
            margin-bottom: 15px;
        }
        .btn-success {
            background-color: #4CAF50;
            border: none;
        }
        .btn-success:hover {
            background-color: #45a049;
        }
        .btn-danger {
            background-color: #dc3545;
            border: none;
        }
        .table {
            margin-top: 20px;
        }
        .table th {
            background-color: #4CAF50;
            color: white;
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
        .customer-info {
            background: #E8F5E9;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .payment-method {
            margin: 15px 0;
        }
        .grand-total {
            font-size: 1.2em;
            font-weight: bold;
            margin: 20px 0;
        }
    </style>
</head>
<body>

<div class="slide-menu">
    <div class="slide-menu-items">
        <a href="dashboard.php" class="slide-menu-item">Dashboard</a>
        <a href="add_medicine.php" class="slide-menu-item">Add Medicines</a>
            <a href="update_medicine.php" class="slide-menu-item">Update Medicines</a>
            <a href="view_medicines.php" class="slide-menu-item">view Medicines</a>
            <a href="view_bills.php" class="slide-menu-item">View Bills</a>
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
    <h2>Generate Bill</h2>
    <form method="POST">
        <div class="customer-info">
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label">Bill ID:</label>
                    <input type="text" class="form-control" name="bill_id" value="<?= $bill_id ?>" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Customer Name:</label>
                    <input type="text" class="form-control" name="customer_name" required  pattern="[A-Za-z\s]+" title="Only characters are allowed">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Contact:</label>
                    <input type="text" class="form-control" name="customer_contact" required pattern="[0-9]{10}" title="Only 10 numbers are allowed">
                </div>
            </div>
            
            <div class="payment-method">
                <label class="form-label">Payment Method:</label>
                <div class="form-check form-check-inline">
                    <input type="radio" class="form-check-input" name="payment_method" value="Cash" required>
                    <label class="form-check-label">Cash</label>
                </div>
                <div class="form-check form-check-inline">
                    <input type="radio" class="form-check-input" name="payment_method" value="Card">
                    <label class="form-check-label">Card</label>
                </div>
                <div class="form-check form-check-inline">
                    <input type="radio" class="form-check-input" name="payment_method" value="UPI">
                    <label class="form-check-label">UPI</label>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered" id="billTable">
                <thead>
                    <tr>
                        <th>Sl No</th>
                        <th>Medicine ID</th>
                        <th>Batch No</th>
                        <th>Medicine Name</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <button type="button" class="btn btn-primary" onclick="addMedicineRow()">Add Medicine</button>

        <div class="grand-total">
            <label>Grand Total:</label>
            <input type="text" class="form-control" name="grand_total" id="grand_total" readonly>
        </div>

        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <button type="submit" name="generate_bill" class="btn btn-success">Generate Bill</button>
            <button type="button" onclick="window.location.href='dashboard.php'" class="btn btn-danger">Go Back</button>
        </div>
    </form>
</div>

<script>  
  document.addEventListener("DOMContentLoaded", function () {
    let billItems = [];

    document.getElementById("add_to_bill").addEventListener("click", function () {
        let medicineName = document.getElementById("medicine_name").value;
        let batchNo = document.getElementById("batch_no").value;
        let expiryDate = new Date(document.getElementById("expiry_date").value);
        let currentDate = new Date();
        let price = parseFloat(document.getElementById("price_per_unit").value);
        let quantity = parseInt(document.getElementById("quantity").value);

        if (!medicineName || !batchNo || isNaN(price) || isNaN(quantity) || !expiryDate) {
            alert("Please fill in all fields correctly.");
            return;
        }

        if (expiryDate < currentDate) {
            alert(`The medicine "${medicineName}" is expired and cannot be added.`);
            return;
        }

        let totalPrice = price * quantity;
        billItems.push({ medicineName, batchNo, price, quantity, totalPrice });

        let table = document.getElementById("bill_table").getElementsByTagName('tbody')[0];
        let newRow = table.insertRow();
        newRow.innerHTML = `
            <td>${medicineName}</td>
            <td>${batchNo}</td>
            <td>${price.toFixed(2)}</td>
            <td>${quantity}</td>
            <td>${totalPrice.toFixed(2)}</td>
        `;

        updateTotal();
    });

    function updateTotal() {
        let total = billItems.reduce((sum, item) => sum + item.totalPrice, 0);
        document.getElementById("total_amount").innerText = `Total: ₹${total.toFixed(2)}`;
    }

    document.getElementById("generate_bill").addEventListener("click", function () {
        if (billItems.length === 0) {
            alert("Cannot generate an empty bill!");
            return;
        }

        let billPreview = "Bill Preview:\n";
        billItems.forEach(item => {
            billPreview += `${item.medicineName} - ${item.quantity} x ₹${item.price.toFixed(2)} = ₹${item.totalPrice.toFixed(2)}\n`;
        });

        billPreview += `\nTotal: ₹${billItems.reduce((sum, item) => sum + item.totalPrice, 0).toFixed(2)}`;
        let confirmDownload = confirm(`${billPreview}\n\nDo you want to download this bill as a PDF?`);

        if (confirmDownload) {
            downloadBillPDF();
        }
    });

    function downloadBillPDF() {
        let pdfContent = "Medicine Name | Batch No | Price | Quantity | Total Price\n";
        pdfContent += "-".repeat(50) + "\n";
        billItems.forEach(item => {
            pdfContent += `${item.medicineName} | ${item.batchNo} | ₹${item.price.toFixed(2)} | ${item.quantity} | ₹${item.totalPrice.toFixed(2)}\n`;
        });

        let blob = new Blob([pdfContent], { type: "text/plain" });
        let link = document.createElement("a");
        link.href = URL.createObjectURL(blob);
        link.download = "bill.txt"; // You can use a library like jsPDF for actual PDFs
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
});





// Function to add a new row to the bill table
function addMedicineRow() {
    let table = document.getElementById('billTable').getElementsByTagName('tbody')[0];
    let row = table.insertRow(); // Create a new row

    // Define the row structure with input fields
    row.innerHTML = `
        <td></td> <!-- Serial number (Sl No) will be updated dynamically -->
        <td><input type="text" class="form-control" name="medicine_id[]" onblur="fetchMedicineDetails(this, 'id')"></td>
        <td><input type="text" class="form-control" name="batch_no[]" onblur="fetchMedicineDetails(this, 'batch')"></td>
        <td><input type="text" class="form-control" name="medicine_name[]" onblur="fetchMedicineDetails(this, 'name')"></td>
        <td><input type="text" class="form-control" name="price_per_unit[]" readonly></td>
        <td><input type="number" class="form-control" name="quantity[]" oninput="checkStockAndCalculateTotal(this)" min="1"></td>
        <td><input type="text" class="form-control" name="total_price[]" readonly></td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Remove</button></td>
    `;

    updateSerialNumbers(); // Update serial numbers after adding a new row
}

// Function to remove a row when the "Remove" button is clicked
function removeRow(btn) {
    let row = btn.parentNode.parentNode; // Get the row to be removed
    row.parentNode.removeChild(row); // Remove the row from the table
    updateSerialNumbers(); // Update serial numbers after deletion
    calculateGrandTotal(); // Update the total amount after removing an item
}

// Function to update serial numbers (Sl No) dynamically
function updateSerialNumbers() {
    let table = document.getElementById('billTable').getElementsByTagName('tbody')[0];
    let rows = table.getElementsByTagName('tr'); // Get all rows in the table body

    for (let i = 0; i < rows.length; i++) {
        rows[i].cells[0].innerText = i + 1; // Update the first cell (Sl No) with a new number
    }
}
// Function to fetch medicine details based on ID, Batch No, or Medicine Name
function fetchMedicineDetails(input, type) {
    let value = input.value.trim(); // Get the entered value and trim whitespace
    if (value === "") return; // Stop if the field is empty

    // AJAX request to fetch medicine details from the server (generate_bill.php)
    $.post('generate_bill.php', { fetch_medicine: true, value: value, type: type }, function(response) {
        try {
            let data = JSON.parse(response); // Convert response to JSON format

            if (!data || Object.keys(data).length === 0) {
                alert("No medicine found. Please enter a valid value.");
                input.value = ""; // Clear input field
                return;
            }

            let row = input.closest('tr'); // Get the row where the input is present

            // Check if the medicine is expired
            let today = new Date().toISOString().split('T')[0]; // Get today's date in YYYY-MM-DD format
            if (data.expiry_date < today) {
                alert("Error: Cannot select expired medicine!");
                input.value = ""; // Clear the input field
                return;
            }

            // Auto-fill medicine details in the respective fields
            row.cells[1].querySelector('input').value = data.medicine_id || ""; // Medicine ID
            row.cells[2].querySelector('input').value = data.batch_no || ""; // Batch No
            row.cells[3].querySelector('input').value = data.medicine_name || ""; // Medicine Name
            row.cells[4].querySelector('input').value = data.price_per_unit || ""; // Price per unit
            row.cells[5].querySelector('input').dataset.maxStock = data.quantity || 0; // Store max stock for validation

            // Lock the fields after auto-filling to prevent manual modification
            row.cells[1].querySelector('input').readOnly = true; // Lock Medicine ID
            row.cells[2].querySelector('input').readOnly = true; // Lock Batch No
            row.cells[3].querySelector('input').readOnly = true; // Lock Medicine Name
            row.cells[4].querySelector('input').readOnly = true; // Lock Price per unit

        } catch (error) {
            console.error("Error parsing JSON:", error);
            alert("Error fetching medicine details. Please try again.");
        }
    }).fail(function() {
        alert("Failed to connect to the server.");
    });
}


// Function to validate stock and calculate total price
function checkStockAndCalculateTotal(input) {
    let row = input.parentNode.parentNode; // Get the row where the quantity is entered
    let quantity = parseInt(input.value, 10) || 0; // Get entered quantity
    let maxStock = parseInt(input.dataset.maxStock, 10); // Get max stock available

    // Validate if entered quantity exceeds available stock
    if (quantity > maxStock) {
        alert("Error: Quantity exceeds available stock!");
        input.value = maxStock; // Reset to max available stock
        quantity = maxStock;
    }

    let pricePerUnit = parseFloat(row.cells[4].querySelector('input').value) || 0; // Get price per unit
    let totalPrice = quantity * pricePerUnit; // Calculate total price
    row.cells[6].querySelector('input').value = totalPrice.toFixed(2); // Update total price field

    calculateGrandTotal(); // Update the final total amount
}
function calculateGrandTotal() {
    let total = 0;
    document.querySelectorAll('[name="total_price[]"]').forEach(input => {
        total += parseFloat(input.value) || 0;
    });
    document.getElementById('grand_total').value = total.toFixed(2);
}

document.querySelector('.menu-toggle').addEventListener('click', function() {
    this.classList.toggle('active');
    document.querySelector('.slide-menu').classList.toggle('active');
});
</script>
</body>
</html>