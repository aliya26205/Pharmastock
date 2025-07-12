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
    $customer_name = $_POST['customer_name'];
    $customer_contact = $_POST['customer_contact'];
    $payment_method = $_POST['payment_method'];
    $total_amount = $_POST['grand_total'];
    $bill_date = date("Y-m-d H:i:s");

    if (!preg_match("/^[a-zA-Z ]+$/", $customer_name)) {
        echo "<script>alert('Error: Customer name should contain only letters.'); window.history.back();</script>";
        exit();
    }
    if (!preg_match("/^[0-9]{10}$/", $customer_contact)) {
        echo "<script>alert('Error: Contact number must be 10 digits.'); window.history.back();</script>";
        exit();
    }

    // Check if there are valid medicines in the bill
    if (empty($_POST['medicine_id'])) {
        echo "<script>alert('Error: No valid medicines added to the bill!'); window.history.back();</script>";
        exit();
    }

    // Insert into Bill Table
    $stmt = $conn->prepare("INSERT INTO bill (bill_id, customer_name, customer_contact, payment_method, total_amount, bill_date) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssds", $bill_id, $customer_name, $customer_contact, $payment_method, $total_amount, $bill_date);
    $stmt->execute();

    // Insert into BillDetails Table and update stock
    foreach ($_POST['medicine_id'] as $index => $medicine_id) {
        $batch_no = $_POST['batch_no'][$index];
        $quantity = $_POST['quantity'][$index];
        $price_per_unit = $_POST['price_per_unit'][$index];
        $total_price = $_POST['total_price'][$index];

        // Prevent billing expired medicine
        $check_expiry = $conn->query("SELECT medicine_name, expiry_date FROM stock WHERE medicine_id = '$medicine_id' AND batch_no = '$batch_no'");
        $expiry_data = $check_expiry->fetch_assoc();
        if ($expiry_data['expiry_date'] < date("Y-m-d")) {
            echo "<script>alert('Error: Cannot bill expired medicine: {$expiry_data['medicine_name']}!'); window.history.back();</script>";
            exit();
        }

        // Insert into BillDetails
        $stmt = $conn->prepare("INSERT INTO BillDetails (bill_no, medicine_id, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiidd", $bill_id, $medicine_id, $quantity, $price_per_unit, $total_price);
        $stmt->execute();

        // Deduct stock
        $conn->query("UPDATE stock SET quantity = quantity - $quantity WHERE medicine_id = '$medicine_id' AND batch_no = '$batch_no'");
    }

    // Redirect to view the bill first
    echo "<script>window.location.href='viewreport.php?bill_id=$bill_id';</script>";
}

date_default_timezone_set('Asia/Kolkata');
?>
