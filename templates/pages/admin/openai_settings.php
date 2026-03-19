<?php
include __DIR__ . '/../../layouts/admin_header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
        <h1 style="font-size: 28px; color: #1f2937; margin-bottom: 5px;">
            <i class="fas fa-brain"></i> OpenAI API Settings
        </h1>
        <p style="color: #6b7280;">Manage your OpenAI API credits and monitor usage</p>
    </div>
    <a href="/admin" class="btn-primary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
</div>

<!-- Stats Overview -->
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); margin-bottom: 30px;">
    <div class="stat-card">
        <div class="icon" style="background: linear-gradient(135deg, #10a37f, #1a7f64);">
            <i class="fas fa-coins"></i>
        </div>
        <div class="value"><?php echo number_format($stats['total_tokens_used']); ?></div>
        <div class="label">Total Tokens Used</div>
    </div>

    <div class="stat-card">
        <div class="icon" style="background: linear-gradient(135deg, #2563eb, #1d4ed8);">
            <i class="fas fa-calendar"></i>
        </div>
        <div class="value"><?php echo number_format($stats['tokens_this_month']); ?></div>
        <div class="label">Tokens This Month</div>
    </div>

    <div class="stat-card">
        <div class="icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
            <i class="fas fa-robot"></i>
        </div>
        <div class="value"><?php echo number_format($stats['total_api_calls']); ?></div>
        <div class="label">Total API Calls</div>
    </div>

    <div class="stat-card">
        <div class="icon" style="background: linear-gradient(135deg, #6b7280, #4b5563);">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="value">$<?php echo number_format($stats['estimated_cost'], 4); ?></div>
        <div class="label">Estimated Cost</div>
    </div>
</div>

<!-- Add Credits Card -->
<div class="feature-card" style="background: linear-gradient(135deg, #10a37f 0%, #1a7f64 100%); color: white; margin-bottom: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
        <div>
            <h3 style="margin: 0 0 10px 0; color: white;">
                <i class="fas fa-plus-circle"></i> Add OpenAI Credits
            </h3>
            <p style="margin: 0; opacity: 0.9;">
                Running low on tokens? Add credits to your OpenAI account to continue using AI features.
            </p>
        </div>
        <a href="https://platform.openai.com/account/billing" target="_blank" class="btn-cta" style="background: white; color: #10a37f; padding: 15px 30px; border-radius: 30px; text-decoration: none; font-weight: bold; display: inline-flex; align-items: center; gap: 10px; white-space: nowrap;">
            <i class="fas fa-external-link-alt"></i> Add Credits on OpenAI
        </a>
    </div>
</div>

<!-- API Key Information -->
<div class="feature-card" style="margin-bottom: 30px;">
    <h3 style="margin-bottom: 15px; color: #1f2937;">
        <i class="fas fa-key"></i> API Key Configuration
    </h3>
    <div style="background: #f9fafb; padding: 20px; border-radius: 8px; border-left: 4px solid #10a37f;">
        <p style="margin: 0 0 15px 0; color: #374151;">
            Your OpenAI API key is configured in the <code>.env</code> file in your project root.
        </p>
        <div style="background: #1f2937; color: #10a37f; padding: 15px; border-radius: 6px; font-family: monospace; font-size: 14px; overflow-x: auto;">
            OPENAI_API_KEY=sk-proj-xxxxxxxxxxxxxxxxxxxxxxxx
        </div>
        <p style="margin: 15px 0 0 0; color: #6b7280; font-size: 14px;">
            <i class="fas fa-info-circle"></i> To update your API key, edit the <code>.env</code> file and restart your web server.
        </p>
    </div>
</div>

<!-- Usage Tips -->
<div class="feature-card" style="margin-bottom: 30px;">
    <h3 style="margin-bottom: 15px; color: #1f2937;">
        <i class="fas fa-lightbulb"></i> Cost-Saving Tips
    </h3>
    <ul style="color: #374151; line-height: 2;">
        <li><strong>Current Rate:</strong> GPT-4o-mini costs approximately $0.0000006 per token (about $0.15 per 250,000 tokens)</li>
        <li><strong>Monitor Usage:</strong> Check this dashboard regularly to track your token consumption</li>
        <li><strong>Optimize Prompts:</strong> Shorter, more focused prompts use fewer tokens</li>
        <li><strong>Set Limits:</strong> Consider implementing usage limits per user to prevent abuse</li>
        <li><strong>Free Tier:</strong> OpenAI provides a small free tier for testing ($5 credit for new accounts)</li>
    </ul>
</div>

<!-- Recent Usage Table -->
<div class="feature-card">
    <h3 style="margin-bottom: 15px; color: #1f2937;">
        <i class="fas fa-history"></i> Recent API Usage
    </h3>
    <?php if (empty($recentUsage)): ?>
        <p style="color: #6b7280; text-align: center; padding: 40px;">
            <i class="fas fa-inbox" style="font-size: 48px; opacity: 0.5;"></i><br><br>
            No usage data yet. Usage will be tracked after the openai_usage_logs table is created.
        </p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date/Time</th>
                        <th>User</th>
                        <th>Prompt Tokens</th>
                        <th>Completion Tokens</th>
                        <th>Total Tokens</th>
                        <th>Est. Cost</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentUsage as $usage): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($usage['created_at']); ?></td>
                            <td><?php echo htmlspecialchars($usage['username'] ?? 'System'); ?></td>
                            <td><?php echo number_format($usage['prompt_tokens']); ?></td>
                            <td><?php echo number_format($usage['completion_tokens']); ?></td>
                            <td><strong><?php echo number_format($usage['total_tokens']); ?></strong></td>
                            <td>$<?php echo number_format($usage['total_tokens'] * 0.0000006, 6); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../layouts/admin_footer.php'; ?>
