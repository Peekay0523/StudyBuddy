<?php
/**
 * Test Career API Endpoints
 */
header('Content-Type: application/json');

require_once __DIR__ . '/config/database.php';

$action = $_GET['action'] ?? 'search';

try {
    $db = Database::getInstance()->getConnection();
    
    if ($action === 'categories') {
        $stmt = $db->query("SELECT DISTINCT category FROM careers ORDER BY category ASC");
        $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo json_encode([
            'success' => true,
            'categories' => $categories,
            'count' => count($categories)
        ]);
    } elseif ($action === 'search') {
        $searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';
        
        if (empty($searchTerm)) {
            echo json_encode([
                'success' => false,
                'error' => 'Search term is required'
            ]);
            exit;
        }
        
        $sql = "SELECT c.*, COUNT(ci.institution_id) as institution_count
                FROM careers c
                LEFT JOIN career_institutions ci ON c.id = ci.career_id
                WHERE (c.name LIKE ? OR c.description LIKE ? OR c.category LIKE ?)
                GROUP BY c.id
                ORDER BY c.min_aps_score ASC";
        
        $stmt = $db->prepare($sql);
        $searchParam = "%{$searchTerm}%";
        $stmt->execute([$searchParam, $searchParam, $searchParam]);
        $careers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get institutions for each career
        foreach ($careers as &$career) {
            $institutionsStmt = $db->prepare("
                SELECT i.*, ci.subject_requirements, ci.min_aps_score as required_aps, ci.additional_requirements
                FROM institutions i
                INNER JOIN career_institutions ci ON i.id = ci.institution_id
                WHERE ci.career_id = ?
                ORDER BY i.name ASC
            ");
            $institutionsStmt->execute([$career['id']]);
            $career['institutions'] = $institutionsStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decode JSON subject requirements
            foreach ($career['institutions'] as &$institution) {
                if ($institution['subject_requirements']) {
                    $decoded = json_decode($institution['subject_requirements'], true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $institution['subject_requirements'] = $decoded;
                    } else {
                        $institution['subject_requirements'] = ['error' => 'Invalid JSON', 'raw' => $institution['subject_requirements']];
                    }
                }
            }
        }
        
        echo json_encode([
            'success' => true,
            'careers' => $careers,
            'count' => count($careers)
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
