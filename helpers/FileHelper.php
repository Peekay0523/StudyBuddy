<?php
/**
 * File Helper - File upload and text extraction
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Smalot\PdfParser\Parser;

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
        }
        
        return $text;
    }
    
    private static function extractTextFromPdf($filePath) {
        try {
            // Use PDFParser library for reliable PDF text extraction
            $parser = new Parser();
            $pdf = $parser->parseFile($filePath);
            $text = $pdf->getText();
            
            // Clean up the text
            $text = trim(preg_replace('/\s+/', ' ', $text));
            
            return $text;
        } catch (Exception $e) {
            error_log("PDF Parser Error: " . $e->getMessage());
            
            // Fallback: PHP-based PDF text extraction
            $content = file_get_contents($filePath);
            if (!$content) {
                return '';
            }

            $text = '';
            
            // Extract text from PDF streams
            if (preg_match_all('/BT\s+(.*?)\s*ET/s', $content, $matches)) {
                foreach ($matches[1] as $block) {
                    if (preg_match_all('/\((.*?)\)\s*Tj/', $block, $textMatches)) {
                        foreach ($textMatches[1] as $t) {
                            $text .= $t . ' ';
                        }
                    }
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

            if (empty(trim($text))) {
                $text = preg_replace('/[^\x20-\x7E\s]/', '', $content);
                $text = preg_replace('/\s+/', ' ', $text);
            }

            return trim($text);
        }
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
        // Simple regex to extract subject-grade pairs
        $pattern = '/([A-Za-z\s]+?)\s*:?\s*([A-D][+-]?|F|[0-9]{1,3}%|[0-9]{1,3})/i';
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);
        
        $grades = [];
        foreach ($matches as $match) {
            $subject = trim($match[1]);
            $grade = trim($match[2]);
            if ($subject && $grade) {
                $grades[$subject] = $grade;
            }
        }
        
        return $grades;
    }
}
