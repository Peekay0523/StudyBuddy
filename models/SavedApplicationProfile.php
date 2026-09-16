<?php
/**
 * SavedApplicationProfile Model
 * Stores personal/academic info so students don't have to re-enter it for future applications.
 */

class SavedApplicationProfile {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Find saved profile by student ID
     */
    public function findByStudentId($studentId) {
        $stmt = $this->db->prepare("SELECT * FROM saved_application_profiles WHERE student_id = ? ORDER BY updated_at DESC LIMIT 1");
        $stmt->execute([$studentId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Save or update profile for a student (upsert)
     */
    public function save($studentId, $data) {
        $existing = $this->findByStudentId($studentId);
        
        $fields = [
            'full_name', 'surname', 'id_number', 'date_of_birth', 'gender',
            'citizenship', 'email', 'phone', 'home_address',
            'school_name', 'matric_year', 'aps_score', 'grade_12_results', 'subjects'
        ];
        
        if ($existing) {
            // Update
            $setClauses = [];
            $params = [];
            foreach ($fields as $field) {
                $setClauses[] = "$field = ?";
                $params[] = $data[$field] ?? null;
            }
            $setClauses[] = "updated_at = CURRENT_TIMESTAMP";
            $params[] = $studentId;
            
            $sql = "UPDATE saved_application_profiles SET " . implode(', ', $setClauses) . " WHERE student_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } else {
            // Insert
            $fieldNames = array_merge(['student_id'], $fields);
            $placeholders = array_fill(0, count($fieldNames), '?');
            $params = array_merge([$studentId], array_map(function($f) use ($data) { return $data[$f] ?? null; }, $fields));
            
            $sql = "INSERT INTO saved_application_profiles (" . implode(', ', $fieldNames) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        }
    }

    /**
     * Delete saved profile for a student
     */
    public function delete($studentId) {
        $stmt = $this->db->prepare("DELETE FROM saved_application_profiles WHERE student_id = ?");
        return $stmt->execute([$studentId]);
    }
}