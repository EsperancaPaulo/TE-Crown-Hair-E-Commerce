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
$enquiryTables = [
    "CREATE TABLE IF NOT EXISTS enquiries (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL,
        subject VARCHAR(160) NOT NULL,
        message TEXT NOT NULL,
        status ENUM('open','in_progress','replied','resolved') NOT NULL DEFAULT 'open',
        request_key CHAR(64) NOT NULL UNIQUE,
        revision INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX customer_enquiries (user_id, id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    "CREATE TABLE IF NOT EXISTS enquiry_replies (
        id INT AUTO_INCREMENT PRIMARY KEY,
        enquiry_id INT NOT NULL,
        admin_id INT NOT NULL,
        message TEXT NOT NULL,
        request_key CHAR(64) NOT NULL UNIQUE,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX enquiry_conversation (enquiry_id, id),
        FOREIGN KEY (enquiry_id) REFERENCES enquiries(id) ON DELETE RESTRICT,
        FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
];
foreach ($enquiryTables as $sql) {
    if (!$conn->query($sql)) throw new RuntimeException($conn->error);
}
echo "Additive shop migration complete.\n";
