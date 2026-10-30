<?php
require_once '../includes/auth.php'; requireAdmin(); require_once '../includes/db.php'; require_once '../includes/order-view.php'; require_once '../includes/admin-layout.php';
$message='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
 checkCsrf();$id=(int)($_POST['id']??0);$next=$_POST['status']??'';$payment=$_POST['payment']??'';
 $allowed=['pending'=>['pending','processing','cancelled'],'processing'=>['processing','shipped','cancelled'],'shipped'=>['shipped','completed'],'completed'=>['completed'],'cancelled'=>['cancelled']];
 try { mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);$conn->begin_transaction();
 $s=$conn->prepare('SELECT * FROM orders WHERE id=? FOR UPDATE');$s->bind_param('i',$id);$s->execute();$o=$s->get_result()->fetch_assoc();
 if ($o && isDemoOrder($conn,$id) && $payment!==$o['payment_status']) throw new RuntimeException('Use the local simulator to change demo payment status.');
 if (!$o || !in_array($next,$allowed[$o['order_status']],true) || !in_array($payment,['pending','paid','failed'],true)) throw new RuntimeException('Invalid order status transition.');
 if ($o['payment_status']==='paid' && $payment!=='paid') throw new RuntimeException('Paid orders require a separate refund process; payment cannot be reversed here.');
 if (in_array($next,['processing','shipped','completed']) && $payment!=='paid') throw new RuntimeException('Confirm EFT receipt before processing this order.');
 if ($next==='cancelled' && $o['payment_status']==='paid') throw new RuntimeException('Resolve the paid order refund before cancellation.');
 if ($o['order_status']==='cancelled' && $payment!==$o['payment_status']) throw new RuntimeException('Cancelled orders cannot accept payments.');
 if ($next==='cancelled' && $o['order_status']!=='cancelled') {
  $s=$conn->prepare('SELECT product_id,SUM(quantity) qty FROM order_items WHERE order_id=? GROUP BY product_id ORDER BY product_id');$s->bind_param('i',$id);$s->execute();
  foreach ($s->get_result() as $item) { $u=$conn->prepare('UPDATE products SET stock=stock+? WHERE id=?');$u->bind_param('ii',$item['qty'],$item['product_id']);$u->execute(); }
 }
 $s=$conn->prepare('UPDATE orders SET order_status=?,payment_status=? WHERE id=?');$s->bind_param('ssi',$next,$payment,$id);$s->execute();$conn->commit();header('Location: orders.php?id='.$id);exit;
 } catch (Throwable $e) { $conn->rollback(); $message=$e instanceof RuntimeException && !($e instanceof mysqli_sql_exception)?$e->getMessage():'The order could not be updated.'; }
}
adminStart('Orders'); if ($message) echo '<p role="alert">'.e($message).'</p>';
$selected=(int)($_GET['id']??0);$s=$conn->prepare('SELECT o.*,u.name,u.email,d.delivery FROM orders o JOIN users u ON u.id=o.user_id LEFT JOIN order_details d ON d.order_id=o.id WHERE (?=0 OR o.id=?) ORDER BY o.id DESC');$s->bind_param('ii',$selected,$selected);$s->execute();
foreach ($s->get_result() as $o): ?>
<section class="admin-panel"><h2>Order #TC<?php echo $o['id']; ?></h2><p><?php echo e($o['name'].' · '.$o['email'].' · '.$o['order_date']); ?></p>
<?php foreach (orderItems($conn,$o['id']) as $item): ?><p><?php echo e($item['name'].' '.$item['colour'].' '.$item['length']); ?> × <?php echo (int)$item['quantity']; ?> — R<?php echo number_format($item['price']*$item['quantity'],2); ?></p><?php endforeach; ?>
<?php if (isDemoOrder($conn,$o['id'])) demoHistory($conn,$o['id']); ?>
<p><strong>R<?php echo number_format($o['total_amount'],2); ?></strong> · <?php echo e(strtoupper($o['payment_method'])); ?></p>
<?php if ($o['delivery']): ?><h3>Delivery</h3><?php foreach (json_decode($o['delivery'],true) ?? [] as $key=>$value) echo '<p>'.e($key).': '.e($value).'</p>'; endif; ?>
<form method="post"><?php echo csrfField(); ?><input type="hidden" name="id" value="<?php echo $o['id']; ?>"><label>Order status <select name="status"><?php foreach (['pending','processing','shipped','completed','cancelled'] as $status) echo '<option '.($status===$o['order_status']?'selected':'').' value="'.$status.'">'.$status.'</option>'; ?></select></label><label>Payment status <select name="payment"><?php foreach (['pending','paid','failed'] as $status) echo '<option '.($status===$o['payment_status']?'selected':'').' value="'.$status.'">'.$status.'</option>'; ?></select></label><p>For live EFT, confirm receipt independently. Demo payment status is controlled by the local simulator.</p><button class="admin-edit-button" type="submit">Save order</button></form></section>
<?php endforeach; adminEnd(); ?>
