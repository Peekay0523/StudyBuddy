<?php
/**
 * Shared "Edit Tutor Profile" modal.
 * Used by: templates/pages/tutor_dashboard.php and templates/pages/tutor_detail.php
 * Expects: $tutorProfile (tutors row)
 */
$tutorModalBillingType = $tutorProfile['billing_type'] ?? 'hourly';
$tutorModalTeachingMode = $tutorProfile['teaching_mode'] ?? 'contact';
if (!in_array($tutorModalTeachingMode, ['contact', 'hybrid', 'online'], true)) { $tutorModalTeachingMode = 'contact'; }
$tutorModalHasPhoto = !empty($tutorProfile['profile_image']);
?>
<!-- Edit Tutor Profile Modal (shared component) -->
<div id="tutorEditProfileModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,41,0.55);z-index:1000;align-items:center;justify-content:center;backdrop-filter:blur(2px);">
    <div style="background:#fff;padding:30px;border-radius:20px;width:90%;max-width:520px;position:relative;box-shadow:0 30px 60px rgba(30,41,59,0.3);max-height:90vh;overflow-y:auto;">
        <button onclick="document.getElementById('tutorEditProfileModal').style.display='none'" aria-label="Close"
                style="position:absolute;top:16px;right:16px;background:#f1f5f9;border:none;width:34px;height:34px;border-radius:10px;font-size:20px;cursor:pointer;color:#64748b;display:inline-flex;align-items:center;justify-content:center;">&times;</button>
        <h2 style="margin-bottom:6px;color:#1e293b;font-size:22px;"><i class="fas fa-id-card" style="color:#f59e0b;"></i> Edit Tutor Profile</h2>
        <p style="color:#64748b;font-size:13.5px;margin:0 0 22px;">This is the information students see when browsing Available Tutors.</p>
        <form method="POST" action="/tutor/update-profile" enctype="multipart/form-data">
            <div style="margin-bottom:18px;">
                <label style="display:block;margin-bottom:8px;font-weight:600;color:#334155;font-size:13.5px;">Profile Picture</label>
                <div style="display:flex;align-items:center;gap:14px;">
                    <div id="tutorPhotoPreviewWrap" style="width:64px;height:64px;border-radius:16px;overflow:hidden;flex-shrink:0;background:linear-gradient(135deg,#f59e0b,#f97316);display:flex;align-items:center;justify-content:center;color:#fff;font-size:24px;font-weight:700;">
                        <?php if ($tutorModalHasPhoto): ?>
                            <img id="tutorPhotoPreview" src="/tutor/profile-image/<?php echo (int)$tutorProfile['id']; ?>?v=<?php echo urlencode($tutorProfile['profile_image']); ?>"
                                 alt="Current profile picture" style="width:100%;height:100%;object-fit:cover;">
                        <?php else: ?>
                            <span id="tutorPhotoInitial"><?php echo strtoupper(mb_substr($tutorProfile['full_name'] ?? 'T', 0, 1)); ?></span>
                            <img id="tutorPhotoPreview" alt="" style="display:none;width:100%;height:100%;object-fit:cover;">
                        <?php endif; ?>
                    </div>
                    <div style="flex:1;">
                        <label for="tutorProfileImageInput" style="display:inline-flex;align-items:center;gap:8px;padding:9px 16px;background:#eff6ff;color:#1d4ed8;border:1.5px solid #bfdbfe;border-radius:11px;font-size:13px;font-weight:600;cursor:pointer;">
                            <i class="fas fa-camera"></i> <?php echo $tutorModalHasPhoto ? 'Change Picture' : 'Upload Picture'; ?>
                        </label>
                        <input type="file" name="profile_image" id="tutorProfileImageInput" accept=".jpg,.jpeg,.png,.webp,.gif" style="display:none;" onchange="tutorPreviewProfileImage(this)">
                        <small style="color:#94a3b8;display:block;margin-top:6px;">JPG, PNG, WEBP or GIF &middot; Max 5MB. Shown on your tutor card and profile.</small>
                    </div>
                </div>
            </div>
            <div style="margin-bottom:18px;">
                <label style="display:block;margin-bottom:8px;font-weight:600;color:#334155;font-size:13.5px;">Full Name <span style="color:#ef4444;">*</span></label>
                <input type="text" name="full_name" required maxlength="100"
                       value="<?php echo htmlspecialchars($tutorProfile['full_name'] ?? ''); ?>"
                       placeholder="Your full name"
                       style="width:100%;padding:11px 13px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:14px;font-family:inherit;">
            </div>
            <div style="margin-bottom:18px;">
                <label style="display:block;margin-bottom:8px;font-weight:600;color:#334155;font-size:13.5px;">Subjects <span style="color:#ef4444;">*</span></label>
                <input type="text" name="subjects" required maxlength="200"
                       value="<?php echo htmlspecialchars($tutorProfile['subjects'] ?? ''); ?>"
                       placeholder="e.g., Mathematics (Grade 10-12), Physical Science (Grade 10-12), English (Grade 8-12)"
                       style="width:100%;padding:11px 13px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:14px;font-family:inherit;">
                <small style="color:#94a3b8;display:block;margin-top:5px;">Comma-separated list. Include the grade/level in brackets so students know what you offer, e.g. &quot;Mathematics (Grade 10-12)&quot;.</small>
            </div>
            <div style="margin-bottom:18px;">
                <label style="display:block;margin-bottom:8px;font-weight:600;color:#334155;font-size:13.5px;">Bio</label>
                <textarea name="bio" rows="3" placeholder="Tell students about your teaching style and experience..."
                          style="width:100%;padding:11px 13px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:14px;resize:vertical;font-family:inherit;"><?php echo htmlspecialchars($tutorProfile['bio'] ?? ''); ?></textarea>
            </div>
            <div style="margin-bottom:18px;">
                <label style="display:block;margin-bottom:8px;font-weight:600;color:#334155;font-size:13.5px;">Lesson Mode</label>
                <div style="display:flex;gap:10px;">
                    <label id="mode-contact-card" style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;padding:12px 10px;border:1.5px solid #e5e7eb;border-radius:12px;cursor:pointer;background:white;">
                        <input type="radio" name="teaching_mode" value="contact" <?php echo $tutorModalTeachingMode === 'contact' ? 'checked' : ''; ?> style="display:none;" onchange="tutorUpdateTeachingMode()">
                        <i class="fas fa-chalkboard-teacher" style="font-size:18px;color:#6b7280;margin-bottom:4px;display:block;"></i>
                        <div style="font-weight:600;color:#4b5563;font-size:13px;">Contact</div>
                    </label>
                    <label id="mode-hybrid-card" style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;padding:12px 10px;border:1.5px solid #e5e7eb;border-radius:12px;cursor:pointer;background:white;">
                        <input type="radio" name="teaching_mode" value="hybrid" <?php echo $tutorModalTeachingMode === 'hybrid' ? 'checked' : ''; ?> style="display:none;" onchange="tutorUpdateTeachingMode()">
                        <i class="fas fa-random" style="font-size:18px;color:#6b7280;margin-bottom:4px;display:block;"></i>
                        <div style="font-weight:600;color:#4b5563;font-size:13px;">Hybrid</div>
                    </label>
                    <label id="mode-online-card" style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;padding:12px 10px;border:1.5px solid #e5e7eb;border-radius:12px;cursor:pointer;background:white;">
                        <input type="radio" name="teaching_mode" value="online" <?php echo $tutorModalTeachingMode === 'online' ? 'checked' : ''; ?> style="display:none;" onchange="tutorUpdateTeachingMode()">
                        <i class="fas fa-laptop" style="font-size:18px;color:#6b7280;margin-bottom:4px;display:block;"></i>
                        <div style="font-weight:600;color:#4b5563;font-size:13px;">Online</div>
                    </label>
                </div>
                <small style="color:#94a3b8;display:block;margin-top:5px;">Contact = fully in-person &middot; Hybrid = online + in-person &middot; Online = fully online.</small>
            </div>
            <div style="margin-bottom:18px;">
                <label style="display:block;margin-bottom:8px;font-weight:600;color:#334155;font-size:13.5px;">How do you teach?</label>
                <textarea name="teaching_approach" rows="3" placeholder="Describe your teaching approach, methods and what a typical lesson looks like..."
                          style="width:100%;padding:11px 13px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:14px;resize:vertical;font-family:inherit;"><?php echo htmlspecialchars($tutorProfile['teaching_approach'] ?? ''); ?></textarea>
                <small style="color:#94a3b8;display:block;margin-top:5px;">Shown on your public tutor profile under "Teaching Approach".</small>
            </div>
            <div style="margin-bottom:18px;">
                <label style="display:block;margin-bottom:8px;font-weight:600;color:#334155;font-size:13.5px;">Tutoring Company Name <span style="color:#94a3b8;font-weight:400;font-size:12px;">(Optional)</span></label>
                <input type="text" name="company_name" maxlength="100"
                       value="<?php echo htmlspecialchars($tutorProfile['company_name'] ?? ''); ?>"
                       placeholder="e.g., Bright Minds Tutoring"
                       style="width:100%;padding:11px 13px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:14px;font-family:inherit;">
            </div>
            <div style="margin-bottom:18px;">
                <label style="display:block;margin-bottom:8px;font-weight:600;color:#334155;font-size:13.5px;">How do you charge?</label>
                <div style="display:flex;gap:10px;">
                    <label id="billing-hourly-card" style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;padding:12px 10px;border:1.5px solid #e5e7eb;border-radius:12px;cursor:pointer;background:white;">
                        <input type="radio" name="billing_type" value="hourly" <?php echo $tutorModalBillingType !== 'monthly' ? 'checked' : ''; ?> style="display:none;" onchange="tutorUpdateBillingType()">
                        <i class="fas fa-clock" style="font-size:18px;color:#6b7280;margin-bottom:4px;display:block;"></i>
                        <div style="font-weight:600;color:#4b5563;font-size:13px;">Hourly</div>
                    </label>
                    <label id="billing-monthly-card" style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;padding:12px 10px;border:1.5px solid #e5e7eb;border-radius:12px;cursor:pointer;background:white;">
                        <input type="radio" name="billing_type" value="monthly" <?php echo $tutorModalBillingType === 'monthly' ? 'checked' : ''; ?> style="display:none;" onchange="tutorUpdateBillingType()">
                        <i class="fas fa-calendar-alt" style="font-size:18px;color:#6b7280;margin-bottom:4px;display:block;"></i>
                        <div style="font-weight:600;color:#4b5563;font-size:13px;">Monthly</div>
                    </label>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:18px;">
                <div>
                    <label style="display:block;margin-bottom:8px;font-weight:600;color:#334155;font-size:13.5px;"><span id="rate-label">Hourly Rate</span></label>
                    <input type="text" name="rate" id="tutor_rate" maxlength="50"
                           value="<?php echo htmlspecialchars($tutorProfile['hourly_rate'] ?? ''); ?>"
                           placeholder="e.g., R150/hour"
                           style="width:100%;padding:11px 13px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:14px;font-family:inherit;">
                </div>
                <div>
                    <label style="display:block;margin-bottom:8px;font-weight:600;color:#334155;font-size:13.5px;">Phone</label>
                    <input type="text" name="phone" maxlength="30"
                           value="<?php echo htmlspecialchars($tutorProfile['phone'] ?? ''); ?>"
                           placeholder="Optional"
                           style="width:100%;padding:11px 13px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:14px;font-family:inherit;">
                </div>
            </div>
            <div style="margin-bottom:18px;">
                <label style="display:block;margin-bottom:8px;font-weight:600;color:#334155;font-size:13.5px;">Email</label>
                <input type="email" name="email" maxlength="100"
                       value="<?php echo htmlspecialchars($tutorProfile['email'] ?? ''); ?>"
                       placeholder="you@example.com"
                       style="width:100%;padding:11px 13px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:14px;font-family:inherit;">
            </div>
            <div style="margin-bottom:18px;">
                <label style="display:block;margin-bottom:8px;font-weight:600;color:#334155;font-size:13.5px;">Grades / Levels Offered <span style="color:#94a3b8;font-weight:400;font-size:12px;">(Optional)</span></label>
                <input type="text" name="grades_offered" maxlength="200"
                       value="<?php echo htmlspecialchars($tutorProfile['grades_offered'] ?? ''); ?>"
                       placeholder="e.g. Grade 10-12, University first year"
                       style="width:100%;padding:11px 13px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:14px;font-family:inherit;">
                <small style="color:#94a3b8;display:block;margin-top:5px;">Shown on your public tutor profile so students know which grades/levels you teach.</small>
            </div>
            <div style="margin-bottom:18px;">
                <label style="display:block;margin-bottom:8px;font-weight:600;color:#334155;font-size:13.5px;">Tutoring Packages <span style="color:#94a3b8;font-weight:400;font-size:12px;">(Optional)</span></label>
                <textarea name="packages" rows="4" placeholder="Single Session | R150 | One-on-one lesson&#10;Group Session (max 5) | R90 per learner | Small group class&#10;Exam Prep Bundle | R1200/month | 8 sessions + mock exams"
                          style="width:100%;padding:11px 13px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:13.5px;resize:vertical;font-family:inherit;"><?php echo htmlspecialchars($tutorProfile['packages'] ?? ''); ?></textarea>
                <small style="color:#94a3b8;display:block;margin-top:5px;">One package per line, in the format: Name | Price | Description. Lines containing "group" are shown as group sessions; "single" / "one-on-one" as single sessions.</small>
            </div>
            <div style="margin-bottom:22px;">
                <label style="display:block;margin-bottom:8px;font-weight:600;color:#334155;font-size:13.5px;">Our Tutors <span style="color:#94a3b8;font-weight:400;font-size:12px;">(Optional)</span></label>
                <textarea name="company_team" rows="4" placeholder="Thabo Mokoena | BSc Mathematics | Mathematics, Physical Science | Grade 10-12&#10;Lerato Khumalo | BA English | English, History | Grade 8-12"
                          style="width:100%;padding:11px 13px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:13.5px;resize:vertical;font-family:inherit;"><?php echo htmlspecialchars($tutorProfile['company_team'] ?? ''); ?></textarea>
                <small style="color:#94a3b8;display:block;margin-top:5px;">One tutor per line, in the format: Name | Qualifications | Subjects (comma-separated) | Grades. Shown under &quot;Our Tutors&quot; on your public tutor profile.</small>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('tutorEditProfileModal').style.display='none'"
                        style="padding:11px 20px;background:#f1f5f9;color:#64748b;border:none;border-radius:12px;cursor:pointer;font-weight:600;font-family:inherit;font-size:14px;">Cancel</button>
                <button type="submit" style="padding:11px 20px;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;border:none;border-radius:12px;cursor:pointer;font-weight:600;font-family:inherit;font-size:14px;box-shadow:0 8px 18px rgba(102,126,234,.30);"><i class="fas fa-check"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>
