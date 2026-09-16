<?php
/**
 * InstitutionApplication Model
 */

class InstitutionApplication {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($studentId, $institutionName, $courseName = null, $institutionType = 'university', $status = 'pending', $deadline = null, $notes = null) {
        $stmt = $this->db->prepare("
            INSERT INTO institution_applications (student_id, institution_name, course_name, institution_type, application_status, deadline, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $studentId,
            $institutionName,
            $courseName,
            $institutionType,
            $status,
            $deadline,
            $notes
        ]);

        return $this->db->lastInsertId();
    }

    public function findByStudentId($studentId) {
        // Get manually added institution applications
        $stmt = $this->db->prepare("SELECT * FROM institution_applications WHERE student_id = ? ORDER BY applied_date DESC");
        $stmt->execute([$studentId]);
        $manualApps = $stmt->fetchAll();
        
        // Also get institutions from Apply For Me submissions
        $applyForMeApps = [];
        try {
            $stmt2 = $this->db->prepare("
                SELECT 
                    afs.id,
                    afs.student_id,
                    afs.university_preference_1 as institution_name,
                    afs.course_preference_1 as course_name,
                    'university' as institution_type,
                    afs.institution_1_status as application_status,
                    afs.created_at as applied_date,
                    'apply_for_me' as source,
                    afs.institution_1_notes as notes
                FROM apply_for_me_submissions afs
                WHERE afs.student_id = ? 
                AND afs.university_preference_1 IS NOT NULL 
                AND afs.university_preference_1 != ''
                AND afs.institution_1_status IN ('completed', 'submitted_to_institution', 'in_progress', 'rejected')
                
                UNION ALL
                
                SELECT 
                    afs.id,
                    afs.student_id,
                    afs.university_preference_2 as institution_name,
                    afs.course_preference_2 as course_name,
                    'university' as institution_type,
                    afs.institution_2_status as application_status,
                    afs.created_at as applied_date,
                    'apply_for_me' as source,
                    afs.institution_2_notes as notes
                FROM apply_for_me_submissions afs
                WHERE afs.student_id = ? 
                AND afs.university_preference_2 IS NOT NULL 
                AND afs.university_preference_2 != ''
                AND afs.institution_2_status IN ('completed', 'submitted_to_institution', 'in_progress', 'rejected')
                
                UNION ALL
                
                SELECT 
                    afs.id,
                    afs.student_id,
                    afs.university_preference_3 as institution_name,
                    afs.course_preference_3 as course_name,
                    'university' as institution_type,
                    afs.institution_3_status as application_status,
                    afs.created_at as applied_date,
                    'apply_for_me' as source,
                    afs.institution_3_notes as notes
                FROM apply_for_me_submissions afs
                WHERE afs.student_id = ? 
                AND afs.university_preference_3 IS NOT NULL 
                AND afs.university_preference_3 != ''
                AND afs.institution_3_status IN ('completed', 'submitted_to_institution', 'in_progress', 'rejected')
                
                ORDER BY applied_date DESC
            ");
            $stmt2->execute([$studentId, $studentId, $studentId]);
            $applyForMeApps = $stmt2->fetchAll();
        } catch (PDOException $e) {
            // Table might not have institution status columns yet
        }
        
        // Merge and deduplicate
        return array_merge($manualApps, $applyForMeApps);
    }

    public function countByStudent($studentId) {
        // Count manually added applications
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM institution_applications WHERE student_id = ?");
        $stmt->execute([$studentId]);
        $manualCount = (int) $stmt->fetchColumn();
        
        // Count institutions from Apply For Me submissions
        $applyForMeCount = 0;
        try {
            $stmt2 = $this->db->prepare("
                SELECT COUNT(*) FROM (
                    SELECT afs.university_preference_1
                    FROM apply_for_me_submissions afs
                    WHERE afs.student_id = ? 
                    AND afs.university_preference_1 IS NOT NULL 
                    AND afs.university_preference_1 != ''
                    AND afs.institution_1_status IN ('completed', 'submitted_to_institution', 'in_progress', 'rejected')
                    
                    UNION ALL
                    
                    SELECT afs.university_preference_2
                    FROM apply_for_me_submissions afs
                    WHERE afs.student_id = ? 
                    AND afs.university_preference_2 IS NOT NULL 
                    AND afs.university_preference_2 != ''
                    AND afs.institution_2_status IN ('completed', 'submitted_to_institution', 'in_progress', 'rejected')
                    
                    UNION ALL
                    
                    SELECT afs.university_preference_3
                    FROM apply_for_me_submissions afs
                    WHERE afs.student_id = ? 
                    AND afs.university_preference_3 IS NOT NULL 
                    AND afs.university_preference_3 != ''
                    AND afs.institution_3_status IN ('completed', 'submitted_to_institution', 'in_progress', 'rejected')
                )
            ");
            $stmt2->execute([$studentId, $studentId, $studentId]);
            $applyForMeCount = (int) $stmt2->fetchColumn();
        } catch (PDOException $e) {
            // Table might not have institution status columns yet
        }
        
        return $manualCount + $applyForMeCount;
    }

    public function countByStatus($studentId, $status) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM institution_applications WHERE student_id = ? AND application_status = ?");
        $stmt->execute([$studentId, $status]);
        return (int) $stmt->fetchColumn();
    }

    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE institution_applications SET application_status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    public function delete($id, $studentId) {
        $stmt = $this->db->prepare("DELETE FROM institution_applications WHERE id = ? AND student_id = ?");
        return $stmt->execute([$id, $studentId]);
    }

    public function getRecentApplications($studentId, $limit = 5) {
        $stmt = $this->db->prepare("
            SELECT * FROM institution_applications 
            WHERE student_id = ? 
            ORDER BY applied_date DESC 
            LIMIT ?
        ");
        $stmt->execute([$studentId, $limit]);
        return $stmt->fetchAll();
    }
}
