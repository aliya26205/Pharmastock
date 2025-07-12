<?php
require('tcpdf/tcpdf.php');
include('db_connect.php'); // Include database connection

// Check if the 'generate_pdf' button is pressed
if (isset($_POST['generate_pdf'])) {
    // Extend TCPDF class to customize header and footer
    class PDF extends TCPDF {
        // Header of the PDF
        function Header() {
            // Set font for the header
            $this->SetFont('helvetica', 'B', 16); // Use Helvetica Bold for title
            $this->Cell(0, 10, 'N S Medical - Daily Sales Report', 0, 1, 'C'); // Title in center
            $this->SetFont('helvetica', '', 10); // Set normal font for address and date
            $this->Cell(0, 10, 'Address: Rahmath Complex, Thokkottu, Karnataka575020 Ph: 9606738604', 0, 1, 'C'); // Address in center
            $this->Cell(0, 10, 'Date: ' . date('Y-m-d'), 0, 1, 'C'); // Date in center
            $this->SetFont('helvetica', '', 12); // Reset to normal font size for table
            $this->Ln(10); // Add a 10 unit space between the date and the table header
            // Store the current Y position after the header
            $this->tableStartY = $this->GetY();
        }

        // Footer of the PDF
        function Footer() {
            $this->SetY(-15); // Position the footer 15 units from the bottom
            $this->SetFont('helvetica', 'I', 8); // Set font to italic for page numbers
            $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'C'); // Display page number in center
        }

        public $tableStartY; // Variable to store the starting Y position of the table
    }

    // Create an instance of the PDF class
    $pdf = new PDF();
    $pdf->AddPage(); // Add a page to the PDF
    $pdf->SetFont('helvetica', 'B', 12); // Set bold font for the column headers

    // Define column widths for better spacing
    $columnWidths = array(80, 35, 30, 35); // Increased Medicine Name width further

    // Set the starting Y position for the table
    $pdf->SetY($pdf->tableStartY);

    // Create the header for the sales data table
    $pdf->Cell($columnWidths[0], 10, 'Medicine Name', 1); // Cell for medicine name
    $pdf->Cell($columnWidths[1], 10, 'Price per Unit', 1); // Cell for price per unit
    $pdf->Cell($columnWidths[2], 10, 'Quantity Sold', 1); // Cell for quantity sold
    $pdf->Cell($columnWidths[3], 10, 'Total', 1); // Cell for total sales (price * quantity)
    $pdf->Ln(); // Move to the next line for the data

    // Get the current date to generate the sales report for today
    $today = date('Y-m-d');

    // Query to fetch sales data for the current day
    $query = "SELECT s.medicine_name, s.price_per_unit, sa.quantity_sold,
                      (s.price_per_unit * sa.quantity_sold) AS total
               FROM sales sa
               JOIN stock s ON sa.medicine_id = s.medicine_id
               WHERE DATE(sa.sale_date) = '$today'";

    // Execute the query and get the result
    $result = mysqli_query($conn, $query);

    // Check if the query was successful and fetch data
    $pdf->SetFont('helvetica', '', 12); // Set normal font for the data rows
    $totalSales = 0; // Initialize variable to store the total sales amount

    // Loop through each row of the sales data
    while ($row = mysqli_fetch_assoc($result)) {
        // Output each row as a new row in the table
        $pdf->Cell($columnWidths[0], 10, $row['medicine_name'], 1); // Medicine name
        $pdf->Cell($columnWidths[1], 10, number_format($row['price_per_unit'], 2), 1, 0, 'R'); // Price per unit, formatted to 2 decimal places, right-aligned
        $pdf->Cell($columnWidths[2], 10, $row['quantity_sold'], 1, 0, 'C'); // Quantity sold, center-aligned
        $pdf->Cell($columnWidths[3], 10, number_format($row['total'], 2), 1, 0, 'R'); // Total sales for that medicine, right-aligned
        $pdf->Ln(); // Move to the next line
        $totalSales += $row['total']; // Add this medicine's total to the grand total sales
    }

    // Display the total sales for the day at the bottom of the table
    $pdf->SetFont('helvetica', 'B', 12); // Bold font for the total sales row
    $pdf->Cell(array_sum($columnWidths) - $columnWidths[3], 10, 'Total Sales for Today:', 1, 0, 'R'); // Label for total sales, right-aligned
    $pdf->Cell($columnWidths[3], 10, number_format($totalSales, 2) . ' INR', 1, 0, 'R'); // Total sales value, formatted to 2 decimal places, right-aligned
    $pdf->Output('Sales_Report_' . date('Y-m-d') . '.pdf', 'D'); // Output the PDF with the current date as the filename

    exit; // Exit the script to prevent any further output
} else {
    // Redirect to the sales page if the PDF generation button is not clicked
    header('Location: sales.php');
    exit;
}
?>