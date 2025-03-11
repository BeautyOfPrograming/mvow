<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auth System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/style.css" rel="stylesheet">

</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
         
            <div class="collapse navbar-collapse">
                <div class="navbar-nav ms-auto">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="dashboard.php" class="nav-item nav-link">Dashboard</a>
                        <a href="logout.php" class="nav-item nav-link">Logout</a>
                    <?php else: ?>
                        <a href="register.php" class="nav-item nav-link">Register</a>
                        <a href="login.php" class="nav-item nav-link">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <div class="container mt-5">