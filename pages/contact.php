<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/enquiries.php';
$customerId = ($_SESSION['user_role'] ?? '') === 'customer' ? (int)$_SESSION['user_id'] : null;
$error = '';
$values = ['name'=>$customerId ? $_SESSION['user_name'] : '', 'email'=>$customerId ? $_SESSION['user_email'] : '', 'subject'=>'', 'message'=>''];
$requestKey = bin2hex(random_bytes(32));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    try {
        $requestKey = enquiryRequestKey();
        $values['subject'] = enquiryText('subject',160);
        $values['message'] = enquiryText('message',5000);
        if (!$customerId) {
            $values['name'] = enquiryText('name',100);
            $values['email'] = enquiryText('email',150);
        }
        if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException('Please enter a valid email address.');
        // A submitted key belongs to this form session. It cannot be used to claim someone else's enquiry.
        $stmt = $conn->prepare('SELECT id,user_id FROM enquiries WHERE request_key=?');
        $stmt->bind_param('s',$requestKey); $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();
        if ($existing) {
            if (($customerId && (int)$existing['user_id'] !== $customerId) || (!$customerId && !in_array($requestKey,$_SESSION['submitted_enquiry_keys'] ?? [],true))) {
                throw new InvalidArgumentException('Please reload the form and try again.');
            }
            $id = (int)$existing['id'];
        } else {
            if (!in_array($requestKey,$_SESSION['enquiry_form_keys'] ?? [],true)) throw new InvalidArgumentException('This form expired. Please reload the page and try again.');
            $recent = array_filter($_SESSION['enquiry_sent_at'] ?? [],fn($at)=>$at>time()-600);
            if (count($recent)>=10) throw new InvalidArgumentException('You have sent several enquiries. Please wait ten minutes before sending another.');
            $stmt=$conn->prepare('INSERT INTO enquiries(user_id,name,email,subject,message,request_key) VALUES (?,?,?,?,?,?)');
            $stmt->bind_param('isssss',$customerId,$values['name'],$values['email'],$values['subject'],$values['message'],$requestKey);
            if (!$stmt->execute()) throw new RuntimeException('Unable to save enquiry.');
            $id=$conn->insert_id;
            $_SESSION['enquiry_sent_at']=[...$recent,time()];
            $_SESSION['submitted_enquiry_keys']=array_slice([...($_SESSION['submitted_enquiry_keys'] ?? []),$requestKey],-20);
        }
        if ($customerId) { header('Location: enquiry.php?id='.$id.'&sent=1',true,303); exit; }
        $_SESSION['guest_enquiry_received']=$id;
        header('Location: contact.php?received=1',true,303); exit;
    } catch (InvalidArgumentException $exception) { http_response_code(422); $error=$exception->getMessage(); }
    catch (Throwable $exception) { http_response_code(503); $error='Your message could not be saved. Please try again. No email has been sent.'; }
}
$_SESSION['enquiry_form_keys']=array_slice([...($_SESSION['enquiry_form_keys'] ?? []),$requestKey],-20);
$received = isset($_GET['received']) ? ($_SESSION['guest_enquiry_received'] ?? null) : null;
unset($_SESSION['guest_enquiry_received']);
include '../includes/header.php';
include '../includes/navbar.php';
?>

<main>

    <!-- ==============================
         CONTACT HEADER
         ============================== -->

    <section class="contact-header">

        <div class="container text-center">

            <p class="section-label">
                TE_CROWN HAIR
            </p>

            <h1>Contact Us</h1>

            <p>
                Have a question? We'd love to hear from you.
            </p>

        </div>

    </section>


    <!-- ==============================
         CONTACT SECTION
         ============================== -->

    <section class="contact-section">

        <div class="container">

            <div class="row g-5">

                <!-- CONTACT INFORMATION -->

                <div class="col-lg-5">

                    <div class="contact-info">

                        <p class="section-label">
                            GET IN TOUCH
                        </p>

                        <h2>
                            We'd Love to Hear From You
                        </h2>

                        <p>
                            Contact TE_Crown Hair if you have
                            questions about our products, orders
                            or services.
                        </p>


                        <div class="contact-detail">

                            <span>Phone</span>

                            <strong>
                                +27 71 234 5678
                            </strong>

                        </div>


                        <div class="contact-detail">

                            <span>Email</span>

                            <strong>
                                info@tecrownhair.co.za
                            </strong>

                        </div>


                        <div class="contact-detail">

                            <span>Location</span>

                            <strong>
                                Gauteng, South Africa
                            </strong>

                        </div>

                    </div>

                </div>


                <!-- CONTACT FORM -->

                <div class="col-lg-7">

                    <div class="contact-form-box">

                        <h2>
                            Send Us a Message
                        </h2>

                        <?php if ($error): ?><p class="enquiry-notice enquiry-error" role="alert"><?php echo e($error); ?></p><?php endif; ?>
                        <?php if ($received): ?><div class="enquiry-notice" role="status">Your guest enquiry #EQ<?php echo (int)$received; ?> has been saved for our team. Guest enquiries do not appear in an account, and no email notification has been sent.</div><?php endif; ?>
                        <p class="contact-tracking-note"><?php if ($customerId): ?>Your enquiry will be saved to your account. Check <a href="account.php#enquiries">My enquiries</a> for replies and status updates.<?php else: ?><a href="login.php">Sign in before sending</a> to track replies privately in your account. Guest messages are saved for our team but cannot be tracked online or automatically linked later, even if the email matches an account. No automatic email reply is sent.<?php endif; ?></p>
                        <form id="contactForm" method="post" action="contact.php">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="request_key" value="<?php echo e($requestKey); ?>">

                            <!-- Name -->

                            <div class="mb-4">

                                <label for="contactName">
                                    Full Name
                                </label>

                                <input
                                    type="text"
                                    id="contactName"
                                    name="name" maxlength="100" autocomplete="name" value="<?php echo e($values['name']); ?>" <?php echo $customerId ? 'readonly' : ''; ?>
                                    class="form-control"
                                    required
                                >

                            </div>


                            <!-- Email -->

                            <div class="mb-4">

                                <label for="contactEmail">
                                    Email Address
                                </label>

                                <input
                                    type="email"
                                    id="contactEmail"
                                    name="email" maxlength="150" autocomplete="email" value="<?php echo e($values['email']); ?>" <?php echo $customerId ? 'readonly' : ''; ?>
                                    class="form-control"
                                    required
                                >

                            </div>


                            <!-- Subject -->

                            <div class="mb-4">

                                <label for="contactSubject">
                                    Subject
                                </label>

                                <input
                                    type="text"
                                    id="contactSubject"
                                    name="subject" maxlength="160" value="<?php echo e($values['subject']); ?>"
                                    class="form-control"
                                    required
                                >

                            </div>


                            <!-- Message -->

                            <div class="mb-4">

                                <label for="contactMessage">
                                    Message
                                </label>

                                <textarea
                                    id="contactMessage"
                                    name="message" maxlength="5000" aria-describedby="messageLimit"
                                    class="form-control"
                                    rows="6"
                                    required
                                ><?php echo e($values['message']); ?></textarea><small id="messageLimit">Up to 5,000 characters. Replies appear in your account when sent while signed in.</small>

                            </div>


                            <!-- Submit -->

                            <button
                                type="submit"
                                class="contact-button"
                            >
                                SEND MESSAGE
                            </button>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </section>

</main>





<?php
include '../includes/footer.php';
?>
