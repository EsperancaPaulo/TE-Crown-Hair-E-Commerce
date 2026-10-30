<?php
if (PHP_SAPI !== 'cli' || getenv('DB_NAME') !== 'te_crown_repair_test') exit("Use only the isolated repair test database.\n");
require __DIR__.'/../includes/db.php';
$stock=(int)$conn->query('SELECT stock FROM products WHERE id=1')->fetch_assoc()['stock'];
$orders=(int)$conn->query('SELECT COUNT(*) n FROM orders')->fetch_assoc()['n'];
$transactions=(int)$conn->query('SELECT COUNT(*) n FROM demo_transactions')->fetch_assoc()['n'];
$paid=$conn->query("SELECT total_amount FROM orders WHERE payment_status='paid'")->fetch_assoc()['total_amount'] ?? null;
if ($stock!==8 || $orders!==2 || $transactions!==3 || $paid!=='4600.00') {fwrite(STDERR,"Inventory/order replay invariant failed.\n");exit(1);}
echo "PASS: stock, total, saved orders and transaction counts match expected invariants.\n";
