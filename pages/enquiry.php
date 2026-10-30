<?php
require_once '../includes/auth.php';
requireCustomer();
require_once '../includes/db.php';
require_once '../includes/enquiries.php';
if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); header('Allow: GET'); exit('Enquiries are read-only here.'); }
$id=filter_var($_GET['id'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]) ?: 0;
$enquiry=customerEnquiry($conn,$id);
if (!$enquiry) { http_response_code(404); exit('Enquiry not found. <a href="account.php#enquiries">My enquiries</a>'); }
$labels=enquiryStatuses();
include '../includes/header.php'; include '../includes/navbar.php'; ?>
<main class="customer-account"><div class="container account-dashboard">
<header class="account-welcome"><div><p class="account-eyebrow">HERE TO HELP</p><h1>Your enquiry</h1><p>Private messages between you and TE_Crown Hair.</p></div><a class="account-action account-action-secondary" href="account.php#enquiries">Back to my account</a></header>
<?php if (isset($_GET['sent'])): ?><p class="enquiry-notice" role="status">Your enquiry has been saved. Our reply and any status updates will appear here. No email notification has been sent.</p><?php endif; ?>
<section class="account-orders-panel enquiry-conversation" aria-labelledby="enquiryHeading">
<header class="account-orders-heading"><div><p class="account-eyebrow">ENQUIRY #EQ<?php echo $id; ?></p><h2 id="enquiryHeading"><?php echo e($enquiry['subject']); ?></h2></div><span class="account-status enquiry-status-<?php echo e($enquiry['status']); ?>"><?php echo e($labels[$enquiry['status']]); ?></span></header>
<p class="enquiry-conversation-updated">Last updated <?php echo e(enquiryDate($enquiry['updated_at'])); ?> SAST</p>
<article class="enquiry-message enquiry-message-customer"><header><strong>Your message</strong><time><?php echo e(enquiryDate($enquiry['created_at'])); ?> SAST</time></header><p><?php echo e($enquiry['message']); ?></p></article>
<?php $replies=enquiryReplies($conn,$id); if (!$replies->num_rows): ?><p class="account-enquiry-awaiting enquiry-waiting">Our team has your message. Check back here for a reply.</p><?php endif; ?>
<?php foreach ($replies as $reply): ?><article class="enquiry-message enquiry-message-admin"><header><strong>TE_Crown Hair</strong><time><?php echo e(enquiryDate($reply['created_at'])); ?> SAST</time></header><p><?php echo e($reply['message']); ?></p></article><?php endforeach; ?>
</section><p class="account-help-note">Something else on your mind? <a href="contact.php">Send a new enquiry</a>.</p>
</div></main><?php include '../includes/footer.php'; ?>
