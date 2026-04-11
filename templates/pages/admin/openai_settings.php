<?php
include __DIR__ . '/../../layouts/admin_header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
        <h1 style="font-size: 28px; color: #1f2937; margin-bottom: 5px;">
            <i class="fas fa-brain"></i> AI Settings - Hybrid AI Configuration
        </h1>
        <p style="color: #6b7280;">Manage OpenAI and Grok/LLaMA API keys, monitor usage, and configure AI routing</p>
    </div>
    <a href="/admin" class="btn-primary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
</div>

<!-- Hybrid AI Info Banner -->
<div class="feature-card" style="background: linear-gradient(135deg, #10a37f 0%, #2563eb 100%); color: white; margin-bottom: 30px;">
    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
        <i class="fas fa-layer-group" style="font-size: 32px;"></i>
        <div>
            <h3 style="margin: 0; color: white;">
                Hybrid AI System Active
            </h3>
        </div>
    </div>
    <p style="margin: 0; opacity: 0.95; line-height: 1.6;">
        Your application now uses both Grok/LLaMA and OpenAI to optimize costs. 
        Grok/LLaMA handles basic and intermediate tasks, while OpenAI manages complex requests.
        This reduces your OpenAI costs by up to 60-70%.
    </p>
</div>

<!-- AI Model Status Cards -->
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); margin-bottom: 30px;">
    <!-- OpenAI Status -->
    <div class="stat-card" style="border-left: 4px solid #10a37f;">
        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 10px;">
            <div class="icon" style="background: linear-gradient(135deg, #10a37f, #1a7f64);">
                <i class="fas fa-robot"></i>
            </div>
            <div>
                <h4 style="margin: 0; color: #1f2937;">OpenAI (GPT-4o-mini)</h4>
                <span style="color: #6b7280; font-size: 12px;">Advanced Tasks</span>
            </div>
        </div>
        <div style="margin-top: 10px;">
            <p style="margin: 5px 0; font-size: 14px; color: #6b7280;">
                <strong>Status:</strong> 
                <span style="color: <?php echo defined('OPENAI_API_KEY') && OPENAI_API_KEY !== 'YOUR_OPENAI_API_KEY_HERE' ? '#10a37f' : '#ef4444'; ?>">
                    <?php echo defined('OPENAI_API_KEY') && OPENAI_API_KEY !== 'YOUR_OPENAI_API_KEY_HERE' ? '✓ Active' : '✗ Not Configured'; ?>
                </span>
            </p>
            <p style="margin: 5px 0; font-size: 14px; color: #6b7280;">
                <strong>Used for:</strong> Memorandum generation, career recommendations, vision/extraction, SEO content
            </p>
        </div>
    </div>

    <!-- Grok/LLaMA Status -->
    <div class="stat-card" style="border-left: 4px solid #2563eb;">
        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 10px;">
            <div class="icon" style="background: linear-gradient(135deg, #2563eb, #1d4ed8);">
                <i class="fas fa-microchip"></i>
            </div>
            <div>
                <h4 style="margin: 0; color: #1f2937;">Grok/LLaMA</h4>
                <span style="color: #6b7280; font-size: 12px;">Basic & Intermediate Tasks</span>
            </div>
        </div>
        <div style="margin-top: 10px;">
            <p style="margin: 5px 0; font-size: 14px; color: #6b7280;">
                <strong>Status:</strong> 
                <span style="color: <?php echo defined('GROK_API_KEY') && GROK_API_KEY !== 'YOUR_GROK_API_KEY_HERE' ? '#10a37f' : '#ef4444'; ?>">
                    <?php echo defined('GROK_API_KEY') && GROK_API_KEY !== 'YOUR_GROK_API_KEY_HERE' ? '✓ Active' : '✗ Not Configured'; ?>
                </span>
            </p>
            <p style="margin: 5px 0; font-size: 14px; color: #6b7280;">
                <strong>Used for:</strong> Chat assistance, topic analysis, study plans, document processing
            </p>
            <?php if (defined('GROK_PROVIDER')): ?>
            <p style="margin: 5px 0; font-size: 14px; color: #6b7280;">
                <strong>Provider:</strong> <?php echo ucfirst(GROK_PROVIDER); ?>
            </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- AI Routing Configuration -->
