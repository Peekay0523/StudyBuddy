<?php
/**
 * Home Controller
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

class HomeController {
    
    public function index() {
        include __DIR__ . '/../templates/pages/home.php';
    }
}