<script>
    // Center the modal when opened via display='block' + close on overlay click
    (function () {
        var m = document.getElementById('tutorEditProfileModal');
        if (m) {
            new MutationObserver(function () {
                if (m.style.display === 'block') { m.style.display = 'flex'; }
            }).observe(m, { attributes: true, attributeFilter: ['style'] });
            window.addEventListener('click', function (event) {
                if (event.target === m) { m.style.display = 'none'; }
            });
        }
    })();

    // Live preview of the selected profile picture
    function tutorPreviewProfileImage(input) {
        if (!input.files || !input.files[0]) return;
        var img = document.getElementById('tutorPhotoPreview');
        var initial = document.getElementById('tutorPhotoInitial');
        if (img) {
            img.src = URL.createObjectURL(input.files[0]);
            img.style.display = 'block';
        }
        if (initial) { initial.style.display = 'none'; }
    }

    function tutorUpdateBillingType() {
        var selected = document.querySelector('input[name="billing_type"]:checked');
        if (!selected) return;
        var isHourly = selected.value === 'hourly';
        var hourlyCard = document.getElementById('billing-hourly-card');
        var monthlyCard = document.getElementById('billing-monthly-card');
        var rateLabel = document.getElementById('rate-label');
        var rateInput = document.getElementById('tutor_rate');

        if (hourlyCard && monthlyCard) {
            if (isHourly) {
                hourlyCard.style.borderColor = '#3b82f6'; hourlyCard.style.background = '#eff6ff';
                hourlyCard.querySelector('i').style.color = '#3b82f6'; hourlyCard.querySelector('div').style.color = '#1f2937';
                monthlyCard.style.borderColor = '#e5e7eb'; monthlyCard.style.background = 'white';
                monthlyCard.querySelector('i').style.color = '#6b7280'; monthlyCard.querySelector('div').style.color = '#4b5563';
            } else {
                monthlyCard.style.borderColor = '#3b82f6'; monthlyCard.style.background = '#eff6ff';
                monthlyCard.querySelector('i').style.color = '#3b82f6'; monthlyCard.querySelector('div').style.color = '#1f2937';
                hourlyCard.style.borderColor = '#e5e7eb'; hourlyCard.style.background = 'white';
                hourlyCard.querySelector('i').style.color = '#6b7280'; hourlyCard.querySelector('div').style.color = '#4b5563';
            }
        }
        if (rateLabel) { rateLabel.textContent = isHourly ? 'Hourly Rate' : 'Monthly Rate'; }
        if (rateInput) { rateInput.placeholder = isHourly ? 'e.g., R150/hour' : 'e.g., R2000/month'; }
    }
    tutorUpdateBillingType();

    function tutorUpdateTeachingMode() {
        var selected = document.querySelector('input[name="teaching_mode"]:checked');
        if (!selected) return;
        ['contact', 'hybrid', 'online'].forEach(function (m) {
            var card = document.getElementById('mode-' + m + '-card');
            if (!card) return;
            if (selected.value === m) {
                card.style.borderColor = '#3b82f6'; card.style.background = '#eff6ff';
                card.querySelector('i').style.color = '#3b82f6'; card.querySelector('div').style.color = '#1f2937';
            } else {
                card.style.borderColor = '#e5e7eb'; card.style.background = 'white';
                card.querySelector('i').style.color = '#6b7280'; card.querySelector('div').style.color = '#4b5563';
            }
        });
    }
    tutorUpdateTeachingMode();
</script>
