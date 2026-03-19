<?php
require_once __DIR__ . '/../../../controllers/AdminController.php';
$adminController = new AdminController();
$bankingDetails = $adminController->getBankingDetails();
include __DIR__ . '/../../layouts/admin_header.php';
?>

<div class="dashboard-overview">
    <h1 class="title"><i class="fas fa-university"></i> Banking Settings</h1>
    <p class="subtitle">Manage EFT banking details displayed to users during checkout</p>
</div>

<div style="max-width: 800px; margin: 30px auto;">

    <?php
    $flash = getFlashMessage();
    if ($flash):
        $alertClass = $flash['type'] === 'success' ? 'alert-success' : ($flash['type'] === 'error' ? 'alert-error' : 'alert-info');
    ?>
        <div class="alert <?php echo $alertClass; ?>" style="padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'error' ? 'exclamation-circle' : 'info-circle'); ?>"></i>
            <?php echo $flash['message']; ?>
        </div>
    <?php endif; ?>

    <div class="feature-card">
        <form method="POST" action="/admin/banking-settings/update">
            <div style="margin-bottom: 25px;">
                <h3 style="margin-bottom: 15px; color: #1f2937;">
                    <i class="fas fa-landmark" style="color: #2563eb;"></i> Bank Account Details
                </h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label for="bank_name" style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">
                            Bank Name <span style="color: #dc2626;">*</span>
                        </label>
                        <input
                            type="text"
                            id="bank_name"
                            name="bank_name"
                            value="<?php echo htmlspecialchars($bankingDetails['bank_name']); ?>"
                            placeholder="e.g., FNB, Standard Bank, Absa"
                            style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 15px;"
                            required
                        >
                        <p style="font-size: 13px; color: #6b7280; margin-top: 5px;">
                            The name of your bank
                        </p>
                    </div>

                    <div>
                        <label for="account_type" style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">
                            Account Type
                        </label>
                        <input
                            type="text"
                            id="account_type"
                            name="account_type"
                            value="<?php echo htmlspecialchars($bankingDetails['account_type']); ?>"
                            placeholder="e.g., Current Account, Savings Account"
                            style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 15px;"
                        >
                    </div>

                    <div>
                        <label for="account_number" style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">
                            Account Number <span style="color: #dc2626;">*</span>
                        </label>
                        <input
                            type="text"
                            id="account_number"
                            name="account_number"
                            value="<?php echo htmlspecialchars($bankingDetails['account_number']); ?>"
                            placeholder="e.g., 62123456789"
                            style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 15px;"
                            required
                        >
                    </div>

                    <div>
                        <label for="branch_code" style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">
                            Branch Code <span style="color: #dc2626;">*</span>
                        </label>
                        <input
                            type="text"
                            id="branch_code"
                            name="branch_code"
                            value="<?php echo htmlspecialchars($bankingDetails['branch_code']); ?>"
                            placeholder="e.g., 250655"
                            style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 15px;"
                            required
                        >
                    </div>
                </div>

                <div style="margin-top: 20px;">
                    <label for="account_holder" style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">
                        Account Holder Name
                    </label>
                    <input
                        type="text"
                        id="account_holder"
                        name="account_holder"
                        value="<?php echo htmlspecialchars($bankingDetails['account_holder']); ?>"
                        placeholder="e.g., StudySmart (Pty) Ltd"
                        style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 15px;"
                    >
                </div>
            </div>

            <div style="margin-bottom: 25px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                <h3 style="margin-bottom: 15px; color: #1f2937;">
                    <i class="fas fa-info-circle" style="color: #2563eb;"></i> Payment Instructions
                </h3>

                <div>
                    <label for="reference_instruction" style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">
                        Reference Instruction
                    </label>
                    <input
                        type="text"
                        id="reference_instruction"
                        name="reference_instruction"
                        value="<?php echo htmlspecialchars($bankingDetails['reference_instruction']); ?>"
                        placeholder="e.g., Use your username and plan (e.g., john-basic)"
                        style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 15px;"
                    >
                    <p style="font-size: 13px; color: #6b7280; margin-top: 5px;">
                        Instructions for what customers should use as payment reference
                    </p>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                    <div>
                        <label for="email_address" style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">
                            Billing Email Address
                        </label>
                        <input
                            type="email"
                            id="email_address"
                            name="email_address"
                            value="<?php echo htmlspecialchars($bankingDetails['email_address']); ?>"
                            placeholder="billing@studysmart.co.za"
                            style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 15px;"
                        >
                        <p style="font-size: 13px; color: #6b7280; margin-top: 5px;">
                            Email for proof of payment submissions
                        </p>
                    </div>

                    <div>
                        <label for="activation_time" style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">
                            Activation Time
                        </label>
                        <input
                            type="text"
                            id="activation_time"
                            name="activation_time"
                            value="<?php echo htmlspecialchars($bankingDetails['activation_time']); ?>"
                            placeholder="e.g., 24-48 hours"
                            style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 15px;"
                        >
                        <p style="font-size: 13px; color: #6b7280; margin-top: 5px;">
                            How long it takes to activate after payment
                        </p>
                    </div>
                </div>
            </div>

            <div style="background: #fef3c7; padding: 15px; border-radius: 8px; border-left: 4px solid #f59e0b; margin-bottom: 25px;">
                <p style="font-size: 14px; color: #92400e; margin: 0;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Important:</strong> These banking details will be displayed to users during the EFT checkout process.
                    Make sure all details are accurate before saving.
                </p>
            </div>

            <div style="display: flex; gap: 15px;">
                <button type="submit" class="btn-primary" style="background: linear-gradient(135deg, #16a34a, #22c55e); padding: 14px 30px; font-size: 16px; font-weight: 600;">
                    <i class="fas fa-save"></i> Save Banking Details
                </button>
                <a href="/admin" style="padding: 14px 30px; background: #f3f4f6; color: #374151; text-decoration: none; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </form>
    </div>

    <div class="feature-card" style="margin-top: 30px;">
        <h3 style="margin-bottom: 15px;">
            <i class="fas fa-eye" style="color: #2563eb;"></i> Preview
        </h3>
        <p style="color: #6b7280; font-size: 14px; margin-bottom: 20px;">
            This is how the banking details will appear to users during checkout:
        </p>

        <div style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); padding: 20px; border-radius: 8px; border-left: 4px solid #0284c7;">
            <h4 style="margin-bottom: 15px; color: #0369a1;">
                <i class="fas fa-university"></i> Bank Details for EFT Transfer
            </h4>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display: block; font-size: 12px; color: #6b7280; margin-bottom: 4px;">Bank Name</label>
                    <div style="font-weight: 600; color: #1f2937;"><?php echo htmlspecialchars($bankingDetails['bank_name']); ?></div>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; color: #6b7280; margin-bottom: 4px;">Account Type</label>
                    <div style="font-weight: 600; color: #1f2937;"><?php echo htmlspecialchars($bankingDetails['account_type']); ?></div>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; color: #6b7280; margin-bottom: 4px;">Account Number</label>
                    <div style="font-weight: 600; color: #1f2937;"><?php echo htmlspecialchars($bankingDetails['account_number']); ?></div>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; color: #6b7280; margin-bottom: 4px;">Branch Code</label>
                    <div style="font-weight: 600; color: #1f2937;"><?php echo htmlspecialchars($bankingDetails['branch_code']); ?></div>
                </div>
            </div>

            <?php if (!empty($bankingDetails['account_holder'])): ?>
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 12px; color: #6b7280; margin-bottom: 4px;">Account Holder</label>
                <div style="font-weight: 600; color: #1f2937;"><?php echo htmlspecialchars($bankingDetails['account_holder']); ?></div>
            </div>
            <?php endif; ?>

            <div style="background: white; padding: 15px; border-radius: 6px; margin-bottom: 15px;">
                <label style="display: block; font-size: 12px; color: #6b7280; margin-bottom: 4px;">Reference (IMPORTANT)</label>
                <div style="font-weight: 700; color: #dc2626; font-size: 15px;">
                    <?php echo htmlspecialchars($bankingDetails['reference_instruction']); ?>
                </div>
            </div>

            <?php if (!empty($bankingDetails['email_address']) || !empty($bankingDetails['activation_time'])): ?>
            <div style="background: #fef3c7; padding: 12px; border-radius: 6px; border-left: 3px solid #f59e0b;">
                <p style="font-size: 13px; color: #92400e; margin: 0;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Important:</strong>
                    <?php if (!empty($bankingDetails['email_address'])): ?>
                        Please email proof of payment to <strong><?php echo htmlspecialchars($bankingDetails['email_address']); ?></strong>.
                    <?php endif; ?>
                    <?php if (!empty($bankingDetails['activation_time'])): ?>
                        Your subscription will be activated within <?php echo htmlspecialchars($bankingDetails['activation_time']); ?> after payment is received.
                    <?php endif; ?>
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/admin_footer.php'; ?>
