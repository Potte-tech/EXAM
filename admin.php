<?php
require 'db.php';
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

// Setup
$perPage = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$startAt = ($page - 1) * $perPage;

$statusFilter = $_GET['status'] ?? '';
$searchRegno = $_GET['search'] ?? '';

$params = [];
$where = "";

if ($statusFilter && in_array($statusFilter, ['pending', 'under review', 'resolved'])) {
    $where .= " AND a.status = ?";
    $params[] = $statusFilter;
}

if ($searchRegno) {
    $where .= " AND s.regno LIKE ?";
    $params[] = "%$searchRegno%";
}

$sql = "SELECT a.id, s.name AS student_name, s.regno, m.module_name, a.reason, a.status, mk.mark
        FROM appeals a 
        JOIN students s ON a.student_regno = s.regno 
        JOIN modules m ON a.module_id = m.id 
        LEFT JOIN marks mk ON mk.student_regno = s.regno AND mk.module_id = m.id
        WHERE 1 $where
        LIMIT $startAt, $perPage";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$appeals = $stmt->fetchAll();

// Total count for pagination
$countStmt = $pdo->prepare("SELECT COUNT(*) 
                            FROM appeals a 
                            JOIN students s ON a.student_regno = s.regno 
                            WHERE 1 $where");
$countStmt->execute($params);
$totalAppeals = $countStmt->fetchColumn();
$totalPages = ceil($totalAppeals / $perPage);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Appeals</title>
    <link rel="stylesheet" href="style.css"> <!-- Link your external CSS file -->
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f6f9;
        }

        header {
            background-color: #1590c1;
            color: white;
            padding: 20px;
            border-radius: 0 0 10px 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            margin: 0 auto;
            font-size: 22px;
        }

        .logout-btn {
            background-color: #e74c3c;
            color: white;
            padding: 8px 14px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .logout-btn:hover {
            background-color: #c0392b;
        }

        .welcome {
            margin: 20px;
            font-size: 16px;
            color: #333;
        }

        .filter-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 20px;
            padding: 10px 0;
        }

        .filter-bar input[type="text"],
        .filter-bar select {
            padding: 10px;
            font-size: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
            flex: 1;
            min-width: 180px;
        }

        .filter-bar button {
            background-color: #28a745;
            color: white;
            padding: 10px 16px;
            font-size: 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .filter-bar button:hover {
            background-color: #218838;
        }

        table {
            width: 96%;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        th, td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #e1e4e8;
        }

        th {
            background-color: #f1f4f8;
            color: #2c3e50;
            font-weight: 600;
        }

        tr:nth-child(even) {
            background-color: #fafafa;
        }

        td form {
            display: flex;
            gap: 8px;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            text-transform: capitalize;
            display: inline-block;
        }

        .status-badge.pending {
            background-color: #f1c40f;
            color: #fff;
        }

        .status-badge.under-review {
            background-color: #3498db;
            color: #fff;
        }

        .status-badge.resolved {
            background-color: #2ecc71;
            color: #fff;
        }

        .pagination {
            margin: 30px;
            text-align: center;
        }

        .pagination a {
            display: inline-block;
            padding: 8px 14px;
            margin: 0 4px;
            background-color: #ecf0f1;
            color: #2c3e50;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.2s;
        }

        .pagination a:hover,
        .pagination a.active {
            background-color: #3498db;
            color: white;
        }
    </style>
</head>
<body>

<header>
    <h4 class="welcome">Welcome, <?= $_SESSION['admin'] ?></h4>
    <h1>Student Appeals Management System</h1>
    <a href="logout.php" class="logout-btn">Logout</a>
</header>

<form method="get" class="filter-bar">
    <input type="text" name="search" placeholder="Search by RegNo" value="<?= htmlspecialchars($searchRegno) ?>">
    <select name="status">
        <option value="">-- Status Filter --</option>
        <option value="pending" <?= $statusFilter == 'pending' ? 'selected' : '' ?>>Pending</option>
        <option value="under review" <?= $statusFilter == 'under review' ? 'selected' : '' ?>>Under Review</option>
        <option value="resolved" <?= $statusFilter == 'resolved' ? 'selected' : '' ?>>Resolved</option>
    </select>
    <button type="submit">Filter</button>
</form>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Student</th>
            <th>Module</th>
            <th>Marks</th>
            <th>Reason</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($appeals as $i => $a): ?>
        <tr>
            <td><?= $i + 1 + $startAt ?></td>
            <td><?= htmlspecialchars($a['student_name']) ?> (<?= $a['regno'] ?>)</td>
            <td><?= htmlspecialchars($a['module_name']) ?></td>
            <td><?= is_numeric($a['mark']) ? $a['mark'] : 'N/A' ?></td>
            <td><?= htmlspecialchars($a['reason']) ?></td>
            <td><span class="status-badge <?= str_replace(' ', '-', strtolower($a['status'])) ?>">
                <?= ucfirst($a['status']) ?>
            </span></td>
            <td>
                <form method="post" action="update_status.php">
                    <input type="hidden" name="id" value="<?= $a['id'] ?>">
                    <select name="status">
                        <option <?= $a['status'] == 'pending' ? 'selected' : '' ?>>pending</option>
                        <option <?= $a['status'] == 'under review' ? 'selected' : '' ?>>under review</option>
                        <option <?= $a['status'] == 'resolved' ? 'selected' : '' ?>>resolved</option>
                    </select>
                    <button type="submit">Update</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="pagination">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a class="<?= $i == $currentPage ? 'active' : '' ?>" href="?page=<?= $i ?>&status=<?= urlencode($statusFilter) ?>&search=<?= urlencode($searchRegno) ?>"><?= $i ?></a>
    <?php endfor; ?>
</div>

</body>
</html>

