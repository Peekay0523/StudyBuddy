<?php
$pageTitle = $pageTitle ?? 'Apply For Me - StudySmart';
$currentPage = $currentPage ?? 'apply-for-me';

include __DIR__ . '/../layouts/header.php';

// WhatsApp number (update this to your actual WhatsApp number)
$whatsappNumber = '27630544086'; // Format: country code without + sign (0630544086 → 27630544086)

// Determine what the user is applying for
$applicationTitle = 'University Application';
$applicationSubtitle = '';
if (!empty($prefillBursary)) {
    $applicationTitle = $prefillBursary;
    $applicationSubtitle = !empty($prefillProvider) ? 'Provider: ' . $prefillProvider : '';
} elseif (!empty($prefillInstitution)) {
    $applicationTitle = $prefillInstitution;
    $applicationSubtitle = !empty($prefillType) ? 'Type: ' . $prefillType : '';
}

// Build WhatsApp message
$whatsappMessage = "Hi StudySmart! I'd like help with my application.\n\n";
$whatsappMessage .= "Application: " . $applicationTitle . "\n";
if ($applicationSubtitle) {
    $whatsappMessage .= $applicationSubtitle . "\n";
}
$whatsappMessage .= "\nI understand the service fee is R20.00. Please let me know the next steps and what documents I need to send.";
$whatsappUrl = 'https://wa.me/' . $whatsappNumber . '?text=' . urlencode($whatsappMessage);
?>

<style>
.apply-simple {
    max-width: 680px;
    margin: 0 auto;
}

.apply-card {
    background: white;
    border-radius: 16px;
    padding: 32px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 10px 30px rgba(15,23,42,0.04);
    border: 1px solid #f1f5f9;
    margin-bottom: 20px;
}

.apply-card h3 {
    margin: 0 0 20px 0;
    font-size: 18px;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 10px;
}

.doc-item {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 14px 16px;
    background: #f8fafc;
    border-radius: 10px;
    margin-bottom: 10px;
    border: 1px solid #f1f5f9;
}

.doc-item:last-child {
    margin-bottom: 0;
}

.doc-number {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 700;
    flex-shrink: 0;
}

.doc-info h4 {
    margin: 0 0 2px 0;
    font-size: 15px;
    color: #1e293b;
}

.doc-info p {
    margin: 0;
    font-size: 13px;
    color: #64748b;
}

.price-box {
    background: linear-gradient(135deg, #ede9fe, #e0e7ff);
    border: 2px solid #c4b5fd;
    border-radius: 14px;
    padding: 24px;
    text-align: center;
    margin-bottom: 24px;
}

.price-amount {
    font-size: 36px;
    font-weight: 800;
    color: #7c3aed;
    margin: 8px 0;
}

.price-note {
    font-size: 14px;
    color: #64748b;
    margin: 0;
}

.whatsapp-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    width: 100%;
    padding: 18px 32px;
    background: #25D366;
    color: white;
    border: none;
    border-radius: 14px;
    font-size: 17px;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s;
    box-shadow: 0 4px 0px 0px #1da851;
}

.whatsapp-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 0px 0px #1da851;
    background: #20bd5a;
    color: white;
    text-decoration: none;
}

.whatsapp-btn:active {
    transform: translateY(3px);
    box-shadow: 0 1px 0px 0px #1da851;
}

.whatsapp-btn i {
    font-size: 22px;
}

.how-it-works {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-top: 20px;
}

.step-card {
    text-align: center;
    padding: 16px 12px;
    background: #f8fafc;
    border-radius: 12px;
    border: 1px solid #f1f5f9;
}

.step-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    margin: 0 auto 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.step-card h4 {
    margin: 0 0 4px 0;
    font-size: 14px;
    color: #1e293b;
}

.step-card p {
    margin: 0;
    font-size: 12px;
    color: #64748b;
    line-height: 1.5;
}

@media (max-width: 640px) {
    .apply-card {
        padding: 24px 18px;
    }
    .how-it-works {
        grid-template-columns: 1fr;
    }
    .price-amount {
        font-size: 30px;
    }
    .whatsapp-btn {
        padding: 16px 24px;
        font-size: 16px;
    }
}
</style>

