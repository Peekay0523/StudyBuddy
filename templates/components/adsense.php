<?php
/**
 * Google AdSense Component
 * Displays AdSense ads on SEO pages
 *
 * Usage:
 * - Include this file where you want to show ads
 * - Set $adSlot (top, middle, bottom, sidebar) before including
 * - Configure your AdSense client ID in config.php or environment
 */

// Get AdSense client ID from config/environment
$adsenseClientId = getenv('GOOGLE_ADSENSE_CLIENT_ID') ?: $_ENV['GOOGLE_ADSENSE_CLIENT_ID'] ?? '';
$adsenseEnabled = !empty($adsenseClientId) && (getenv('GOOGLE_ADSENSE_ENABLED') ?: $_ENV['GOOGLE_ADSENSE_ENABLED'] ?? 'true') === 'true';

// Ad slot configurations
$adSlots = [
    'top' => [
        'slot' => '1234567890', // Replace with your actual slot ID
        'format' => 'auto',
        'class' => 'adsense-ad-top'
    ],
    'middle' => [
        'slot' => '2345678901', // Replace with your actual slot ID
        'format' => 'auto',
        'class' => 'adsense-ad-middle'
    ],
    'bottom' => [
        'slot' => '3456789012', // Replace with your actual slot ID
        'format' => 'auto',
        'class' => 'adsense-ad-bottom'
    ],
    'sidebar' => [
        'slot' => '4567890123', // Replace with your actual slot ID
        'format' => 'auto',
        'class' => 'adsense-ad-sidebar'
    ]
];

if (!$adsenseEnabled):
?>
<!-- Google AdSense Disabled -->
<div class="adsense-placeholder">
    <small>Advertisement space (AdSense not configured)</small>
</div>
<?php else: ?>

<!-- Google AdSense -->
<?php
$slot = $adSlot ?? 'top';
$adConfig = $adSlots[$slot] ?? $adSlots['top'];
?>
<div class="adsense-ad-container <?php echo htmlspecialchars($adConfig['class']); ?>">
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=<?php echo htmlspecialchars($adsenseClientId); ?>"
            crossorigin="anonymous"></script>
    <ins class="adsbygoogle"
         style="display:block"
         data-ad-client="<?php echo htmlspecialchars($adsenseClientId); ?>"
         data-ad-slot="<?php echo htmlspecialchars($adConfig['slot']); ?>"
         data-ad-format="<?php echo htmlspecialchars($adConfig['format']); ?>"
         data-full-width-responsive="true"></ins>
    <script>
         (adsbygoogle = window.adsbygoogle || []).push({});
    </script>
</div>

<?php endif; ?>
