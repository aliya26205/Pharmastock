# 💊 PharmaStock Manager

**PharmaStock Manager** is a simple and efficient web-based application built using **HTML, CSS, JavaScript, PHP, and MySQL**. It helps medical shops or pharmacies manage their inventory, sales, and user accounts effectively.

---

## 📌 Features

- ✅ **Medicine Stock Management**
  - Add, update, and delete medicines
  - Input and validate supplier details
  - Auto-generate `medicine_id`
  - Alerts for expired and low stock medicines

- 💵 **Sales & Billing System**
  - Generate bills with multiple items
  - Select payment method (Cash/Card/UPI)
  - Auto-generated `bill_id`
  - Generate **PDF invoices** with medical store name and details

- 📊 **Dashboard**
  - View monthly sales summary
  - Low stock alerts with option to generate a **PDF report**

- 📈 **Reports**
  - View detailed **sales reports**
  - Download sales reports as **PDF**

- 👤 **User Management**
  - Admin can manage users (add/update/delete)
  - All users have equal access to features

---

## 🗂️ Technologies Used

- **Frontend**: HTML, CSS, Bootstrap, JavaScript
- **Backend**: PHP
- **Database**: MySQL
- **PDF Generation**: HTML to PDF using PHP libraries (e.g., TCPDF / FPDF)

## 🧰 How to Run the Project

1. **Clone the Repository**
   ```bash
   git clone https://github.com/your-username/pharmastock-manager.git
2. **Set Up Local Server**

   * Use **XAMPP** or **WAMP** for local development.
   * Place the project folder inside the `htdocs` directory.

3. **Import the Database**

   * Create a MySQL database (e.g., `pharmastock_database`).
   * Import the provided `.sql` file using **phpMyAdmin**.

4. **Configure Database Connection**

   * Update your database credentials in the PHP config file (usually `db.php` or `config.php`).

5. **Run the Project**

   * Visit `http://localhost/pharmastock-manager` in your browser.
  
📄 License
This project is created as part of an academic course. You are free to use or adapt it for learning purposes.

✨ Author
Aliya Banu

