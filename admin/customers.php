<?php
require_once '../includes/auth.php'; requireAdmin(); require_once '../includes/db.php'; require_once '../includes/order-view.php'; require_once '../includes/admin-layout.php';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    checkCsrf();$id=filter_var($_POST['id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
    if (!$id) {http_response_code(422);exit('Choose a valid customer.');}
    $stmt=$conn->prepare("UPDATE users SET is_active=1-is_active WHERE id=? AND role='customer'");$stmt->bind_param('i',$id);
    if (!$stmt->execute()) {http_response_code(503);exit('Customer access could not be updated.');}
    header('Location: customers.php?updated=1',true,303);exit;
}
$customers=$conn->query("SELECT u.id,u.name,u.email,u.created_at,u.email_verified,u.is_active,(SELECT COUNT(*) FROM orders o WHERE o.user_id=u.id) order_count FROM users u WHERE u.role='customer' ORDER BY u.id DESC")->fetch_all(MYSQLI_ASSOC);
$active=array_sum(array_column($customers,'is_active'));
adminStart('Customers'); ?>
<p class="admin-page-intro">Registered customer accounts from this shop’s connected database.</p>
<div class="admin-summary-strip"><div><span>Registered customers</span><strong><?php echo count($customers); ?></strong></div><div><span>Active accounts</span><strong><?php echo $active; ?></strong></div></div>
<?php if (isset($_GET['updated'])): ?><p class="enquiry-notice" role="status">Customer access updated.</p><?php endif; ?>
<section class="admin-panel"><div class="admin-customer-search-row"><div><p class="account-eyebrow">ACCOUNT DIRECTORY</p><h2>Customer accounts</h2></div><div><label for="customerSearch">Find a customer</label><input id="customerSearch" type="search" class="form-control" placeholder="Search name or email"></div></div>
<div class="admin-table-wrapper" tabindex="0" aria-label="Data table; scroll horizontally to view all columns on small screens"><table class="admin-table"><thead><tr><th>Customer</th><th>Registered</th><th>Email verification</th><th>Access</th><th>Orders</th><th>Action</th></tr></thead><tbody>
<?php if (!$customers): ?><tr><td colspan="6" class="admin-empty-cell">No customers have registered yet. New customer accounts will appear here automatically.</td></tr><?php endif; ?>
<?php foreach ($customers as $customer): ?><tr class="customer-data-row"><td><strong><?php echo e($customer['name']); ?></strong><span class="admin-cell-note"><?php echo e($customer['email']); ?></span></td><td><?php echo e((new DateTimeImmutable($customer['created_at'],new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Africa/Johannesburg'))->format('d M Y')); ?></td><td><span class="account-status <?php echo $customer['email_verified']?'status-completed':'status-pending'; ?>"><?php echo $customer['email_verified']?'Verified':'Pending'; ?></span></td><td><span class="account-status <?php echo $customer['is_active']?'status-completed':'status-cancelled'; ?>"><?php echo $customer['is_active']?'Active':'Inactive'; ?></span></td><td><?php echo (int)$customer['order_count']; ?></td><td><form method="post"><?php echo csrfField(); ?><input type="hidden" name="id" value="<?php echo (int)$customer['id']; ?>"><button type="submit" class="admin-edit-button"><?php echo $customer['is_active']?'Deactivate':'Activate'; ?></button></form></td></tr><?php endforeach; ?>
<tr id="customerNoMatches" hidden><td colspan="6" class="admin-empty-cell">No customers match your search.</td></tr></tbody></table></div></section>
<script>document.getElementById('customerSearch').addEventListener('input',function(){let visible=0;for(const row of document.querySelectorAll('.customer-data-row')){row.hidden=!row.textContent.toLowerCase().includes(this.value.toLowerCase());if(!row.hidden)visible++;}document.getElementById('customerNoMatches').hidden=visible>0||!document.querySelector('.customer-data-row');});</script>
<?php adminEnd(); ?>