<div class="feature-card" style="margin-bottom: 30px;">
    <h3 style="margin-bottom: 15px; color: #1f2937;">
        <i class="fas fa-route"></i> AI Routing Configuration
    </h3>
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f9fafb; border-bottom: 2px solid #e5e7eb;">
                    <th style="padding: 12px; text-align: left; color: #374151;">Complexity Level</th>
                    <th style="padding: 12px; text-align: left; color: #374151;">Assigned Model</th>
                    <th style="padding: 12px; text-align: left; color: #374151;">Example Tasks</th>
                </tr>
            </thead>
            <tbody>
                <tr style="border-bottom: 1px solid #e5e7eb;">
                    <td style="padding: 12px;"><strong>Basic</strong></td>
                    <td style="padding: 12px;">
                        <span style="background: <?php echo defined('AI_BASIC_MODEL') && AI_BASIC_MODEL === 'grok' ? '#d1fae5' : '#fee2e2'; ?>; color: <?php echo defined('AI_BASIC_MODEL') && AI_BASIC_MODEL === 'grok' ? '#065f46' : '#991b1b'; ?>; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: bold;">
                            <?php echo defined('AI_BASIC_MODEL') ? strtoupper(AI_BASIC_MODEL) : 'NOT SET'; ?>
                        </span>
                    </td>
                    <td style="padding: 12px; color: #6b7280;">Simple Q&A, greetings, basic explanations</td>
                </tr>
                <tr style="border-bottom: 1px solid #e5e7eb;">
                    <td style="padding: 12px;"><strong>Intermediate</strong></td>
                    <td style="padding: 12px;">
                        <span style="background: <?php echo defined('AI_INTERMEDIATE_MODEL') && AI_INTERMEDIATE_MODEL === 'grok' ? '#d1fae5' : '#fee2e2'; ?>; color: <?php echo defined('AI_INTERMEDIATE_MODEL') && AI_INTERMEDIATE_MODEL === 'grok' ? '#065f46' : '#991b1b'; ?>; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: bold;">
                            <?php echo defined('AI_INTERMEDIATE_MODEL') ? strtoupper(AI_INTERMEDIATE_MODEL) : 'NOT SET'; ?>
                        </span>
                    </td>
                    <td style="padding: 12px; color: #6b7280;">Study plans, topic analysis, explanations</td>
                </tr>
                <tr>
                    <td style="padding: 12px;"><strong>Advanced</strong></td>
                    <td style="padding: 12px;">
                        <span style="background: <?php echo defined('AI_ADVANCED_MODEL') && AI_ADVANCED_MODEL === 'openai' ? '#d1fae5' : '#fee2e2'; ?>; color: <?php echo defined('AI_ADVANCED_MODEL') && AI_ADVANCED_MODEL === 'openai' ? '#065f46' : '#991b1b'; ?>; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: bold;">
                            <?php echo defined('AI_ADVANCED_MODEL') ? strtoupper(AI_ADVANCED_MODEL) : 'NOT SET'; ?>
                        </span>
                    </td>
                    <td style="padding: 12px; color: #6b7280;">Memorandum generation, career recommendations, SEO content</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Stats Overview -->
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); margin-bottom: 30px;">
    <div class="stat-card">
        <div class="icon" style="background: linear-gradient(135deg, #10a37f, #1a7f64);">
            <i class="fas fa-coins"></i>
        </div>
        <div class="value"><?php echo number_format($stats['total_tokens_used']); ?></div>
        <div class="label">Total Tokens (All Models)</div>
    </div>

    <div class="stat-card">
        <div class="icon" style="background: linear-gradient(135deg, #2563eb, #1d4ed8);">
            <i class="fas fa-robot"></i>
        </div>
        <div class="value"><?php echo number_format($stats['openai_tokens']); ?></div>
        <div class="label">OpenAI Tokens</div>
    </div>

    <div class="stat-card">
        <div class="icon" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
            <i class="fas fa-microchip"></i>
        </div>
        <div class="value"><?php echo number_format($stats['grok_tokens']); ?></div>
        <div class="label">Grok/LLaMA Tokens</div>
    </div>

    <div class="stat-card">
        <div class="icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="value">$<?php echo number_format($stats['estimated_cost'], 4); ?></div>
        <div class="label">Estimated OpenAI Cost</div>
    </div>
</div>

<?php if (isset($stats['estimated_savings']) && $stats['estimated_savings'] > 0): ?>
<div class="feature-card" style="background: linear-gradient(135deg, #10a37f 0%, #059669 100%); color: white; margin-bottom: 30px;">
    <div style="display: flex; align-items: center; gap: 15px;">
        <i class="fas fa-piggy-bank" style="font-size: 28px;"></i>
        <div>
            <h4 style="margin: 0; color: white;">Estimated Savings from Hybrid AI</h4>
            <p style="margin: 5px 0 0 0; opacity: 0.9; font-size: 24px; font-weight: bold;">
                $<?php echo number_format($stats['estimated_savings'], 4); ?>
            </p>
            <p style="margin: 5px 0 0 0; opacity: 0.85; font-size: 14px;">
                By using Grok/LLaMA for basic and intermediate tasks
            </p>
        </div>
    </div>
</div>
<?php endif; ?>

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
        <i class="fas fa-lightbulb"></i> Hybrid AI Cost-Saving Tips
    </h3>
    <ul style="color: #374151; line-height: 2;">
        <li><strong>OpenAI Rate:</strong> GPT-4o-mini costs approximately $0.0000006 per token (about $0.15 per 250,000 tokens)</li>
        <li><strong>Grok/LLaMA Rate:</strong> Typically 50-70% cheaper than OpenAI for comparable tasks</li>
        <li><strong>Smart Routing:</strong> Basic and intermediate tasks automatically use Grok/LLaMA to save costs</li>
        <li><strong>Monitor Usage:</strong> Check the stats above to track token consumption per model</li>
        <li><strong>Optimize Prompts:</strong> Shorter, more focused prompts use fewer tokens</li>
        <li><strong>Configure Routing:</strong> Adjust AI_BASIC_MODEL, AI_INTERMEDIATE_MODEL, and AI_ADVANCED_MODEL in your .env file</li>
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