<div class="apply-simple">
    <!-- Page Header -->
    <div style="text-align: center; margin-bottom: 24px;">
        <div style="width: 64px; height: 64px; background: linear-gradient(135deg, #25D366, #128C7E); border-radius: 18px; display: inline-flex; align-items: center; justify-content: center; color: white; font-size: 28px; margin-bottom: 16px;">
            <i class="fas fa-paper-plane"></i>
        </div>
        <h1 style="font-size: 26px; margin: 0 0 8px 0; color: #1e293b;">Apply For Me</h1>
        <p style="color: #64748b; font-size: 15px; margin: 0; line-height: 1.6;">We'll handle your university or bursary application for you.<br>Just send us your documents on WhatsApp!</p>
    </div>

    <!-- Application Being Applied For -->
    <?php if (!empty($prefillBursary) || !empty($prefillInstitution)): ?>
    <div class="apply-card" style="padding: 20px 24px; display: flex; align-items: center; gap: 14px;">
        <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #8b5cf6, #7c3aed); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; flex-shrink: 0;">
            <i class="<?php echo !empty($prefillBursary) ? 'fas fa-hand-holding-dollar' : 'fas fa-university'; ?>"></i>
        </div>
        <div>
            <p style="margin: 0; font-size: 13px; color: #64748b;">You're applying for:</p>
            <p style="margin: 2px 0 0 0; font-weight: 700; color: #1e293b; font-size: 16px;"><?php echo htmlspecialchars($applicationTitle); ?></p>
            <?php if ($applicationSubtitle): ?>
                <p style="margin: 2px 0 0 0; font-size: 13px; color: #64748b;"><?php echo htmlspecialchars($applicationSubtitle); ?></p>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Price -->
    <div class="price-box">
        <div style="font-size: 14px; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em;">Service Fee</div>
        <div class="price-amount">R20.00</div>
        <p class="price-note">One-time fee per application. We handle the entire submission process for you.</p>
    </div>

    <!-- Required Documents -->
    <div class="apply-card">
        <h3>
            <span style="width: 36px; height: 36px; background: #dcfce7; color: #16a34a; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0;">
                <i class="fas fa-file-lines"></i>
            </span>
            Documents You'll Need
        </h3>
        <p style="color: #64748b; font-size: 14px; margin: -10px 0 18px 46px;">Have these ready before contacting us on WhatsApp:</p>

        <div class="doc-item">
            <div class="doc-number">1</div>
            <div class="doc-info">
                <h4><i class="fas fa-id-card" style="color: #8b5cf6; margin-right: 6px;"></i> South African ID Copy</h4>
                <p>Clear photo or scan of your ID document (both sides)</p>
            </div>
        </div>

        <div class="doc-item">
            <div class="doc-number">2</div>
            <div class="doc-info">
                <h4><i class="fas fa-certificate" style="color: #f59e0b; margin-right: 6px;"></i> Matric Certificate</h4>
                <p>Your National Senior Certificate or latest results</p>
            </div>
        </div>

        <div class="doc-item">
            <div class="doc-number">3</div>
            <div class="doc-info">
                <h4><i class="fas fa-home" style="color: #10b981; margin-right: 6px;"></i> Proof of Residence</h4>
                <p>Utility bill, bank statement, or official letter showing your address</p>
            </div>
        </div>

        <div class="doc-item">
            <div class="doc-number">4</div>
            <div class="doc-info">
                <h4><i class="fas fa-file-alt" style="color: #3b82f6; margin-right: 6px;"></i> Grade 12 Results</h4>
                <p>Your latest report card or matric results statement</p>
            </div>
        </div>
    </div>

    <!-- How It Works -->
    <div class="apply-card">
        <h3>
            <span style="width: 36px; height: 36px; background: #dbeafe; color: #2563eb; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0;">
                <i class="fas fa-list-check"></i>
            </span>
            How It Works
        </h3>

        <div class="how-it-works">
            <div class="step-card">
                <div class="step-icon" style="background: #ede9fe; color: #7c3aed;">
                    <i class="fab fa-whatsapp"></i>
                </div>
                <h4>1. Contact Us</h4>
                <p>Tap the WhatsApp button below to start your application</p>
            </div>
            <div class="step-card">
                <div class="step-icon" style="background: #dcfce7; color: #16a34a;">
                    <i class="fas fa-paperclip"></i>
                </div>
                <h4>2. Send Documents</h4>
                <p>Send your required documents and personal details via WhatsApp</p>
            </div>
            <div class="step-card">
                <div class="step-icon" style="background: #fef3c7; color: #d97706;">
                    <i class="fas fa-check-double"></i>
                </div>
                <h4>3. We Submit</h4>
                <p>We complete and submit your application, then keep you updated</p>
            </div>
        </div>
    </div>

    <!-- WhatsApp Contact Button -->
    <a href="<?php echo $whatsappUrl; ?>" target="_blank" class="whatsapp-btn" id="whatsapp-contact-btn">
        <i class="fab fa-whatsapp"></i>
        <span>Contact Us on WhatsApp</span>
    </a>

    <p style="text-align: center; font-size: 13px; color: #94a3b8; margin-top: 16px; line-height: 1.6;">
        Our team will respond within a few minutes during business hours.<br>
        Payment details will be shared on WhatsApp.
    </p>
</div>

<script>
// Track the WhatsApp click
document.getElementById('whatsapp-contact-btn').addEventListener('click', function() {
    // Optional: send analytics event
    if (typeof gtag !== 'undefined') {
        gtag('event', 'whatsapp_contact', { 'application_type': '<?php echo !empty($prefillBursary) ? 'bursary' : 'university'; ?>' });
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>