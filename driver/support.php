<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config/db.php';
if (!isset($_SESSION['driver_id'])) { header("Location: index.php"); exit(); }

$ticket_sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_ticket'])) {
    // In production: save to support_tickets table or send email
    $ticket_sent = true;
}

include '../driver/includes/header.php';
?>

<div class="page-header">
    <h1>Support Center</h1>
    <p>Get help with rides, payments, account issues, or road safety.</p>
</div>

<!-- Contact cards -->
<div class="support-grid">

    <div class="support-contact-card emergency">
        <div class="support-icon emergency"><i class="fas fa-phone-volume"></i></div>
        <h4>Emergency Assistance</h4>
        <a href="tel:+919999900000" class="support-contact-value">+91 99999 00000</a>
        <p>For on-road emergencies, accidents, or safety threats. Available 24/7.</p>
        <a href="tel:+919999900000" class="d-btn d-btn-danger" style="margin-top:var(--d-sp-md);">
            <i class="fas fa-phone"></i> Call Now
        </a>
    </div>

    <div class="support-contact-card payment">
        <div class="support-icon payment"><i class="fas fa-envelope"></i></div>
        <h4>Payment Queries</h4>
        <a href="mailto:support.drivers@ruscab.com" class="support-contact-value">support.drivers@ruscab.com</a>
        <p>Wallet issues, payout delays, or fare disputes. Response within 4 hours.</p>
        <a href="mailto:support.drivers@ruscab.com" class="d-btn d-btn-outline" style="margin-top:var(--d-sp-md);">
            <i class="fas fa-envelope"></i> Send Email
        </a>
    </div>

    <div class="support-contact-card general">
        <div class="support-icon general"><i class="fas fa-comments"></i></div>
        <h4>General Support</h4>
        <a href="https://wa.me/919999900001" class="support-contact-value">+91 99999 00001</a>
        <p>Account questions, app issues, or onboarding help. WhatsApp preferred.</p>
        <a href="https://wa.me/919999900001" class="d-btn d-btn-primary" style="margin-top:var(--d-sp-md);">
            <i class="fab fa-whatsapp"></i> WhatsApp
        </a>
    </div>

    <div class="support-contact-card safety">
        <div class="support-icon safety"><i class="fas fa-shield-halved"></i></div>
        <h4>Safety & Conduct</h4>
        <a href="mailto:safety@ruscab.com" class="support-contact-value">safety@ruscab.com</a>
        <p>Report passenger misconduct, harassment, or route violations.</p>
        <a href="mailto:safety@ruscab.com" class="d-btn d-btn-warning" style="margin-top:var(--d-sp-md);">
            <i class="fas fa-flag"></i> Report Issue
        </a>
    </div>

</div>

<!-- Ticket form -->
<div class="support-form-card">
    <div class="d-card-header">
        <h3><i class="fas fa-ticket"></i> Submit a Support Ticket</h3>
        <span class="text-dim" style="font-size:0.75rem;">Avg. response time: 2–4 hours</span>
    </div>
    <div class="d-card-body">

        <?php if ($ticket_sent): ?>
            <div class="d-alert d-alert-success">
                <i class="fas fa-circle-check"></i>
                Your ticket has been submitted. Our team will respond within 2–4 hours.
            </div>
        <?php else: ?>

        <form action="support.php" method="POST" class="d-form-grid">
            <div class="d-form-group">
                <label>Issue Category</label>
                <select name="category" class="d-select" required>
                    <option value="">Select category...</option>
                    <option value="Payment Issue">Payment / Wallet Issue</option>
                    <option value="Passenger Complaint">Passenger Complaint</option>
                    <option value="App / Technical">App / Technical Issue</option>
                    <option value="Account">Account Problem</option>
                    <option value="Ride Dispute">Ride Dispute</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="d-form-group">
                <label>Related Ride ID <span class="d-form-hint">(if applicable)</span></label>
                <input type="text" name="ride_id" class="d-input" placeholder="e.g. #1042">
            </div>
            <div class="d-form-group span-2">
                <label>Describe Your Issue</label>
                <textarea name="description" class="d-input" rows="5"
                          placeholder="Provide as much detail as possible — what happened, when, and what you need resolved..."
                          required style="resize:vertical;min-height:120px;"></textarea>
            </div>
            <div class="d-form-group span-2">
                <label>Preferred Contact Method</label>
                <select name="contact_pref" class="d-select">
                    <option value="Email">Email</option>
                    <option value="Phone">Phone Call</option>
                    <option value="WhatsApp">WhatsApp</option>
                </select>
            </div>
            <div class="d-form-group span-2">
                <button type="submit" name="submit_ticket" class="d-btn d-btn-primary d-btn-full">
                    <i class="fas fa-paper-plane"></i> Submit Ticket
                </button>
            </div>
        </form>

        <?php endif; ?>
    </div>
</div>

<!-- FAQ strip -->
<div class="d-card" style="margin-top:var(--d-sp-lg);">
    <div class="d-card-header">
        <h3><i class="fas fa-circle-question"></i> Frequently Asked</h3>
    </div>
    <div class="d-card-body" style="display:flex;flex-direction:column;gap:0;">
        <?php
        $faqs = [
            ["When do I get paid?",   "Payouts are processed every Monday directly to your linked bank account. Wallet balances update instantly after each completed ride."],
            ["Why was I suspended?",  "Accounts with more than 3 cancellations in 7 days are temporarily suspended. Contact support to appeal."],
            ["How is my rating calculated?", "Your rating is the average of all passenger star ratings from completed rides. Ratings below 3.5 trigger a review."],
            ["What if a passenger is rude?", "Use the safety@ruscab.com channel immediately. Do not engage — end the ride safely if needed and report via ticket."],
        ];
        foreach ($faqs as $faq): ?>
        <details class="d-faq-item">
            <summary class="d-faq-q">
                <i class="fas fa-chevron-right"></i>
                <?php echo $faq[0]; ?>
            </summary>
            <div class="d-faq-a"><?php echo $faq[1]; ?></div>
        </details>
        <?php endforeach; ?>
    </div>
</div>

<?php include '../driver/includes/footer.php'; ?>