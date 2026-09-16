<?php
$pageTitle = $pageTitle ?? 'Apply For Me - Checkout - StudySmart';
$currentPage = $currentPage ?? 'apply-for-me';
$isBursary = !empty($formData['bursary_name'] ?? '');
$isInstitution = !empty($formData['institution_name'] ?? '');
include __DIR__ . '/../layouts/header.php';
?>

<style>
.checkout-card {
    background: white;
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 10px 30px rgba(15,23,42,0.04);
    border: 1px solid #f1f5f9;
}

.checkout-card h2 {
    margin: 0 0 6px 0;
    font-size: 18px;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 10px;
}

.checkout-card .desc {
    color: #64748b;
    margin: 0 0 20px 0;
    font-size: 14px;
    line-height: 1.6;
}

.app-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 0;
    border-bottom: 1px solid #f1f5f9;
}

.app-row:last-child {
    border-bottom: none;
}

.app-choice {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
    color: white;
    flex-shrink: 0;
}

.fee-line {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    font-size: 14px;
    color: #475569;
}

.fee-line.total {
    border-top: 2px solid #e5e7eb;
    margin-top: 8px;
    padding-top: 16px;
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
}

.fee-line.total .amount {
    color: #7c3aed;
    font-size: 24px;
}

.info-box {
    border-radius: 12px;
    padding: 18px 20px;
    display: flex;
    gap: 14px;
    align-items: flex-start;
}

.info-box i {
    font-size: 20px;
    flex-shrink: 0;
    margin-top: 2px;
}

.info-box h4 {
    margin: 0 0 4px 0;
    font-size: 14px;
    font-weight: 700;
}

.info-box p {
    margin: 0;
    font-size: 13px;
    line-height: 1.5;
}

@media (max-width: 640px) {
    .checkout-card { padding: 20px; }
    .app-row { flex-direction: column; align-items: flex-start; gap: 6px; }
    .afm-checkout-actions {
        flex-direction: column !important;
    }
    .afm-checkout-actions a,
    .afm-checkout-actions button {
        width: 100%;
        justify-content: center;
    }
    .info-box {
        flex-direction: column;
        gap: 10px;
    }
    .checkout-grid-2col {
        grid-template-columns: 1fr !important;
    }
    .checkout-header h1 {
        font-size: 22px !important;
    }
    .checkout-header h1 span {
        width: 36px !important;
        height: 36px !important;
        font-size: 16px !important;
    }
}

/* Checkout submit button loading state */
#checkout-submit-btn.loading {
    opacity: 0.7;
    pointer-events: none;
    cursor: not-allowed;
}

#checkout-submit-btn.loading::after {
    content: '';
    width: 18px;
    height: 18px;
    border: 2px solid rgba(255,255,255,0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: afm-spin 0.6s linear infinite;
    margin-left: 8px;
}

@keyframes afm-spin {
    to { transform: rotate(360deg); }
}
</style>

