<?php
require_once '../includes/auth.php'; requireCustomer(); require_once '../includes/db.php'; require_once '../includes/order-view.php';
$id=(int)($_GET['id']??$_POST['id']??0);$message='';
function findDemoOrder($conn,$id,$lock=false) {
 $s=$conn->prepare('SELECT o.* FROM orders o JOIN demo_payments d ON d.order_id=o.id WHERE o.id=? AND o.user_id=?'.($lock?' FOR UPDATE':''));$s->bind_param('ii',$id,$_SESSION['user_id']);$s->execute();return $s->get_result()->fetch_assoc();
}
if ($_SERVER['REQUEST_METHOD']==='POST') {
 checkCsrf();
 try {
  // Only opaque demo tokens reach the server. Card name, number, expiry and CVC stay in the browser.
  $action=$_POST['outcome']??'';$demoCard=$_POST['demo_card']??'';$key=$_POST['request_key']??'';
  $results=['approved'=>'success','review'=>'pending','declined'=>'decline'];
  $outcome=$action==='cancel'?'cancel':($action==='card' && is_string($demoCard)?($results[$demoCard]??''):'');
  if (!is_string($outcome) || !in_array($outcome,['success','pending','decline','cancel'],true) || !is_string($key) || !preg_match('/^[a-f0-9]{64}$/',$key)) throw new InvalidArgumentException('Choose a valid demo outcome.');
  mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);$conn->begin_transaction();$order=findDemoOrder($conn,$id,true);
  if (!$order) throw new InvalidArgumentException('Demo order not found.');
  $s=$conn->prepare('SELECT id FROM demo_transactions WHERE request_key=?');$s->bind_param('s',$key);$s->execute();
  if ($s->get_result()->num_rows || $order['payment_status']==='paid' || $order['order_status']==='cancelled') { $conn->commit();header('Location: order-confirmation.php?id='.$id,true,303);exit; }
  if ($order['order_status']!=='pending') throw new InvalidArgumentException('This order cannot accept a demo payment.');
  $reference='DEMO-'.bin2hex(random_bytes(12));
  $s=$conn->prepare('INSERT INTO demo_transactions(order_id,request_key,reference,outcome) VALUES (?,?,?,?)');$s->bind_param('isss',$id,$key,$reference,$outcome);$s->execute();
  $payment=$outcome==='success'?'paid':($outcome==='pending'?'pending':'failed');$status=$outcome==='success'?'processing':($outcome==='cancel'?'cancelled':'pending');
  if ($outcome==='cancel') {
   $s=$conn->prepare('SELECT product_id,SUM(quantity) qty FROM order_items WHERE order_id=? GROUP BY product_id ORDER BY product_id');$s->bind_param('i',$id);$s->execute();
   foreach ($s->get_result() as $item) { $u=$conn->prepare('UPDATE products SET stock=stock+? WHERE id=?');$u->bind_param('ii',$item['qty'],$item['product_id']);$u->execute(); }
  }
  $s=$conn->prepare('UPDATE orders SET payment_status=?,order_status=? WHERE id=?');$s->bind_param('ssi',$payment,$status,$id);$s->execute();$conn->commit();
  header('Location: order-confirmation.php?id='.$id,true,303);exit;
 } catch (Throwable $e) { try {$conn->rollback();}catch(Throwable $ignored){} http_response_code($e instanceof InvalidArgumentException?422:500);$message=$e instanceof InvalidArgumentException?$e->getMessage():'The simulation could not be saved. Please retry.'; }
}
$order=findDemoOrder($conn,$id);if (!$order) {http_response_code(404);exit('Demo order not found.');}
include '../includes/header.php';include '../includes/navbar.php'; ?>
<link rel="stylesheet" href="/assets/css/demo-payment.css">
<main class="demo-payment-page container py-5">
 <nav class="demo-checkout-steps" aria-label="Checkout progress">Delivery <span aria-hidden="true">/</span> <strong aria-current="step">Test card payment</strong> <span aria-hidden="true">/</span> Confirmation</nav>
 <div class="demo-payment-layout">
 <section class="demo-card-panel" aria-labelledby="demoHeading">
  <header class="demo-card-heading"><div><h1 id="demoHeading">Local sandbox<br>card checkout</h1><p>Card payment · no real charge</p></div><span class="demo-test-badge">TEST MODE</span></header>
  <p class="demo-safety">Use the demo numbers below only. Never enter a real card.</p>
  <div class="demo-card-preview" aria-hidden="true"><div class="demo-card-brand">VISA</div><div class="demo-card-number" id="cardPreviewNumber">•••• •••• •••• ••••</div><div class="demo-card-bottom"><div><span>CARDHOLDER</span><strong id="cardPreviewName">YOUR NAME</strong></div><div><span>EXPIRES</span><strong id="cardPreviewExpiry">MM/YY</strong></div></div></div>
  <?php if ($message): ?><p class="demo-payment-error" role="alert"><?php echo e($message); ?></p><?php endif; ?>
  <?php if ($order['payment_status']!=='paid' && $order['order_status']==='pending'): ?>
  <form method="post" id="demoCardForm" autocomplete="off">
   <?php echo csrfField(); ?><input type="hidden" name="id" value="<?php echo $id; ?>"><input type="hidden" name="request_key" value="<?php echo bin2hex(random_bytes(32)); ?>"><input type="hidden" name="outcome" value="card"><input type="hidden" name="demo_card" id="demoCardToken" value="">
   <div class="demo-input-group"><label for="demoCardName">Name on card</label><input id="demoCardName" type="text" placeholder="e.g. Example Demo Customer" maxlength="80" required autocomplete="off"></div>
   <div class="demo-input-group"><label for="demoCardNumber">Card number</label><input id="demoCardNumber" type="text" inputmode="numeric" placeholder="0000 0000 0000 0000" maxlength="19" required aria-describedby="demoCardHelp" autocomplete="off"></div>
   <div class="demo-card-input-row"><div class="demo-input-group"><label for="demoCardExpiry">Expiry date</label><input id="demoCardExpiry" type="text" inputmode="numeric" placeholder="MM/YY" maxlength="5" required autocomplete="off"></div><div class="demo-input-group"><label for="demoCardCvc">Security code</label><input id="demoCardCvc" type="password" inputmode="numeric" placeholder="CVC" maxlength="3" required autocomplete="off"></div></div>
   <aside class="demo-test-cards" id="demoCardHelp"><h2>Use a demo card number</h2><p>These test numbers control the simulated result. Other card numbers are rejected.</p>
    <button type="button" data-demo-number="4242424242424242"><strong>Approved</strong><span>4242 4242 4242 4242</span></button>
    <button type="button" data-demo-number="4111111111111111"><strong>Pending review</strong><span>4111 1111 1111 1111</span></button>
    <button type="button" data-demo-number="4000000000000002"><strong>Declined</strong><span>4000 0000 0000 0002</span></button>
    <p>Use any future expiry date, a 3-digit code and a demo cardholder name. Card fields stay in your browser and are never saved or sent to the server.</p>
   </aside>
   <p id="demoCardError" class="demo-payment-error" role="alert" tabindex="-1" hidden></p>
   <button type="submit" class="demo-pay-button">Pay R<?php echo number_format($order['total_amount'],2); ?> in test mode</button>
   <noscript><p>Enable JavaScript to validate the demo card and continue. You can still cancel the demo order below.</p></noscript>
  </form>
  <form method="post" class="demo-cancel-form"><?php echo csrfField(); ?><input type="hidden" name="id" value="<?php echo $id; ?>"><input type="hidden" name="request_key" value="<?php echo bin2hex(random_bytes(32)); ?>"><button type="submit" name="outcome" value="cancel" class="demo-cancel-button">Cancel demo order</button></form>
  <?php else: ?><p class="demo-complete-notice" role="status">This order is <?php echo e($order['order_status']); ?> and payment is <?php echo e($order['payment_status']); ?>. No further payment is required here.</p><?php endif; ?>
 </section>
 <aside class="demo-order-summary"><h2>Order summary</h2><p>Order #TC<?php echo $id; ?></p><?php foreach (orderItems($conn,$id) as $item): ?><div class="demo-summary-item"><span><?php echo e(trim($item['name'].' '.$item['colour'].' '.$item['length'])); ?> × <?php echo (int)$item['quantity']; ?></span><strong>R<?php echo number_format($item['price']*$item['quantity'],2); ?></strong></div><?php endforeach; ?><div class="demo-summary-item"><span>Delivery</span><span>Included (R0.00)</span></div><div class="demo-summary-item demo-summary-total"><span>Total</span><strong>R<?php echo number_format($order['total_amount'],2); ?></strong></div><p>Your order is already saved and stock reserved. An approval starts processing; pending review keeps the order unpaid; a decline allows retry. Cancellation releases reserved stock.</p><p><a href="order-confirmation.php?id=<?php echo $id; ?>">View order</a> · <a href="account.php">My account</a></p></aside>
 </div>
</main><script src="/assets/js/demo-payment.js" defer></script><?php include '../includes/footer.php'; ?>
