<?php
require_once('tcpdf/tcpdf.php');
require_once('db_connect.php'); // Database connection

// Fetch all low-stock medicines (quantity ≤ 50)
$query = "SELECT medicine_id, medicine_name, batch_no, price_per_unit, quantity, supplier_name
          FROM stock WHERE quantity <= 50 ORDER BY quantity ASC";
$result = $conn->query($query);

// Create a new PDF document
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle("PharmaStock - Low Stock Report");
$pdf->SetHeaderData('', 0, 'N S Medical', "Rahmath Complex, Thokkottu, Karnataka 575020 \nPhone: 9606738604");
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(10, 20, 10);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(TRUE, 15);

// **Set Unicode Font for ₹ Symbol**
$pdf->SetFont('dejavusans', '', 12);
$pdf->AddPage();
date_default_timezone_set('Asia/Kolkata');

// Report Header
$html = "
<h2 align='center' style='margin-bottom: 10px;'>Low Stock Report</h2>
<p align='center' style='margin-bottom: 15px;'><strong>Generated On:</strong> " . date('Y-m-d H:i:s') . "</p>
<table border='1' cellpadding='7' cellspacing='0' style='border-collapse: collapse; width: 100%; text-align: center; font-size: 10px;'>
    <tr style='background-color: #d9d9d9; font-weight: bold;'>
        <th width='6%'>Sl No</th>
        <th width='12%'>Medicine ID</th>
        <th width='30%'>Medicine Name</th>
        <th width='12%'>Batch No</th>
        <th width='12%'>Price (₹)</th>
        <th width='10%'>Quantity</th>
        <th width='18%'>Supplier</th>
    </tr>";

// Display all low-stock medicines
$sl_no = 1;
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $html .= "<tr>
                    <td width='6%' style='border: 1px solid black;'>{$sl_no}</td>
                    <td width='12%' style='border: 1px solid black;'>{$row['medicine_id']}</td>
                    <td width='30%' style='border: 1px solid black;'>{$row['medicine_name']}</td>
                    <td width='12%' style='border: 1px solid black;'>{$row['batch_no']}</td>
                    <td width='12%' style='border: 1px solid black;'>₹ {$row['price_per_unit']}</td>
                    <td width='10%' style='border: 1px solid black;'>{$row['quantity']}</td>
                    <td width='18%' style='border: 1px solid black;'>{$row['supplier_name']}</td>
                </tr>";
        $sl_no++;
    }
} else {
    $html .= "<tr><td colspan='7' align='center' style='border: 1px solid black; padding: 10px;'>No Low Stock Medicines</td></tr>";
}

$html .= "</table><br><br>
<p align='center' style='font-size: 12px;'><em> Medicines which are less than 50 and need restocking.</em></p>";

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output("Low_Stock_Report.pdf", 'D');
$conn->close();
?>