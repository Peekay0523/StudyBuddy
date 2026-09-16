<?php
/**
 * SearchController - Smart Search Assistant (Ask App)
 * Provides application-wide search across pages, scripts, documents, and more.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

class SearchController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get the full searchable index
     */
    private function getSearchIndex() {
        $index = [];

        // 1. Static pages & routes
        $staticPages = [
            ['title' => 'Home', 'url' => '/', 'keywords' => 'home landing main page start', 'category' => 'Pages', 'icon' => 'fas fa-home'],
            ['title' => 'Dashboard', 'url' => '/dashboard', 'keywords' => 'dashboard profile overview my account', 'category' => 'Pages', 'icon' => 'fas fa-chart-line'],
            ['title' => 'Upload Script', 'url' => '/upload-script', 'keywords' => 'upload script paper memorandum essay upload document', 'category' => 'Pages', 'icon' => 'fas fa-file-upload'],
            ['title' => 'Browse Scripts', 'url' => '/browse-scripts/12', 'keywords' => 'browse scripts papers grade 12 grade 11 grade 10 past papers documents', 'category' => 'Pages', 'icon' => 'fas fa-folder-open'],
            ['title' => 'Study Plan', 'url' => '/study-plan', 'keywords' => 'study plan timetable schedule revision plan calendar', 'category' => 'Pages', 'icon' => 'fas fa-calendar-alt'],
            ['title' => 'Study Group', 'url' => '/study-group', 'keywords' => 'study group collaborate friends group chat share', 'category' => 'Pages', 'icon' => 'fas fa-users'],
            ['title' => 'Scan to PDF', 'url' => '/scan', 'keywords' => 'scan pdf camera photograph document converter', 'category' => 'Pages', 'icon' => 'fas fa-camera'],
            ['title' => 'Upload Report Card', 'url' => '/upload-report-card', 'keywords' => 'report card upload grades academic results matric', 'category' => 'Pages', 'icon' => 'fas fa-graduation-cap'],
            ['title' => 'Career Recommendations', 'url' => '/upload-report-card', 'keywords' => 'career recommendations jobs professions future career guidance', 'category' => 'Pages', 'icon' => 'fas fa-bullseye'],
            ['title' => 'AI Chat', 'url' => '/ai-chat', 'keywords' => 'ai chat assistant artificial intelligence help ask question bot', 'category' => 'Pages', 'icon' => 'fas fa-robot'],
            ['title' => 'Simulate', 'url' => '/simulate', 'keywords' => 'simulate simulation exam test practice mock', 'category' => 'Pages', 'icon' => 'fas fa-vial'],
            ['title' => 'Data Sheets', 'url' => '/data-sheets', 'keywords' => 'data sheets reference cheat sheet formulas tables', 'category' => 'Pages', 'icon' => 'fas fa-file-lines'],
            ['title' => 'Subscription', 'url' => '/subscription', 'keywords' => 'subscription plan pricing premium basic free upgrade payment', 'category' => 'Pages', 'icon' => 'fas fa-crown'],
            ['title' => 'Apply For Me', 'url' => '/apply-for-me', 'keywords' => 'apply university bursary application form submit apply for me', 'category' => 'Pages', 'icon' => 'fas fa-paper-plane'],
            ['title' => 'Login', 'url' => '/login', 'keywords' => 'login sign in account access', 'category' => 'Pages', 'icon' => 'fas fa-sign-in-alt'],
            ['title' => 'Register', 'url' => '/register', 'keywords' => 'register sign up create account new', 'category' => 'Pages', 'icon' => 'fas fa-user-plus'],
        ];

        foreach ($staticPages as $page) {
            $index[] = array_merge($page, ['type' => 'page', 'priority' => 10]);
        }

        // 2. Subjects / Labs
        $subjects = [
            ['title' => 'Mathematics Lab', 'url' => '/math', 'keywords' => 'math mathematics maths calculator algebra geometry trigonometry calculus equations', 'category' => 'Subjects', 'icon' => 'fas fa-square-root-variable'],
            ['title' => 'Physics Lab', 'url' => '/physics', 'keywords' => 'physics science mechanics electricity magnetism waves optics', 'category' => 'Subjects', 'icon' => 'fas fa-atom'],
            ['title' => 'Chemistry Lab', 'url' => '/chemistry', 'keywords' => 'chemistry chemical reactions elements periodic table organic', 'category' => 'Subjects', 'icon' => 'fas fa-flask'],
        ];

        foreach ($subjects as $subj) {
            $index[] = array_merge($subj, ['type' => 'subject', 'priority' => 20]);
        }

        // 2a. Physics Lab Topics (specific simulations)
        $physicsTopics = [
            ['title' => 'Simple Pendulum', 'url' => '/physics#pendulum', 'keywords' => 'simple pendulum oscillation gravity period frequency amplitude harmonic motion', 'category' => 'Physics Lab', 'icon' => 'fas fa-dot-circle'],
            ['title' => 'Free Fall', 'url' => '/physics#freefall', 'keywords' => 'free fall gravity acceleration dropping object weight', 'category' => 'Physics Lab', 'icon' => 'fas fa-arrow-down'],
            ['title' => 'Projectile Motion', 'url' => '/physics#projectile', 'keywords' => 'projectile motion trajectory launch angle velocity 2d kinematics parabola', 'category' => 'Physics Lab', 'icon' => 'fas fa-rocket'],
            ['title' => 'Doppler Effect', 'url' => '/physics#doppler', 'keywords' => 'doppler effect sound waves frequency shift pitch movement source observer redshift blueshift', 'category' => 'Physics Lab', 'icon' => 'fas fa-wave-square'],
            ['title' => "Newton's 1st Law", 'url' => '/physics#newton1', 'keywords' => "newton first law inertia rest motion friction force net force balanced", 'category' => 'Physics Lab', 'icon' => 'fas fa-square'],
            ['title' => "Newton's 2nd Law", 'url' => '/physics#newton2', 'keywords' => "newton second law force mass acceleration F=ma net force", 'category' => 'Physics Lab', 'icon' => 'fas fa-weight-hanging'],
            ['title' => "Newton's 3rd Law", 'url' => '/physics#newton3', 'keywords' => "newton third law action reaction equal opposite forces collision", 'category' => 'Physics Lab', 'icon' => 'fas fa-exchange-alt'],
            ['title' => 'Gravitation', 'url' => '/physics#gravity', 'keywords' => 'gravitation newton universal gravitation gravitational force mass attraction planet orbit', 'category' => 'Physics Lab', 'icon' => 'fas fa-globe'],
            ['title' => "Coulomb's Law", 'url' => '/physics#coulomb', 'keywords' => "coulomb law electrostatic force charges positive negative attraction repulsion electric", 'category' => 'Physics Lab', 'icon' => 'fas fa-bolt'],
        ];

        foreach ($physicsTopics as $topic) {
            $index[] = array_merge($topic, ['type' => 'topic', 'priority' => 25]);
        }

        // 2b. Math Lab Topics (specific sections)
        $mathTopics = [
            ['title' => 'Euclidean Geometry', 'url' => '/math#geometry', 'keywords' => 'euclidean geometry circle theorems angles segments cyclic quadrilateral semicircle', 'category' => 'Math Lab', 'icon' => 'fas fa-shapes'],
            ['title' => 'Trigonometry Lab', 'url' => '/math#trigonometry', 'keywords' => 'trigonometry sine cosine tangent waves functions graphs logarithmic', 'category' => 'Math Lab', 'icon' => 'fas fa-wave-square'],
            ['title' => 'Algebra & Graphs Lab', 'url' => '/math#algebra', 'keywords' => 'algebra graphs linear quadratic parabola hyperbola exponential straight line functions', 'category' => 'Math Lab', 'icon' => 'fas fa-chart-line'],
        ];

        foreach ($mathTopics as $topic) {
            $index[] = array_merge($topic, ['type' => 'topic', 'priority' => 25]);
        }

        // 2c. Chemistry Lab Topics (specific sections)
        $chemistryTopics = [
            ['title' => 'Element Viewer', 'url' => '/chemistry#elements', 'keywords' => 'element viewer periodic table bohr model atomic structure hydrogen helium carbon oxygen', 'category' => 'Chemistry Lab', 'icon' => 'fas fa-atom'],
            ['title' => 'Organic Chemistry Lab', 'url' => '/chemistry#organic', 'keywords' => 'organic chemistry hydrocarbons alkanes alkenes alkynes carbon compounds bonds molecular', 'category' => 'Chemistry Lab', 'icon' => 'fas fa-flask'],
            ['title' => 'Common Reactions', 'url' => '/chemistry#reactions', 'keywords' => 'chemical reactions types synthesis decomposition combustion displacement', 'category' => 'Chemistry Lab', 'icon' => 'fas fa-fire'],
            ['title' => 'Molecular Reaction Simulator', 'url' => '/chemistry#molecular', 'keywords' => 'molecular reaction simulator animation h2o hcl nh3 co2 nacl water', 'category' => 'Chemistry Lab', 'icon' => 'fas fa-atom'],
        ];

        foreach ($chemistryTopics as $topic) {
            $index[] = array_merge($topic, ['type' => 'topic', 'priority' => 25]);
        }

        // 3. SEO Pages from database
        try {
            $seoPages = $this->db->query("SELECT title, slug, meta_description FROM seo_pages WHERE is_published = 1")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($seoPages as $page) {
                $index[] = [
                    'title' => $page['title'],
                    'url' => '/seo/' . $page['slug'],
                    'keywords' => $page['title'] . ' ' . ($page['meta_description'] ?? ''),
                    'category' => 'Resources',
                    'icon' => 'fas fa-book',
                    'type' => 'seo',
                    'priority' => 15
                ];
            }
        } catch (PDOException $e) {
            // Table might not exist yet
        }

        // 4. Bursaries from database
        try {
            $bursaries = $this->db->query("SELECT id, name, provider FROM bursaries WHERE is_active = 1")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($bursaries as $bursary) {
                $index[] = [
                    'title' => $bursary['name'],
                    'url' => '/upload-report-card',
                    'keywords' => $bursary['name'] . ' ' . $bursary['provider'] . ' bursary scholarship funding financial aid',
                    'category' => 'Bursaries',
                    'icon' => 'fas fa-hand-holding-dollar',
                    'type' => 'bursary',
                    'priority' => 18
                ];
            }
        } catch (PDOException $e) {
            // Table might not exist
        }

        // 5. Uploaded scripts from database
        try {
            $scripts = $this->db->query("SELECT id, title, subject, grade_level FROM uploaded_scripts ORDER BY created_at DESC LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($scripts as $script) {
                $index[] = [
                    'title' => $script['title'],
                    'url' => '/browse-scripts/' . $script['grade_level'],
                    'keywords' => $script['title'] . ' ' . $script['subject'] . ' grade ' . $script['grade_level'] . ' script paper memorandum',
                    'category' => 'Scripts',
                    'icon' => 'fas fa-file-alt',
                    'type' => 'script',
                    'priority' => 12
                ];
            }
        } catch (PDOException $e) {
            // Table might not exist
        }

        // 6. Memorandums from database
        try {
            $memos = $this->db->query("SELECT m.id, m.title, s.title as script_title, s.subject, s.grade_level 
                FROM memorandums m 
                LEFT JOIN uploaded_scripts s ON m.script_id = s.id 
                ORDER BY m.created_at DESC LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($memos as $memo) {
                $index[] = [
                    'title' => ($memo['script_title'] ? $memo['script_title'] . ' - ' : '') . ($memo['title'] ?: 'Memorandum'),
                    'url' => '/browse-scripts/' . ($memo['grade_level'] ?? '12'),
                    'keywords' => ($memo['title'] ?? '') . ' memorandum memo ' . ($memo['subject'] ?? '') . ' grade ' . ($memo['grade_level'] ?? '') . ' answers solutions',
                    'category' => 'Memorandums',
                    'icon' => 'fas fa-clipboard-check',
                    'type' => 'memorandum',
                    'priority' => 12
                ];
            }
        } catch (PDOException $e) {
            // Table might not exist
        }

        // 7. Features / Tools (non-page features)
        $features = [
            ['title' => 'Calculator', 'url' => '#calculator', 'keywords' => 'calculator calc maths arithmetic compute trigonometry logarithm scientific casio fx-300', 'category' => 'Tools', 'icon' => 'fas fa-calculator', 'type' => 'feature', 'priority' => 25, 'action' => 'toggleCalculator'],
            ['title' => 'Memorandum Generator', 'url' => '/upload-script', 'keywords' => 'memorandum generator memo answer solutions marking guidelines', 'category' => 'Tools', 'icon' => 'fas fa-wand-magic-sparkles', 'type' => 'feature', 'priority' => 18],
            ['title' => 'Past Papers', 'url' => '/browse-scripts/12', 'keywords' => 'past papers exam papers previous year questions grade 12 grade 11', 'category' => 'Tools', 'icon' => 'fas fa-file-lines', 'type' => 'feature', 'priority' => 18],
            ['title' => 'Grade Calculator', 'url' => '#calculator', 'keywords' => 'grade calculator marks percentage average APS score compute', 'category' => 'Tools', 'icon' => 'fas fa-calculator', 'type' => 'feature', 'priority' => 22, 'action' => 'toggleCalculator'],
            ['title' => 'Formula Sheet', 'url' => '/data-sheets', 'keywords' => 'formula sheet reference formulas equations cheat sheet tables mathematics physics', 'category' => 'Tools', 'icon' => 'fas fa-table', 'type' => 'feature', 'priority' => 16],
            ['title' => 'OCR Scanner', 'url' => '/scan', 'keywords' => 'ocr scanner camera capture text recognition optical character', 'category' => 'Tools', 'icon' => 'fas fa-camera', 'type' => 'feature', 'priority' => 16],
            ['title' => 'Exam Simulator', 'url' => '/simulate', 'keywords' => 'exam simulator practice test mock exam simulation timer quiz', 'category' => 'Tools', 'icon' => 'fas fa-vial', 'type' => 'feature', 'priority' => 17],
            ['title' => 'Progress Tracker', 'url' => '/dashboard', 'keywords' => 'progress tracker dashboard stats achievements badges streak login', 'category' => 'Tools', 'icon' => 'fas fa-chart-line', 'type' => 'feature', 'priority' => 15],
        ];

        foreach ($features as $feature) {
            $item = array_merge($feature, []);
            if (isset($feature['action'])) {
                $item['action'] = $feature['action'];
            }
            $index[] = $item;
        }

        // 8. Courses from AI recommendations (static catalog)
        $courses = [
            'Computer Science' => 'computer science software programming coding developer',
            'Information Technology' => 'information technology IT networking systems',
            'Software Engineering' => 'software engineering development programming apps',
            'Data Science' => 'data science analytics machine learning artificial intelligence',
            'Electrical Engineering' => 'electrical engineering electronics circuits power',
            'Mechanical Engineering' => 'mechanical engineering machines manufacturing design',
            'Civil Engineering' => 'civil engineering construction infrastructure building',
            'Medicine' => 'medicine doctor medical mbchb healthcare health',
            'Law' => 'law llb lawyer legal attorney justice',
            'Business Science' => 'business commerce management entrepreneurship',
            'Accounting' => 'accounting finance auditing bcom chartered',
            'Psychology' => 'psychology mental health counselling behaviour',
            'Education' => 'education teaching bed teacher training',
            'Nursing' => 'nursing nurse healthcare medical',
            'Architecture' => 'architecture design buildings structures',
        ];

        foreach ($courses as $name => $keywords) {
            $index[] = [
                'title' => $name,
                'url' => '/upload-report-card',
                'keywords' => $name . ' ' . $keywords . ' course degree diploma university study',
                'category' => 'Courses',
                'icon' => 'fas fa-graduation-cap',
                'type' => 'course',
                'priority' => 14
            ];
        }

        // 9. Universities / Institutions
        $institutions = [
            'University of Cape Town' => 'UCT cape town university',
            'University of the Witwatersrand' => 'Wits witwatersrand johannesburg university',
            'University of Pretoria' => 'UP pretoria tuks university',
            'University of Johannesburg' => 'UJ johannesburg university',
            'Stellenbosch University' => 'stellenbosch sun university',
            'University of KwaZulu-Natal' => 'UKZN kwaZulu-Natal durban university',
            'University of the Free State' => 'UFS bloemfontein free state university',
            'North-West University' => 'NWU north west potchefstroom university',
            'University of Limpopo' => 'UL limpopo polokwane university',
            'University of Venda' => 'Univen venda university',
            'Tshwane University of Technology' => 'TUT tshwane technology pretoria',
            'Cape Peninsula University of Technology' => 'CPUT cape peninsula technology',
            'UNISA' => 'unisa distance learning correspondence',
        ];

        foreach ($institutions as $name => $keywords) {
            $index[] = [
                'title' => $name,
                'url' => '/upload-report-card',
                'keywords' => $name . ' ' . $keywords . ' university college institution campus',
                'category' => 'Institutions',
                'icon' => 'fas fa-university',
                'type' => 'institution',
                'priority' => 16
            ];
        }

        // 10. Grade-specific content
        for ($grade = 8; $grade <= 12; $grade++) {
            $index[] = [
                'title' => "Grade {$grade} Content",
                'url' => '/browse-scripts/' . $grade,
                'keywords' => "grade {$grade} scripts papers memorandums notes study materials",
                'category' => 'Grades',
                'icon' => 'fas fa-school',
                'type' => 'grade',
                'priority' => 11
            ];
        }

        // 11. Admin pages (only if admin)
        if (isLoggedIn() && getCurrentUser()['role'] === 'admin') {
            $adminPages = [
                ['title' => 'Admin Panel', 'url' => '/admin', 'keywords' => 'admin panel management dashboard control', 'category' => 'Admin', 'icon' => 'fas fa-shield-halved'],
                ['title' => 'Manage Users', 'url' => '/admin/users', 'keywords' => 'manage users accounts list all users', 'category' => 'Admin', 'icon' => 'fas fa-users-gear'],
                ['title' => 'Manage Subscriptions', 'url' => '/admin/subscriptions', 'keywords' => 'subscriptions payments manage plans', 'category' => 'Admin', 'icon' => 'fas fa-credit-card'],
                ['title' => 'Manage Bursaries', 'url' => '/admin/bursaries', 'keywords' => 'bursaries management add edit bursary', 'category' => 'Admin', 'icon' => 'fas fa-hand-holding-dollar'],
                ['title' => 'Manage Scripts', 'url' => '/admin/scripts', 'keywords' => 'scripts management uploaded scripts', 'category' => 'Admin', 'icon' => 'fas fa-file-lines'],
            ];
            foreach ($adminPages as $page) {
                $index[] = array_merge($page, ['type' => 'admin', 'priority' => 8]);
            }
        }

        return $index;
    }

    /**
     * Search the index for matching items
     */
    public function search($query = '') {
        header('Content-Type: application/json');

        $query = trim($query);
        if (empty($query)) {
            echo json_encode(['success' => true, 'results' => [], 'suggestions' => $this->getSuggestions('')]);
            return;
        }

        $queryLower = strtolower($query);
        $queryWords = array_filter(explode(' ', $queryLower));
        $index = $this->getSearchIndex();
        $results = [];

        foreach ($index as $item) {
            $score = 0;
            $titleLower = strtolower($item['title']);
            $keywordsLower = strtolower($item['keywords']);

            // Exact title match (highest priority)
            if ($titleLower === $queryLower) {
                $score += 1000;
            }

            // Title starts with query
            if (strpos($titleLower, $queryLower) === 0) {
                $score += 500;
            }

            // Title contains query
            if (strpos($titleLower, $queryLower) !== false) {
                $score += 300;
            }

            // Keywords contain full query
            if (strpos($keywordsLower, $queryLower) !== false) {
                $score += 200;
            }

            // Individual word matching
            foreach ($queryWords as $word) {
                if (strlen($word) < 2) continue;

                if (strpos($titleLower, $word) !== false) {
                    $score += 50;
                }
                if (strpos($keywordsLower, $word) !== false) {
                    $score += 30;
                }

                // Fuzzy match: check if word is contained in any keyword
                $kwWords = explode(' ', $keywordsLower);
                foreach ($kwWords as $kw) {
                    if (strlen($kw) < 2) continue;
                    if (strpos($kw, $word) !== false || strpos($word, $kw) !== false) {
                        $score += 10;
                    }
                }
            }

            // Boost by item priority
            $score += ($item['priority'] ?? 0);

            if ($score > 0) {
                $result = [
                    'title' => $item['title'],
                    'url' => $item['url'],
                    'category' => $item['category'],
                    'icon' => $item['icon'],
                    'type' => $item['type'],
                    'score' => $score
                ];
                if (isset($item['action'])) {
                    $result['action'] = $item['action'];
                }
                $results[] = $result;
            }
        }

        // Sort by score descending
        usort($results, function($a, $b) {
            return $b['score'] - $a['score'];
        });

        // Limit to top 15 results
        $results = array_slice($results, 0, 15);

        // Remove score from output
        $results = array_map(function($r) {
            unset($r['score']);
            return $r;
        }, $results);

        echo json_encode([
            'success' => true,
            'query' => $query,
            'results' => $results,
            'count' => count($results)
        ]);
    }

    /**
     * Get search suggestions for autocomplete
     */
    public function suggestions($query = '') {
        header('Content-Type: application/json');

        $query = trim($query);
        $suggestions = $this->getSuggestions($query);

        echo json_encode([
            'success' => true,
            'suggestions' => $suggestions
        ]);
    }

    /**
     * Internal suggestion generator
     */
    private function getSuggestions($query) {
        if (empty($query)) {
            // Return popular/default suggestions
            return [
                'Grade 12 Scripts',
                'Mathematics Notes',
                'Study Plan',
                'Career Recommendations',
                'Calculator',
                'Memorandum',
                'AI Chat',
                'Scan to PDF',
                'Bursaries',
                'Apply For Me'
            ];
        }

        $queryLower = strtolower($query);
        $index = $this->getSearchIndex();
        $suggestions = [];
        $seen = [];

        foreach ($index as $item) {
            $titleLower = strtolower($item['title']);
            if (strpos($titleLower, $queryLower) !== false && !in_array($item['title'], $seen)) {
                $suggestions[] = $item['title'];
                $seen[] = $item['title'];
                if (count($suggestions) >= 8) break;
            }
        }

        // If not enough, check keywords
        if (count($suggestions) < 8) {
            foreach ($index as $item) {
                if (in_array($item['title'], $seen)) continue;
                $keywordsLower = strtolower($item['keywords']);
                foreach (explode(' ', $queryLower) as $word) {
                    if (strlen($word) >= 2 && strpos($keywordsLower, $word) !== false) {
                        $suggestions[] = $item['title'];
                        $seen[] = $item['title'];
                        break;
                    }
                }
                if (count($suggestions) >= 8) break;
            }
        }

        return $suggestions;
    }

    /**
     * Get the full search index (for client-side caching)
     */
    public function getIndex() {
        header('Content-Type: application/json');

        $index = $this->getSearchIndex();
        // Strip down to essential fields for client
        $lightIndex = array_map(function($item) {
            return [
                't' => $item['title'],
                'u' => $item['url'],
                'k' => $item['keywords'],
                'c' => $item['category'],
                'i' => $item['icon'],
                'p' => $item['priority'] ?? 0
            ];
        }, $index);

        echo json_encode([
            'success' => true,
            'index' => $lightIndex,
            'count' => count($lightIndex)
        ]);
    }
}