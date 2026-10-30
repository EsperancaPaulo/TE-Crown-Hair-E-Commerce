<?php
require_once '../includes/auth.php'; requireAdmin(); require_once '../includes/db.php'; require_once '../includes/order-view.php'; require_once '../includes/admin-layout.php';
adminStart('Dashboard');
$queries=['Customers'=>"SELECT COUNT(*) n FROM users WHERE role='customer'",'Orders'=>'SELECT COUNT(*) n FROM orders','Active products'=>"SELECT COUNT(*) n FROM products WHERE status='active'",'Paid revenue'=>"SELECT COALESCE(SUM(total_amount),0) n FROM orders WHERE payment_status='paid' AND order_status!='cancelled' AND id NOT IN (SELECT order_id FROM demo_payments)",'Low stock'=>"SELECT COUNT(*) n FROM products WHERE stock<=5 AND status='active'"];
echo '<div class="admin-stat-grid">';foreach ($queries as $label=>$sql) { $n=$conn->query($sql)->fetch_assoc()['n']; echo '<div class="admin-stat-card"><div><span>'.e($label).'</span><strong>'.($label==='Paid revenue'?'R'.number_format($n,2):$n).'</strong></div></div>'; } echo '</div><div class="admin-panel"><h2>Recent orders</h2>';
foreach ($conn->query('SELECT id,total_amount,order_status,payment_status FROM orders ORDER BY id DESC LIMIT 10') as $o) echo '<p><a href="orders.php?id='.$o['id'].'">#TC'.$o['id'].'</a> — R'.number_format($o['total_amount'],2).' — '.e($o['order_status']).' / '.e($o['payment_status']).(isDemoOrder($conn,$o['id'])?' (demo)':'').'</p>';
echo '</div>';adminEnd();
