<?php
// dashboard/includes/header.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Dashboard'; ?> | Samaru Waste</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <style>
        :root {
            --primary-green: #2e7d32;
            --secondary-green: #e8f5e9;
            --text-dark: #1f2937;
            --text-muted: #6b7280;
            --sidebar-width: 260px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            color: var(--text-dark);
            overflow-x: hidden;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: #ffffff;
            border-right: 1px solid #e5e7eb;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            padding: 1.5rem;
            transition: transform 0.3s ease;
        }

        .brand-logo {
            font-family: 'Outfit', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-green);
            margin-bottom: 2.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--text-muted);
            padding: 0.85rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s ease;
            margin-bottom: 0.25rem;
        }

        .nav-link:hover, .nav-link.active {
            background-color: var(--secondary-green);
            color: var(--primary-green);
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem 2.5rem;
            min-height: 100vh;
        }

        /* Common Elements */
        .page-header { margin-bottom: 2rem; display: flex; align-items: center; justify-content: space-between; }
        .page-title { font-family: 'Outfit', sans-serif; font-weight: 700; color: #111827; margin: 0; font-size: 1.75rem; }
        
        .content-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #f0f0f0;
            margin-bottom: 1.5rem;
        }

        .table th {
            font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em;
            color: var(--text-muted); padding-bottom: 1rem; border-bottom: 2px solid #f3f4f6;
        }
        .table td {
            vertical-align: middle; padding: 1rem 0;
            border-bottom: 1px solid #f3f4f6; color: #374151; font-size: 0.95rem;
        }

        .form-control { padding: 0.75rem; border-radius: 8px; border: 1px solid #d1d5db; }
        .form-control:focus { border-color: var(--primary-green); box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.1); }
        .btn-primary { background-color: var(--primary-green); border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 500; }
        .btn-primary:hover { background-color: #1b5e20; }

        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); width: 280px; }
            .sidebar.active { transform: translateX(0); }
            .main-content { margin-left: 0; padding: 1.5rem; }
            .mobile-toggle { display: block !important; cursor: pointer; font-size: 1.5rem; color: var(--primary-green); margin-right: 15px; }
        }
        .mobile-toggle { display: none; }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="brand-logo">
            <i class="bi bi-recycle"></i> Samaru Waste
        </div>
        
        <div class="nav flex-column">
            <a href="index.php" class="nav-link <?php echo ($activePage=='overview') ? 'active' : ''; ?>"><i class="bi bi-grid-1x2"></i> Overview</a>
            <a href="collectors.php" class="nav-link <?php echo ($activePage=='collectors') ? 'active' : ''; ?>"><i class="bi bi-people"></i> Waste Collectors</a>
            <a href="community_collectors.php" class="nav-link <?php echo ($activePage=='community') ? 'active' : ''; ?>"><i class="bi bi-building"></i> Community Mgrs</a>
            <a href="records.php" class="nav-link <?php echo ($activePage=='records') ? 'active' : ''; ?>"><i class="bi bi-file-earmark-text"></i> Waste Records</a>
            <a href="benefits.php" class="nav-link <?php echo ($activePage=='benefits') ? 'active' : ''; ?>"><i class="bi bi-graph-up"></i> Benefits & Sales</a>
        </div>

        <div class="mt-auto">
            <a href="../index.php" class="nav-link text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </div>
    </nav>

    <div class="main-content">
        <!-- Mobile Toggle (Visible only on small screens) -->
        <div class="d-lg-none d-flex align-items-center mb-4">
            <i class="bi bi-list mobile-toggle" id="menuToggle"></i>
            <span class="fw-bold h5 m-0">Menu</span>
        </div>
