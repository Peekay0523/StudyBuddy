<?php
$pageTitle = 'Track Progress - StudySmart';
$currentPage = 'track-progress';
include __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard-header-grid" style="display: grid; grid-template-columns: 1fr auto; gap: 30px; align-items: center; margin-bottom: 30px;">
    <div>
        <h1 class="title" style="margin-bottom: 10px;">
            Academic <span>Progress</span> Tracking
        </h1>
        <p class="subtitle" style="margin: 0;">
            A detailed subject-by-subject breakdown of your child's mastered topics and learning milestones.
        </p>
    </div>
</div>

<!-- Activity & Hours Section -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-bottom: 30px;">
    <!-- Activity Graph -->
    <div style="background: white; padding: 30px; border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="font-size: 20px; color: #1e293b; margin: 0; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-chart-area" style="color: #6366f1;"></i> Weekly Activity (Minutes)
            </h2>
            <div style="display: flex; align-items: center; gap: 10px; padding: 8px 16px; border-radius: 30px; background: <?php echo $activityStatus['bg']; ?>; color: <?php echo $activityStatus['color']; ?>; border: 1px solid <?php echo $activityStatus['color']; ?>22; font-weight: 700; font-size: 14px;">
                <i class="fas <?php echo $activityStatus['icon']; ?>"></i>
                <?php echo $activityStatus['label']; ?>
            </div>
        </div>
        <div style="height: 300px; width: 100%;">
            <canvas id="activityChart"></canvas>
        </div>
    </div>

    <!-- Hours Spent Card -->
    <div style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color: white; padding: 30px; border-radius: 16px; box-shadow: 0 10px 20px rgba(99, 102, 241, 0.2); display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center;">
        <div style="width: 70px; height: 70px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
            <i class="fas fa-clock" style="font-size: 32px;"></i>
        </div>
        <div style="font-size: 14px; opacity: 0.9; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">Weekly Time Spent</div>
        <div style="font-size: 64px; font-weight: 800; margin: 10px 0; line-height: 1;"><?php echo $weeklyHours; ?></div>
        <div style="font-size: 18px; font-weight: 500; opacity: 0.9;">Hours Total</div>
        <div style="margin-top: 20px; padding: 10px 20px; background: rgba(255,255,255,0.1); border-radius: 30px; font-size: 13px;">
            <i class="fas fa-arrow-up"></i> 12% from last week
        </div>
    </div>
</div>

<!-- Track Progress Detailed Section -->
<div style="background: white; padding: 40px; border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 30px;">
    <h2 style="font-size: 24px; color: #1e293b; margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-chart-line" style="color: #3b82f6;"></i> Mastery Overview
    </h2>

    <?php if (empty($masteredTopicsSummary)): ?>
        <div style="text-align: center; padding: 60px 20px;">
            <i class="fas fa-graduation-cap" style="font-size: 64px; color: #e2e8f0; margin-bottom: 20px;"></i>
            <h3 style="color: #64748b;">No Progress Data Yet</h3>
            <p style="color: #94a3b8; max-width: 400px; margin: 10px auto;">Once your child starts uploading scripts and creating study plans, their progress will appear here.</p>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px;">
            <?php foreach ($masteredTopicsSummary as $subject => $count): ?>
                <div style="background: #ffffff; padding: 25px; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,0.02); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                        <div style="width: 45px; height: 45px; background: #eff6ff; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #3b82f6;">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <div style="background: #f0fdf4; color: #16a34a; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase;">
                            Active
                        </div>
                    </div>
                    
                    <h3 style="font-size: 20px; font-weight: 700; color: #1e293b; margin-bottom: 10px;"><?php echo htmlspecialchars($subject); ?></h3>
                    
                    <div style="background: #f8fafc; padding: 15px; border-radius: 12px; display: flex; align-items: center; gap: 15px;">
                        <div style="font-size: 28px; font-weight: 800; color: #3b82f6;"><?php echo $count; ?></div>
                        <div style="line-height: 1.2;">
                            <div style="font-size: 14px; font-weight: 600; color: #475569;">Topics</div>
                            <div style="font-size: 12px; color: #94a3b8;">Mastered Successfully</div>
                        </div>
                    </div>

                    <div style="margin-top: 20px; height: 8px; background: #f1f5f9; border-radius: 4px; overflow: hidden;">
                        <?php 
                        // Arbitrary goal of 20 topics per subject for visual progress
                        $progressWidth = min(100, ($count / 20) * 100); 
                        ?>
                        <div style="width: <?php echo $progressWidth; ?>%; height: 100%; background: linear-gradient(90deg, #3b82f6, #60a5fa); border-radius: 4px;"></div>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-top: 8px; font-size: 11px; color: #94a3b8; font-weight: 500;">
                        <span>Current Mastery</span>
                        <span>Level <?php echo floor($count/5) + 1; ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
    <div style="background: #f8fafc; padding: 30px; border-radius: 16px; border-left: 5px solid #10b981;">
        <h4 style="margin: 0 0 10px 0; color: #065f46; font-size: 18px;"><i class="fas fa-info-circle"></i> How to Read This</h4>
        <p style="margin: 0; color: #374151; font-size: 14px; line-height: 1.6;">
            Each card represents a subject your child is studying. The "Topics Mastered" count increases every time the AI successfully processes a script and extracts key educational concepts. A higher count indicates deeper engagement and coverage of the curriculum.
        </p>
    </div>

    <div style="background: #f8fafc; padding: 30px; border-radius: 16px; border-left: 5px solid #f59e0b;">
        <h4 style="margin: 0 0 10px 0; color: #92400e; font-size: 18px;"><i class="fas fa-lightbulb"></i> Support Tip</h4>
        <p style="margin: 0; color: #374151; font-size: 14px; line-height: 1.6;">
            If you notice a subject has fewer topics mastered than others, encourage your child to upload their notes for that subject. Our AI can help summarize and simplify difficult sections to boost their progress.
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('activityChart').getContext('2d');
    
    // Gradient for the chart
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(99, 102, 241, 0.5)');
    gradient.addColorStop(1, 'rgba(99, 102, 241, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($activityLabels); ?>,
            datasets: [{
                label: 'Activity (Minutes)',
                data: <?php echo json_encode($activityData); ?>,
                borderColor: '#6366f1',
                borderWidth: 3,
                backgroundColor: gradient,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#6366f1',
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#1e293b',
                    padding: 12,
                    titleFont: { size: 14, weight: 'bold' },
                    bodyFont: { size: 13 },
                    cornerRadius: 8,
                    displayColors: false
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#64748b',
                        font: { size: 12, weight: '500' }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#f1f5f9'
                    },
                    ticks: {
                        color: '#64748b',
                        font: { size: 12 }
                    }
                }
            }
        }
    });
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
