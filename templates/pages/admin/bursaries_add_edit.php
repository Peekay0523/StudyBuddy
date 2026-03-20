<?php
include __DIR__ . '/../../layouts/admin_header.php';

$subjectsList = [
    'Mathematics', 'Mathematical Literacy', 'Physical Sciences', 'Life Sciences',
    'English', 'Afrikaans', 'Zulu', 'Xhosa',
    'Accounting', 'Business Studies', 'Economics',
    'History', 'Geography', 'Tourism',
    'Computer Applications Technology', 'Information Technology',
    'Visual Arts', 'Dramatic Arts', 'Music',
    'Agricultural Sciences', 'Consumer Studies'
];
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h1 style="font-size: 28px; margin-bottom: 5px; color: #1f2937;">
            <i class="fas fa-scholarship"></i> 
            <?php echo $isEdit ? 'Edit Bursary' : 'Add New Bursary'; ?>
        </h1>
        <p style="color: #6b7280;">
            <?php echo $isEdit ? 'Update bursary information' : 'Create a new bursary opportunity for students'; ?>
        </p>
    </div>
    <a href="/admin/bursaries" class="btn-secondary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
</div>

<div class="admin-section">
    <form method="POST" action="<?php echo $isEdit ? '/admin/bursaries/update/' . $bursary['id'] : '/admin/bursaries/create'; ?>" style="max-width: 800px;">
        
        <!-- Basic Information -->
        <div style="margin-bottom: 30px;">
            <h3 style="color: #1f2937; margin-bottom: 20px; font-size: 18px;">
                <i class="fas fa-info-circle"></i> Basic Information
            </h3>
            
            <div style="display: grid; gap: 20px;">
                <div>
                    <label for="name" style="display: block; margin-bottom: 8px; font-weight: 500; color: #374151;">
                        Bursary Name <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="text" id="name" name="name" required 
                           value="<?php echo htmlspecialchars($bursary['name'] ?? ''); ?>"
                           placeholder="e.g., Funza Lushaka Bursary"
                           style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                </div>

                <div>
                    <label for="provider" style="display: block; margin-bottom: 8px; font-weight: 500; color: #374151;">
                        Provider/Organization <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="text" id="provider" name="provider" required 
                           value="<?php echo htmlspecialchars($bursary['provider'] ?? ''); ?>"
                           placeholder="e.g., Department of Basic Education"
                           style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                </div>

                <div>
                    <label for="eligibility" style="display: block; margin-bottom: 8px; font-weight: 500; color: #374151;">
                        Eligibility Criteria <span style="color: #ef4444;">*</span>
                    </label>
                    <textarea id="eligibility" name="eligibility" required rows="4"
                              placeholder="Describe who is eligible for this bursary..."
                              style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;"><?php echo htmlspecialchars($bursary['eligibility'] ?? ''); ?></textarea>
                </div>

                <div>
                    <label for="covers" style="display: block; margin-bottom: 8px; font-weight: 500; color: #374151;">
                        What the Bursary Covers
                    </label>
                    <textarea id="covers" name="covers" rows="3"
                              placeholder="e.g., Tuition fees, accommodation, books, meals"
                              style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;"><?php echo htmlspecialchars($bursary['covers'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>

        <!-- Application Details -->
        <div style="margin-bottom: 30px;">
            <h3 style="color: #1f2937; margin-bottom: 20px; font-size: 18px;">
                <i class="fas fa-calendar-alt"></i> Application Details
            </h3>
            
            <div style="display: grid; gap: 20px;">
                <div>
                    <label for="deadline" style="display: block; margin-bottom: 8px; font-weight: 500; color: #374151;">
                        Application Deadline <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="date" id="deadline" name="deadline" required 
                           value="<?php echo htmlspecialchars($bursary['deadline'] ?? ''); ?>"
                           style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                </div>

                <div>
                    <label for="apply_url" style="display: block; margin-bottom: 8px; font-weight: 500; color: #374151;">
                        Application URL
                    </label>
                    <input type="url" id="apply_url" name="apply_url" 
                           value="<?php echo htmlspecialchars($bursary['apply_url'] ?? ''); ?>"
                           placeholder="https://example.com/apply"
                           style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                </div>

                <div>
                    <label for="contact" style="display: block; margin-bottom: 8px; font-weight: 500; color: #374151;">
                        Contact Information
                    </label>
                    <input type="text" id="contact" name="contact" 
                           value="<?php echo htmlspecialchars($bursary['contact'] ?? ''); ?>"
                           placeholder="e.g., info@bursary.co.za or +27 11 123 4567"
                           style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                </div>
            </div>
        </div>

        <!-- Academic Requirements -->
        <div style="margin-bottom: 30px;">
            <h3 style="color: #1f2937; margin-bottom: 20px; font-size: 18px;">
                <i class="fas fa-graduation-cap"></i> Academic Requirements
            </h3>
            
            <div style="display: grid; gap: 20px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label for="min_grade_average" style="display: block; margin-bottom: 8px; font-weight: 500; color: #374151;">
                            Minimum Grade Average (%)
                        </label>
                        <input type="number" id="min_grade_average" name="min_grade_average" 
                               min="0" max="100" step="0.1"
                               value="<?php echo htmlspecialchars($bursary['min_grade_average'] ?? '0'); ?>"
                               style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                    </div>

                    <div>
                        <label for="max_grade_average" style="display: block; margin-bottom: 8px; font-weight: 500; color: #374151;">
                            Maximum Grade Average (%)
                        </label>
                        <input type="number" id="max_grade_average" name="max_grade_average" 
                               min="0" max="100" step="0.1"
                               value="<?php echo htmlspecialchars($bursary['max_grade_average'] ?? '100'); ?>"
                               style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;">
                    </div>
                </div>

                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #374151;">
                        Required Subjects
                    </label>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; padding: 15px; background: #f9fafb; border-radius: 8px;">
                        <?php foreach ($subjectsList as $subject): ?>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 8px; border-radius: 6px; transition: background 0.2s;" onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='transparent'">
                                <input type="checkbox" name="required_subjects[]" value="<?php echo htmlspecialchars($subject); ?>"
                                       <?php echo (isset($bursary['required_subjects']) && in_array($subject, $bursary['required_subjects'])) ? 'checked' : ''; ?>
                                       style="width: 18px; height: 18px; cursor: pointer;">
                                <span style="color: #374151; font-size: 13px;"><?php echo htmlspecialchars($subject); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <p style="margin-top: 8px; font-size: 13px; color: #6b7280;">
                        <i class="fas fa-info-circle"></i> Select subjects that are required for this bursary. Leave empty if no specific subjects are required.
                    </p>
                </div>
            </div>
        </div>

        <!-- Status -->
        <div style="margin-bottom: 30px;">
            <h3 style="color: #1f2937; margin-bottom: 20px; font-size: 18px;">
                <i class="fas fa-toggle-on"></i> Status
            </h3>
            
            <div style="padding: 15px; background: #f9fafb; border-radius: 8px;">
                <label style="display: flex; align-items: center; gap: 12px; cursor: pointer;">
                    <input type="checkbox" name="is_active" value="1"
                           <?php echo !isset($bursary['is_active']) || $bursary['is_active'] ? 'checked' : ''; ?>
                           style="width: 20px; height: 20px; cursor: pointer;">
                    <div>
                        <strong style="color: #374151;">Active</strong>
                        <p style="margin: 4px 0 0 0; font-size: 13px; color: #6b7280;">
                            This bursary will be visible to students
                        </p>
                    </div>
                </label>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div style="display: flex; gap: 10px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
            <button type="submit" class="btn-primary" style="display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-save"></i> <?php echo $isEdit ? 'Update Bursary' : 'Create Bursary'; ?>
            </button>
            <a href="/admin/bursaries" class="btn-secondary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>

    </form>
</div>

<?php include __DIR__ . '/../../layouts/admin_footer.php'; ?>
