<?php
require_once '../includes/auth.php';
requireCustomer();
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['error'=>'Use checkout to place an order.']); exit; }
if (!is_string($_POST['csrf'] ?? null) || !hash_equals($_SESSION['csrf'],$_POST['csrf'])) {
 http_response_code(403); echo json_encode(['error'=>'Your checkout form expired. Sign in again or reload checkout before retrying.']); exit;
}
require_once '../includes/db.php';
function rejectOrder($message) { throw new InvalidArgumentException($message); }
try {
 $delivery=[];
 foreach (['firstName'=>100,'lastName'=>100,'phone'=>30,'address'=>250,'city'=>100,'province'=>30,'postalCode'=>4,'notes'=>1000] as $field=>$limit) {
  $value=$_POST[$field] ?? ''; if (!is_string($value) || strlen(trim($value))>$limit || ($field!=='notes' && trim($value)==='')) rejectOrder('Please complete your delivery details correctly.');
  $delivery[$field]=trim($value);
 }
 if (!preg_match('/^[0-9+() -]{7,30}$/',$delivery['phone']) || !preg_match('/^\d{4}$/',$delivery['postalCode'])) rejectOrder('Enter a valid phone number and four-digit postal code.');
 if (!in_array($delivery['province'],['Gauteng','Western Cape','KwaZulu-Natal','Eastern Cape','Free State','Limpopo','Mpumalanga','North West','Northern Cape'])) rejectOrder('Choose a valid province.');
 if (($_POST['paymentMethod'] ?? '')!=='demo') rejectOrder('Select the local demo payment method.');
 $key=$_POST['request_key'] ?? '';
 if (!is_string($key) || !preg_match('/^[a-f0-9]{64}$/',$key)) rejectOrder('Reload checkout and try again.');
 // Replays return the original order without reserving stock again.
 $s=$conn->prepare('SELECT d.order_id FROM order_details d JOIN orders o ON o.id=d.order_id WHERE d.request_key=? AND o.user_id=?');
 $s->bind_param('si',$key,$_SESSION['user_id']);$s->execute();
 if ($previous=$s->get_result()->fetch_assoc()) { echo json_encode(['order_id'=>$previous['order_id']]); exit; }
 $cart=json_decode($_POST['cart'] ?? '',true,32,JSON_THROW_ON_ERROR);
 if (!is_array($cart) || !$cart || count($cart)>100) rejectOrder('Your cart is empty or too large.');
 $items=[];$quantities=[];
 foreach ($cart as $item) {
  if (!is_array($item)) rejectOrder('Invalid cart.');
  $id=filter_var($item['id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
  $qty=filter_var($item['quantity']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1,'max_range'=>999]]);
  if (!$qty) rejectOrder('Please choose a positive whole quantity for each item.');
  // Older versions of the cart stored names without IDs. Resolve only a unique current catalogue match.
  if (!isset($item['id']) || $item['id']==='') {
   $name=$item['name'] ?? null;
   if (!is_string($name) || strlen($name)>150) rejectOrder('An older cart item could not be matched. Please update your cart.');
   $lookup=$conn->prepare("SELECT id FROM products WHERE name=? AND status='active' LIMIT 2");
   $lookup->bind_param('s',$name);$lookup->execute();$matches=$lookup->get_result();
   if ($matches->num_rows!==1) rejectOrder('An older cart item is unavailable or ambiguous. Please update your cart.');
   $id=(int)$matches->fetch_assoc()['id'];
  }
  if (!$id) rejectOrder('A cart item could not be matched. Please update your cart.');
  $colour=$item['colour']??'';$length=(string)($item['length']??'');
  if (!is_string($colour) || !in_array($colour,['','Natural Black','1B','Brown','Burgundy','Blonde']) || !in_array($length,['','12','14','16','18','20'])) rejectOrder('Invalid product options.');
  $items[]=['id'=>$id,'quantity'=>$qty,'colour'=>$colour,'length'=>$length];
  $quantities[$id]=($quantities[$id]??0)+$qty;
 }
 ksort($quantities); mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); $conn->begin_transaction();
 $products=[];$total=0;
 foreach ($quantities as $id=>$qty) {
  $s=$conn->prepare('SELECT id,name,price,stock,status FROM products WHERE id=? FOR UPDATE');$s->bind_param('i',$id);$s->execute();$p=$s->get_result()->fetch_assoc();
  if (!$p || $p['status']!=='active' || $p['stock']<$qty) rejectOrder('An item is unavailable or exceeds stock. Please update your cart.');
  $products[$id]=$p; $cents=(int)round((float)$p['price']*100);$total+=$cents*$qty;
 }
 if ($total<=0 || $total>9999999999) rejectOrder('Invalid order total.');
 $amount=number_format($total/100,2,'.','');
 $s=$conn->prepare("INSERT INTO orders(user_id,total_amount,payment_method) VALUES (?,?,'card')");$s->bind_param('is',$_SESSION['user_id'],$amount);$s->execute();$orderId=$conn->insert_id;
 $s=$conn->prepare('INSERT INTO demo_payments(order_id) VALUES (?)');$s->bind_param('i',$orderId);$s->execute();
 $json=json_encode($delivery,JSON_THROW_ON_ERROR);
 $s=$conn->prepare('INSERT INTO order_details(order_id,request_key,delivery) VALUES (?,?,?)');$s->bind_param('iss',$orderId,$key,$json);$s->execute();
 foreach ($items as $item) {
  $price=$products[$item['id']]['price'];
  $s=$conn->prepare('INSERT INTO order_items(order_id,product_id,quantity,price) VALUES (?,?,?,?)');$s->bind_param('iiis',$orderId,$item['id'],$item['quantity'],$price);$s->execute();$itemId=$conn->insert_id;
  $s=$conn->prepare('INSERT INTO order_item_options(item_id,colour,length) VALUES (?,?,?)');$s->bind_param('iss',$itemId,$item['colour'],$item['length']);$s->execute();
 }
 foreach ($quantities as $id=>$qty) { $s=$conn->prepare('UPDATE products SET stock=stock-? WHERE id=?');$s->bind_param('ii',$qty,$id);$s->execute(); }
 $conn->commit(); echo json_encode(['order_id'=>$orderId]);
} catch (Throwable $e) {
 try { $conn->rollback(); } catch (Throwable $ignored) {}
 http_response_code($e instanceof InvalidArgumentException || $e instanceof JsonException ? 422 : 500);
 echo json_encode(['error'=>$e instanceof InvalidArgumentException ? $e->getMessage() : 'The order could not be saved. Please retry; your cart has been kept.']);
}
