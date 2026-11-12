<?php
require_once '../includes/auth.php'; requireAdmin(); require_once '../includes/db.php'; require_once '../includes/order-view.php'; require_once '../includes/admin-layout.php';
adminStart('Dashboard');
$queries = [
    'Customers' => "SELECT COUNT(*) n FROM users WHERE role='customer'",
    'Orders' => 'SELECT COUNT(*) n FROM orders',
    'Active products' => "SELECT COUNT(*) n FROM products WHERE status='active'",
    'Open enquiries' => "SELECT COUNT(*) n FROM enquiries WHERE status IN ('open','in_progress')",
    'Low stock' => "SELECT COUNT(*) n FROM products WHERE stock<=5 AND status='active'",
];
$payments = $conn->query("
    SELECT COALESCE(SUM(CASE WHEN d.order_id IS NULL THEN o.total_amount END), 0) AS revenue,
           COALESCE(SUM(CASE WHEN d.order_id IS NOT NULL THEN o.total_amount END), 0) AS demo_total
    FROM orders o
    LEFT JOIN demo_payments d ON d.order_id = o.id
    WHERE o.payment_status = 'paid' AND o.order_status <> 'cancelled'
")->fetch_assoc();

echo '<div class="admin-stat-grid">';
foreach ($queries as $label => $sql) {
    $count = (int)$conn->query($sql)->fetch_assoc()['n'];
    echo '<div class="admin-stat-card"><div><span>'.e($label).'</span><strong>'.$count.'</strong></div></div>';
}
foreach ([
    ['Paid revenue', $payments['revenue'], 'Actual payments received'],
    ['Demo payments approved', $payments['demo_total'], 'Test orders only'],
] as [$label, $total, $description]) {
    echo '<div class="admin-stat-card"><div><span>'.e($label).'</span><strong>R'
        .number_format((float)$total, 2).'</strong><small class="admin-form-hint">'.e($description).'</small></div></div>';
}
echo '</div><div class="admin-panel"><h2>Recent orders</h2>';
$recentOrders=$conn->query('SELECT id,total_amount,order_status,payment_status FROM orders ORDER BY id DESC LIMIT 10');
if (!$recentOrders->num_rows) echo '<p class="admin-form-hint">No orders have been placed yet.</p>';
foreach ($recentOrders as $o) echo '<p><a class="admin-text-link" href="orders.php?id='.$o['id'].'">#TC'.$o['id'].'</a> — R'.number_format($o['total_amount'],2).' — '.e($o['order_status']).' / '.e($o['payment_status']).(isDemoOrder($conn,$o['id'])?' (demo)':'').'</p>';
echo '</div>';adminEnd();
