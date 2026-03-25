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

    /**
     * Show the landing page
     * This is the main landing page for visitors
     */
    public function landing() {
        include __DIR__ . '/../templates/pages/home.php';
    }
}
