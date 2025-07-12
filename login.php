<?php
// Database Connection
$host = "localhost";
$username = "root";  // Default in XAMPP
$password = "";      // Default in XAMPP (empty)
$database = "pharmastock"; 

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process Login (When form is submitted)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userid = trim($_POST['userid']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validate user input
    if (empty($userid) || empty($name) || empty($email) || empty($password)) {
        echo "<script>alert('Please enter all fields: User ID, Name, Email, and Password!');</script>";
        exit();
    }

    // Prepare SQL statement to check all credentials
    $stmt = $conn->prepare("SELECT password FROM users WHERE userid = ? AND name = ? AND email = ?");
    $stmt->bind_param("sss", $userid, $name, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // If user exists, verify password
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            session_start();
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['userid'] = $userid;
            $_SESSION['name'] = $name;
            $_SESSION['email'] = $email;

            header("Location: dashboard.php");
            exit();
        } else {
            echo "<script>alert('Invalid credentials! Please check your password.');</script>";
        }
    } else {
        echo "<script>alert('No matching user found! Please check your User ID, Name, and Email.');</script>";
    }

    $stmt->close();
}

// Close database connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Login Page</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        
        @keyframes scaleIn {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background: url('') no-repeat center center fixed;
            background-size: cover;
            background: #4CAF50;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            animation: 
                fadeIn 1s ease-out,
                scaleIn 0.7s cubic-bezier(0.17, 0.67, 0.83, 0.67),
                gradientShift 15s ease infinite;
            overflow: hidden;
            position: relative;
        }
        .container {
            display: flex;
            background: white;
            width: 900px;
            border-radius: 10px;
            box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            animation: slideIn 1s ease-out;
        }
        .login-section {
            flex: 1;
            padding: 40px;
        }
        .login-section h2 {
            margin-bottom: 20px;
            animation: fadeIn 1s ease-out 0.3s backwards;
        }
        .login-section input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            animation: fadeIn 0.5s ease-out backwards;
        }
        .login-section input:nth-child(2) { animation-delay: 0.4s; }
        .login-section input:nth-child(3) { animation-delay: 0.5s; }
        .login-section input:nth-child(4) { animation-delay: 0.6s; }
        .login-section input:nth-child(5) { animation-delay: 0.7s; }

        .login-section input:focus {
            transform: scale(1.02);
            box-shadow: 0 0 10px rgba(0,123,255,0.2);
        }

        .login-section button {
            width: 100%;
            background: #007bff;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            animation: fadeIn 0.5s ease-out 0.8s backwards;
        }
        .login-section button:hover {
            background: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .image-section {
            flex: 1;
            background: url('https://www.millihealth.com/wp-content/uploads/2019/09/MIP_FollowUp_Illistration.png') no-repeat center;
            background-size: cover;
            animation: fadeIn 1s ease-out 0.5s backwards;
        }
        .nav {
            position: absolute;
            top: 20px;
            right: 40px;
        }
        .nav a {
            text-decoration: none;
            color: #333;
            margin: 0 10px;
            transition: color 0.3s ease;
            animation: fadeIn 0.5s ease-out 1s backwards;
        }
        .nav a:hover {
            color: #007bff;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
<img src="logofinal.png" alt="Logo" style="position: absolute; top: 10px; left: 10px; height: 90px; width: auto;">
    
    <div class="container">
        <div class="login-section">
            <h2>Login</h2>
            <form action="" method="POST">
                <input type="text" name="userid" placeholder="Id" required>
                <input type="text" name="name" placeholder="Name" required pattern="[A-Za-z\s]+" title="Only letters are allowed">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
        </div>
        <div class="image-section"></div>
    </div>
</body>
</html>
