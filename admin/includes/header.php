<?php
// ตรวจสอบ session admin - ถ้าไม่ใช่ admin ให้กลับไปหน้า login
if(!isset($_SESSION['admin_id'])){
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Admin Dashboard'; ?> - MindVault</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        :root {
            --admin-primary: #667eea;
            --admin-secondary: #764ba2;
            --admin-accent: #f093fb;
        }
        
        .admin-header {
            background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-secondary) 100%);
            padding: 1rem 2rem;
            color: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .admin-header-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .admin-logo {
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .admin-nav {
            display: flex;
            gap: 2rem;
            align-items: center;
        }
        
        .admin-nav a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .admin-nav a:hover,
        .admin-nav a.active {
            background: rgba(255,255,255,0.2);
        }
        
        .admin-user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .admin-user-name {
            font-weight: 600;
        }
        
        .logout-btn {
            background: rgba(255,255,255,0.2);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            color: white;
            transition: all 0.3s;
        }
        
        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .admin-content {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: var(--card);
            padding: 1.5rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border-left: 4px solid var(--admin-primary);
        }
        
        .stat-card h3 {
            font-size: 0.9rem;
            color: var(--muted);
            margin-bottom: 0.5rem;
        }
        
        .stat-card .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--admin-primary);
            margin-bottom: 0.5rem;
        }
        
        .stat-card .stat-label {
            font-size: 0.85rem;
            color: var(--muted);
        }
        
        .data-table {
            background: var(--card);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
        }
        
        .data-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th,
        .data-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .data-table th {
            background: var(--admin-primary);
            color: white;
            font-weight: 600;
        }
        
        .data-table tr:hover {
            background: rgba(102, 126, 234, 0.1);
        }
        
        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .badge-admin {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        
        .badge-user {
            background: rgba(100, 255, 218, 0.2);
            color: var(--accent);
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="admin-header-content">
            <div class="admin-logo">
                🔐 MindVault Admin
            </div>
            
            <nav class="admin-nav">
                <a href="index.php" class="<?php echo ($current_page ?? '') == 'dashboard' ? 'active' : ''; ?>">
                    📊 Dashboard
                </a>
                <a href="manage_users.php" class="<?php echo ($current_page ?? '') == 'users' ? 'active' : ''; ?>">
                    👥 จัดการผู้ใช้
                </a>
                <a href="analytics.php" class="<?php echo ($current_page ?? '') == 'analytics' ? 'active' : ''; ?>">
                    📈 Analytics
                </a>
            </nav>
            
            <div class="admin-user-info">
                <span class="admin-user-name">👤 <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></span>
                <a href="logout.php" class="logout-btn">ออกจากระบบ</a>
            </div>
        </div>
    </div>
    
    <div class="admin-content">
