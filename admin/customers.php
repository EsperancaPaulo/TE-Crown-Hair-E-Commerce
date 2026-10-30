<?php
require_once '../includes/auth.php'; requireAdmin(); require_once '../includes/db.php'; require_once '../includes/order-view.php'; require_once '../includes/admin-layout.php';
if ($_SERVER['REQUEST_METHOD']==='POST') { checkCsrf();$id=(int)($_POST['id']??0);$s=$conn->prepare("UPDATE users SET is_active=1-is_active WHERE id=? AND role='customer'");$s->bind_param('i',$id);$s->execute();header('Location: customers.php');exit; }
adminStart('Customers'); ?>
<div class="admin-panel"><label for="customerSearch">Search customers</label><input id="customerSearch" type="search" class="form-control"><div class="admin-table-wrapper"><table class="admin-table"><thead><tr><th>Name</th><th>Email</th><th>Registered</th><th>Verification</th><th>Access</th><th>Orders</th><th>Action</th></tr></thead><tbody>
<?php foreach ($conn->query("SELECT u.*, (SELECT COUNT(*) FROM orders o WHERE o.user_id=u.id) order_count FROM users u WHERE role='customer' ORDER BY id DESC") as $u): ?>
<tr><td><?php echo e($u['name']); ?></td><td><?php echo e($u['email']); ?></td><td><?php echo e($u['created_at']); ?></td><td><?php echo $u['email_verified']?'Verified':'Pending'; ?></td><td><?php echo $u['is_active']?'Active':'Inactive'; ?></td><td><?php echo (int)$u['order_count']; ?></td><td><form method="post"><?php echo csrfField(); ?><input type="hidden" name="id" value="<?php echo $u['id']; ?>"><button type="submit" class="admin-edit-button"><?php echo $u['is_active']?'Deactivate':'Activate'; ?></button></form></td></tr>
<?php endforeach; ?></tbody></table></div></div>
<script>document.getElementById('customerSearch').addEventListener('input',function(){for(const row of document.querySelectorAll('tbody tr'))row.hidden=!row.textContent.toLowerCase().includes(this.value.toLowerCase());});</script>
<?php adminEnd(); ?>
