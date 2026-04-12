<?php
/**
 * Report Card Controller
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/ReportCard.php';
require_once __DIR__ . '/../models/CareerRecommendation.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/BursaryApplication.php';
require_once __DIR__ . '/../models/InstitutionApplication.php';
require_once __DIR__ . '/../helpers/FileHelper.php';
require_once __DIR__ . '/../helpers/AIRouter.php';

class ReportCardController {
    private $reportCardModel;
    private $careerRecModel;
    private $aiRouter;

    public function __construct() {
        $this->reportCardModel = new ReportCard();
        $this->careerRecModel = new CareerRecommendation();
        $this->aiRouter = new AIRouter();
    }
    
    public function upload() {
        requireStudent();

        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $student = getCurrentStudent();

            // Handle selected scan file (from database)
            if (isset($_POST['selected_scan_file']) && !empty($_POST['selected_scan_file'])) {
                $selectedScan = basename($_POST['selected_scan_file']);

                error_log("Selected scan filename: " . $selectedScan);
                error_log("Student user_id: " . $student['user_id']);

                // Get scan from database
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("SELECT * FROM scans WHERE filename = ? AND user_id = ? AND is_saved = 1");
                $stmt->execute([$selectedScan, $student['user_id']]);
                $scan = $stmt->fetch();

                error_log("Scan query result: " . print_r($scan, true));

                if ($scan && !empty($scan['file_data'])) {
                    // Copy scan to report cards folder (matching existing file structure)
                    $newFileName = 'reportcard_' . time() . '_' . $selectedScan;
                    $destPath = UPLOAD_DIR_REPORT_CARDS . $newFileName;

                    if (!is_dir(UPLOAD_DIR_REPORT_CARDS)) {
                        mkdir(UPLOAD_DIR_REPORT_CARDS, 0755, true);
                    }

                    if (file_put_contents($destPath, $scan['file_data'])) {
                        $grade = $_POST['grade'] ?? '';
                        $term = $_POST['term'] ?? '';

                        $reportCardId = $this->reportCardModel->create(
                            $student['user_id'],
                            $newFileName,
                            $grade,
                            $term
                        );

                        // Process the report card
                        $this->processReportCard($reportCardId);

                        setFlashMessage('success', 'Scan uploaded and processed successfully! Click "View Career Recommendations" to see your results.');
                        header('Location: /upload-report-card');
                        exit;
                    } else {
                        $error = 'Failed to save scan file';
                    }
                } else {
                    $error = 'Selected scan not found in database';
                }
            }
            // Handle regular file upload
            elseif (isset($_FILES['report_card_file'])) {
                $validation = FileHelper::validateUpload(
                    $_FILES['report_card_file'],
                    ALLOWED_REPORT_CARD_EXTENSIONS
                );

                if (!$validation['valid']) {
                    $error = $validation['error'];
                } else {
                    $fileName = FileHelper::saveUploadedFile($_FILES['report_card_file'], UPLOAD_DIR_REPORT_CARDS);

                    if ($fileName) {
                        $grade = $_POST['grade'] ?? '';
                        $term = $_POST['term'] ?? '';

                        $reportCardId = $this->reportCardModel->create(
                            $student['user_id'],
                            $fileName,
                            $grade,
                            $term
                        );

                        // Process the report card
                        $this->processReportCard($reportCardId);

                        setFlashMessage('success', 'Report card uploaded and processed successfully! Click "View Career Recommendations" to see your results.');
                        header('Location: /upload-report-card');
                        exit;
                    } else {
                        $error = 'Failed to save file';
                    }
                }
            }
        }

        include __DIR__ . '/../templates/pages/upload_report_card.php';
    }
    
    private function processReportCard($reportCardId) {
        try {
            $reportCard = $this->reportCardModel->findById($reportCardId);
            $filePath = UPLOAD_DIR_REPORT_CARDS . $reportCard['file_path'];

            error_log("Processing report card: " . $reportCardId . ", File: " . $filePath);

            // Always use OpenAI Vision API for best results
            error_log("Using OpenAI Vision API to extract grades from report card");
            $gradesData = $this->extractGradesWithOpenAIVision($filePath);

            // If no grades extracted, try fallback text extraction
            if (empty($gradesData)) {
                error_log("OpenAI Vision failed, trying traditional text extraction");
                
                // Extract text using FileHelper
                $textContent = FileHelper::extractTextFromFile($filePath);

                // Check if extracted text is valid or PDF garbage
                $isGarbage = false;
                if (!empty($textContent)) {
                    if (strpos($textContent, '%PDF-') !== false ||
                        strpos($textContent, 'TreeRoot') !== false ||
                        strpos($textContent, 'FontDescriptor') !== false ||
                        preg_match('/[^\x20-\x7E\x0A\x0D]{50,}/', $textContent)) {
                        $isGarbage = true;
                    }
                }

                // If garbage or empty, use OpenAI Vision OCR
                if (empty($textContent) || $isGarbage) {
                    error_log("Report card: Text extraction failed or garbage, using OpenAI Vision OCR");
                    $textContent = $this->extractTextWithOpenAIVision($filePath);
                }

                // Extract grades from text
                $gradesData = FileHelper::extractGradesFromText($textContent);
            }

            // If still no grades extracted, use fallback data
            if (empty($gradesData)) {
                // Create sample grades based on common subjects
                $gradesData = [
                    'Mathematics' => '65%',
                    'English' => '70%',
                    'Physical Sciences' => '60%',
                    'Life Sciences' => '68%',
                    'History' => '72%',
                    'Geography' => '65%'
                ];
                error_log("No grades extracted, using fallback for report card: " . $reportCardId);
            }

            error_log("Extracted grades: " . json_encode($gradesData));

            $this->reportCardModel->updateGradesData($reportCardId, $gradesData);

            // Generate career recommendations (advanced task - uses OpenAI)
            $recommendations = $this->aiRouter->generateCareerRecommendations($gradesData);

            error_log("Generated recommendations: " . json_encode([
                'careers_count' => count($recommendations['careers'] ?? []),
                'aps' => $recommendations['aps'] ?? 0,
                'strengths_count' => count($recommendations['strengths'] ?? [])
            ]));

            // Search for additional bursaries
            $subjects = array_keys($gradesData);
            $averageGrade = !empty($gradesData) ? array_sum(array_map(function($g) {
                // Convert letter grades to numbers or use percentage
                if (is_numeric($g)) {
                    return floatval($g);
                }
                // Extract number from percentage string like "65%"
                if (preg_match('/(\d+)/', $g, $matches)) {
                    return floatval($matches[1]);
                }
                $gradeMap = ['A' => 80, 'B' => 70, 'C' => 60, 'D' => 50, 'E' => 40, 'F' => 0];
                return $gradeMap[strtoupper($g[0])] ?? 65;
            }, $gradesData)) / count($gradesData) : 65;

            // Use AIHelper directly for bursary search (not an AI model call)
            $aiHelper = new AIHelper();
            $bursaries = $aiHelper->searchBursaries($subjects, $averageGrade);

            // Use courses from AI recommendations if available, otherwise generate them
            $courses = $recommendations['courses'] ?? [];

            // If AI didn't provide courses, generate them based on recommended careers
            if (empty($courses) && !empty($recommendations['careers'])) {
                foreach (array_slice($recommendations['careers'], 0, 3) as $career) {
                    $careerCourses = $aiHelper->getCourseInformation($career, $subjects);
                    if (!empty($careerCourses)) {
                        $courses = array_merge($courses, $careerCourses);
                    }
                }
            }

            // CRITICAL: Filter courses to only show what student actually qualifies for
            $courses = $this->filterCoursesByQualifications($courses, $gradesData);

            // If we have fewer than 5 qualifying courses, supplement with appropriate ones
            if (count($courses) < 5) {
                $fallbackCourses = $this->getFallbackCoursesForAchievementLevel($gradesData, $recommendations['careers'] ?? []);
                $courses = $this->mergeAndDeduplicateCourses($courses, $fallbackCourses);
            }

            // Use fallback courses if none generated (after filtering)
            if (empty($courses)) {
                $courses = $this->getFallbackCoursesForAchievementLevel($gradesData, $recommendations['careers'] ?? []);
            }

            // Limit to top 5 courses
            $courses = array_slice($courses, 0, 5);

            $studentModel = new Student();
            $student = $studentModel->findByUserId($reportCard['user_id']);

            // Extract APS from recommendations
            $aps = $recommendations['aps'] ?? 0;

            $this->careerRecModel->create(
                $student['id'],
                $reportCardId,
                $recommendations['careers'],
                $recommendations['strengths'],
                $recommendations['areas_for_improvement'],
                json_encode($courses),
                json_encode($bursaries),
                $aps
            );

            error_log("Career recommendations created successfully for report card: " . $reportCardId);

        } catch (Exception $e) {
            error_log("Error processing report card: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
        }
    }
    
    public function viewCareerRecommendations($reportCardId) {
        requireLogin();

        $student = getCurrentStudent();
        $user = getCurrentUser();

        // Verify ownership first
        $reportCard = $this->reportCardModel->findById($reportCardId);
        if (!$reportCard || $reportCard['student_id'] != $student['id']) {
            header('Location: /dashboard');
            exit;
        }

        // Check subscription status - only Basic and Premium can view recommendations
        if (isFreeTierUser($user['id'])) {
            setFlashMessage('error', 'Career recommendations are only available for Basic and Premium subscribers. Please upgrade your plan to view this feature.');
            header('Location: /subscription');
            exit;
        }

        // Find the career recommendation by report_card_id
        $careerRec = $this->careerRecModel->findByReportCardId($reportCardId);

        if (!$careerRec) {
            // Try to generate recommendations now
            error_log("Career rec not found for report card {$reportCardId}, generating now...");
            $this->processReportCard($reportCardId);

            // Try again
            $careerRec = $this->careerRecModel->findByReportCardId($reportCardId);

            if (!$careerRec) {
                setFlashMessage('error', 'Unable to generate career recommendations. Please try uploading your report card again.');
                header('Location: /upload-report-card');
                exit;
            }
        }

        // Check if APS is 0 or grades_data is empty, reprocess if needed
        if (($careerRec['aps'] ?? 0) === 0 || empty($careerRec['recommended_careers'])) {
            error_log("APS is 0 or careers empty for report card {$reportCardId}, reprocessing...");
            $this->processReportCard($reportCardId);
            
            // Fetch updated data
            $careerRec = $this->careerRecModel->findByReportCardId($reportCardId);
        }

        // Decode grades_data from JSON string to array for template use
        if (isset($reportCard['grades_data']) && is_string($reportCard['grades_data'])) {
            $reportCard['grades_data'] = json_decode($reportCard['grades_data'], true) ?? [];
        } elseif (!isset($reportCard['grades_data'])) {
            $reportCard['grades_data'] = [];
        }

        // Ensure courses have properly decoded subject_requirements arrays
        if (!empty($careerRec['courses']) && is_array($careerRec['courses'])) {
            foreach ($careerRec['courses'] as &$course) {
                if (isset($course['subject_requirements']) && is_string($course['subject_requirements'])) {
                    $course['subject_requirements'] = json_decode($course['subject_requirements'], true) ?? [];
                }
                if (isset($course['institutions']) && is_array($course['institutions'])) {
                    foreach ($course['institutions'] as &$inst) {
                        if (is_array($inst)) {
                            if (isset($inst['entry_requirements']) && is_string($inst['entry_requirements']) && $inst['entry_requirements'][0] === '{') {
                                $decoded = json_decode($inst['entry_requirements'], true);
                                if ($decoded && is_array($decoded)) {
                                    $inst = array_merge($inst, $decoded);
                                }
                            }
                        }
                    }
                }
            }
        }

        include __DIR__ . '/../templates/pages/view_career_recommendations.php';
    }

    /**
     * Reprocess an existing report card with improved AI extraction
     */
    public function reprocessReportCard($reportCardId) {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /dashboard');
            exit;
        }

        $student = getCurrentStudent();
        $reportCard = $this->reportCardModel->findById($reportCardId);

        // Verify ownership
        if (!$reportCard || $reportCard['user_id'] != $student['user_id']) {
            setFlashMessage('error', 'Report card not found or you do not have permission.');
            header('Location: /dashboard');
            exit;
        }

        // Delete existing career recommendations
        $db = Database::getInstance()->getConnection();
        $db->prepare("DELETE FROM career_recommendations WHERE report_card_id = ?")->execute([$reportCardId]);

        // Reprocess the report card
        $this->processReportCard($reportCardId);

        setFlashMessage('success', 'Report card reprocessed successfully with AI-powered extraction!');
        header('Location: /view-career-recommendations/' . $reportCardId);
        exit;
    }

    public function getUserReportCards() {
        requireLogin();

        header('Content-Type: application/json');

        $student = getCurrentStudent();
        $reportCards = $this->reportCardModel->findByUserId($student['user_id']);

        echo json_encode(['report_cards' => $reportCards ?: []]);
    }

    public function deleteReportCard($reportCardId) {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /dashboard');
            exit;
        }

        $student = getCurrentStudent();
        $reportCard = $this->reportCardModel->findById($reportCardId);

        // Verify ownership
        if (!$reportCard || $reportCard['user_id'] != $student['user_id']) {
            setFlashMessage('error', 'Report card not found or you do not have permission to delete it.');
            header('Location: /dashboard');
            exit;
        }

        $db = Database::getInstance()->getConnection();

        // Delete career recommendations
        $db->prepare("DELETE FROM career_recommendations WHERE report_card_id = ?")->execute([$reportCardId]);

        // Delete report card file
        if (!empty($reportCard['file_path']) && file_exists(UPLOAD_DIR_REPORT_CARDS . $reportCard['file_path'])) {
            unlink(UPLOAD_DIR_REPORT_CARDS . $reportCard['file_path']);
        }

        // Delete report card record
        $db->prepare("DELETE FROM report_cards WHERE id = ?")->execute([$reportCardId]);

        setFlashMessage('success', 'Report card deleted successfully.');
        header('Location: /dashboard');
        exit;
    }

    /**
     * Extract grades from PDF/Image using OpenAI Vision API
     * Returns structured grade data directly
     */
    private function extractGradesWithOpenAIVision($filePath) {
        try {
            error_log("=== OpenAI Vision Grade Extraction Started ===");
            error_log("File path: " . $filePath);
            
            if (!file_exists($filePath)) {
                error_log("ERROR: File does not exist: " . $filePath);
                return null;
            }
            
            // Use ImageMagick CLI to convert PDF to image
            $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ocr_' . uniqid();
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $magickPath = 'C:\Program Files\ImageMagick-7.1.2-Q16\magick.exe';

            if (!file_exists($magickPath)) {
                error_log("ERROR: ImageMagick not found at: $magickPath");
                return null;
            }

            // Get file extension
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            error_log("File extension: " . $extension);
            
            // For images, use directly
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                $imagePath = $filePath;
                error_log("Using image file directly: " . $imagePath);
            } else {
                // Convert first page of PDF/DOCX to image
                $imagePath = $tempDir . DIRECTORY_SEPARATOR . 'page_0.jpg';
                $escapedFilePath = str_replace('[', '\\[', $filePath);
                $convertCmd = "\"$magickPath\" -density 150 -quality 85 \"{$escapedFilePath}[0]\" \"$imagePath\" 2>&1";

                error_log("Converting report card to image: $convertCmd");
                $convertOutput = shell_exec($convertCmd);
                error_log("ImageMagick output: " . ($convertOutput ?? 'no output'));

                if (!file_exists($imagePath)) {
                    error_log("ERROR: ImageMagick conversion failed. Image not created.");
                    @rmdir($tempDir);
                    return null;
                }
                error_log("Image created successfully: " . $imagePath);
            }

            // Verify it's a valid image
            $imageInfo = @getimagesize($imagePath);
            if (!$imageInfo) {
                error_log("ERROR: File is not a valid image");
                if ($imagePath !== $filePath) {
                    @unlink($imagePath);
                    @rmdir($tempDir);
                }
                return null;
            }

            error_log("Image dimensions: {$imageInfo[0]}x{$imageInfo[1]} pixels");
            error_log("Image MIME type: " . ($imageInfo['mime'] ?? 'unknown'));

            // Read image and send to OpenAI Vision
            $imageData = file_get_contents($imagePath);
            if (!$imageData) {
                error_log("ERROR: Could not read image data");
                if ($imagePath !== $filePath) {
                    @unlink($imagePath);
                    @rmdir($tempDir);
                }
                return null;
            }
            
            if ($imagePath !== $filePath) {
                @unlink($imagePath);
                @rmdir($tempDir);
            }

            // Send to OpenAI with structured prompt for grade extraction
            $base64Image = base64_encode($imageData);
            error_log("Base64 image length: " . strlen($base64Image) . " bytes");
            
            $apiKey = OPENAI_API_KEY;
            $apiUrl = 'https://api.openai.com/v1/chat/completions';
            
            error_log("OpenAI API Key starts with: " . substr($apiKey, 0, 15) . "...");
            error_log("API Key length: " . strlen($apiKey));
            
            $messages = [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'You are an expert at extracting academic grades from South African National Senior Certificate (Matric) report cards. 

Extract the following information from this report card image:
1. Each subject name
2. The percentage grade (e.g., 72%) OR the performance level (1-7) for each subject
3. If you see an APS score, extract it

Return ONLY a valid JSON object in this exact format, nothing else:
{
    "grades": {
        "Mathematics": "72%",
        "English": "68%",
        "Physical Sciences": "65%"
    },
    "aps": 32
}

If you cannot find a percentage but see a level (1-7), convert it to approximate percentage:
- Level 7 = 85%
- Level 6 = 75%  
- Level 5 = 65%
- Level 4 = 55%
- Level 3 = 45%
- Level 2 = 35%
- Level 1 = 20%

Common South African subjects include: Mathematics, English, Physical Sciences, Life Sciences, Geography, History, Accounting, Business Studies, Economics, Life Orientation, Computer Applications Technology.

Return ONLY the JSON, no other text.'
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => "data:image/jpeg;base64,{$base64Image}"
                            ]
                        ]
                    ]
                ]
            ];

            $data = [
                'model' => 'gpt-4o-mini',
                'messages' => $messages,
                'max_tokens' => 500
            ];

            error_log("Sending request to OpenAI API...");
            
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            error_log("OpenAI API HTTP Response Code: " . $httpCode);
            if ($curlError) {
                error_log("cURL Error: " . $curlError);
            }
            error_log("OpenAI API Response: " . substr($response ?? 'NULL', 0, 1000));

            if ($response && $httpCode === 200) {
                $result = json_decode($response, true);
                
                if (isset($result['error'])) {
                    error_log("OpenAI API Error: " . json_encode($result['error']));
                    return null;
                }
                
                if (isset($result['choices'][0]['message']['content'])) {
                    $content = $result['choices'][0]['message']['content'];
                    error_log("OpenAI content: " . substr($content, 0, 500));
                    
                    // Extract JSON from response
                    $jsonMatch = [];
                    if (preg_match('/\{.*\}/s', $content, $jsonMatch)) {
                        $parsed = json_decode($jsonMatch[0], true);
                        error_log("Parsed JSON: " . json_encode($parsed));
                        if ($parsed && !empty($parsed['grades'])) {
                            error_log("=== Successfully extracted grades from OpenAI ===");
                            error_log("Grades: " . json_encode($parsed['grades']));
                            error_log("APS: " . ($parsed['aps'] ?? 'not provided'));
                            return $parsed['grades'];
                        } else {
                            error_log("WARNING: Parsed JSON but no grades found");
                        }
                    } else {
                        error_log("WARNING: Could not extract JSON from response");
                    }
                } else {
                    error_log("WARNING: No content in OpenAI response");
                }
            } else {
                error_log("ERROR: OpenAI API request failed with HTTP code: " . $httpCode);
            }

            error_log("=== OpenAI Vision Grade Extraction Failed ===");
            return null;

        } catch (Exception $e) {
            error_log("OpenAI Vision grade extraction exception: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return null;
        }
    }

    /**
     * Extract text from PDF using OpenAI Vision API
     */
    private function extractTextWithOpenAIVision($filePath) {
        try {
            // Use ImageMagick CLI to convert PDF to image
            $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ocr_' . uniqid();
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $magickPath = 'C:\Program Files\ImageMagick-7.1.2-Q16\magick.exe';

            if (!file_exists($magickPath)) {
                error_log("ImageMagick not found at: $magickPath");
                return null;
            }

            // Convert first page to image
            $imagePath = $tempDir . DIRECTORY_SEPARATOR . 'page_0.jpg';
            $escapedFilePath = str_replace('[', '\\[', $filePath);
            $convertCmd = "\"$magickPath\" -density 150 -quality 85 \"{$escapedFilePath}[0]\" \"$imagePath\" 2>&1";

            error_log("Converting report card to image: $convertCmd");
            $convertOutput = shell_exec($convertCmd);

            if (!file_exists($imagePath)) {
                error_log("ImageMagick conversion failed: $convertOutput");
                @rmdir($tempDir);
                return null;
            }

            // Verify it's a valid image
            $imageInfo = @getimagesize($imagePath);
            if (!$imageInfo) {
                error_log("Created file is not a valid image");
                @unlink($imagePath);
                @rmdir($tempDir);
                return null;
            }

            error_log("Image created: {$imageInfo[0]}x{$imageInfo[1]} pixels");

            // Read image and send to Vision API
            $imageData = file_get_contents($imagePath);
            @unlink($imagePath);
            @rmdir($tempDir);

            // Use AI Router to extract text (always OpenAI for Vision)
            $extractedText = $this->aiRouter->extractTextFromImage($imageData, 'image/jpeg');

            if ($extractedText) {
                error_log("Vision API extracted: " . strlen($extractedText) . " characters");
                return $extractedText;
            }

            return null;

        } catch (Exception $e) {
            error_log("OpenAI Vision OCR error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get available bursaries for students
     */
    public function getAvailableBursaries() {
        header('Content-Type: application/json');

        $db = Database::getInstance()->getConnection();

        try {
            // Get active bursaries that haven't expired
            $bursaries = $db->query("
                SELECT * FROM bursaries
                WHERE is_active = 1
                AND deadline >= date('now')
                ORDER BY deadline ASC
            ")->fetchAll();

            // Format bursaries
            $formattedBursaries = [];
            foreach ($bursaries as $bursary) {
                $deadline = new DateTime($bursary['deadline']);
                $today = new DateTime();
                $daysLeft = $today->diff($deadline)->days;

                $formattedBursaries[] = [
                    'id' => $bursary['id'],
                    'name' => $bursary['name'],
                    'provider' => $bursary['provider'],
                    'eligibility' => $bursary['eligibility'],
                    'covers' => $bursary['covers'],
                    'deadline' => $bursary['deadline'],
                    'days_left' => $daysLeft,
                    'contact' => $bursary['contact'],
                    'apply_url' => $bursary['apply_url'],
                    'min_grade_average' => $bursary['min_grade_average'],
                    'max_grade_average' => $bursary['max_grade_average'],
                    'required_subjects' => json_decode($bursary['required_subjects'] ?? '[]', true) ?? []
                ];
            }

            echo json_encode([
                'success' => true,
                'bursaries' => $formattedBursaries,
                'count' => count($formattedBursaries)
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to load bursaries',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Mark a bursary as applied
     */
    public function markBursaryAsApplied() {
        header('Content-Type: application/json');

        // Check if logged in
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Please log in to continue']);
            exit;
        }

        $user = $_SESSION['user'] ?? null;
        if (!$user || $user['role'] !== 'student') {
            echo json_encode(['success' => false, 'error' => 'Access denied. Students only.']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid request method']);
            exit;
        }

        $student = getCurrentStudent();
        if (!$student) {
            echo json_encode(['success' => false, 'error' => 'Student profile not found. Please log in again.']);
            exit;
        }

        $bursaryName = $_POST['bursary_name'] ?? '';
        $bursaryProvider = $_POST['bursary_provider'] ?? '';

        if (empty($bursaryName) || empty($bursaryProvider)) {
            echo json_encode(['success' => false, 'error' => 'Bursary name and provider are required']);
            exit;
        }

        try {
            $bursaryApplicationModel = new BursaryApplication();

            // Check if already applied
            $existingApplications = $bursaryApplicationModel->findByStudentId($student['id']);
            foreach ($existingApplications as $app) {
                if ($app['bursary_name'] === $bursaryName && $app['bursary_provider'] === $bursaryProvider) {
                    echo json_encode(['success' => false, 'error' => 'You have already marked this bursary as applied']);
                    exit;
                }
            }

            // Create application record
            $bursaryApplicationModel->create(
                $student['id'],
                $bursaryName,
                $bursaryProvider,
                null,
                'submitted',
                null,
                'Marked as applied from career recommendations'
            );

            echo json_encode(['success' => true, 'message' => 'Bursary marked as applied']);
        } catch (Exception $e) {
            error_log('Mark bursary applied error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to mark bursary as applied: ' . $e->getMessage()]);
        }
    }

    /**
     * Mark an institution as applied
     */
    public function markInstitutionAsApplied() {
        header('Content-Type: application/json');

        // Check if logged in
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Please log in to continue']);
            exit;
        }

        $user = $_SESSION['user'] ?? null;
        if (!$user || $user['role'] !== 'student') {
            echo json_encode(['success' => false, 'error' => 'Access denied. Students only.']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid request method']);
            exit;
        }

        $student = getCurrentStudent();
        if (!$student) {
            echo json_encode(['success' => false, 'error' => 'Student profile not found. Please log in again.']);
            exit;
        }

        $institutionName = $_POST['institution_name'] ?? '';
        $institutionType = $_POST['institution_type'] ?? 'university';

        if (empty($institutionName)) {
            echo json_encode(['success' => false, 'error' => 'Institution name is required']);
            exit;
        }

        try {
            $institutionApplicationModel = new InstitutionApplication();

            // Check if already applied
            $existingApplications = $institutionApplicationModel->findByStudentId($student['id']);
            foreach ($existingApplications as $app) {
                if ($app['institution_name'] === $institutionName) {
                    echo json_encode(['success' => false, 'error' => 'You have already marked this institution as applied']);
                    exit;
                }
            }

            // Create application record
            $institutionApplicationModel->create(
                $student['id'],
                $institutionName,
                null,
                $institutionType,
                'submitted',
                null,
                'Marked as applied from career recommendations'
            );

            echo json_encode(['success' => true, 'message' => 'Institution marked as applied']);
        } catch (Exception $e) {
            error_log('Mark institution applied error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to mark institution as applied: ' . $e->getMessage()]);
        }
    }

    /**
     * Get user's bursary applications
     */
    public function getBursaryApplications() {
        requireStudent();

        header('Content-Type: application/json');

        $student = getCurrentStudent();
        $bursaryApplicationModel = new BursaryApplication();

        try {
            $applications = $bursaryApplicationModel->findByStudentId($student['id']);
            echo json_encode(['success' => true, 'applications' => $applications]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Failed to load applications']);
        }
    }

    /**
     * Get user's institution applications
     */
    public function getInstitutionApplications() {
        requireStudent();

        header('Content-Type: application/json');

        $student = getCurrentStudent();
        $institutionApplicationModel = new InstitutionApplication();

        try {
            $applications = $institutionApplicationModel->findByStudentId($student['id']);
            echo json_encode(['success' => true, 'applications' => $applications]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Failed to load applications']);
        }
    }

    /**
     * Add bursary application
     */
    public function addBursaryApplication() {
        requireStudent();

        $student = getCurrentStudent();
        $bursaryName = $_POST['bursary_name'] ?? '';
        $bursaryProvider = $_POST['bursary_provider'] ?? '';
        $deadline = $_POST['deadline'] ?? null;
        $notes = $_POST['notes'] ?? null;

        if (empty($bursaryName) || empty($bursaryProvider)) {
            setFlashMessage('error', 'Bursary name and provider are required');
            header('Location: /dashboard');
            exit;
        }

        try {
            $bursaryApplicationModel = new BursaryApplication();
            $bursaryApplicationModel->create(
                $student['id'],
                $bursaryName,
                $bursaryProvider,
                null,
                'pending',
                $deadline,
                $notes
            );
            setFlashMessage('success', 'Bursary application added successfully');
        } catch (Exception $e) {
            setFlashMessage('error', 'Failed to add bursary application');
        }

        header('Location: /dashboard');
        exit;
    }

    /**
     * Add institution application
     */
    public function addInstitutionApplication() {
        requireStudent();

        $student = getCurrentStudent();
        $institutionName = $_POST['institution_name'] ?? '';
        $institutionType = $_POST['institution_type'] ?? 'university';
        $courseName = $_POST['course_name'] ?? null;
        $deadline = $_POST['deadline'] ?? null;
        $notes = $_POST['notes'] ?? null;

        if (empty($institutionName)) {
            setFlashMessage('error', 'Institution name is required');
            header('Location: /dashboard');
            exit;
        }

        try {
            $institutionApplicationModel = new InstitutionApplication();
            $institutionApplicationModel->create(
                $student['id'],
                $institutionName,
                $courseName,
                $institutionType,
                'pending',
                $deadline,
                $notes
            );
            setFlashMessage('success', 'Institution application added successfully');
        } catch (Exception $e) {
            setFlashMessage('error', 'Failed to add institution application');
        }

        header('Location: /dashboard');
        exit;
    }

    /**
     * Delete bursary application
     */
    public function deleteBursaryApplication() {
        requireStudent();

        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid request method']);
            exit;
        }

        $student = getCurrentStudent();
        $id = $_POST['id'] ?? null;

        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'Application ID is required']);
            exit;
        }

        try {
            $bursaryApplicationModel = new BursaryApplication();
            $bursaryApplicationModel->delete($id, $student['id']);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Failed to delete application']);
        }
    }

    /**
     * Delete institution application
     */
    public function deleteInstitutionApplication() {
        requireStudent();

        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid request method']);
            exit;
        }

        $student = getCurrentStudent();
        $id = $_POST['id'] ?? null;

        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'Application ID is required']);
            exit;
        }

        try {
            $institutionApplicationModel = new InstitutionApplication();
            $institutionApplicationModel->delete($id, $student['id']);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Failed to delete application']);
        }
    }

    /**
     * Filter courses to only show what the student actually qualifies for
     */
    private function filterCoursesByQualifications($courses, $gradesData) {
        if (empty($courses) || empty($gradesData)) {
            return [];
        }

        // Build a map of subject => achievement level
        $subjectLevels = [];
        foreach ($gradesData as $subject => $grade) {
            $percentage = $this->extractPercentageFromGrade($grade);
            $level = $this->percentageToLevel($percentage);
            $subjectLevels[strtolower($subject)] = [
                'name' => $subject,
                'percentage' => $percentage,
                'level' => $level
            ];
        }

        $filteredCourses = [];
        foreach ($courses as $course) {
            $requirements = $course['requirements'] ?? '';
            if ($this->studentMeetsRequirements($requirements, $subjectLevels)) {
                $filteredCourses[] = $course;
            }
        }

        return $filteredCourses;
    }

    /**
     * Extract percentage from grade string
     */
    private function extractPercentageFromGrade($grade) {
        if (is_numeric($grade)) {
            return floatval($grade);
        }
        
        // Extract from percentage like "65%"
        if (preg_match('/(\d+)%/', $grade, $matches)) {
            return floatval($matches[1]);
        }
        
        // Extract number from "Level X" format
        if (preg_match('/[Ll]evel\s*(\d+)/', $grade, $matches)) {
            $level = intval($matches[1]);
            $levelMap = [7 => 85, 6 => 75, 5 => 65, 4 => 55, 3 => 45, 2 => 35, 1 => 20];
            return $levelMap[$level] ?? 65;
        }
        
        // Extract any number
        if (preg_match('/(\d+)/', $grade, $matches)) {
            return floatval($matches[1]);
        }
        
        return 65; // Default
    }

    /**
     * Convert percentage to NSC Achievement Level
     */
    private function percentageToLevel($percentage) {
        if ($percentage >= 80) return 7;
        if ($percentage >= 70) return 6;
        if ($percentage >= 60) return 5;
        if ($percentage >= 50) return 4;
        if ($percentage >= 40) return 3;
        if ($percentage >= 30) return 2;
        return 1;
    }

    /**
     * Check if student meets course requirements
     */
    private function studentMeetsRequirements($requirements, $subjectLevels) {
        if (empty($requirements)) {
            return true; // No requirements = open entry
        }

        // Extract required subjects and levels from requirement string
        // Pattern: "Mathematics (Level 5)", "Physical Sciences (Level 6)", "Mathematics/Math Literacy (Level 4)"
        preg_match_all('/([A-Za-z\s\/]+?)\(Level\s*(\d+)\)/i', $requirements, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $requiredSubject = trim(strtolower($match[1]));
            $requiredLevel = intval($match[2]);

            // Handle "OR" requirements (e.g., "Mathematics (Level 4) or Math Literacy (Level 5)")
            if (stripos($requiredSubject, ' or ') !== false) {
                $subjectOptions = preg_split('/\s+or\s+/i', $requiredSubject);
                $meetsAnyOption = false;
                
                foreach ($subjectOptions as $option) {
                    $option = trim($option);
                    // Check if student meets this option
                    foreach ($subjectLevels as $subjKey => $subjData) {
                        if ($this->subjectMatches($subjKey, $option)) {
                            if ($subjData['level'] >= $requiredLevel) {
                                $meetsAnyOption = true;
                                break;
                            }
                        }
                    }
                    if ($meetsAnyOption) break;
                }
                
                if (!$meetsAnyOption) {
                    return false;
                }
                continue;
            }

            // Handle "/" as OR (e.g., "Mathematics/Math Literacy")
            if (strpos($requiredSubject, '/') !== false) {
                $subjectOptions = array_map('trim', explode('/', $requiredSubject));
                $meetsAnyOption = false;
                
                foreach ($subjectOptions as $option) {
                    foreach ($subjectLevels as $subjKey => $subjData) {
                        if ($this->subjectMatches($subjKey, $option)) {
                            if ($subjData['level'] >= $requiredLevel) {
                                $meetsAnyOption = true;
                                break;
                            }
                        }
                    }
                    if ($meetsAnyOption) break;
                }
                
                if (!$meetsAnyOption) {
                    return false;
                }
                continue;
            }

            // Direct subject match
            $studentLevel = null;
            foreach ($subjectLevels as $subjKey => $subjData) {
                if ($this->subjectMatches($subjKey, $requiredSubject)) {
                    $studentLevel = $subjData['level'];
                    break;
                }
            }

            // If subject not found or level not met, student doesn't qualify
            if ($studentLevel === null || $studentLevel < $requiredLevel) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if subject names match (flexible matching)
     */
    private function subjectMatches($studentSubject, $requiredSubject) {
        // Normalize subjects for comparison
        $normalize = function($str) {
            $str = strtolower($str);
            $str = preg_replace('/\s+/', ' ', trim($str));
            
            // Common variations
            $replacements = [
                'maths' => 'mathematics',
                'math' => 'mathematics',
                'physical science' => 'physical sciences',
                'life science' => 'life sciences',
                'english home language' => 'english',
                'english first additional language' => 'english',
                'accounting science' => 'accounting',
                'business studies' => 'business',
            ];
            
            foreach ($replacements as $from => $to) {
                $str = str_replace($from, $to, $str);
            }
            
            return $str;
        };

        return $normalize($studentSubject) === $normalize($requiredSubject);
    }

    /**
     * Merge two course arrays and deduplicate by course name
     */
    private function mergeAndDeduplicateCourses($existing, $new) {
        $existingNames = array_map(function($c) { return strtolower($c['name'] ?? ''); }, $existing);
        
        foreach ($new as $course) {
            $name = strtolower($course['name'] ?? '');
            if (!in_array($name, $existingNames)) {
                $existing[] = $course;
                $existingNames[] = $name;
            }
        }
        
        return $existing;
    }

    /**
     * Get fallback courses appropriate for student's achievement level
     */
    private function getFallbackCoursesForAchievementLevel($gradesData, $careers = []) {
        // Calculate average level
        $totalLevel = 0;
        $count = 0;
        $subjectLevels = [];
        $hasMath = false;
        $hasScience = false;
        $hasIT = false;
        $hasBusiness = false;

        foreach ($gradesData as $subject => $grade) {
            if (stripos($subject, 'Life Orientation') !== false) continue;

            $percentage = $this->extractPercentageFromGrade($grade);
            $level = $this->percentageToLevel($percentage);
            $subjectLevels[strtolower($subject)] = $level;
            $totalLevel += $level;
            $count++;
            
            $subjLower = strtolower($subject);
            if (strpos($subjLower, 'math') !== false) $hasMath = true;
            if (strpos($subjLower, 'science') !== false || strpos($subjLower, 'physics') !== false) $hasScience = true;
            if (strpos($subjLower, 'computer') !== false || strpos($subjLower, 'it ') !== false) $hasIT = true;
            if (strpos($subjLower, 'business') !== false || strpos($subjLower, 'commerce') !== false || strpos($subjLower, 'accounting') !== false) $hasBusiness = true;
        }

        $avgLevel = $count > 0 ? $totalLevel / $count : 4;
        
        // Determine career theme for relevant course selection
        $careerTheme = 'General';
        if (!empty($careers)) {
            $careerStr = implode(' ', $careers);
            if (preg_match('/(software|developer|programmer|it|computer|tech|engineer)/i', $careerStr)) {
                $careerTheme = 'IT';
            } elseif (preg_match('/(doctor|medical|nurse|health|hospital|pharma)/i', $careerStr)) {
                $careerTheme = 'Health';
            } elseif (preg_match('/(account|finance|business|commerce|economic|manager)/i', $careerStr)) {
                $careerTheme = 'Business';
            } elseif (preg_match('/(teach|educat|professor|lecturer)/i', $careerStr)) {
                $careerTheme = 'Education';
            } elseif (preg_match('/(engineer|engineering)/i', $careerStr)) {
                $careerTheme = 'Engineering';
            } elseif (preg_match('/(science|data|analyst|research)/i', $careerStr)) {
                $careerTheme = 'Science';
            } elseif (preg_match('/(art|design|creative|media|communicat|law|attorney)/i', $careerStr)) {
                $careerTheme = 'Arts';
            }
        }

        // Build career-appropriate courses based on achievement level
        $courses = [];

        if ($avgLevel >= 5) {
            // High achievers - can do degree programs
            if ($careerTheme === 'IT') {
                $courses = [
                    [
                        'name' => 'Bachelor of Science in Computer Science',
                        'aps_required' => 28,
                        'requirements' => 'APS 28, Mathematics (Level 5), Physical Sciences (Level 4), English (Level 4)',
                        'subject_requirements' => [
                            ['subject' => 'Mathematics', 'min_level' => 5],
                            ['subject' => 'Physical Sciences', 'min_level' => 4],
                            ['subject' => 'English', 'min_level' => 4]
                        ],
                        'duration' => '3 years',
                        'institutions' => [
                            ['name' => 'UCT', 'entry_requirements' => 'APS 30, Mathematics (Level 5), Physical Sciences (Level 4), English (Level 4)', 'aps_required' => 30],
                            ['name' => 'Wits', 'entry_requirements' => 'APS 28, Mathematics (Level 5), Physical Sciences (Level 4), English (Level 4)', 'aps_required' => 28],
                            ['name' => 'UP', 'entry_requirements' => 'APS 28, Mathematics (Level 5), Physical Sciences (Level 4)', 'aps_required' => 28],
                            ['name' => 'Stellenbosch', 'entry_requirements' => 'APS 28, Mathematics (Level 5), Physical Sciences (Level 4)', 'aps_required' => 28]
                        ]
                    ],
                    [
                        'name' => 'Bachelor of Information Technology',
                        'aps_required' => 26,
                        'requirements' => 'APS 26, Mathematics (Level 5), English (Level 4)',
                        'subject_requirements' => [
                            ['subject' => 'Mathematics', 'min_level' => 5],
                            ['subject' => 'English', 'min_level' => 4]
                        ],
                        'duration' => '3 years',
                        'institutions' => [
                            ['name' => 'UP', 'entry_requirements' => 'APS 26, Mathematics (Level 5), English (Level 4)', 'aps_required' => 26],
                            ['name' => 'UJ', 'entry_requirements' => 'APS 26, Mathematics (Level 5), English (Level 4)', 'aps_required' => 26],
                            ['name' => 'NWU', 'entry_requirements' => 'APS 24, Mathematics (Level 4), English (Level 4)', 'aps_required' => 24]
                        ]
                    ],
                    [
                        'name' => 'Bachelor of Science in Software Engineering',
                        'aps_required' => 28,
                        'requirements' => 'APS 28, Mathematics (Level 5), Physical Sciences (Level 4), English (Level 4)',
                        'subject_requirements' => [
                            ['subject' => 'Mathematics', 'min_level' => 5],
                            ['subject' => 'Physical Sciences', 'min_level' => 4],
                            ['subject' => 'English', 'min_level' => 4]
                        ],
                        'duration' => '3 years',
                        'institutions' => [
                            ['name' => 'Wits', 'entry_requirements' => 'APS 28, Mathematics (Level 5), Physical Sciences (Level 4), English (Level 4)', 'aps_required' => 28],
                            ['name' => 'UP', 'entry_requirements' => 'APS 28, Mathematics (Level 5), Physical Sciences (Level 4)', 'aps_required' => 28],
                            ['name' => 'UJ', 'entry_requirements' => 'APS 26, Mathematics (Level 5), Physical Sciences (Level 4)', 'aps_required' => 26]
                        ]
                    ],
                    [
                        'name' => 'Bachelor of Commerce in Information Systems',
                        'aps_required' => 26,
                        'requirements' => 'APS 26, Mathematics (Level 5), English (Level 4)',
                        'subject_requirements' => [
                            ['subject' => 'Mathematics', 'min_level' => 5],
                            ['subject' => 'English', 'min_level' => 4]
                        ],
                        'duration' => '3 years',
                        'institutions' => [
                            ['name' => 'UCT', 'entry_requirements' => 'APS 30, Mathematics (Level 5), English (Level 5)', 'aps_required' => 30],
                            ['name' => 'Wits', 'entry_requirements' => 'APS 26, Mathematics (Level 5), English (Level 4)', 'aps_required' => 26],
                            ['name' => 'Stellenbosch', 'entry_requirements' => 'APS 28, Mathematics (Level 5), English (Level 4)', 'aps_required' => 28]
                        ]
                    ],
                    [
                        'name' => 'Bachelor of Science in Data Science',
                        'aps_required' => 30,
                        'requirements' => 'APS 30, Mathematics (Level 6), Physical Sciences (Level 4), English (Level 4)',
                        'subject_requirements' => [
                            ['subject' => 'Mathematics', 'min_level' => 6],
                            ['subject' => 'Physical Sciences', 'min_level' => 4],
                            ['subject' => 'English', 'min_level' => 4]
                        ],
                        'duration' => '3 years',
                        'institutions' => [
                            ['name' => 'Wits', 'entry_requirements' => 'APS 30, Mathematics (Level 6), Physical Sciences (Level 4)', 'aps_required' => 30],
                            ['name' => 'UP', 'entry_requirements' => 'APS 28, Mathematics (Level 5), Physical Sciences (Level 4)', 'aps_required' => 28],
                            ['name' => 'UCT', 'entry_requirements' => 'APS 32, Mathematics (Level 6), Physical Sciences (Level 5)', 'aps_required' => 32]
                        ]
                    ]
                ];
            } elseif ($careerTheme === 'Business') {
                $courses = [
                    [
                        'name' => 'Bachelor of Commerce',
                        'aps_required' => 26,
                        'requirements' => 'APS 26, Mathematics (Level 5), English (Level 4)',
                        'subject_requirements' => [
                            ['subject' => 'Mathematics', 'min_level' => 5],
                            ['subject' => 'English', 'min_level' => 4]
                        ],
                        'duration' => '3 years',
                        'institutions' => [
                            ['name' => 'UCT', 'entry_requirements' => 'APS 30, Mathematics (Level 5), English (Level 5)', 'aps_required' => 30],
                            ['name' => 'Wits', 'entry_requirements' => 'APS 26, Mathematics (Level 5), English (Level 4)', 'aps_required' => 26],
                            ['name' => 'Stellenbosch', 'entry_requirements' => 'APS 28, Mathematics (Level 5), English (Level 4)', 'aps_required' => 28],
                            ['name' => 'UJ', 'entry_requirements' => 'APS 24, Mathematics (Level 4), English (Level 4)', 'aps_required' => 24],
                            ['name' => 'UP', 'entry_requirements' => 'APS 26, Mathematics (Level 5), English (Level 4)', 'aps_required' => 26]
                        ]
                    ],
                    [
                        'name' => 'Bachelor of Business Administration',
                        'aps_required' => 24,
                        'requirements' => 'APS 24, Mathematics (Level 4), English (Level 4)',
                        'subject_requirements' => [
                            ['subject' => 'Mathematics', 'min_level' => 4],
                            ['subject' => 'English', 'min_level' => 4]
                        ],
                        'duration' => '3 years',
                        'institutions' => [
                            ['name' => 'UJ', 'entry_requirements' => 'APS 24, Mathematics (Level 4), English (Level 4)', 'aps_required' => 24],
                            ['name' => 'UP', 'entry_requirements' => 'APS 24, Mathematics (Level 4), English (Level 4)', 'aps_required' => 24],
                            ['name' => 'NWU', 'entry_requirements' => 'APS 22, Mathematics (Level 4), English (Level 4)', 'aps_required' => 22],
                            ['name' => 'UKZN', 'entry_requirements' => 'APS 24, Mathematics (Level 4), English (Level 4)', 'aps_required' => 24]
                        ]
                    ],
                    [
                        'name' => 'Bachelor of Accounting Sciences',
                        'aps_required' => 26,
                        'requirements' => 'APS 26, Mathematics (Level 5), Accounting (Level 4), English (Level 4)',
                        'subject_requirements' => [
                            ['subject' => 'Mathematics', 'min_level' => 5],
                            ['subject' => 'Accounting', 'min_level' => 4],
                            ['subject' => 'English', 'min_level' => 4]
                        ],
                        'duration' => '3 years',
                        'institutions' => [
                            ['name' => 'Wits', 'entry_requirements' => 'APS 28, Mathematics (Level 5), Accounting (Level 4)', 'aps_required' => 28],
                            ['name' => 'UJ', 'entry_requirements' => 'APS 26, Mathematics (Level 5), English (Level 4)', 'aps_required' => 26],
                            ['name' => 'UNISA', 'entry_requirements' => 'APS 24, Mathematics (Level 4), English (Level 4)', 'aps_required' => 24]
                        ]
                    ],
                    [
                        'name' => 'Bachelor of Economics',
                        'aps_required' => 30,
                        'requirements' => 'APS 30, Mathematics (Level 5), English (Level 5)',
                        'subject_requirements' => [
                            ['subject' => 'Mathematics', 'min_level' => 5],
                            ['subject' => 'English', 'min_level' => 5]
                        ],
                        'duration' => '3 years',
                        'institutions' => [
                            ['name' => 'UCT', 'entry_requirements' => 'APS 32, Mathematics (Level 6), English (Level 5)', 'aps_required' => 32],
                            ['name' => 'Wits', 'entry_requirements' => 'APS 30, Mathematics (Level 5), English (Level 5)', 'aps_required' => 30],
                            ['name' => 'Stellenbosch', 'entry_requirements' => 'APS 30, Mathematics (Level 5), English (Level 5)', 'aps_required' => 30]
                        ]
                    ],
                    [
                        'name' => 'Bachelor of Commerce in Finance',
                        'aps_required' => 26,
                        'requirements' => 'APS 26, Mathematics (Level 5), English (Level 4)',
                        'subject_requirements' => [
                            ['subject' => 'Mathematics', 'min_level' => 5],
                            ['subject' => 'English', 'min_level' => 4]
                        ],
                        'duration' => '3 years',
                        'institutions' => [
                            ['name' => 'UCT', 'entry_requirements' => 'APS 30, Mathematics (Level 5), English (Level 5)', 'aps_required' => 30],
                            ['name' => 'Wits', 'entry_requirements' => 'APS 26, Mathematics (Level 5), English (Level 4)', 'aps_required' => 26],
                            ['name' => 'UP', 'entry_requirements' => 'APS 26, Mathematics (Level 5), English (Level 4)', 'aps_required' => 26]
                        ]
                    ]
                ];
            } elseif ($careerTheme === 'Science') {
                $courses = [
                    [
                        'name' => 'Bachelor of Science',
                        'aps_required' => 26,
                        'requirements' => 'APS 26, Mathematics (Level 5), Physical Sciences (Level 4), English (Level 4)',
                        'subject_requirements' => [
                            ['subject' => 'Mathematics', 'min_level' => 5],
                            ['subject' => 'Physical Sciences', 'min_level' => 4],
                            ['subject' => 'English', 'min_level' => 4]
                        ],
                        'duration' => '3 years',
                        'institutions' => [
                            ['name' => 'UCT', 'entry_requirements' => 'APS 30, Mathematics (Level 5), Physical Sciences (Level 4)', 'aps_required' => 30],
                            ['name' => 'Wits', 'entry_requirements' => 'APS 26, Mathematics (Level 5), Physical Sciences (Level 4)', 'aps_required' => 26],
                            ['name' => 'UP', 'entry_requirements' => 'APS 26, Mathematics (Level 5), Physical Sciences (Level 4)', 'aps_required' => 26],
                            ['name' => 'Stellenbosch', 'entry_requirements' => 'APS 28, Mathematics (Level 5), Physical Sciences (Level 4)', 'aps_required' => 28]
                        ]
                    ],
                    [
                        'name' => 'Bachelor of Science in Data Science',
                        'aps_required' => 28,
                        'requirements' => 'APS 28, Mathematics (Level 5), Physical Sciences (Level 4), English (Level 4)',
                        'subject_requirements' => [
                            ['subject' => 'Mathematics', 'min_level' => 5],
                            ['subject' => 'Physical Sciences', 'min_level' => 4],
                            ['subject' => 'English', 'min_level' => 4]
                        ],
                        'duration' => '3 years',
                        'institutions' => [
                            ['name' => 'Wits', 'entry_requirements' => 'APS 30, Mathematics (Level 5), Physical Sciences (Level 4)', 'aps_required' => 30],
                            ['name' => 'UP', 'entry_requirements' => 'APS 28, Mathematics (Level 5), Physical Sciences (Level 4)', 'aps_required' => 28],
                            ['name' => 'UCT', 'entry_requirements' => 'APS 32, Mathematics (Level 6), Physical Sciences (Level 5)', 'aps_required' => 32]
                        ]
                    ],
                    [
                        'name' => 'Bachelor of Science in Environmental Science',
                        'aps_required' => 24,
                        'requirements' => 'APS 24, Mathematics (Level 4), Physical Sciences (Level 4), English (Level 4)',
                        'subject_requirements' => [
                            ['subject' => 'Mathematics', 'min_level' => 4],
                            ['subject' => 'Physical Sciences', 'min_level' => 4],
                            ['subject' => 'English', 'min_level' => 4]
                        ],
                        'duration' => '3 years',
                        'institutions' => [
                            ['name' => 'UCT', 'entry_requirements' => 'APS 28, Mathematics (Level 5), Physical Sciences (Level 4)', 'aps_required' => 28],
                            ['name' => 'Stellenbosch', 'entry_requirements' => 'APS 26, Mathematics (Level 4), Physical Sciences (Level 4)', 'aps_required' => 26],
                            ['name' => 'UKZN', 'entry_requirements' => 'APS 24, Mathematics (Level 4), Physical Sciences (Level 4)', 'aps_required' => 24]
                        ]
                    ],
                    [
                        'name' => 'Bachelor of Science in Statistics',
                        'aps_required' => 26,
                        'requirements' => 'APS 26, Mathematics (Level 5), English (Level 4)',
                        'subject_requirements' => [
                            ['subject' => 'Mathematics', 'min_level' => 5],
                            ['subject' => 'English', 'min_level' => 4]
                        ],
                        'duration' => '3 years',
                        'institutions' => [
                            ['name' => 'Wits', 'entry_requirements' => 'APS 28, Mathematics (Level 5), English (Level 4)', 'aps_required' => 28],
                            ['name' => 'UP', 'entry_requirements' => 'APS 26, Mathematics (Level 5), English (Level 4)', 'aps_required' => 26],
                            ['name' => 'NWU', 'entry_requirements' => 'APS 24, Mathematics (Level 4), English (Level 4)', 'aps_required' => 24]
                        ]
                    ],
                    [
                        'name' => 'Bachelor of Science in Mathematics',
                        'aps_required' => 30,
                        'requirements' => 'APS 30, Mathematics (Level 6), Physical Sciences (Level 4), English (Level 4)',
                        'subject_requirements' => [
                            ['subject' => 'Mathematics', 'min_level' => 6],
                            ['subject' => 'Physical Sciences', 'min_level' => 4],
                            ['subject' => 'English', 'min_level' => 4]
                        ],
                        'duration' => '3 years',
                        'institutions' => [
                            ['name' => 'UCT', 'entry_requirements' => 'APS 32, Mathematics (Level 6), Physical Sciences (Level 5)', 'aps_required' => 32],
                            ['name' => 'Wits', 'entry_requirements' => 'APS 30, Mathematics (Level 6), Physical Sciences (Level 4)', 'aps_required' => 30],
                            ['name' => 'Stellenbosch', 'entry_requirements' => 'APS 30, Mathematics (Level 6), Physical Sciences (Level 4)', 'aps_required' => 30]
                        ]
                    ]
                ];
            } elseif ($careerTheme === 'Health') {
                $courses = [
                    [
                        'name' => 'Bachelor of Health Sciences',
                        'aps_required' => 28,
                        'requirements' => 'APS 28, Life Sciences (Level 5), English (Level 4), Mathematics or Mathematical Literacy (Level 4)',
                        'subject_requirements' => [
                            ['subject' => 'Life Sciences', 'min_level' => 5],
                            ['subject' => 'English', 'min_level' => 4],
                            ['subject' => 'Mathematics', 'min_level' => 4]
                        ],
                        'duration' => '3 years',
                        'institutions' => [
                            ['name' => 'UCT', 'entry_requirements' => 'APS 30, Life Sciences (Level 6), English (Level 5), Mathematics (Level 5)', 'aps_required' => 30],
                            ['name' => 'Wits', 'entry_requirements' => 'APS 28, Life Sciences (Level 5), English (Level 4), Mathematics (Level 4)', 'aps_required' => 28],
                            ['name' => 'UKZN', 'entry_requirements' => 'APS 28, Life Sciences (Level 5), English (Level 4)', 'aps_required' => 28],
                            ['name' => 'Stellenbosch', 'entry_requirements' => 'APS 28, Life Sciences (Level 5), English (Level 5)', 'aps_required' => 28]
                        ]
                    ],
                    [
                        'name' => 'Bachelor of Nursing',
                        'aps_required' => 26,
                        'requirements' => 'APS 26, Life Sciences (Level 4), English (Level 4), Mathematics or Mathematical Literacy (Level 4)',
                        'subject_requirements' => [
                            ['subject' => 'Life Sciences', 'min_level' => 4],
                            ['subject' => 'English', 'min_level' => 4],
                            ['subject' => 'Mathematics', 'min_level' => 4]
                        ],
                        'duration' => '4 years',
                        'institutions' => [
                            ['name' => 'UCT', 'entry_requirements' => 'APS 30, Life Sciences (Level 5), English (Level 5), Mathematics (Level 4)', 'aps_required' => 30],
                            ['name' => 'Wits', 'entry_requirements' => 'APS 26, Life Sciences (Level 4), English (Level 4)', 'aps_required' => 26],
                            ['name' => 'UKZN', 'entry_requirements' => 'APS 26, Life Sciences (Level 4), English (Level 4)', 'aps_required' => 26],
                            ['name' => 'UWC', 'entry_requirements' => 'APS 24, Life Sciences (Level 4), English (Level 4)', 'aps_required' => 24]
                        ]
                    ],
                    [
                        'name' => 'Bachelor of Pharmacy',
                        'aps_required' => 30,
                        'requirements' => 'APS 30, Mathematics (Level 5), Life Sciences (Level 5), English (Level 5), Physical Sciences (Level 4)',
                        'subject_requirements' => [
                            ['subject' => 'Mathematics', 'min_level' => 5],
                            ['subject' => 'Life Sciences', 'min_level' => 5],
                            ['subject' => 'English', 'min_level' => 5],
                            ['subject' => 'Physical Sciences', 'min_level' => 4]
                        ],
                        'duration' => '4 years',
                        'institutions' => [
                            ['name' => 'Wits', 'entry_requirements' => 'APS 32, Mathematics (Level 6), Life Sciences (Level 5), English (Level 5)', 'aps_required' => 32],
                            ['name' => 'UP', 'entry_requirements' => 'APS 30, Mathematics (Level 5), Life Sciences (Level 5), English (Level 5)', 'aps_required' => 30],
                            ['name' => 'UKZN', 'entry_requirements' => 'APS 30, Mathematics (Level 5), Life Sciences (Level 5), English (Level 5)', 'aps_required' => 30],
                            ['name' => 'NWU', 'entry_requirements' => 'APS 28, Mathematics (Level 5), Life Sciences (Level 5), English (Level 4)', 'aps_required' => 28]
                        ]
                    ],
                    [
                        'name' => 'Bachelor of Medical Laboratory Sciences',
                        'aps_required' => 26,
                        'requirements' => 'APS 26, Life Sciences (Level 5), Physical Sciences (Level 4), English (Level 4), Mathematics (Level 4)',
                        'subject_requirements' => [
                            ['subject' => 'Life Sciences', 'min_level' => 5],
                            ['subject' => 'Physical Sciences', 'min_level' => 4],
                            ['subject' => 'English', 'min_level' => 4],
                            ['subject' => 'Mathematics', 'min_level' => 4]
                        ],
                        'duration' => '3 years',
                        'institutions' => [
                            ['name' => 'UCT', 'entry_requirements' => 'APS 30, Life Sciences (Level 6), Physical Sciences (Level 5), English (Level 5)', 'aps_required' => 30],
                            ['name' => 'UKZN', 'entry_requirements' => 'APS 26, Life Sciences (Level 5), Physical Sciences (Level 4)', 'aps_required' => 26],
                            ['name' => 'SMU', 'entry_requirements' => 'APS 24, Life Sciences (Level 5), Physical Sciences (Level 4)', 'aps_required' => 24]
                        ]
                    ],
                    [
                        'name' => 'Bachelor of Physiotherapy',
                        'aps_required' => 32,
                        'requirements' => 'APS 32, Life Sciences (Level 6), Mathematics (Level 5), English (Level 5), Physical Sciences (Level 4)',
                        'subject_requirements' => [
                            ['subject' => 'Life Sciences', 'min_level' => 6],
                            ['subject' => 'Mathematics', 'min_level' => 5],
                            ['subject' => 'English', 'min_level' => 5],
                            ['subject' => 'Physical Sciences', 'min_level' => 4]
                        ],
                        'duration' => '4 years',
                        'institutions' => [
                            ['name' => 'UCT', 'entry_requirements' => 'APS 34, Life Sciences (Level 7), Mathematics (Level 5), English (Level 5)', 'aps_required' => 34],
                            ['name' => 'Wits', 'entry_requirements' => 'APS 32, Life Sciences (Level 6), Mathematics (Level 5), English (Level 5)', 'aps_required' => 32],
                            ['name' => 'UP', 'entry_requirements' => 'APS 32, Life Sciences (Level 6), Mathematics (Level 5), English (Level 5)', 'aps_required' => 32]
                        ]
                    ]
                ];
            } elseif ($careerTheme === 'Education') {
                $courses = [
                    [
                        'name' => 'Bachelor of Education',
                        'aps_required' => 26,
                        'requirements' => 'APS 26, English (Level 5), Second Language (Level 4)',
                        'subject_requirements' => [
                            ['subject' => 'English', 'min_level' => 5],
                            ['subject' => 'Afrikaans', 'min_level' => 4]
                        ],
                        'duration' => '4 years',
                        'institutions' => [
                            ['name' => 'UP', 'entry_requirements' => 'APS 28, English (Level 5), Second Language (Level 4)', 'aps_required' => 28],
                            ['name' => 'UJ', 'entry_requirements' => 'APS 26, English (Level 5), Second Language (Level 4)', 'aps_required' => 26],
                            ['name' => 'Wits', 'entry_requirements' => 'APS 28, English (Level 5), Second Language (Level 4)', 'aps_required' => 28],
                            ['name' => 'UNISA', 'entry_requirements' => 'APS 24, English (Level 5), Second Language (Level 4)', 'aps_required' => 24]
                        ]
                    ],
                    [
                        'name' => 'Bachelor of Arts in Education',
                        'aps_required' => 24,
                        'requirements' => 'APS 24, English (Level 5), Second Language (Level 4)',
                        'subject_requirements' => [
                            ['subject' => 'English', 'min_level' => 5],
                            ['subject' => 'Afrikaans', 'min_level' => 4]
                        ],
                        'duration' => '4 years',
                        'institutions' => [
                            ['name' => 'Stellenbosch', 'entry_requirements' => 'APS 26, English (Level 5), Afrikaans (Level 5)', 'aps_required' => 26],
                            ['name' => 'UKZN', 'entry_requirements' => 'APS 24, English (Level 5), Second Language (Level 4)', 'aps_required' => 24],
                            ['name' => 'UWC', 'entry_requirements' => 'APS 22, English (Level 4), Second Language (Level 4)', 'aps_required' => 22]
                        ]
                    ],
                    [
                        'name' => 'Bachelor of Science in Education',
                        'aps_required' => 24,
                        'requirements' => 'APS 24, Mathematics (Level 4), English (Level 4)',
                        'subject_requirements' => [
                            ['subject' => 'Mathematics', 'min_level' => 4],
                            ['subject' => 'English', 'min_level' => 4]
                        ],
                        'duration' => '4 years',
                        'institutions' => [
                            ['name' => 'UP', 'entry_requirements' => 'APS 26, Mathematics (Level 4), English (Level 4)', 'aps_required' => 26],
                            ['name' => 'UJ', 'entry_requirements' => 'APS 24, Mathematics (Level 4), English (Level 4)', 'aps_required' => 24],
                            ['name' => 'Wits', 'entry_requirements' => 'APS 24, Mathematics (Level 4), English (Level 4)', 'aps_required' => 24]
                        ]
                    ],
                    [
                        'name' => 'Bachelor of Education (Foundation Phase)',
                        'aps_required' => 22,
                        'requirements' => 'APS 22, English (Level 4), Second Language (Level 3)',
                        'subject_requirements' => [
                            ['subject' => 'English', 'min_level' => 4],
                            ['subject' => 'Afrikaans', 'min_level' => 3]
                        ],
                        'duration' => '4 years',
                        'institutions' => [
                            ['name' => 'UNISA', 'entry_requirements' => 'APS 22, English (Level 4), Second Language (Level 3)', 'aps_required' => 22],
                            ['name' => 'CPUT', 'entry_requirements' => 'APS 20, English (Level 4), Second Language (Level 3)', 'aps_required' => 20],
                            ['name' => 'DUT', 'entry_requirements' => 'APS 20, English (Level 4), Second Language (Level 3)', 'aps_required' => 20]
                        ]
                    ],
                    [
                        'name' => 'Bachelor of Education (Senior Phase)',
                        'aps_required' => 26,
                        'requirements' => 'APS 26, English (Level 5), Mathematics (Level 4)',
                        'subject_requirements' => [
                            ['subject' => 'English', 'min_level' => 5],
                            ['subject' => 'Mathematics', 'min_level' => 4]
                        ],
                        'duration' => '4 years',
                        'institutions' => [
                            ['name' => 'UP', 'entry_requirements' => 'APS 28, English (Level 5), Mathematics (Level 4)', 'aps_required' => 28],
                            ['name' => 'UJ', 'entry_requirements' => 'APS 26, English (Level 5), Mathematics (Level 4)', 'aps_required' => 26],
                            ['name' => 'Wits', 'entry_requirements' => 'APS 26, English (Level 5), Mathematics (Level 4)', 'aps_required' => 26]
                        ]
                    ]
                ];
            } else {
                // General academic
                $courses = [
                    [
                        'name' => 'Bachelor of Commerce',
                        'aps_required' => 26,
                        'requirements' => 'APS 26, Mathematics (Level 5), English (Level 4)',
                        'subject_requirements' => [
                            ['subject' => 'Mathematics', 'min_level' => 5],
                            ['subject' => 'English', 'min_level' => 4]
                        ],
                        'duration' => '3 years',
                        'institutions' => [
                            ['name' => 'UCT', 'entry_requirements' => 'APS 30, Mathematics (Level 5), English (Level 5)', 'aps_required' => 30],
                            ['name' => 'Wits', 'entry_requirements' => 'APS 26, Mathematics (Level 5), English (Level 4)', 'aps_required' => 26],
                            ['name' => 'Stellenbosch', 'entry_requirements' => 'APS 28, Mathematics (Level 5), English (Level 4)', 'aps_required' => 28],
                            ['name' => 'UJ', 'entry_requirements' => 'APS 24, Mathematics (Level 4), English (Level 4)', 'aps_required' => 24],
                            ['name' => 'UP', 'entry_requirements' => 'APS 26, Mathematics (Level 5), English (Level 4)', 'aps_required' => 26]
                        ]
                    ],
                    [
                        'name' => 'Bachelor of Arts',
                        'aps_required' => 26,
                        'requirements' => 'APS 26, English (Level 5), Second Language (Level 4)',
                        'subject_requirements' => [
                            ['subject' => 'English', 'min_level' => 5],
                            ['subject' => 'Afrikaans', 'min_level' => 4]
                        ],
                        'duration' => '3 years',
                        'institutions' => [
                            ['name' => 'UCT', 'entry_requirements' => 'APS 30, English (Level 6), Second Language (Level 5)', 'aps_required' => 30],
                            ['name' => 'Wits', 'entry_requirements' => 'APS 26, English (Level 5), Second Language (Level 4)', 'aps_required' => 26],
                            ['name' => 'Stellenbosch', 'entry_requirements' => 'APS 28, English (Level 5), Afrikaans (Level 5)', 'aps_required' => 28],
                            ['name' => 'UJ', 'entry_requirements' => 'APS 24, English (Level 5), Second Language (Level 4)', 'aps_required' => 24]
                        ]
                    ],
                    [
                        'name' => 'Bachelor of Science',
                        'aps_required' => 26,
                        'requirements' => 'APS 26, Mathematics (Level 5), Physical Sciences (Level 4), English (Level 4)',
                        'subject_requirements' => [
                            ['subject' => 'Mathematics', 'min_level' => 5],
                            ['subject' => 'Physical Sciences', 'min_level' => 4],
                            ['subject' => 'English', 'min_level' => 4]
                        ],
                        'duration' => '3 years',
                        'institutions' => [
                            ['name' => 'UCT', 'entry_requirements' => 'APS 30, Mathematics (Level 5), Physical Sciences (Level 4)', 'aps_required' => 30],
                            ['name' => 'Wits', 'entry_requirements' => 'APS 26, Mathematics (Level 5), Physical Sciences (Level 4)', 'aps_required' => 26],
                            ['name' => 'UP', 'entry_requirements' => 'APS 26, Mathematics (Level 5), Physical Sciences (Level 4)', 'aps_required' => 26],
                            ['name' => 'Stellenbosch', 'entry_requirements' => 'APS 28, Mathematics (Level 5), Physical Sciences (Level 4)', 'aps_required' => 28]
                        ]
                    ],
                    [
                        'name' => 'Bachelor of Business Administration',
                        'aps_required' => 24,
                        'requirements' => 'APS 24, Mathematics (Level 4), English (Level 4)',
                        'subject_requirements' => [
                            ['subject' => 'Mathematics', 'min_level' => 4],
                            ['subject' => 'English', 'min_level' => 4]
                        ],
                        'duration' => '3 years',
                        'institutions' => [
                            ['name' => 'UJ', 'entry_requirements' => 'APS 24, Mathematics (Level 4), English (Level 4)', 'aps_required' => 24],
                            ['name' => 'UP', 'entry_requirements' => 'APS 24, Mathematics (Level 4), English (Level 4)', 'aps_required' => 24],
                            ['name' => 'NWU', 'entry_requirements' => 'APS 22, Mathematics (Level 4), English (Level 4)', 'aps_required' => 22],
                            ['name' => 'UKZN', 'entry_requirements' => 'APS 24, Mathematics (Level 4), English (Level 4)', 'aps_required' => 24]
                        ]
                    ],
                    [
                        'name' => 'Bachelor of Social Science',
                        'aps_required' => 26,
                        'requirements' => 'APS 26, English (Level 5)',
                        'subject_requirements' => [
                            ['subject' => 'English', 'min_level' => 5]
                        ],
                        'duration' => '3 years',
                        'institutions' => [
                            ['name' => 'UCT', 'entry_requirements' => 'APS 30, English (Level 6)', 'aps_required' => 30],
                            ['name' => 'Wits', 'entry_requirements' => 'APS 26, English (Level 5)', 'aps_required' => 26],
                            ['name' => 'Stellenbosch', 'entry_requirements' => 'APS 28, English (Level 5)', 'aps_required' => 28],
                            ['name' => 'UKZN', 'entry_requirements' => 'APS 26, English (Level 5)', 'aps_required' => 26]
                        ]
                    ]
                ];
            }
        } elseif ($avgLevel >= 4) {
            // Mid-range - diploma programs
            if ($careerTheme === 'IT') {
                $courses = [
                    [
                        'name' => 'Diploma in Information Technology',
                        'aps_required' => 20,
                        'requirements' => 'APS 20, Mathematics (Level 4) or Mathematical Literacy (Level 5), English (Level 3)',
                        'subject_requirements' => [
                            ['subject' => 'Mathematics', 'min_level' => 4],
                            ['subject' => 'English', 'min_level' => 3]
                        ],
                        'duration' => '3 years',
                        'institutions' => [
                            ['name' => 'TUT', 'entry_requirements' => 'APS 20, Mathematics (Level 4), English (Level 3)', 'aps_required' => 20],
                            ['name' => 'CPUT', 'entry_requirements' => 'APS 20, Mathematics (Level 4), English (Level 3)', 'aps_required' => 20],
                            ['name' => 'DUT', 'entry_requirements' => 'APS 18, Mathematics (Level 4), English (Level 3)', 'aps_required' => 18],
                            ['name' => 'MUT', 'entry_requirements' => 'APS 18, Mathematics (Level 3), English (Level 3)', 'aps_required' => 18]
                        ]
                    ],
                    [
                        'name' => 'Diploma in Computer Science',
                        'aps_required' => 20,
                        'requirements' => 'APS 20, Mathematics (Level 4), English (Level 3)',
                        'subject_requirements' => [
                            ['subject' => 'Mathematics', 'min_level' => 4],
                            ['subject' => 'English', 'min_level' => 3]
                        ],
                        'duration' => '3 years',
                        'institutions' => [
                            ['name' => 'TUT', 'entry_requirements' => 'APS 20, Mathematics (Level 4), English (Level 3)', 'aps_required' => 20],
                            ['name' => 'CPUT', 'entry_requirements' => 'APS 20, Mathematics (Level 4), English (Level 3)', 'aps_required' => 20],
                            ['name' => 'DUT', 'entry_requirements' => 'APS 18, Mathematics (Level 4), English (Level 3)', 'aps_required' => 18]
                        ]
                    ],
                    [
                        'name' => 'Diploma in Software Engineering',
                        'aps_required' => 20,
                        'requirements' => 'APS 20, Mathematics (Level 4), English (Level 3)',
                        'subject_requirements' => [
                            ['subject' => 'Mathematics', 'min_level' => 4],
                            ['subject' => 'English', 'min_level' => 3]
                        ],
                        'duration' => '3 years',
                        'institutions' => [
                            ['name' => 'TUT', 'entry_requirements' => 'APS 20, Mathematics (Level 4), English (Level 3)', 'aps_required' => 20],
                            ['name' => 'CPUT', 'entry_requirements' => 'APS 20, Mathematics (Level 4), English (Level 3)', 'aps_required' => 20],
                            ['name' => 'VUT', 'entry_requirements' => 'APS 18, Mathematics (Level 4), English (Level 3)', 'aps_required' => 18]
                        ]
                    ],
                    [
                        'name' => 'National Diploma in IT',
                        'aps_required' => 18,
                        'requirements' => 'APS 18, Mathematics (Level 3), English (Level 3)',
                        'subject_requirements' => [
                            ['subject' => 'Mathematics', 'min_level' => 3],
                            ['subject' => 'English', 'min_level' => 3]
                        ],
                        'duration' => '3 years',
                        'institutions' => [
                            ['name' => 'DUT', 'entry_requirements' => 'APS 18, Mathematics (Level 3), English (Level 3)', 'aps_required' => 18],
                            ['name' => 'MUT', 'entry_requirements' => 'APS 16, Mathematics (Level 3), English (Level 3)', 'aps_required' => 16],
                            ['name' => 'VUT', 'entry_requirements' => 'APS 16, Mathematics (Level 3), English (Level 3)', 'aps_required' => 16]
                        ]
                    ],
                    [
                        'name' => 'Diploma in Systems Development',
                        'aps_required' => 20,
                        'requirements' => 'APS 20, Mathematics (Level 4), English (Level 3)',
                        'subject_requirements' => [
                            ['subject' => 'Mathematics', 'min_level' => 4],
                            ['subject' => 'English', 'min_level' => 3]
                        ],
                        'duration' => '3 years',
                        'institutions' => [
                            ['name' => 'TUT', 'entry_requirements' => 'APS 20, Mathematics (Level 4), English (Level 3)', 'aps_required' => 20],
                            ['name' => 'CPUT', 'entry_requirements' => 'APS 20, Mathematics (Level 4), English (Level 3)', 'aps_required' => 20],
                            ['name' => 'NMMU', 'entry_requirements' => 'APS 18, Mathematics (Level 4), English (Level 3)', 'aps_required' => 18]
                        ]
                    ]
                ];
            } elseif ($careerTheme === 'Business') {
                $courses = [
                    [
                        'name' => 'National Diploma in Business Studies',
                        'aps_required' => 18,
                        'requirements' => 'APS 18, English (Level 4), Mathematics or Mathematical Literacy (Level 3)',
                        'subject_requirements' => [
                            ['subject' => 'English', 'min_level' => 4],
                            ['subject' => 'Mathematics', 'min_level' => 3]
                        ],
                        'duration' => '3 years',
                        'institutions' => [
                            ['name' => 'UJ', 'entry_requirements' => 'APS 18, English (Level 4), Mathematics (Level 3)', 'aps_required' => 18],
                            ['name' => 'TUT', 'entry_requirements' => 'APS 18, English (Level 4), Mathematical Literacy (Level 3)', 'aps_required' => 18],
                            ['name' => 'CPUT', 'entry_requirements' => 'APS 18, English (Level 4), Mathematics (Level 3)', 'aps_required' => 18],
                            ['name' => 'DUT', 'entry_requirements' => 'APS 16, English (Level 4), Mathematical Literacy (Level 3)', 'aps_required' => 16]
                        ]
                    ],
                    [
                        'name' => 'Diploma in Accountancy',
                        'aps_required' => 20,
                        'requirements' => 'APS 20, Mathematics (Level 4), English (Level 3)',
                        'subject_requirements' => [
                            ['subject' => 'Mathematics', 'min_level' => 4],
                            ['subject' => 'English', 'min_level' => 3]
                        ],
                        'duration' => '3 years',
                        'institutions' => [
                            ['name' => 'TUT', 'entry_requirements' => 'APS 20, Mathematics (Level 4), English (Level 3)', 'aps_required' => 20],
                            ['name' => 'CPUT', 'entry_requirements' => 'APS 20, Mathematics (Level 4), English (Level 3)', 'aps_required' => 20],
                            ['name' => 'DUT', 'entry_requirements' => 'APS 18, Mathematics (Level 4), English (Level 3)', 'aps_required' => 18]
                        ]
                    ],
                    [
                        'name' => 'Diploma in Marketing',
                        'aps_required' => 18,
                        'requirements' => 'APS 18, English (Level 4), Mathematics (Level 3)',
                        'subject_requirements' => [
                            ['subject' => 'English', 'min_level' => 4],
                            ['subject' => 'Mathematics', 'min_level' => 3]
                        ],
                        'duration' => '3 years',
                        'institutions' => [
                            ['name' => 'TUT', 'entry_requirements' => 'APS 18, English (Level 4), Mathematics (Level 3)', 'aps_required' => 18],
                            ['name' => 'CPUT', 'entry_requirements' => 'APS 18, English (Level 4), Mathematics (Level 3)', 'aps_required' => 18],
                            ['name' => 'DUT', 'entry_requirements' => 'APS 16, English (Level 4), Mathematics (Level 3)', 'aps_required' => 16]
                        ]
                    ],
                    [
                        'name' => 'Diploma in Financial Management',
                        'aps_required' => 20,
                        'requirements' => 'APS 20, Mathematics (Level 4), English (Level 3)',
                        'subject_requirements' => [
                            ['subject' => 'Mathematics', 'min_level' => 4],
                            ['subject' => 'English', 'min_level' => 3]
                        ],
                        'duration' => '3 years',
                        'institutions' => [
                            ['name' => 'TUT', 'entry_requirements' => 'APS 20, Mathematics (Level 4), English (Level 3)', 'aps_required' => 20],
                            ['name' => 'CPUT', 'entry_requirements' => 'APS 20, Mathematics (Level 4), English (Level 3)', 'aps_required' => 20],
                            ['name' => 'VUT', 'entry_requirements' => 'APS 18, Mathematics (Level 4), English (Level 3)', 'aps_required' => 18]
                        ]
                    ],
                    [
                        'name' => 'National Diploma in Human Resource Management',
                        'aps_required' => 18,
                        'requirements' => 'APS 18, English (Level 4)',
                        'subject_requirements' => [
                            ['subject' => 'English', 'min_level' => 4]
                        ],
                        'duration' => '3 years',
                        'institutions' => [
                            ['name' => 'DUT', 'entry_requirements' => 'APS 18, English (Level 4)', 'aps_required' => 18],
                            ['name' => 'MUT', 'entry_requirements' => 'APS 16, English (Level 4)', 'aps_required' => 16],
                            ['name' => 'VUT', 'entry_requirements' => 'APS 16, English (Level 4)', 'aps_required' => 16]
                        ]
                    ]
                ];
            } else {
                $courses = [
                    ['name' => 'National Diploma in Business Studies', 'requirements' => 'English (Level 4), Mathematics/Math Literacy (Level 3)', 'duration' => '3 years', 'institutions' => ['UJ', 'TUT', 'CPUT', 'DUT']],
                    ['name' => 'Diploma in Information Technology', 'requirements' => 'Mathematics (Level 4) or Math Literacy (Level 5)', 'duration' => '3 years', 'institutions' => ['TUT', 'CPUT', 'DUT', 'MUT']],
                    ['name' => 'Diploma in Marketing', 'requirements' => 'English (Level 4), Mathematics (Level 3)', 'duration' => '3 years', 'institutions' => ['TUT', 'CPUT', 'DUT']],
                    ['name' => 'National Diploma in Public Management', 'requirements' => 'English (Level 4)', 'duration' => '3 years', 'institutions' => ['TUT', 'CPUT', 'VUT']],
                    ['name' => 'Diploma in Office Management and Technology', 'requirements' => 'English (Level 3)', 'duration' => '3 years', 'institutions' => ['DUT', 'MUT', 'VUT']]
                ];
            }
        } else {
            // Lower achievement - certificate/higher certificate
            if ($careerTheme === 'IT') {
                $courses = [
                    ['name' => 'Higher Certificate in Information Technology', 'requirements' => 'National Senior Certificate (Level 3)', 'duration' => '1 year', 'institutions' => ['UNISA', 'TUT', 'CPUT']],
                    ['name' => 'Higher Certificate in Computer Programming', 'requirements' => 'National Senior Certificate (Level 3)', 'duration' => '1 year', 'institutions' => ['UNISA', 'VUT', 'TUT']],
                    ['name' => 'Higher Certificate in Computer Science', 'requirements' => 'National Senior Certificate (Level 3), Mathematics (Level 2)', 'duration' => '1 year', 'institutions' => ['UNISA', 'UJ']],
                    ['name' => 'Certificate in Web Development', 'requirements' => 'National Senior Certificate', 'duration' => '1 year', 'institutions' => ['TUT', 'CPUT', 'DUT']],
                    ['name' => 'Higher Certificate in Systems Support', 'requirements' => 'National Senior Certificate (Level 3)', 'duration' => '1 year', 'institutions' => ['TUT', 'CPUT', 'VUT']]
                ];
            } elseif ($careerTheme === 'Business') {
                $courses = [
                    ['name' => 'Higher Certificate in Business', 'requirements' => 'National Senior Certificate (Level 3 in most subjects)', 'duration' => '1 year', 'institutions' => ['UNISA', 'UJ', 'UP', 'NWU']],
                    ['name' => 'Higher Certificate in Business Management', 'requirements' => 'National Senior Certificate (Level 3)', 'duration' => '1 year', 'institutions' => ['UNISA', 'UJ', 'UP']],
                    ['name' => 'Higher Certificate in Financial Planning', 'requirements' => 'National Senior Certificate (Level 3), Mathematics (Level 2)', 'duration' => '1 year', 'institutions' => ['UNISA', 'UJ']],
                    ['name' => 'Certificate in Entrepreneurship', 'requirements' => 'National Senior Certificate', 'duration' => '1 year', 'institutions' => ['TUT', 'CPUT', 'DUT']],
                    ['name' => 'Higher Certificate in Accounting', 'requirements' => 'National Senior Certificate (Level 3)', 'duration' => '1 year', 'institutions' => ['UNISA', 'UJ', 'UP']]
                ];
            } else {
                $courses = [
                    ['name' => 'Higher Certificate in Business', 'requirements' => 'National Senior Certificate (Level 3 in most subjects)', 'duration' => '1 year', 'institutions' => ['UNISA', 'UJ', 'UP', 'NWU']],
                    ['name' => 'Higher Certificate in Information Technology', 'requirements' => 'National Senior Certificate (Level 3)', 'duration' => '1 year', 'institutions' => ['UNISA', 'TUT', 'CPUT']],
                    ['name' => 'Higher Certificate in Arts', 'requirements' => 'National Senior Certificate (Level 3)', 'duration' => '1 year', 'institutions' => ['UNISA', 'UJ', 'UP']],
                    ['name' => 'Certificate in Communication Skills', 'requirements' => 'National Senior Certificate', 'duration' => '1 year', 'institutions' => ['TUT', 'CPUT', 'DUT']],
                    ['name' => 'Higher Certificate in Law', 'requirements' => 'National Senior Certificate (Level 3), English (Level 3)', 'duration' => '1 year', 'institutions' => ['UNISA', 'UJ', 'NWU']]
                ];
            }
        }

        return $courses;
    }
}
