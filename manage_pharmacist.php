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

// Add Pharmacist (With User ID Check)

if (isset($_POST['add_pharmacist'])) {
    
    $userid = trim($_POST['userid']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Check if User ID Exists
    $check_query = $conn->prepare("SELECT userid FROM users WHERE userid = ?");
    $check_query->bind_param("s", $userid);
    $check_query->execute();
    $check_query->store_result();
    $user_exists = $check_query->num_rows > 0;
    $check_query->close();

    // Check if Email Exists
    $check_email_query = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $check_email_query->bind_param("s", $email);
    $check_email_query->execute();
    $check_email_query->store_result();
    $email_exists = $check_email_query->num_rows > 0;
    $check_email_query->close();

    if ($user_exists) {
        echo "<script>alert('User ID already exists! Please try another.');window.location.href='manage_pharmacist.php';</script>";
    } elseif ($email_exists) {
        echo "<script>alert('Email already exists! Please use a different email.');window.location.href='manage_pharmacist.php';</script>";
    } else {
        // Insert New Pharmacist
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO users (userid, name, email, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $userid, $name, $email, $hashed_password);

        if ($stmt->execute()) {
            echo "<script>alert('Pharmacist added successfully!');window.location.href='manage_pharmacist.php';</script>";
        } else {
            echo "<script>alert('Error adding pharmacist! Please try again.');window.location.href='manage_pharmacist.php';</script>";
        }

        $stmt->close();
    }
    // 🔹 Redirect to clear POST data (Prevents alerts on refresh)
    echo "<script>window.location.href = 'manage_pharmacist.php';</script>";
    exit(); // Stop further script execution
}


// Update Pharmacist
if (isset($_POST['update_pharmacist'])) {
    $userid = $_POST['userid'];
    $name = $_POST['name'];
    $email = $_POST['email'];

    // Check if Email Already Exists (excluding the current user)
    $check_email_query = $conn->prepare("SELECT email FROM users WHERE email = ? AND userid != ?");
    $check_email_query->bind_param("ss", $email, $userid);
    $check_email_query->execute();
    $check_email_query->store_result();

    if ($check_email_query->num_rows > 0) {
        echo "<script>alert('Email already exists! Please use a different email.');window.location.href='manage_pharmacist.php';</script>";
    } else {
        // Proceed with Updating the Pharmacist
        $stmt = $conn->prepare("UPDATE users SET name=?, email=? WHERE userid=?");
        $stmt->bind_param("sss", $name, $email, $userid);

        if ($stmt->execute()) {
            echo "<script>alert('Pharmacist updated successfully!');window.location.href='manage_pharmacist.php';</script>";
        } else {
            echo "<script>alert('Error updating pharmacist!');</script>";
        }
        $stmt->close();
    }
    // 🔹 Redirect to clear POST data (Prevents alerts on refresh)
    echo "<script>window.location.href = 'manage_pharmacist.php';window.location.href='manage_pharmacist.php';</script>";
    exit(); // Stop further script execution

    $check_email_query->close(); // Close query
}


// Delete Pharmacist
if (isset($_POST['delete_pharmacist'])) {
    $userid = $_POST['userid'];

    $stmt = $conn->prepare("DELETE FROM users WHERE userid=?");
    $stmt->bind_param("s", $userid);

    if ($stmt->execute()) {
        echo "<script>alert('Pharmacist deleted successfully!');window.location.href='manage_pharmacist.php';</script>";
    } else {
        echo "<script>alert('Error deleting pharmacist!');window.location.href='manage_pharmacist.php';</script>";
    }
    $stmt->close();
}

// Fetch All Pharmacists
$result = $conn->query("SELECT * FROM users");

// Handle AJAX Request for Checking User ID
if (isset($_POST['check_userid'])) {
    $userid = $_POST['userid'];
    
    $stmt = $conn->prepare("SELECT userid FROM users WHERE userid = ?");
    $stmt->bind_param("s", $userid);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "exists"; // JavaScript will detect this and show alert
    } else {
        echo "available";
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Pharmacists</title>
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
        h2, h3 {
            color: #4CAF50;
            text-align: center;
            margin-bottom: 30px;
        }
        .form-control {
            margin-bottom: 15px;
        }
        .btn-primary {
            background-color: #4CAF50;
            border: none;
            margin-bottom: 20px;
        }
        .btn-primary:hover {
            background-color: #45a049;
        }
        table {
            width: 100%;
            margin-top: 20px;
            background: white;
            border-collapse: collapse;
        }
        th {
            background-color: #4CAF50;
            color: white;
            padding: 12px;
        }
        td {
            padding: 12px;
            border: 1px solid #ddd;
        }
        .btn-update {
            background-color: #ffc107;
            color: black;
            border: none;
            padding: 5px 10px;
            margin-right: 5px;
        }
        .btn-delete {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
        }
        .pharmacist-form {
            background: #E8F5E9;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
         /* Added styles for slide menu */
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
    </style>
    <script>
        function checkUserId() {
            var userid = document.getElementById("userid").value;
            if (userid.length > 0) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "", true);
                xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        if (xhr.responseText == "exists") {
                            alert("User ID already exists! Please try anothe.");
                            document.getElementById("userid").value = "";
                        }
                    }
                };
                xhr.send("check_userid=1&userid=" + userid);
            }
        }


    </script>
