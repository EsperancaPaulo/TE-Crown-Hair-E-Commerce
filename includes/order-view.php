<?php
function e($value) { return htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8'); }
function orderItems($conn,$orderId) {
 $s=$conn->prepare("SELECT p.name,i.quantity,i.price,COALESCE(v.colour,'') colour,COALESCE(v.length,'') length FROM order_items i JOIN products p ON p.id=i.product_id LEFT JOIN order_item_options v ON v.item_id=i.id WHERE i.order_id=?");$s->bind_param('i',$orderId);$s->execute();return $s->get_result();
}

function isDemoOrder($conn,$id) { $s=$conn->prepare('SELECT order_id FROM demo_payments WHERE order_id=?');$s->bind_param('i',$id);$s->execute();return $s->get_result()->num_rows>0; }
function demoHistory($conn,$id) {
 $s=$conn->prepare('SELECT reference,outcome,created_at FROM demo_transactions WHERE order_id=? ORDER BY id');$s->bind_param('i',$id);$s->execute();
 echo '<p><strong>Sandbox / demo — no real payment.</strong></p>';
 foreach ($s->get_result() as $t) echo '<p>'.e($t['reference'].' · '.$t['outcome'].' · '.$t['created_at']).'</p>';
}
