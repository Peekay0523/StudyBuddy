<?php
/**
 * File Helper - File upload and text extraction
 */

// Check if composer autoload exists
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

class FileHelper {
    
    public static function validateUpload($file, $allowedExtensions, $maxSize = MAX_FILE_SIZE) {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'error' => 'File upload failed'];
        }
        
        if ($file['size'] > $maxSize) {
            return ['valid' => false, 'error' => 'File is too large'];
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions)) {
            return ['valid' => false, 'error' => 'Invalid file type'];
        }
        
        return ['valid' => true];
    }
    
    public static function saveUploadedFile($file, $destinationDir) {
        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }
        
        $fileName = uniqid() . '_' . basename($file['name']);
        $filePath = $destinationDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            return $fileName;
        }
        
        return false;
    }
    
    public static function extractTextFromFile($filePath) {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $text = '';

        switch ($extension) {
            case 'txt':
                $text = file_get_contents($filePath);
                break;

            case 'pdf':
                $text = self::extractTextFromPdf($filePath);
                break;

            case 'docx':
                $text = self::extractTextFromDocx($filePath);
                break;
                
            default:
                error_log("Unsupported file extension: " . $extension);
                return '';
        }

        if (empty(trim($text))) {
            error_log("Empty text extracted from file: " . $filePath . " (extension: " . $extension . ")");
        }

        return $text;
    }
    
    private static function extractTextFromPdf($filePath) {
        $text = '';
        
        // Try 1: Use PDFParser library if available
        if (class_exists('Smalot\PdfParser\Parser')) {
            try {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($filePath);
                $text = $pdf->getText();
                
                // Clean up the text
                $text = trim(preg_replace('/\s+/', ' ', $text));
                
                if (!empty(trim($text))) {
                    error_log("PDFParser successfully extracted " . strlen($text) . " characters");
                    return $text;
                }
            } catch (Exception $e) {
                error_log("PDFParser Error: " . $e->getMessage());
            }
        } else {
            error_log("PDFParser library not installed. Run: composer require smalot/pdf-parser");
        }

        // Try 2: Fallback - Manual PDF text extraction
        error_log("Attempting manual PDF text extraction for: $filePath");
        $content = file_get_contents($filePath);
        if (!$content) {
            error_log("Failed to read PDF file content");
            return '';
        }

        $text = '';

        // Extract text from PDF streams (BT...ET blocks)
        if (preg_match_all('/BT\s+(.*?)\s*ET/s', $content, $matches)) {
            foreach ($matches[1] as $block) {
                // Extract text from Tj operator
                if (preg_match_all('/\((.*?)\)\s*Tj/', $block, $textMatches)) {
                    foreach ($textMatches[1] as $t) {
                        $text .= $t . ' ';
                    }
                }
                // Extract text from TJ operator
                if (preg_match_all('/\[(.*?)\]\s*TJ/si', $block, $arrayMatches)) {
                    foreach ($arrayMatches[1] as $arr) {
                        if (preg_match_all('/\((.*?)\)/', $arr, $items)) {
                            foreach ($items[1] as $item) {
                                $text .= $item . ' ';
                            }
                        }
                    }
                }
            }
        }

        // Try 3: Extract from FlateDecode streams
        if (empty(trim($text)) && preg_match_all('/\/FlateDecode.*?stream\s+(.*?)\s+endstream/s', $content, $streamMatches)) {
            foreach ($streamMatches[1] as $stream) {
                $decoded = @gzuncompress($stream);
                if ($decoded && preg_match_all('/\((.*?)\)\s*Tj/', $decoded, $decodedMatches)) {
                    foreach ($decodedMatches[1] as $t) {
                        $text .= $t . ' ';
                    }
                }
            }
        }

        // Try 4: Extract any readable text
        if (empty(trim($text))) {
            error_log("Manual extraction failed, trying raw text extraction");
            $text = preg_replace('/[^\x20-\x7E\s]/', '', $content);
            $text = preg_replace('/\s+/', ' ', $text);
            $text = trim($text);
        }

        // Log result
        if (!empty(trim($text))) {
            error_log("Manual extraction successful: " . strlen($text) . " characters");
        } else {
            error_log("All extraction methods failed for: $filePath");
        }

        return $text;
    }
    
    private static function extractTextFromDocx($filePath) {
        // Simple DOCX text extraction
        // For production, use a PHP library like phpoffice/phpword
        
        $zip = new ZipArchive();
        if ($zip->open($filePath) === true) {
            $xml = $zip->getFromName('word/document.xml');
            if ($xml) {
                $text = strip_tags($xml);
                $text = preg_replace('/\s+/', ' ', $text);
                $zip->close();
                return trim($text);
            }
            $zip->close();
        }
        
        return '';
    }
    
    public static function extractGradesFromText($content) {
        // Clean up OCR artifacts - join fragmented words
        $content = preg_replace('/\s+/', ' ', $content);
        
        $grades = [];
        
        // South African Matric subjects and levels (1-7) or percentages
        $subjects = [
            'Mathematics', 'Maths', 'Math',
            'English', 'Home Language', 'First Additional Language', 'Setswana', 'Sepedi', 'Sesotho', 'Zulu', 'Xhosa',
            'Physical Sciences', 'Physics', 'Chemistry',
            'Life Sciences', 'Biology',
            'Geography',
            'History',
            'Accounting',
            'Business Studies',
            'Economics',
            'Life Orientation', 'LO',
            'Visual Arts', 'Dramatic Arts',
            'Computer Applications Technology', 'CAT',
            'Information Technology', 'IT',
            'Engineering Graphics Design', 'EGD',
            'Civil Technology', 'Mechanical Technology', 'Electrical Technology'
        ];
        
        // Try to find subjects with their grades/levels
        // Pattern 1: "SubjectName 72 6" or "SubjectName 72%"
        foreach ($subjects as $subject) {
            // Look for subject followed by numbers (percentage and/or level)
            $pattern = '/' . preg_quote($subject, '/') . '\s+(\d{1,3})\s+(\d{1,2})?/i';
            
            if (preg_match($pattern, $content, $matches)) {
                $grade = trim($matches[1]);
                $level = isset($matches[2]) ? trim($matches[2]) : null;
                
                // Use percentage if it looks like one (40-100), otherwise use level
                if (intval($grade) >= 40 && intval($grade) <= 100) {
                    $grades[$subject] = $grade . '%';
                } elseif ($level && intval($level) >= 1 && intval($level) <= 7) {
                    $grades[$subject] = 'Level ' . $level;
                } else {
                    $grades[$subject] = $grade;
                }
            }
        }
        
        // Pattern 2: Table format - subject followed by percentage then level
        if (empty($grades)) {
            $tablePattern = '/([A-Z][a-z]+(?:\s+[A-Z][a-z]+)*)\s+(\d{2,3})\s+(\d{1,2})/i';
            preg_match_all($tablePattern, $content, $matches, PREG_SET_ORDER);
            
            foreach ($matches as $match) {
                $subject = trim($match[1]);
                $percentage = trim($match[2]);
                $level = trim($match[3]);
                
                // Filter out non-subject words
                $excludeWords = ['The', 'This', 'That', 'With', 'From', 'South', 'Africa', 'National', 'Senior', 'Certificate', 'Awarded', 'Identity', 'number', 'Achievement', 'candidate', 'has', 'met', 'minimum', 'requirements', 'admission', 'bachelor', 'degree', 'diploma', 'higher', 'certificate', 'study', 'gazetted', 'education', 'subject', 'institutions', 'concerned', 'effect', 'from', 'December', 'Chief', 'Executive', 'Officer', 'Council', 'Quality', 'Assurance', 'General', 'Further', 'and', 'Training'];
                
                if (strlen($subject) > 3 && !in_array($subject, $excludeWords) && intval($percentage) >= 40) {
                    $grades[$subject] = $percentage . '%';
                }
            }
        }
        
        // If still no grades, return subjects found without grades
        if (empty($grades)) {
            foreach ($subjects as $subject) {
                if (stripos($content, $subject) !== false) {
                    $grades[$subject] = 'Not specified';
                }
            }
        }
        
        return $grades;
    }
}
