<?php
require_once '../includes/auth.php'; requireCustomer(); require_once '../includes/db.php'; require_once '../includes/order-view.php';
$id=(int)($_GET['id']??$_POST['id']??0);$message='';
function findDemoOrder($conn,$id,$lock=false) {
 $s=$conn->prepare('SELECT o.* FROM orders o JOIN demo_payments d ON d.order_id=o.id WHERE o.id=? AND o.user_id=?'.($lock?' FOR UPDATE':''));$s->bind_param('ii',$id,$_SESSION['user_id']);$s->execute();return $s->get_result()->fetch_assoc();
}
if ($_SERVER['REQUEST_METHOD']==='POST') {
 checkCsrf();
 try {
  $outcome=$_POST['outcome']??'';$key=$_POST['request_key']??'';
  if (!is_string($outcome) || !in_array($outcome,['success','decline','cancel'],true) || !is_string($key) || !preg_match('/^[a-f0-9]{64}$/',$key)) throw new InvalidArgumentException('Choose a valid demo outcome.');
  mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);$conn->begin_transaction();$order=findDemoOrder($conn,$id,true);
  if (!$order) throw new InvalidArgumentException('Demo order not found.');
  $s=$conn->prepare('SELECT id FROM demo_transactions WHERE request_key=?');$s->bind_param('s',$key);$s->execute();
  if ($s->get_result()->num_rows || $order['payment_status']==='paid' || $order['order_status']==='cancelled') { $conn->commit();header('Location: order-confirmation.php?id='.$id);exit; }
  if ($order['order_status']!=='pending') throw new InvalidArgumentException('This order cannot accept a demo payment.');
  $reference='DEMO-'.bin2hex(random_bytes(12));
  $s=$conn->prepare('INSERT INTO demo_transactions(order_id,request_key,reference,outcome) VALUES (?,?,?,?)');$s->bind_param('isss',$id,$key,$reference,$outcome);$s->execute();
  $payment=$outcome==='success'?'paid':'failed';$status=$outcome==='success'?'processing':($outcome==='cancel'?'cancelled':'pending');
  if ($outcome==='cancel') {
   $s=$conn->prepare('SELECT product_id,SUM(quantity) qty FROM order_items WHERE order_id=? GROUP BY product_id ORDER BY product_id');$s->bind_param('i',$id);$s->execute();
   foreach ($s->get_result() as $item) { $u=$conn->prepare('UPDATE products SET stock=stock+? WHERE id=?');$u->bind_param('ii',$item['qty'],$item['product_id']);$u->execute(); }
  }
  $s=$conn->prepare('UPDATE orders SET payment_status=?,order_status=? WHERE id=?');$s->bind_param('ssi',$payment,$status,$id);$s->execute();$conn->commit();
  header('Location: order-confirmation.php?id='.$id);exit;
 } catch (Throwable $e) { try {$conn->rollback();}catch(Throwable $ignored){} http_response_code($e instanceof InvalidArgumentException?422:500);$message=$e instanceof InvalidArgumentException?$e->getMessage():'The simulation could not be saved. Please retry.'; }
}
$order=findDemoOrder($conn,$id);if (!$order) {http_response_code(404);exit('Demo order not found.');}
include '../includes/header.php';include '../includes/navbar.php'; ?>
<main class="container py-5"><div class="account-card"><h1>Local payment sandbox</h1><p><strong>DEMO ONLY — no real money moves. No card or banking details are collected.</strong></p><p>Order #TC<?php echo $id; ?> — R<?php echo number_format($order['total_amount'],2); ?></p><p role="alert"><?php echo e($message); ?></p>
<?php if ($order['payment_status']!=='paid' && $order['order_status']==='pending'): ?>
<form method="post"><?php echo csrfField(); ?><input type="hidden" name="id" value="<?php echo $id; ?>"><input type="hidden" name="request_key" value="<?php echo bin2hex(random_bytes(32)); ?>"><p>Choose a test outcome:</p><button type="submit" name="outcome" value="success" class="admin-edit-button">Simulate success</button> <button type="submit" name="outcome" value="decline" class="admin-edit-button">Simulate decline</button> <button type="submit" name="outcome" value="cancel" class="admin-delete-button">Cancel demo order</button></form>
<?php endif; ?><p><a href="order-confirmation.php?id=<?php echo $id; ?>">View order</a> · <a href="account.php">My account</a></p></div></main><?php include '../includes/footer.php'; ?>
