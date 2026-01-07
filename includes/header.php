<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบแจ้งซ่อมออนไลน์</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    
    <style>
        body {
        font-family: 'Kanit', sans-serif;
        background: linear-gradient(to bottom right, #F9F8F6, #D9CFC7);
        }
        :root {
            --bs-primary: #B0E0E6; 
            --bs-primary-rgb: 176, 224, 230;
            --bs-link-color: #0d6efd;
        }
        .btn-primary {
            background-color: #007BFF; 
            border-color: #007BFF;
            color: #FFFFFF;
        }
        .btn {
            border-radius: 0.5rem; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.1); 
        }
        .card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .navbar-brand {
            font-weight: 500;
        }
        
    .btn,
    .nav-pills .nav-link {
        transition: color .15s ease-in-out,
                    background-color .15s ease-in-out,
                    border-color .15s ease-in-out,
                    box-shadow .15s ease-in-out,
                    transform 0.1s ease-out; 
    }

    .btn:active,
    .nav-pills .nav-link:active {
        transform: scale(0.98);
    }
</style>
</head>
<body class="d-flex flex-column min-vh-100">

    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <img src="logo/logo.png" alt="โลโก้" height="45" class="d-inline-block align-text-top me-2">
            ระบบแจ้งซ่อมอุปกรณ์ IT
        </a>
        <a href="admin/" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-person-circle"></i> สำหรับผู้ดูเเล
        </a>
    </div>
</nav>



    <div class="container mt-4 mb-5">