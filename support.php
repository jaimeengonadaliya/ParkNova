<?php
require_once __DIR__ . '/includes_header.php';
require_once __DIR__ . '/includes_navbar.php';
?>

<div class="container py-5 mt-5">
    <div class="glass-panel p-5 animate-up">
        <h1 class="hero-title mb-4 fw-bold text-center">Help <span class="text-gradient">Center</span></h1>
        <p class="lead text-secondary mb-5 fs-4 text-center">The ParkNova Help Center provides answers to common questions.</p>
        
        <div class="row g-4 mt-4">
            <div class="col-lg-8 mx-auto">
                <div class="card-3d p-4 mb-4">
                    <h4 class="text-primary fw-bold mb-4"><i class="fa-solid fa-circle-question me-2"></i> Frequently Asked Questions</h4>
                    
                    <div class="accordion accordion-flush custom-accordion" id="faqAccordion">
                        <div class="accordion-item bg-transparent border-bottom border-secondary border-opacity-10 mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed bg-transparent text-primary fw-bold fs-5 px-0 pb-3" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    How do I book a parking slot?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-secondary px-0 pb-3">
                                    Login to your account, choose a parking location, select a slot, enter booking details, and complete the payment.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item bg-transparent border-bottom border-secondary border-opacity-10 mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed bg-transparent text-primary fw-bold fs-5 px-0 pb-3" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    How do I enter the parking area?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-secondary px-0 pb-3">
                                    Show your <strong>Booking ID or Vehicle Number</strong> to the parking manager for verification at the entry point.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item bg-transparent border-bottom border-secondary border-opacity-10 mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed bg-transparent text-primary fw-bold fs-5 px-0 pb-3" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    What payment methods are supported?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-secondary px-0 pb-3">
                                    ParkNova supports secure online payments using integrated payment gateways for safe transactions.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-3d p-4 text-center bg-primary text-white bg-opacity-5">
                    <p class="fs-5 mb-0 fw-medium">If you need additional assistance, please contact our support team.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.custom-accordion .accordion-button:not(.collapsed) {
    box-shadow: none;
    color: var(--primary-color);
}
.custom-accordion .accordion-button::after {
    filter: brightness(0.5);
}
</style>

<?php require_once __DIR__ . '/includes_footer.php'; ?>


