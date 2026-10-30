<?php
// Additive upgrade only; never resets existing customer, product or order data.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__.'/../includes/db.php';
$columns=['email_verified'=>'TINYINT(1) NOT NULL DEFAULT 0','verification_token'=>'VARCHAR(64) NULL','verification_expires'=>'DATETIME NULL','is_active'=>'TINYINT(1) NOT NULL DEFAULT 1'];
foreach ($columns as $column=>$definition) {
 $result=$conn->query("SHOW COLUMNS FROM users LIKE '$column'");
 if (!$result->num_rows && !$conn->query("ALTER TABLE users ADD COLUMN $column $definition")) { throw new RuntimeException($conn->error); }
}
$sql="CREATE TABLE IF NOT EXISTS order_details (
 order_id INT PRIMARY KEY, request_key VARCHAR(64) NOT NULL UNIQUE, delivery TEXT NOT NULL,
 FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB";
if (!$conn->query($sql)) throw new RuntimeException($conn->error);
if (!$conn->query("CREATE TABLE IF NOT EXISTS order_item_options (item_id INT PRIMARY KEY, colour VARCHAR(30) NOT NULL DEFAULT '', length VARCHAR(10) NOT NULL DEFAULT '', FOREIGN KEY(item_id) REFERENCES order_items(id) ON DELETE CASCADE) ENGINE=InnoDB")) throw new RuntimeException($conn->error);
if (!$conn->query("CREATE TABLE IF NOT EXISTS demo_payments (order_id INT PRIMARY KEY, FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE CASCADE) ENGINE=InnoDB")) throw new RuntimeException($conn->error);
if (!$conn->query("CREATE TABLE IF NOT EXISTS demo_transactions (id INT AUTO_INCREMENT PRIMARY KEY, order_id INT NOT NULL, request_key VARCHAR(64) NOT NULL UNIQUE, reference VARCHAR(40) NOT NULL UNIQUE, outcome ENUM('success','decline','cancel') NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE CASCADE) ENGINE=InnoDB")) throw new RuntimeException($conn->error);
if (!$conn->query("CREATE TABLE IF NOT EXISTS auth_attempts (bucket CHAR(64) PRIMARY KEY, attempts INT UNSIGNED NOT NULL DEFAULT 1, started_at DATETIME NOT NULL) ENGINE=InnoDB")) throw new RuntimeException($conn->error);
echo "Additive shop migration complete.\n";
