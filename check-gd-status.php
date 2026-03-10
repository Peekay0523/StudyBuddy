<?php
/**
 * Check GD Library Status
 * Access: http://localhost:8000/check-gd-status
 */

echo "<h1>GD Library Check</h1>";

if (function_exists('imagecreatefromstring')) {
    echo "<p style='color: green; font-size: 20px;'>✅ GD Library is ENABLED</p>";
    echo "<pre>";
    print_r(gd_info());
    echo "</pre>";
} else {
    echo "<p style='color: red; font-size: 20px;'>❌ GD Library is DISABLED</p>";
    echo "<p>To enable GD library:</p>";
    echo "<ol>";
    echo "<li>Open your php.ini file</li>";
    echo "<li>Find the line: <code>;extension=gd</code></li>";
    echo "<li>Remove the semicolon: <code>extension=gd</code></li>";
    echo "<li>Restart your PHP server</li>";
    echo "</ol>";
    echo "<p><strong>Common php.ini locations:</strong></p>";
    echo "<ul>";
    echo "<li>Windows: C:\\PHP\\php.ini</li>";
    echo "<li>XAMPP: C:\\xampp\\php\\php.ini</li>";
    echo "<li>WAMP: C:\\wamp64\\bin\\php\\php-[version]\\php.ini</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<h3>Other Image Extensions:</h3>";
echo "<ul>";
echo "<li>imagecreatefrompng: " . (function_exists('imagecreatefrompng') ? '✅' : '❌') . "</li>";
echo "<li>imagecreatefromjpeg: " . (function_exists('imagecreatefromjpeg') ? '✅' : '❌') . "</li>";
echo "<li>imagecreatefromgif: " . (function_exists('imagecreatefromgif') ? '✅' : '❌') . "</li>";
echo "<li>imagejpeg: " . (function_exists('imagejpeg') ? '✅' : '❌') . "</li>";
echo "</ul>";

?>