<div style="max-width: 800px; margin: 0 auto;">

    <!-- Header -->
    <div class="checkout-header" style="margin-bottom: 30px;">
        <a href="/apply-for-me" style="color: #8b5cf6; text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 10px;">
            <i class="fas fa-arrow-left"></i> Back to Form
        </a>
        <h1 style="font-size: 28px; margin-bottom: 5px; display: flex; align-items: center; gap: 12px;">
            <span style="width: 44px; height: 44px; background: linear-gradient(135deg, #8b5cf6, #7c3aed); border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; color: white; font-size: 20px;">
                <i class="fas fa-credit-card"></i>
            </span>
            <?php echo $isBursary ? 'Bursary Application Checkout' : ($isInstitution ? 'Institution Application Checkout' : 'Application Checkout'); ?>
        </h1>
        <p style="color: #6b7280; font-size: 15px;">Review your <?php echo $isBursary ? 'bursary' : ($isInstitution ? 'institution' : ''); ?> application details and service fee before submitting.</p>
    </div>

    <!-- Communication Notice -->
    <div class="info-box" style="background: linear-gradient(135deg, #eff6ff, #dbeafe); border: 1px solid #93c5fd; margin-bottom: 24px;">
        <i class="fas fa-comments" style="color: #2563eb;"></i>
        <div>
            <h4 style="color: #1e40af;">How We'll Communicate With You</h4>
            <p style="color: #1e3a5f;">All communication regarding your application will be done through <strong>email</strong> or <strong>WhatsApp</strong>. Please ensure your email address <strong><?php echo htmlspecialchars($formData['email']); ?></strong> is valid and regularly checked. We'll send you updates on your application status, document verification, and payment instructions for university application fees.</p>
        </div>
    </div>

    <!-- Application Summary -->
    <div class="checkout-card" style="margin-bottom: 24px;">
        <h2><i class="fas fa-user-check" style="color: #8b5cf6;"></i> Application Summary</h2>
        <?php if ($isBursary): ?>
            <p class="desc" style="margin-bottom: 12px;">
                <span style="display: inline-flex; align-items: center; gap: 6px; background: linear-gradient(135deg, #ede9fe, #e0e7ff); padding: 6px 14px; border-radius: 8px; font-size: 14px; color: #5b21b6; font-weight: 600;">
                    <i class="fas fa-hand-holding-dollar"></i> Bursary Application
                </span>
            </p>
            <p class="desc" style="margin-bottom: 4px;">Applicant: <strong><?php echo htmlspecialchars($formData['full_name'] . ' ' . $formData['surname']); ?></strong></p>
            <p class="desc">Applying for: <strong style="color: #7c3aed;"><?php echo htmlspecialchars($formData['bursary_name']); ?></strong>
                <?php if (!empty($formData['bursary_provider'])): ?>
                    — Provider: <strong><?php echo htmlspecialchars($formData['bursary_provider']); ?></strong>
                <?php endif; ?>
            </p>
        <?php elseif ($isInstitution): ?>
            <p class="desc" style="margin-bottom: 12px;">
                <span style="display: inline-flex; align-items: center; gap: 6px; background: linear-gradient(135deg, #ede9fe, #e0e7ff); padding: 6px 14px; border-radius: 8px; font-size: 14px; color: #5b21b6; font-weight: 600;">
                    <i class="fas fa-university"></i> Institution Application
                </span>
            </p>
            <p class="desc" style="margin-bottom: 4px;">Applicant: <strong><?php echo htmlspecialchars($formData['full_name'] . ' ' . $formData['surname']); ?></strong></p>
            <p class="desc">Applying to: <strong style="color: #7c3aed;"><?php echo htmlspecialchars($formData['institution_name']); ?></strong>
                <?php if (!empty($formData['institution_type'])): ?>
                    — Type: <strong><?php echo htmlspecialchars($formData['institution_type']); ?></strong>
                <?php endif; ?>
            </p>
        <?php else: ?>
            <p class="desc">Applicant: <strong><?php echo htmlspecialchars($formData['full_name'] . ' ' . $formData['surname']); ?></strong></p>
        <?php endif; ?>

        <div class="checkout-grid-2col" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
            <div>
                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 2px;">Email</div>
                <div style="font-size: 14px; color: #1e293b;"><?php echo htmlspecialchars($formData['email']); ?></div>
            </div>
            <div>
                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 2px;">Phone</div>
                <div style="font-size: 14px; color: #1e293b;"><?php echo htmlspecialchars($formData['phone']); ?></div>
            </div>
            <div>
                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 2px;">School</div>
                <div style="font-size: 14px; color: #1e293b;"><?php echo htmlspecialchars($formData['school_name']); ?></div>
            </div>
            <div>
                <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 2px;">Matric Year</div>
                <div style="font-size: 14px; color: #1e293b;"><?php echo htmlspecialchars($formData['matric_year']); ?></div>
            </div>
        </div>

        <?php if ($isBursary): ?>
        <!-- Bursary Application -->
        <div style="background: linear-gradient(135deg, #f5f3ff, #ede9fe); border-radius: 10px; padding: 16px 20px; margin-top: 10px; border: 1px solid #ddd6fe;">
            <div style="font-size: 13px; font-weight: 600; color: #5b21b6; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.05em;">
                <i class="fas fa-hand-holding-dollar"></i> Bursary Application
            </div>
            <div class="app-row">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span class="app-choice" style="background: #7c3aed;"><i class="fas fa-star" style="font-size: 14px;"></i></span>
                    <div>
                        <div style="font-weight: 600; color: #1e293b; font-size: 15px;"><?php echo htmlspecialchars($formData['bursary_name']); ?></div>
                        <?php if (!empty($formData['bursary_provider'])): ?>
                            <div style="font-size: 13px; color: #64748b;">Provider: <?php echo htmlspecialchars($formData['bursary_provider']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div style="color: #7c3aed; font-weight: 600; font-size: 14px;">R20.00</div>
            </div>
        </div>
        <?php elseif ($isInstitution): ?>
        <!-- Institution Application -->
        <div style="background: linear-gradient(135deg, #f5f3ff, #ede9fe); border-radius: 10px; padding: 16px 20px; margin-top: 10px; border: 1px solid #ddd6fe;">
            <div style="font-size: 13px; font-weight: 600; color: #5b21b6; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.05em;">
                <i class="fas fa-university"></i> Institution Application
            </div>
            <div class="app-row">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span class="app-choice" style="background: #7c3aed;"><i class="fas fa-graduation-cap" style="font-size: 14px;"></i></span>
                    <div>
                        <div style="font-weight: 600; color: #1e293b; font-size: 15px;"><?php echo htmlspecialchars($formData['institution_name']); ?></div>
                        <?php if (!empty($formData['institution_type'])): ?>
                            <div style="font-size: 13px; color: #64748b;">Type: <?php echo htmlspecialchars($formData['institution_type']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div style="color: #7c3aed; font-weight: 600; font-size: 14px;">R20.00</div>
            </div>
        </div>
        <?php else: ?>
        <!-- University Applications -->
        <div style="background: #f8fafc; border-radius: 10px; padding: 16px 20px; margin-top: 10px;">
            <div style="font-size: 13px; font-weight: 600; color: #64748b; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.05em;">
                <i class="fas fa-university"></i> University Applications (<?php echo $universityCount; ?>)
            </div>
            <?php foreach ($applications as $app): ?>
            <div class="app-row">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span class="app-choice" style="background: <?php echo $app['choice'] === '1st' ? '#8b5cf6' : ($app['choice'] === '2nd' ? '#2563eb' : '#059669'); ?>;"><?php echo $app['choice']; ?></span>
                    <div>
                        <div style="font-weight: 600; color: #1e293b; font-size: 15px;"><?php echo htmlspecialchars($app['university']); ?></div>
                        <div style="font-size: 13px; color: #64748b;"><?php echo htmlspecialchars($app['course']); ?></div>
                    </div>
                </div>
                <div style="color: #7c3aed; font-weight: 600; font-size: 14px;">R20.00</div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Service Fee Breakdown -->
    <div class="checkout-card" style="margin-bottom: 24px;">
        <h2><i class="fas fa-receipt" style="color: #8b5cf6;"></i> Service Fee</h2>

        <div style="background: #f8fafc; border-radius: 10px; padding: 16px 20px; margin-bottom: 16px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <span style="font-size: 14px; color: #475569;">Service fee for <?php echo $isBursary ? 'bursary' : ($isInstitution ? 'institution' : 'university'); ?> application assistance</span>
                <span style="font-weight: 600; color: #1e293b;">R20.00</span>
            </div>
            <?php if (!$isBursary && !$isInstitution): ?>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <span style="font-size: 14px; color: #475569;">Number of university applications</span>
                <span style="font-weight: 600; color: #1e293b;">&times; <?php echo $universityCount; ?></span>
            </div>
            <?php endif; ?>
        </div>

        <div class="fee-line total">
            <span>Total Service Fee</span>
            <span class="amount">R<?php echo number_format($totalFee, 2); ?></span>
        </div>

        <div style="margin-top: 16px; font-size: 13px; color: #64748b; line-height: 1.6;">
            <strong>Important:</strong> This is a <strong>service fee</strong> for our application assistance service — it is <strong>not</strong> an application fee.
            <?php if ($isBursary): ?>
                The actual bursary application is free, but our service fee covers the work we do on your behalf. You will receive payment instructions for the service fee via email or WhatsApp after we process your application.
            <?php elseif ($isInstitution): ?>
                The actual institution application fees (if any) are separate and will be communicated to you separately. You will receive payment instructions via email or WhatsApp after we process your application.
            <?php else: ?>
                The actual university application fees (if any) are separate and will be communicated to you separately. You will receive payment instructions for university application fees via email or WhatsApp after we process your application.
            <?php endif; ?>
        </div>
    </div>

    <!-- Banking Details from Settings -->
    <div class="checkout-card" style="margin-bottom: 24px;">
        <h2><i class="fas fa-building-columns" style="color: #0284c7;"></i> Bank Details for Payment</h2>
        <p class="desc">Make your EFT transfer to the following account and use the reference below.</p>

        <div style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); padding: 20px; border-radius: 10px; border-left: 4px solid #0284c7;">
            <div class="checkout-grid-2col" style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-size: 12px; color: #6b7280; margin-bottom: 2px;">Bank Name</label>
                    <div style="font-weight: 600; color: #1e293b;"><?php echo htmlspecialchars($bankingDetails['bank_name']); ?></div>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; color: #6b7280; margin-bottom: 2px;">Account Type</label>
                    <div style="font-weight: 600; color: #1e293b;"><?php echo htmlspecialchars($bankingDetails['account_type']); ?></div>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; color: #6b7280; margin-bottom: 2px;">Account Number</label>
                    <div style="font-weight: 600; color: #1e293b;"><?php echo htmlspecialchars($bankingDetails['account_number']); ?></div>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; color: #6b7280; margin-bottom: 2px;">Branch Code</label>
                    <div style="font-weight: 600; color: #1e293b;"><?php echo htmlspecialchars($bankingDetails['branch_code']); ?></div>
                </div>
            </div>
            <?php if (!empty($bankingDetails['account_holder'])): ?>
            <div style="margin-bottom: 10px;">
                <label style="display: block; font-size: 12px; color: #6b7280; margin-bottom: 2px;">Account Holder</label>
                <div style="font-weight: 600; color: #1e293b;"><?php echo htmlspecialchars($bankingDetails['account_holder']); ?></div>
            </div>
            <?php endif; ?>
            <div style="background: white; padding: 12px; border-radius: 6px;">
                <label style="display: block; font-size: 12px; color: #6b7280; margin-bottom: 2px;">Reference (IMPORTANT)</label>
                <div style="font-weight: 700; color: #dc2626; font-size: 16px;">
                    <?php echo htmlspecialchars(getCurrentUser()['username']); ?>-APP
                </div>
                <p style="font-size: 12px; color: #6b7280; margin: 4px 0 0;">
                    <?php echo htmlspecialchars($bankingDetails['reference_instruction']); ?>
                </p>
            </div>
        </div>
    </div>

    <!-- POPIA Acceptance + Important Notes -->
    <div style="display: grid; gap: 16px; margin-bottom: 30px;">
        <div class="info-box" style="background: #fefce8; border: 1px solid #fde68a;">
            <i class="fas fa-envelope" style="color: #d97706;"></i>
            <div>
                <h4 style="color: #92400e;">Email Verification</h4>
                <p style="color: #92400e;">Please double-check that your email address <strong><?php echo htmlspecialchars($formData['email']); ?></strong> is correct. All application updates, payment instructions, and university responses will be sent to this email.</p>
            </div>
        </div>

        <div class="info-box" style="background: #f0fdf4; border: 1px solid #bbf7d0;">
            <i class="fas fa-whatsapp" style="color: #16a34a;"></i>
            <div>
                <h4 style="color: #166534;">WhatsApp Communication</h4>
                <p style="color: #166534;">We may also reach out to you via WhatsApp at <strong><?php echo htmlspecialchars($formData['phone']); ?></strong> for urgent updates. Make sure your phone number is correct.</p>
            </div>
        </div>

        <div class="info-box" style="background: #f5f3ff; border: 1px solid #ddd6fe;">
            <i class="fas fa-shield-halved" style="color: #7c3aed;"></i>
            <div>
                <h4 style="color: #5b21b6;">What Happens Next?</h4>
                <?php if ($isBursary): ?>
                    <p style="color: #5b21b6;">After submitting, our team will review your documents within 1-2 business days. If your documents are valid, we'll send you payment instructions for the service fee. Once payment is confirmed, we'll submit your bursary application to <strong><?php echo htmlspecialchars($formData['bursary_name']); ?></strong>.</p>
                <?php elseif ($isInstitution): ?>
                    <p style="color: #5b21b6;">After submitting, our team will review your documents within 1-2 business days. If your documents are valid, we'll send you payment instructions for the service fee. Once payment is confirmed, we'll submit your application to <strong><?php echo htmlspecialchars($formData['institution_name']); ?></strong>.</p>
                <?php else: ?>
                    <p style="color: #5b21b6;">After submitting, our team will review your documents within 1-2 business days. If your documents are valid, we'll send you payment instructions for the service fee. Once payment is confirmed, we'll submit your application to the selected universities.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <form method="POST" action="/apply-for-me/process-checkout">
    <!-- POPIA Terms Acceptance -->
    <div class="checkout-card" style="margin-bottom: 24px; border: 2px solid #c4b5fd;">
        <h2><i class="fas fa-shield-halved" style="color: #7c3aed;"></i> POPIA Terms Acceptance</h2>
        
        <div style="background: #f5f3ff; border-radius: 10px; padding: 18px; margin-bottom: 16px; font-size: 13px; color: #5b21b6; line-height: 1.7;">
            <p style="margin: 0 0 10px;"><strong>Protection of Personal Information Act (POPIA) — Consent Declaration</strong></p>
            <p style="margin: 0 0 10px;">
                By submitting this application, I acknowledge and consent to the following:
            </p>
            <ul style="margin: 0; padding-left: 20px;">
                <li style="margin-bottom: 6px;"><strong>Personal Information Collection:</strong> StudySmart will collect and process my personal information (including name, ID number, contact details, academic records, and uploaded documents) for the sole purpose of submitting university and/or bursary applications on my behalf.</li>
                <li style="margin-bottom: 6px;"><strong>Information Sharing:</strong> My personal information and documents may be shared with the selected universities and/or bursary providers as part of the application submission process.</li>
                <li style="margin-bottom: 6px;"><strong>Data Retention:</strong> My personal information will be retained only for as long as necessary to complete the application process and comply with legal obligations.</li>
                <li style="margin-bottom: 6px;"><strong>Communication:</strong> StudySmart may contact me via email or WhatsApp regarding my application status, payment instructions, and university responses.</li>
                <li style="margin-bottom: 6px;"><strong>Data Security:</strong> StudySmart will take reasonable steps to protect my personal information from unauthorised access, loss, or misuse.</li>
                <li style="margin-bottom: 6px;"><strong>Right to Access:</strong> I have the right to request access to my personal information and to request corrections if necessary.</li>
                <li style="margin-bottom: 0;"><strong>Right to Withdraw Consent:</strong> I understand that I may withdraw my consent at any time by contacting StudySmart, subject to any legal or contractual obligations.</li>
            </ul>
        </div>

        <label style="display: flex; align-items: flex-start; gap: 12px; cursor: pointer; padding: 14px; border: 2px solid #e5e7eb; border-radius: 8px; transition: border-color 0.2s;"
               onmouseover="this.style.borderColor='#8b5cf6'" onmouseout="this.style.borderColor='#e5e7eb'">
            <input type="checkbox" name="popia_accept" value="1" required
                   style="width: 20px; height: 20px; margin-top: 2px; accent-color: #7c3aed; flex-shrink: 0;" />
            <div>
                <strong style="color: #1e293b; font-size: 14px;">I have read and accept the POPIA terms</strong>
                <p style="margin: 2px 0 0; font-size: 12px; color: #6b7280;">
                    I consent to StudySmart collecting, processing, and sharing my personal information as described above for the purpose of submitting my university/bursary application.
                </p>
            </div>
        </label>
    </div>

    <!-- Action Buttons -->
    <div style="display: flex; gap: 16px; justify-content: flex-end; flex-wrap: wrap;" class="afm-checkout-actions">
        <a href="/apply-for-me/cancel" onclick="return confirm('Are you sure you want to cancel? You\'ll need to fill out the form again.');" 
           style="padding: 14px 24px; border: 2px solid #e5e7eb; border-radius: 12px; color: #64748b; text-decoration: none; font-weight: 600; font-size: 15px; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; min-height: 48px;">
            <i class="fas fa-times"></i> Cancel
        </a>
        <button type="submit" id="checkout-submit-btn"
                style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: white; border: none; border-radius: 12px; padding: 14px 32px; font-size: 16px; font-weight: 700; cursor: pointer; transition: all 0.2s; box-shadow: 0px 4px 0px 0px #6d28d9; display: inline-flex; align-items: center; gap: 10px; min-height: 48px; -webkit-tap-highlight-color: transparent; touch-action: manipulation;">
            <i class="fas fa-paper-plane"></i> <span>Confirm & Submit Application</span>
        </button>
    </div>
    </form>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var checkoutForm = document.querySelector('form[action="/apply-for-me/process-checkout"]');
        var checkoutBtn = document.getElementById('checkout-submit-btn');
        if (checkoutForm && checkoutBtn) {
            checkoutForm.addEventListener('submit', function() {
                var popiaCheckbox = checkoutForm.querySelector('input[name="popia_accept"]');
                if (popiaCheckbox && !popiaCheckbox.checked) {
                    alert('Please accept the POPIA terms before submitting.');
                    return false;
                }
                checkoutBtn.classList.add('loading');
                checkoutBtn.querySelector('span').textContent = 'Submitting...';
            });
        }
    });
    </script>

</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>