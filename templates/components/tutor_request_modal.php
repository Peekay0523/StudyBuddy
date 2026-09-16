<?php
/**
 * Tutor request modal component used on the public tutor detail page.
 * Expects $tutor and $subjectsWithGrades to be available in scope.
 */
?>
<div id="tutorRequestModal" class="tp-modal" onclick="if(event.target === this) closeTutorRequestModal();">
    <div class="tp-modal-box">
        <div class="tp-modal-head">
            <h3><i class="fas fa-user-plus" style="color:#667eea;margin-right:8px;"></i>Request Tutor</h3>
            <button type="button" onclick="closeTutorRequestModal()" style="background:none;border:none;font-size:20px;color:#64748b;cursor:pointer;line-height:1;">&times;</button>
        </div>
        <form method="POST" action="/tutor/request/<?php echo $tutor['id']; ?>" id="tutorRequestForm" style="margin:0;" onsubmit="return validateTutorRequestForm()">
            <div class="tp-modal-body" style="display:flex;flex-direction:column;gap:18px;">
                <p style="margin:0;color:var(--sg-muted);font-size:13.5px;line-height:1.6;">Tell us what you need help with. The tutor will review your request and get back to you.</p>

                <!-- Grade / Level -->
                <div>
                    <label class="tp-form-label" for="reqGrade">Grade / Level you need help with</label>
                    <select name="grade" id="reqGrade" required class="tp-form-select">
                        <option value="" disabled selected>Select grade/level</option>
                        <option value="Grade 1-3">Grade 1-3</option>
                        <option value="Grade 4-6">Grade 4-6</option>
                        <option value="Grade 7-9">Grade 7-9</option>
                        <option value="Grade 10">Grade 10</option>
                        <option value="Grade 11">Grade 11</option>
                        <option value="Grade 12">Grade 12</option>
                        <option value="University/College">University/College</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <!-- Subjects -->
                <div>
                    <label class="tp-form-label">Subject(s) you want tutoring in</label>
                    <?php if (!empty($subjectsWithGrades)): ?>
                        <div style="display:flex;flex-direction:column;gap:8px;">
                            <?php foreach ($subjectsWithGrades as $item): ?>
                                <label class="tp-checkbox-row" data-check="subject">
                                    <input type="checkbox" name="subjects[]" value="<?php echo htmlspecialchars($item['name']); ?>" onchange="updateTutorRequestPrice()">
                                    <div>
                                        <div style="font-weight:600;font-size:13.5px;color:var(--sg-ink);"><?php echo htmlspecialchars($item['name']); ?></div>
                                        <?php if ($item['grade'] !== ''): ?>
                                            <div style="font-size:12px;color:var(--sg-muted);"><?php echo htmlspecialchars($item['grade']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <p id="subjectHint" style="margin:8px 0 0;font-size:12px;color:#64748b;"><i class="fas fa-info-circle" style="color:#667eea;margin-right:5px;"></i>Select one or more subjects. Each subject adds to the total price.</p>
                    <?php else: ?>
                        <input type="text" name="subjects[]" placeholder="e.g. Mathematics" class="tp-form-input" required>
                    <?php endif; ?>
                </div>

                <!-- Teaching mode -->
                <div>
                    <label class="tp-form-label">How would you like the lessons?</label>
                    <div style="display:flex;flex-wrap:wrap;gap:10px;">
                        <label class="tp-checkbox-row" style="flex:1;min-width:120px;" data-check="mode">
                            <input type="radio" name="teaching_mode" value="online" checked onchange="updateTutorRequestPrice()">
                            <span style="font-weight:600;font-size:13.5px;color:var(--sg-ink);"><i class="fas fa-laptop" style="color:#667eea;margin-right:6px;"></i>Online</span>
                        </label>
                        <label class="tp-checkbox-row" style="flex:1;min-width:120px;" data-check="mode">
                            <input type="radio" name="teaching_mode" value="contact" onchange="updateTutorRequestPrice()">
                            <span style="font-weight:600;font-size:13.5px;color:var(--sg-ink);"><i class="fas fa-map-marker-alt" style="color:#667eea;margin-right:6px;"></i>In-person (Contact)</span>
                        </label>
                    </div>
                </div>

                <!-- Schedule -->
                <div>
                    <label class="tp-form-label">When are you available?</label>
                    <div style="display:flex;flex-wrap:wrap;gap:10px;">
                        <label class="tp-checkbox-row" style="flex:1;min-width:120px;" data-check="schedule">
                            <input type="radio" name="schedule" value="weekdays" checked>
                            <span style="font-weight:600;font-size:13.5px;color:var(--sg-ink);"><i class="fas fa-calendar-day" style="color:#667eea;margin-right:6px;"></i>Weekdays</span>
                        </label>
                        <label class="tp-checkbox-row" style="flex:1;min-width:120px;" data-check="schedule">
                            <input type="radio" name="schedule" value="weekends">
                            <span style="font-weight:600;font-size:13.5px;color:var(--sg-ink);"><i class="fas fa-calendar-weekend" style="color:#667eea;margin-right:6px;"></i>Weekends</span>
                        </label>
                        <label class="tp-checkbox-row" style="flex:1;min-width:120px;" data-check="schedule">
                            <input type="radio" name="schedule" value="both">
                            <span style="font-weight:600;font-size:13.5px;color:var(--sg-ink);"><i class="fas fa-calendar" style="color:#667eea;margin-right:6px;"></i>Both</span>
                        </label>
                    </div>
                </div>

                <!-- Price estimate -->
                <div class="tp-price-box">
                    <div style="font-size:12.5px;font-weight:600;color:#065f46;margin-bottom:4px;">Estimated Price</div>
                    <div class="price" id="tutorRequestPrice">—</div>
                    <div class="note" id="tutorRequestPriceNote">Select subjects to see an estimate.</div>
                </div>
            </div>
            <div class="tp-modal-foot">
                <button type="button" onclick="closeTutorRequestModal()" style="padding:10px 18px;background:#f1f5f9;color:#64748b;border:none;border-radius:11px;cursor:pointer;font-weight:600;font-family:inherit;font-size:13.5px;">Cancel</button>
                <button type="submit" class="tp-cta" style="padding:10px 22px;min-height:42px;font-size:14px;"><i class="fas fa-paper-plane"></i> Send Request</button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    window.tutorBaseRate = <?php echo is_numeric(preg_replace('/[^\d.]/', '', $tutor['hourly_rate'] ?? '')) && (float) preg_replace('/[^\d.]/', '', $tutor['hourly_rate'] ?? '') > 0 ? (float) preg_replace('/[^\d.]/', '', $tutor['hourly_rate'] ?? '') : 0; ?>;
    window.tutorCurrencySymbol = '<?php echo addslashes($tutor['currency_symbol'] ?? 'R'); ?>';
    window.tutorBillingType = '<?php echo ($tutor['billing_type'] ?? 'hourly') === 'monthly' ? '/month' : '/hour'; ?>';

    function openTutorRequestModal() {
        var m = document.getElementById('tutorRequestModal');
        if (m) { m.style.display = 'flex'; }
        updateTutorRequestPrice();
    }
    window.openTutorRequestModal = openTutorRequestModal;

    function closeTutorRequestModal() {
        var m = document.getElementById('tutorRequestModal');
        if (m) { m.style.display = 'none'; }
    }
    window.closeTutorRequestModal = closeTutorRequestModal;

    function formatMoney(amount) {
        return window.tutorCurrencySymbol + amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    window.updateTutorRequestPrice = function() {
        var checkboxes = document.querySelectorAll('#tutorRequestForm input[name="subjects[]"]:checked');
        var count = checkboxes.length;
        var priceEl = document.getElementById('tutorRequestPrice');
        var noteEl = document.getElementById('tutorRequestPriceNote');

        document.querySelectorAll('#tutorRequestForm label[data-check="subject"]').forEach(function(label) {
            var cb = label.querySelector('input[type="checkbox"]');
            if (cb && cb.checked) { label.classList.add('selected'); }
            else { label.classList.remove('selected'); }
        });

        if (window.tutorBaseRate > 0) {
            if (count === 0) {
                priceEl.textContent = formatMoney(window.tutorBaseRate) + window.tutorBillingType;
                noteEl.textContent = 'Base rate for one subject. Select more subjects to update the estimate.';
            } else {
                var total = window.tutorBaseRate * count;
                priceEl.textContent = formatMoney(total) + window.tutorBillingType;
                noteEl.textContent = count + ' subject' + (count > 1 ? 's' : '') + ' × ' + formatMoney(window.tutorBaseRate) + window.tutorBillingType;
            }
        } else {
            priceEl.textContent = '—';
            noteEl.textContent = 'The tutor has not set a rate. Contact them for pricing.';
        }
    };

    window.validateTutorRequestForm = function() {
        var checkboxes = document.querySelectorAll('#tutorRequestForm input[name="subjects[]"]:checked');
        var textInput = document.querySelector('#tutorRequestForm input[name="subjects[]"][type="text"]');
        if (checkboxes.length === 0 && (!textInput || textInput.value.trim() === '')) {
            alert('Please select at least one subject you want tutoring in.');
            return false;
        }
        return true;
    };

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') { closeTutorRequestModal(); }
    });
})();
</script>


