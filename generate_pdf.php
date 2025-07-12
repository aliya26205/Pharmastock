<?php
require_once('tcpdf/tcpdf.php');
require_once('db_connect.php'); // Database connection

if (isset($_GET['bill_id'])) {
    $bill_id = intval($_GET['bill_id']);

    // Fetch bill details
    $bill_query = $conn->prepare("SELECT * FROM bill WHERE bill_id = ?");
    $bill_query->bind_param("i", $bill_id);
    $bill_query->execute();
    $bill_result = $bill_query->get_result();
    $bill_data = $bill_result->fetch_assoc();

    if (!$bill_data) {
        die("Bill not found.");
    }

    // Fetch bill items
    $items_query = $conn->prepare("SELECT bd.*, s.medicine_name, s.batch_no, s.price_per_unit 
                                   FROM billdetails bd 
                                   JOIN stock s ON bd.medicine_id = s.medicine_id 
                                   WHERE bd.bill_no = ?");
    $items_query->bind_param("i", $bill_id);
    $items_query->execute();
    $items_result = $items_query->get_result();

    // Create PDF document
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetTitle("PharmaStock - Bill Invoice");
    $pdf->SetHeaderData('', 0, 'N S Medical', "Rahmath Complex, Thokkottu, Karnataka575020 \nPhone: 9606738604");
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetMargins(10, 20, 10);
    $pdf->SetHeaderMargin(5);
    $pdf->SetFooterMargin(10);
    $pdf->SetAutoPageBreak(TRUE, 10);

    // **Set Unicode Font for ₹ Symbol**
    $pdf->SetFont('dejavusans', '', 12);
    $pdf->AddPage();
    date_default_timezone_set('Asia/Kolkata');
    // Bill Header
    $html = "
    <h2 align='center'>PharmaStock Invoice</h2>
    <table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>
        <tr>
            <td><strong>Bill ID:</strong> {$bill_data['bill_id']}</td>
            <td><strong>Bill Date And Time:</strong> {$bill_data['bill_date']}</td>
        </tr>
        <tr>
            <td><strong>Customer:</strong> {$bill_data['customer_name']}</td>
            <td><strong>Contact:</strong> {$bill_data['customer_contact']}</td>
        </tr>
        <tr>
            <td colspan='2'><strong>Payment Method:</strong> {$bill_data['payment_method']}</td>
        </tr>
    </table>
    <br>
    <h3>Medicines Purchased:</h3>
    <table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%; text-align: center;'>
        <tr style='background-color: #f2f2f2; font-weight: bold;'>
            <th border='1'>Sl No</th>
            <th border='1'>Medicine</th>
            <th border='1'>Batch No</th>
            <th border='1'>Price Per Unit (₹)</th>
            <th border='1'>Quantity</th>
            <th border='1'>Total Price (₹)</th>
        </tr>";

    $sl_no = 1;
    $grand_total = 0;
    while ($row = $items_result->fetch_assoc()) {
        $total_price = $row['quantity'] * $row['unit_price'];
        $grand_total += $total_price;
        $html .= "<tr>
                    <td border='1'>{$sl_no}</td>
                    <td border='1'>{$row['medicine_name']}</td>
                    <td border='1'>{$row['batch_no']}</td>
                    <td border='1'>₹ {$row['unit_price']}</td>
                    <td border='1'>{$row['quantity']}</td>
                    <td border='1'>₹ {$total_price}</td>
                  </tr>";
        $sl_no++;
    }

    // Grand Total Section
    $html .= "</table>
    <h3 align='right'>Grand Total: ₹ {$grand_total}</h3>
    <p align='center'>Thank you for shopping with us!</p>";

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output("Bill_{$bill_id}.pdf", 'D');
}
?>