</head>
<body><div class="slide-menu">
        <div class="slide-menu-items">
            <a href="dashboard.php" class="slide-menu-item">Dashboard</a>
            <a href="add_medicine.php" class="slide-menu-item">Add medicine</a>
            <a href="update_medicine.php" class="slide-menu-item">Update Medicines</a>
            <a href="generate_bill.php" class="slide-menu-item">Bill</a>
            <a href="sales.php" class="slide-menu-item">Reports</a>
            <a href="expired_medicines.php" class="slide-menu-item">Expired medicines</a>
            <a href="logout.php" class="slide-menu-item">Logout</a>
        </div>
    </div>

    <div class="menu-toggle">
        <div class="menu-line"></div>
        <div class="menu-line"></div>
        <div class="menu-line"></div>
    </div>
    <div class="container">
        <h2>Manage Pharmacists</h2>
        
        <button class="btn btn-primary" onclick="window.location.href='dashboard.php'">Go Back to Dashboard</button>

        <!-- Add Pharmacist Form -->
        <div class="pharmacist-form">
            <h3>Add New Pharmacist</h3>
            <form method="post" class="row g-3">
                <div class="col-md-6">
                    <label for="userid" class="form-label">User ID:</label>
                    <input type="text" class="form-control" id="userid" name="userid" placeholder="Enter User ID" required onblur="checkUserId()">
                </div>
                <div class="col-md-6">
                    <label for="name" class="form-label">Name:</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Enter Name" required pattern="[A-Za-z\s]+" 
                    title="Only alphabets are allowed">
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email" required>
                </div>
                <div class="col-md-6">
                    <label for="password" class="form-label">Password:</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter Password" required>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary" name="add_pharmacist">Add Pharmacist</button>
                </div>
            </form>
        </div>

        <!-- Pharmacists Table -->
        <h3>Pharmacist List</h3>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['userid']; ?></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td>
                            <!-- Update Pharmacist -->
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="userid" value="<?php echo $row['userid']; ?>">
                                <input type="text" class="form-control d-inline-block w-auto" name="name" value="<?php echo $row['name']; ?>" required pattern="[A-Za-z\s]+" 
                                title="Only alphabets are allowed">
                                <input type="email" class="form-control d-inline-block w-auto" name="email" value="<?php echo $row['email']; ?>" >
                                <button type="submit" class="btn btn-update" name="update_pharmacist">Update</button>
                            </form>

                            <!-- Delete Pharmacist -->
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="userid" value="<?php echo $row['userid']; ?>">
                                <button type="submit" class="btn btn-delete" name="delete_pharmacist" onclick="return confirm('Are you sure you want to delete this pharmacist?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        var menuToggle = document.querySelector(".menu-toggle");
        var slideMenu = document.querySelector(".slide-menu");

        menuToggle.addEventListener("click", function () {
            slideMenu.classList.toggle("active"); // Toggle sidebar visibility
        });

        // Optional: Close menu if user clicks outside
        document.addEventListener("click", function (event) {
            if (!menuToggle.contains(event.target) && !slideMenu.contains(event.target)) {
                slideMenu.classList.remove("active");
            }
        });
    });
</script>

</body>
</html>

<?php $conn->close(); ?>
