<?php
require_once '../includes/auth.php'; requireCustomer(); require_once '../includes/db.php'; require_once '../includes/enquiries.php';
$page=filter_var($_GET['page'] ?? 1,FILTER_VALIDATE_INT,['options'=>['min_range'=>1,'max_range'=>100000]]) ?: 1;
$stmt=$conn->prepare('SELECT COUNT(*) total FROM enquiries WHERE user_id=?');$stmt->bind_param('i',$_SESSION['user_id']);$stmt->execute();$total=(int)$stmt->get_result()->fetch_assoc()['total'];
$items=customerEnquiries($conn,20,($page-1)*20);
include '../includes/header.php';include '../includes/navbar.php'; ?>
<main class="customer-account"><div class="container account-dashboard"><header class="account-welcome"><div><p class="account-eyebrow">HERE TO HELP</p><h1>My enquiries</h1><p>Your messages, replies and progress in one place.</p></div><a class="account-action account-action-primary" href="contact.php">Send an enquiry</a></header><section class="account-orders-panel">
<?php if (!$items->num_rows): ?><div class="account-empty-state"><h2>No enquiries here yet</h2><p>Send us a message when you need a hand.</p></div><?php endif; ?>
<?php foreach ($items as $item) renderEnquirySummary($item); ?></section>
<nav class="enquiry-pagination" aria-label="Enquiry pages"><?php if ($page>1): ?><a class="account-order-link" href="?page=<?php echo $page-1; ?>">Previous page</a><?php endif; ?><span>Page <?php echo $page; ?></span><?php if ($page*20<$total): ?><a class="account-order-link" href="?page=<?php echo $page+1; ?>">Next page</a><?php endif; ?></nav><p class="account-help-note"><a href="account.php#enquiries">Back to my account</a></p></div></main><?php include '../includes/footer.php'; ?>
