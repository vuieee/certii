<?php
session_start();
require '../config/db.php';
require_once '../includes/functions.php';
requireRole(['Manager', 'Admin']);

$stmt = $pdo->query("
    SELECT e.Name, e.Department, c.Title, cert.ExpirationDate
    FROM EMPLOYEE e
    JOIN CERTIFICATION cert ON cert.EmployeeID = e.EmployeeID
    JOIN COURSE c ON cert.CourseID = c.CourseID
    WHERE e.Role = 'Employee'
    ORDER BY cert.ExpirationDate ASC
");
$rows = $stmt->fetchAll();

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="compliance-report.csv"');

    $out = fopen('php://output', 'w');
    fputcsv($out, ['Employee', 'Department', 'Course', 'Expiration Date', 'Status']);
    foreach ($rows as $row) {
        $status = certStatus($row['ExpirationDate']);
        fputcsv($out, [$row['Name'], $row['Department'], $row['Title'], $row['ExpirationDate'], $status['label']]);
    }
    fclose($out);
    exit();
}

require '../includes/header.php';
?>

<div class="eyebrow">Manager Workspace</div>
<h1>Team Compliance Report</h1>
<p class="page-subtitle">Every certification on record for your team, soonest expiration first.</p>

<div class="card">
    <div class="card-row">
        <h2>Certifications</h2>
        <a href="?export=csv" class="btn btn-sm btn-secondary">Export CSV</a>
    </div>
    <table>
        <tr>
            <th>Employee</th>
            <th>Department</th>
            <th>Course</th>
            <th>Expiration Date</th>
            <th>Status</th>
        </tr>
        <?php if (empty($rows)): ?>
            <tr><td colspan="5" class="empty-state">No certifications recorded yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($rows as $row): $status = certStatus($row['ExpirationDate']); ?>
        <tr>
            <td><?= htmlspecialchars($row['Name']) ?></td>
            <td><?= htmlspecialchars($row['Department'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($row['Title']) ?></td>
            <td><?= htmlspecialchars($row['ExpirationDate']) ?></td>
            <td><span class="badge <?= $status['class'] ?>"><?= $status['label'] ?></span></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php require '../includes/footer.php'; ?>
