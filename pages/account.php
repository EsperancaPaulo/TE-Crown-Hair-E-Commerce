<?php
require_once '../includes/auth.php';
requireCustomer();
require_once '../includes/db.php';
require_once '../includes/order-view.php';
require_once '../includes/enquiries.php';
$myEnquiries=customerEnquiries($conn);
$countEnquiries=$conn->prepare('SELECT COUNT(*) total FROM enquiries WHERE user_id=?');
$countEnquiries->bind_param('i',$_SESSION['user_id']);$countEnquiries->execute();
$enquiryCount=(int)$countEnquiries->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare('SELECT * FROM orders WHERE user_id=? ORDER BY id DESC');
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$orders = $stmt->get_result();
$orderCount = $orders->num_rows;

include '../includes/header.php';
include '../includes/navbar.php';
?>
<main class="customer-account">
    <div class="container account-dashboard">
        <header class="account-welcome">
            <div>
                <p class="account-eyebrow">YOUR PERSONAL SPACE</p>
                <h1>My account</h1>
                <p>Good to see you, <strong><?php echo e($_SESSION['user_name']); ?></strong>.<br>Keep track of your orders and enquiries in one place.</p>
            </div>
            <a class="account-action account-action-primary" href="shop.php">Continue shopping</a>
        </header>

        <div class="account-dashboard-grid">
            <aside class="account-profile-panel" aria-labelledby="profileHeading">
                <div class="account-profile-intro">
                    <span class="account-profile-mark" aria-hidden="true">♕</span>
                    <p class="account-eyebrow">CUSTOMER PROFILE</p>
                    <h2 id="profileHeading"><?php echo e($_SESSION['user_name']); ?></h2>
                    <span class="account-verified"><span aria-hidden="true">✓</span> Email verified</span>
                </div>
                <dl class="account-profile-details">
                    <div><dt>Full name</dt><dd><?php echo e($_SESSION['user_name']); ?></dd></div>
                    <div><dt>Email address</dt><dd><?php echo e($_SESSION['user_email']); ?></dd></div>
                </dl>
                <a class="account-action account-action-secondary" href="cart.php">View my cart</a>
                <div class="account-signout">
                    <p>Finished for now?</p>
                    <form action="logout.php" method="post">
                        <?php echo csrfField(); ?>
                        <button type="submit" class="account-action account-action-logout">Log out</button>
                    </form>
                </div>
            </aside>

            <div class="account-activity-column">
            <section class="account-orders-panel" aria-labelledby="ordersHeading">
                <header class="account-orders-heading">
                    <div><p class="account-eyebrow">YOUR SHOPPING JOURNEY</p><h2 id="ordersHeading">Order history</h2></div>
                    <span class="account-order-count"><?php echo $orderCount; ?> <?php echo $orderCount === 1 ? 'order' : 'orders'; ?></span>
                </header>
                <?php if (!$orderCount): ?>
                    <div class="account-empty-state">
                        <span class="account-empty-icon" aria-hidden="true"><svg viewBox="0 0 48 48" fill="none"><path d="M12 16h24l3 25H9l3-25Z" stroke="currentColor" stroke-width="1.8"/><path d="M18 18V12a6 6 0 0 1 12 0v6" stroke="currentColor" stroke-width="1.8"/></svg></span>
                        <h3>Your next crown is waiting</h3>
                        <p>Once you place your first order, you’ll find its details and progress right here.</p>
                        <a class="account-action account-action-primary" href="shop.php">Explore the collection</a>
                        <span class="account-empty-note">Wigs, extensions &amp; the finishing touches.</span>
                    </div>
                <?php else: ?>
                    <div class="account-orders-list">
                        <?php while ($order = $orders->fetch_assoc()): ?>
                            <article class="account-order-row">
                                <div class="account-order-main">
                                    <p class="account-order-reference">ORDER #TC<?php echo (int)$order['id']; ?></p>
                                    <time datetime="<?php echo e(str_replace(' ', 'T', $order['order_date']).'Z'); ?>"><?php echo e((new DateTimeImmutable($order['order_date'], new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Africa/Johannesburg'))->format('d M Y')); ?></time>
                                    <?php if (isDemoOrder($conn, $order['id'])): ?><span class="account-demo-label">Sandbox / demo · no real payment</span><?php endif; ?>
                                </div>
                                <div class="account-order-progress">
                                    <span class="account-status status-<?php echo e($order['order_status']); ?>"><?php echo e(ucfirst($order['order_status'])); ?></span>
                                    <span class="account-payment-label">Payment <?php echo e($order['payment_status']); ?></span>
                                </div>
                                <div class="account-order-end">
                                    <strong>R<?php echo number_format($order['total_amount'], 2); ?></strong>
                                    <a class="account-order-link" href="order-confirmation.php?id=<?php echo (int)$order['id']; ?>" aria-label="View order TC<?php echo (int)$order['id']; ?>">View details</a>
                                </div>
                            </article>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </section>
            <section id="enquiries" class="account-orders-panel" aria-labelledby="enquiriesHeading">
                <header class="account-orders-heading"><div><p class="account-eyebrow">WE’RE HERE FOR YOU</p><h2 id="enquiriesHeading">My enquiries</h2></div><span class="account-order-count"><?php echo $enquiryCount; ?> <?php echo $enquiryCount===1 ? 'enquiry' : 'enquiries'; ?></span></header>
                <?php if (!$enquiryCount): ?><div class="account-empty-state account-enquiry-empty"><h3>A little help with your crown?</h3><p>Send us a question about a product or order. You’ll find your message, our replies and its status here.</p><a class="account-action account-action-primary" href="contact.php">Send an enquiry</a></div>
                <?php else: ?><?php foreach ($myEnquiries as $item) renderEnquirySummary($item); ?><div class="account-enquiry-actions"><a class="account-order-link" href="enquiries.php">View all enquiries</a><a class="account-action account-action-secondary" href="contact.php">Send a new enquiry</a></div><?php endif; ?>
            </section>
            </div>
        </div>
        <p class="account-help-note">A little help with your crown? <a href="contact.php">Get in touch with us</a>.</p>
    </div>
</main>
<?php include '../includes/footer.php'; ?>
