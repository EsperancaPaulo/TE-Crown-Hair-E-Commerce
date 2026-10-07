<?php
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

                        <form id="contactForm">

                            <!-- Name -->

                            <div class="mb-4">

                                <label for="contactName">
                                    Full Name
                                </label>

                                <input
                                    type="text"
                                    id="contactName"
                                    name="name"
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
                                    name="email"
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
                                    name="subject"
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
                                    name="message"
                                    class="form-control"
                                    rows="6"
                                    required
                                ></textarea>

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


<script src="../assets/js/contact.js"></script>


<?php
include '../includes/footer.php';
?>
