<?php
require_once __DIR__.'/order-view.php';

function enquiryStatuses() {
    return ['open'=>'Open', 'in_progress'=>'In progress', 'replied'=>'Replied', 'resolved'=>'Resolved'];
}

function enquiryDate($value) {
    return (new DateTimeImmutable($value, new DateTimeZone('UTC')))
        ->setTimezone(new DateTimeZone('Africa/Johannesburg'))->format('d M Y, H:i');
}

function enquiryText($field, $max, $required=true) {
    $value = $_POST[$field] ?? '';
    if (!is_string($value) || strlen(trim($value)) > $max || ($required && trim($value) === '')) {
        throw new InvalidArgumentException('Please complete the form and keep each field within its stated limit.');
    }
    return trim($value);
}

function enquiryRequestKey() {
    $key = $_POST['request_key'] ?? '';
    if (!is_string($key) || !preg_match('/^[a-f0-9]{64}$/', $key)) {
        throw new InvalidArgumentException('Please reload the form and try again.');
    }
    return $key;
}

function customerEnquiry($conn, $id) {
    // Ownership always comes from the authenticated session, never request data.
    $stmt = $conn->prepare('SELECT * FROM enquiries WHERE id=? AND user_id=?');
    $stmt->bind_param('ii', $id, $_SESSION['user_id']);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function enquiryReplies($conn, $id) {
    $stmt = $conn->prepare('SELECT message,created_at FROM enquiry_replies WHERE enquiry_id=? ORDER BY id');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    return $stmt->get_result();
}

function customerEnquiries($conn, $limit=5, $offset=0) {
    $stmt = $conn->prepare("SELECT e.*, r.message reply_message, r.created_at reply_at
        FROM enquiries e LEFT JOIN enquiry_replies r
          ON r.id=(SELECT MAX(x.id) FROM enquiry_replies x WHERE x.enquiry_id=e.id)
        WHERE e.user_id=? ORDER BY e.id DESC LIMIT ? OFFSET ?");
    $stmt->bind_param('iii', $_SESSION['user_id'], $limit, $offset);
    $stmt->execute();
    return $stmt->get_result();
}

function renderEnquirySummary($item) {
    $labels = enquiryStatuses(); ?>
    <article class="account-enquiry-row">
        <div class="account-enquiry-title">
            <div><p class="account-order-reference">ENQUIRY #EQ<?php echo (int)$item['id']; ?></p>
                <h3><?php echo e($item['subject']); ?></h3></div>
            <span class="account-status enquiry-status-<?php echo e($item['status']); ?>"><?php echo e($labels[$item['status']]); ?></span>
        </div>
        <p class="account-enquiry-date">Sent <?php echo e(enquiryDate($item['created_at'])); ?> · Updated <?php echo e(enquiryDate($item['updated_at'])); ?> SAST</p>
        <?php if ($item['reply_message'] !== null): ?>
            <div class="account-enquiry-preview"><span>Latest reply from TE_Crown Hair</span><p><?php echo e($item['reply_message']); ?></p><time><?php echo e(enquiryDate($item['reply_at'])); ?> SAST</time></div>
        <?php else: ?><p class="account-enquiry-awaiting">Your message is with our team. Any reply will appear here.</p><?php endif; ?>
        <a class="account-order-link" href="enquiry.php?id=<?php echo (int)$item['id']; ?>">View enquiry</a>
    </article>
<?php }
