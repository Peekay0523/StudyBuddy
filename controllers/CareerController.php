<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/AIRouter.php';

/**
 * CareerController - Handles career search and institution lookup
 */
class CareerController {
    private $aiRouter;

    public function __construct() {
        $this->aiRouter = new AIRouter();
    }

    /**
     * Call AI API for career information (using hybrid routing)
     */
    private function callAI($prompt) {
        // Career information is complex, but Grok/LLaMA can handle it well
        // Let the AI Router decide based on availability
        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a career guidance assistant for South African students. Provide accurate information about careers, including APS requirements and subject prerequisites for South African universities. Return responses in JSON format only.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ];

        // Use AI Router - will use Grok if available, fallback to OpenAI
        // Career search is intermediate/advanced - router will pick best available model
        return $this->aiRouter->makeRequest($messages, 2000, 0.7);
    }
    
    /**
     * Parse AI response into structured career data
     */
    private function parseAICareerData($aiResponse, $searchTerm) {
        // Try to extract JSON from the response
        $jsonStart = strpos($aiResponse, '{');
        $jsonEnd = strrpos($aiResponse, '}');
        
        if ($jsonStart !== false && $jsonEnd !== false) {
            $jsonStr = substr($aiResponse, $jsonStart, $jsonEnd - $jsonStart + 1);
            $parsed = json_decode($jsonStr, true);
            if ($parsed) {
                return $parsed;
            }
        }
        
        // Fallback: Create structured data from text
        return [
            'careers' => [
                [
                    'id' => 0,
                    'name' => ucfirst($searchTerm),
                    'description' => 'Career information provided by AI',
                    'category' => 'General',
                    'min_aps_score' => 24,
                    'institutions' => []
                ]
            ]
        ];
    }

    /**
     * Search careers by keyword (always uses AI for comprehensive results)
     */
    public function search($query = '') {
        header('Content-Type: application/json');

        $searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';
        $minAps = isset($_GET['min_aps']) ? (int)$_GET['min_aps'] : 0;

        if (empty($searchTerm)) {
            echo json_encode([
                'success' => false,
                'error' => 'Search term is required'
            ]);
            return;
        }

        try {
            $db = Database::getInstance()->getConnection();
            $careers = [];
            $fromAI = false;

            // First, try database search
            $sql = "SELECT c.*,
                    COUNT(ci.institution_id) as institution_count
                    FROM careers c
                    LEFT JOIN career_institutions ci ON c.id = ci.career_id";

            $conditions = [];
            $params = [];

            if (!empty($searchTerm)) {
                $conditions[] = "(c.name LIKE ? OR c.description LIKE ? OR c.category LIKE ?)";
                $searchParam = "%{$searchTerm}%";
                $params = [$searchParam, $searchParam, $searchParam];
            }

            if ($minAps > 0) {
                $conditions[] = "c.min_aps_score <= ?";
                $params[] = $minAps;
            }

            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(' AND ', $conditions);
            }

            $sql .= " GROUP BY c.id ORDER BY c.min_aps_score ASC";

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $dbCareers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get institutions for each career
            if (!empty($dbCareers)) {
                foreach ($dbCareers as &$career) {
                    $institutionsStmt = $db->prepare("
                        SELECT i.*, ci.subject_requirements, ci.min_aps_score as required_aps, ci.additional_requirements
                        FROM institutions i
                        INNER JOIN career_institutions ci ON i.id = ci.institution_id
                        WHERE ci.career_id = ?
                        ORDER BY i.name ASC
                    ");
                    $institutionsStmt->execute([$career['id']]);
                    $career['institutions'] = $institutionsStmt->fetchAll(PDO::FETCH_ASSOC);

                    // Decode JSON subject requirements and extract qualifications
                    foreach ($career['institutions'] as &$institution) {
                        if ($institution['subject_requirements']) {
                            $decoded = json_decode($institution['subject_requirements'], true);
                            if ($decoded) {
                                if (isset($decoded['subjects'])) {
                                    $institution['subject_requirements'] = $decoded['subjects'];
                                    if (isset($decoded['qualifications']) && !empty($decoded['qualifications'])) {
                                        $institution['qualifications'] = $decoded['qualifications'];
                                    } else {
                                        $institution['qualifications'] = $this->getDefaultQualifications($career['name'], $institution['type']);
                                    }
                                } else if (is_array($decoded)) {
                                    $institution['subject_requirements'] = $decoded;
                                    $institution['qualifications'] = $this->getDefaultQualifications($career['name'], $institution['type']);
                                } else {
                                    $institution['subject_requirements'] = $decoded;
                                    $institution['qualifications'] = $this->getDefaultQualifications($career['name'], $institution['type']);
                                }
                            }
                        } else {
                            $institution['subject_requirements'] = [];
                            $institution['qualifications'] = $this->getDefaultQualifications($career['name'], $institution['type']);
                        }
                    }
                }
                $careers = $dbCareers;
                error_log("Database returned " . count($dbCareers) . " careers for '{$searchTerm}'");
            }

            // ALWAYS use AI to get comprehensive career information
            // AI will supplement database results or provide results if database is empty
            error_log("Calling AI for career information for '{$searchTerm}'");
            $aiResponse = $this->getAICareerInfo($searchTerm);
            
            if ($aiResponse && isset($aiResponse['careers']) && !empty($aiResponse['careers'])) {
                // If database had results, merge with AI (prefer AI for more comprehensive data)
                if (!empty($careers)) {
                    // Use AI results as they're typically more comprehensive
                    error_log("Merging database and AI results, using AI for comprehensive data");
                } else {
                    error_log("Database empty, using AI results only");
                }
                $careers = $aiResponse['careers'];
                $fromAI = true;
            }

            if (empty($careers)) {
                error_log("No careers found in database or AI for '{$searchTerm}'");
            } else {
                error_log("Returning " . count($careers) . " careers for '{$searchTerm}' (from_ai: " . ($fromAI ? 'true' : 'false') . ")");
            }

            echo json_encode([
                'success' => true,
                'careers' => $careers,
                'count' => count($careers),
                'from_ai' => $fromAI
            ]);

        } catch (Exception $e) {
            error_log("Error in career search: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            echo json_encode([
                'success' => false,
                'error' => 'Error searching careers: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get default qualifications based on career name and institution type
     */
    private function getDefaultQualifications($careerName, $institutionType) {
        $qualifications = [];
        $careerNameLower = strtolower($careerName);

        // Define qualification mappings for common careers
        $qualificationMap = [
            'software developer' => [
                'University' => [
                    ['name' => 'BSc Computer Science', 'type' => 'Degree', 'duration' => '3 years', 'qualification_code' => 'CS-DEG-01'],
                    ['name' => 'BEng Computer Engineering', 'type' => 'Degree', 'duration' => '4 years', 'qualification_code' => 'CE-DEG-01']
                ],
                'University of Technology' => [
                    ['name' => 'Diploma in Computer Science', 'type' => 'Diploma', 'duration' => '3 years', 'qualification_code' => 'CS-DIP-01'],
                    ['name' => 'Advanced Diploma in Software Development', 'type' => 'Advanced Diploma', 'duration' => '1 year', 'qualification_code' => 'SD-ADV-DIP-01']
                ],
                'TVET College' => [
                    ['name' => 'National Certificate in Software Development', 'type' => 'Certificate', 'duration' => '1-2 years', 'qualification_code' => 'SD-CERT-01']
                ]
            ],
            'doctor' => [
                'University' => [
                    ['name' => 'MBChB Bachelor of Medicine and Bachelor of Surgery', 'type' => 'Degree', 'duration' => '6 years', 'qualification_code' => 'MED-01'],
                    ['name' => 'BSc Medical Sciences', 'type' => 'Degree', 'duration' => '3 years', 'qualification_code' => 'MED-SCI-01']
                ]
            ],
            'engineer' => [
                'University' => [
                    ['name' => 'BSc Engineering', 'type' => 'Degree', 'duration' => '4 years', 'qualification_code' => 'ENG-DEG-01'],
                    ['name' => 'BEng Civil Engineering', 'type' => 'Degree', 'duration' => '4 years', 'qualification_code' => 'CE-DEG-01']
                ],
                'University of Technology' => [
                    ['name' => 'Diploma in Mechanical Engineering', 'type' => 'Diploma', 'duration' => '3 years', 'qualification_code' => 'ME-DIP-01'],
                    ['name' => 'National Diploma in Electrical Engineering', 'type' => 'Diploma', 'duration' => '3 years', 'qualification_code' => 'EE-DIP-01']
                ]
            ],
            'teacher' => [
                'University' => [
                    ['name' => 'Bachelor of Education (BEd)', 'type' => 'Degree', 'duration' => '4 years', 'qualification_code' => 'BED-01'],
                    ['name' => 'BA in Education', 'type' => 'Degree', 'duration' => '3 years', 'qualification_code' => 'BA-EDU-01']
                ],
                'University of Technology' => [
                    ['name' => 'Diploma in Early Childhood Development', 'type' => 'Diploma', 'duration' => '3 years', 'qualification_code' => 'ECD-DIP-01']
                ]
            ],
            'accountant' => [
                'University' => [
                    ['name' => 'BCom Accounting Sciences', 'type' => 'Degree', 'duration' => '3 years', 'qualification_code' => 'ACC-01'],
                    ['name' => 'BCom Finance and Tax', 'type' => 'Degree', 'duration' => '3 years', 'qualification_code' => 'FIN-01']
                ],
                'University of Technology' => [
                    ['name' => 'Diploma in Accounting', 'type' => 'Diploma', 'duration' => '3 years', 'qualification_code' => 'ACC-DIP-01']
                ]
            ],
            'lawyer' => [
                'University' => [
                    ['name' => 'LLB Bachelor of Laws', 'type' => 'Degree', 'duration' => '4 years', 'qualification_code' => 'LLB-01'],
                    ['name' => 'BCom Law', 'type' => 'Degree', 'duration' => '3 years', 'qualification_code' => 'LAW-01']
                ]
            ],
            'nurse' => [
                'University' => [
                    ['name' => 'Bachelor of Nursing', 'type' => 'Degree', 'duration' => '4 years', 'qualification_code' => 'NUR-DEG-01'],
                    ['name' => 'BSc Nursing Science', 'type' => 'Degree', 'duration' => '4 years', 'qualification_code' => 'NUR-SCI-01']
                ],
                'University of Technology' => [
                    ['name' => 'Diploma in Nursing', 'type' => 'Diploma', 'duration' => '3 years', 'qualification_code' => 'NUR-DIP-01']
                ]
            ],
            'chef' => [
                'University of Technology' => [
                    ['name' => 'Diploma in Culinary Arts', 'type' => 'Diploma', 'duration' => '3 years', 'qualification_code' => 'CHEF-DIP-01']
                ],
                'TVET College' => [
                    ['name' => 'National Certificate in Professional Cooking', 'type' => 'Certificate', 'duration' => '1-2 years', 'qualification_code' => 'COOK-CERT-01']
                ]
            ],
            'electrician' => [
                'University of Technology' => [
                    ['name' => 'National Diploma in Electrical Engineering', 'type' => 'Diploma', 'duration' => '3 years', 'qualification_code' => 'ELEC-DIP-01']
                ],
                'TVET College' => [
                    ['name' => 'National Certificate in Electrical Infrastructure', 'type' => 'Certificate', 'duration' => '1-2 years', 'qualification_code' => 'ELEC-CERT-01']
                ]
            ]
        ];

        // Find matching career
        foreach ($qualificationMap as $careerKey => $quals) {
            if (strpos($careerNameLower, $careerKey) !== false) {
                if (isset($quals[$institutionType])) {
                    return $quals[$institutionType];
                }
            }
        }

        // Default qualifications if no match found
        return [
            ['name' => 'Bachelor\'s Degree in ' . $careerName, 'type' => 'Degree', 'duration' => '3 years', 'qualification_code' => 'DEG-01'],
            ['name' => 'Diploma in ' . $careerName, 'type' => 'Diploma', 'duration' => '3 years', 'qualification_code' => 'DIP-01']
        ];
    }
    
    /**
     * Get career information from AI
     */
    private function getAICareerInfo($searchTerm) {
        $prompt = <<<PROMPT
A South African student is searching for information about the career: "$searchTerm"

Please provide comprehensive career information in valid JSON format with this exact structure:
{
    "careers": [
        {
            "id": 0,
            "name": "Career Name",
            "description": "Brief description of the career",
            "category": "Career category",
            "min_aps_score": 24,
            "institutions": [
                {
                    "name": "University Name",
                    "type": "University",
                    "location": "City",
                    "province": "Province",
                    "website": "https://university.ac.za",
                    "qualifications": [
                        {
                            "name": "BSc Computer Science",
                            "type": "Degree",
                            "duration": "3 years",
                            "qualification_code": "CS01"
                        },
                        {
                            "name": "Diploma in Computer Science",
                            "type": "Diploma",
                            "duration": "3 years",
                            "qualification_code": "DIP-CS"
                        }
                    ],
                    "subject_requirements": [
                        {"subject": "Subject Name", "level": 5, "description": "Level 5 (60-69%)"}
                    ],
                    "min_aps_score": 24,
                    "additional_requirements": "Any additional requirements"
                }
            ]
        }
    ]
}

IMPORTANT: For each institution, include 2-4 specific qualification names that students can apply for.
Use real qualification names like:
- "BSc Computer Science" 
- "Bachelor of Commerce in Finance"
- "Diploma in Mechanical Engineering"
- "National Diploma in Electrical Engineering"
- "BTech in Information Technology"

Include at least 3-5 South African universities or institutions that offer this career.
Include realistic APS requirements (typically 20-36 for South African universities).
Include subject requirements with levels (Level 3-7, where Level 4 = 50-59%, Level 5 = 60-69%, Level 6 = 70-79%, Level 7 = 80-100%).
Only return the JSON, no other text.
PROMPT;

        $aiResponse = $this->callAI($prompt);

        if ($aiResponse) {
            $parsed = $this->parseAICareerData($aiResponse, $searchTerm);
            if (isset($parsed['careers']) && !empty($parsed['careers'])) {
                return $parsed;
            }
        }
        
        return null;
    }
    
    /**
     * Get all categories
     */
    public function categories() {
        header('Content-Type: application/json');
        
        try {
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->query("SELECT DISTINCT category FROM careers ORDER BY category ASC");
            $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo json_encode([
                'success' => true,
                'categories' => $categories
            ]);
            
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get career details by ID
     */
    public function show($id) {
        header('Content-Type: application/json');
        
        try {
            $db = Database::getInstance()->getConnection();
            
            // Get career details
            $stmt = $db->prepare("SELECT * FROM careers WHERE id = ?");
            $stmt->execute([$id]);
            $career = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$career) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Career not found'
                ]);
                return;
            }
            
            // Get institutions offering this career
            $institutionsStmt = $db->prepare("
                SELECT i.*, ci.subject_requirements, ci.min_aps_score as required_aps, ci.additional_requirements
                FROM institutions i
                INNER JOIN career_institutions ci ON i.id = ci.institution_id
                WHERE ci.career_id = ?
                ORDER BY i.name ASC
            ");
            $institutionsStmt->execute([$id]);
            $institutions = $institutionsStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decode JSON subject requirements
            foreach ($institutions as &$institution) {
                if ($institution['subject_requirements']) {
                    $institution['subject_requirements'] = json_decode($institution['subject_requirements'], true);
                }
            }
            
            $career['institutions'] = $institutions;
            
            echo json_encode([
                'success' => true,
                'career' => $career
            ]);
            
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get all institutions
     */
    public function institutions() {
        header('Content-Type: application/json');
        
        try {
            $db = Database::getInstance()->getConnection();
            
            $type = isset($_GET['type']) ? $_GET['type'] : '';
            
            $sql = "SELECT * FROM institutions";
            $params = [];
            
            if (!empty($type)) {
                $sql .= " WHERE type = ?";
                $params[] = $type;
            }
            
            $sql .= " ORDER BY name ASC";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $institutions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'institutions' => $institutions,
                'count' => count($institutions)
            ]);
            
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }
}
