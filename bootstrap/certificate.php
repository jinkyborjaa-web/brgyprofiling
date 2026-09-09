<?php
session_start();
if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../admin-login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Certificate Information</title>
</head>
<body>

<h1>Certificate Page</h1>
<a href="dashboard.php">Back to Dashboard</a>

</body>
</html>
