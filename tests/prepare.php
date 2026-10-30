<?php
// Fictional fixtures only. Explicit opt-in and dedicated database required.
if (PHP_SAPI !== 'cli' || getenv('SHOP_TEST_SETUP') !== '1' || getenv('DB_NAME') !== 'te_crown_repair_test') exit("Set SHOP_TEST_SETUP=1 DB_NAME=te_crown_repair_test; use an empty test database.\n");
require __DIR__.'/../includes/db.php';
if ($conn->query('SHOW TABLES')->num_rows) exit("Test database must be empty.\n");
mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
$sql=preg_replace('/CREATE DATABASE IF NOT EXISTS te_crown_hair;|USE te_crown_hair;/','',file_get_contents(__DIR__.'/../database/schema.sql'));
$conn->multi_query($sql);do {if($result=$conn->store_result())$result->free();}while($conn->more_results() && $conn->next_result());
foreach ([['Repair Customer','customer@example.invalid','customer',1],['Repair Admin','admin@example.invalid','admin',1],['Other Customer','other@example.invalid','customer',1],['Unverified','unverified@example.invalid','customer',0],['Expired','expired@example.invalid','customer',0]] as [$name,$email,$role,$verified]) {
 $hash=password_hash('RepairTestPass123!',PASSWORD_DEFAULT);$s=$conn->prepare('INSERT INTO users(name,email,password,role,email_verified) VALUES (?,?,?,?,?)');$s->bind_param('ssssi',$name,$email,$hash,$role,$verified);$s->execute();
}
$conn->query("UPDATE users SET verification_token=REPEAT('a',64),verification_expires=DATE_ADD(UTC_TIMESTAMP(),INTERVAL 30 MINUTE) WHERE email='unverified@example.invalid'");
$conn->query("UPDATE users SET verification_token=REPEAT('b',64),verification_expires=DATE_SUB(UTC_TIMESTAMP(),INTERVAL 1 MINUTE) WHERE email='expired@example.invalid'");
echo "Fictional test fixtures created. Run database/migrate.php with the same DB_NAME next.\n";
