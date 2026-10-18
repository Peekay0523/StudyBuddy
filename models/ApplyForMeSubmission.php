<?php
/**
 * ApplyForMeSubmission Model
 */

class ApplyForMeSubmission {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($data) {
        $fields = [
            'student_id', 'full_name', 'surname', 'id_number', 'date_of_birth', 'gender', 'citizenship',
            'email', 'phone', 'home_address', 'school_name', 'grade_12_results', 'aps_score', 'subjects',
            'matric_year', 'university_preference_1', 'university_preference_2', 'university_preference_3',
            'course_preference_1', 'course_preference_2', 'course_preference_3', 'id_copy_path',
            'matric_cert_path', 'transcript_path', 'proof_res_path', 'cv_path',
            'payment_status', 'payment_amount', 'application_status', 'policy_accepted',
            'bursary_name', 'bursary_provider', 'institution_name', 'institution_type'
        ];

        $placeholderStrings = array_fill(0, count($fields), '?');
        $sql = "INSERT INTO apply_for_me_submissions (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholderStrings) . ")";
        
        $stmt = $this->db->prepare($sql);
        
        $params = [];
        foreach ($fields as $field) {
            $params[] = $data[$field] ?? null;
        }
        
        $stmt->execute($params);
        return $this->db->lastInsertId();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM apply_for_me_submissions WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByIdWithUser($id) {
        $stmt = $this->db->prepare("
            SELECT afs.*, u.username, u.email as user_email, u.phone as user_phone,
                   s.student_id as student_number
            FROM apply_for_me_submissions afs
            LEFT JOIN students s ON afs.student_id = s.id
            LEFT JOIN users u ON s.user_id = u.id
            WHERE afs.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByStudentId($studentId) {
        $stmt = $this->db->prepare("SELECT * FROM apply_for_me_submissions WHERE student_id = ? ORDER BY created_at DESC");
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    public function findByStudentUserId($userId) {
        $stmt = $this->db->prepare("
            SELECT afs.*
            FROM apply_for_me_submissions afs
            JOIN students s ON afs.student_id = s.id
            WHERE s.user_id = ?
            ORDER BY afs.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function findAllWithUser($filters = []) {
        $query = "
            SELECT afs.*, u.username, u.email as user_email, u.phone as user_phone,
                   s.student_id as student_number, u.id as user_id,
                   v.username as verified_by_name
            FROM apply_for_me_submissions afs
            LEFT JOIN students s ON afs.student_id = s.id
            LEFT JOIN users u ON s.user_id = u.id
            LEFT JOIN users v ON afs.verified_by = v.id
        ";

        $where = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "afs.application_status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['payment_status'])) {
            $where[] = "afs.payment_status = ?";
            $params[] = $filters['payment_status'];
        }

        if (!empty($filters['document_verification'])) {
            $where[] = "afs.document_verification = ?";
            $params[] = $filters['document_verification'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(afs.full_name LIKE ? OR afs.surname LIKE ? OR afs.email LIKE ? OR afs.id_number LIKE ? OR u.username LIKE ?)";
            $search = "%{$filters['search']}%";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        if (!empty($where)) {
            $query .= " WHERE " . implode(' AND ', $where);
        }

        $query .= " ORDER BY afs.created_at DESC";

        if (!empty($filters['limit'])) {
            $query .= " LIMIT ?";
            $params[] = $filters['limit'];
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countAll($filters = []) {
        $query = "SELECT COUNT(*) FROM apply_for_me_submissions afs LEFT JOIN students s ON afs.student_id = s.id LEFT JOIN users u ON s.user_id = u.id";

        $where = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "afs.application_status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['payment_status'])) {
            $where[] = "afs.payment_status = ?";
            $params[] = $filters['payment_status'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(afs.full_name LIKE ? OR afs.surname LIKE ? OR afs.email LIKE ? OR afs.id_number LIKE ? OR u.username LIKE ?)";
            $search = "%{$filters['search']}%";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        if (!empty($where)) {
            $query .= " WHERE " . implode(' AND ', $where);
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    public function getStats() {
        $stats = [];
        $stats['total'] = $this->db->query("SELECT COUNT(*) FROM apply_for_me_submissions")->fetchColumn();
        $stats['pending_review'] = $this->db->query("SELECT COUNT(*) FROM apply_for_me_submissions WHERE document_verification = 'pending'")->fetchColumn();
        $stats['documents_valid'] = $this->db->query("SELECT COUNT(*) FROM apply_for_me_submissions WHERE document_verification = 'valid'")->fetchColumn();
        $stats['documents_invalid'] = $this->db->query("SELECT COUNT(*) FROM apply_for_me_submissions WHERE document_verification = 'invalid'")->fetchColumn();
        $stats['paid'] = $this->db->query("SELECT COUNT(*) FROM apply_for_me_submissions WHERE payment_status = 'paid'")->fetchColumn();
        $stats['unpaid'] = $this->db->query("SELECT COUNT(*) FROM apply_for_me_submissions WHERE payment_status = 'pending'")->fetchColumn();
        $stats['submitted'] = $this->db->query("SELECT COUNT(*) FROM apply_for_me_submissions WHERE application_status = 'submitted'")->fetchColumn();
        $stats['in_progress'] = $this->db->query("SELECT COUNT(*) FROM apply_for_me_submissions WHERE application_status = 'in_progress'")->fetchColumn();
        $stats['completed'] = $this->db->query("SELECT COUNT(*) FROM apply_for_me_submissions WHERE application_status = 'completed'")->fetchColumn();
        $stats['rejected'] = $this->db->query("SELECT COUNT(*) FROM apply_for_me_submissions WHERE application_status = 'rejected'")->fetchColumn();
        return $stats;
    }

    public function updateDocumentVerification($id, $status, $notes = null, $verifiedBy = null) {
        $stmt = $this->db->prepare("
            UPDATE apply_for_me_submissions 
            SET document_verification = ?, 
                document_verification_notes = ?,
                verified_by = ?,
                verified_at = CURRENT_TIMESTAMP,
                updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        return $stmt->execute([$status, $notes, $verifiedBy, $id]);
    }

    public function rejectDocuments($id, $reason, $notes = null, $verifiedBy = null) {
        $stmt = $this->db->prepare("
            UPDATE apply_for_me_submissions 
            SET document_verification = 'invalid',
                rejection_reason = ?,
                document_verification_notes = ?,
                verified_by = ?,
                verified_at = CURRENT_TIMESTAMP,
                application_status = 'rejected',
                updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        return $stmt->execute([$reason, $notes, $verifiedBy, $id]);
    }

    public function updateApplicationStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE apply_for_me_submissions SET application_status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    public function updatePaymentStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE apply_for_me_submissions SET payment_status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    public function updateProgress($id, $data) {
        $fields = [];
        $params = [];

        $allowedFields = [
            'application_status', 'payment_status', 'document_verification',
            'admin_notes', 'rejection_reason', 'document_verification_notes',
            'submitted_to_institution_at', 'institution_response', 'current_step'
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $fields[] = "updated_at = CURRENT_TIMESTAMP";
        $params[] = $id;

        $sql = "UPDATE apply_for_me_submissions SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function updateAdminNotes($id, $notes) {
        $stmt = $this->db->prepare("UPDATE apply_for_me_submissions SET admin_notes = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        return $stmt->execute([$notes, $id]);
    }

    public function updateProofOfPayment($id, $path) {
        $stmt = $this->db->prepare("
            UPDATE apply_for_me_submissions 
            SET proof_of_payment_path = ?,
                proof_of_payment_uploaded_at = CURRENT_TIMESTAMP,
                updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        return $stmt->execute([$path, $id]);
    }

    public function approvePayment($id, $adminId) {
        $stmt = $this->db->prepare("
            UPDATE apply_for_me_submissions 
            SET payment_status = 'paid',
                application_status = 'in_progress',
                current_step = 'in_progress',
                verified_by = ?,
                verified_at = CURRENT_TIMESTAMP,
                updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        return $stmt->execute([$adminId, $id]);
    }

    public function rejectPayment($id, $reason = null) {
        $stmt = $this->db->prepare("
            UPDATE apply_for_me_submissions 
            SET payment_status = 'pending',
                admin_notes = CONCAT(COALESCE(CASE WHEN admin_notes IS NULL OR admin_notes = '' THEN '' ELSE CONCAT(admin_notes, CHAR(10)) END, ''), ?),
                updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        return $stmt->execute([$reason ?? 'Payment not verified', $id]);
    }

    public function updateStepsCompleted($id, $steps) {
        $stmt = $this->db->prepare("
            UPDATE apply_for_me_submissions 
            SET steps_completed = ?,
                steps_updated_at = CURRENT_TIMESTAMP,
                updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        return $stmt->execute([$steps, $id]);
    }

    public function toggleStep($id, $stepKey) {
        $submission = $this->findById($id);
        $steps = json_decode($submission['steps_completed'] ?? '[]', true) ?? [];
        
        if (in_array($stepKey, $steps)) {
            // Remove step
            $steps = array_values(array_diff($steps, [$stepKey]));
        } else {
            // Add step
            $steps[] = $stepKey;
        }
        
        $steps = array_values(array_unique($steps));
        return $this->updateStepsCompleted($id, json_encode($steps));
    }

    public function markSubmittedToInstitution($id) {
        $stmt = $this->db->prepare("
            UPDATE apply_for_me_submissions 
            SET submitted_to_institution_at = CURRENT_TIMESTAMP,
                application_status = 'submitted_to_institution',
                current_step = 'submitted_to_institution',
                updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        return $stmt->execute([$id]);
    }

    /**
     * Toggle a progress step for a specific institution
     */
    public function toggleInstitutionStep($submissionId, $institutionNumber, $stepKey) {
        $validNumbers = [1, 2, 3];
        if (!in_array($institutionNumber, $validNumbers)) {
            return false;
        }

        $field = "institution_{$institutionNumber}_steps_completed";
        $submission = $this->findById($submissionId);
        $steps = json_decode($submission[$field] ?? '[]', true) ?? [];
        
        if (in_array($stepKey, $steps)) {
            $steps = array_values(array_diff($steps, [$stepKey]));
        } else {
            $steps[] = $stepKey;
        }
        
        $steps = array_values(array_unique($steps));
        
        $stmt = $this->db->prepare("
            UPDATE apply_for_me_submissions 
            SET {$field} = ?,
                updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        return $stmt->execute([json_encode($steps), $submissionId]);
    }

    /**
     * Get steps completed for a specific institution
     */
    public function getInstitutionSteps($submissionId, $institutionNumber) {
        $validNumbers = [1, 2, 3];
        if (!in_array($institutionNumber, $validNumbers)) {
            return [];
        }

        $field = "institution_{$institutionNumber}_steps_completed";
        $submission = $this->findById($submissionId);
        return json_decode($submission[$field] ?? '[]', true) ?? [];
    }

    /**
     * Update status for a specific institution preference (1, 2, or 3)
     */
    public function updateInstitutionStatus($submissionId, $institutionNumber, $status, $notes = null, $response = null) {
        $validNumbers = [1, 2, 3];
        if (!in_array($institutionNumber, $validNumbers)) {
            return false;
        }

        $validStatuses = ['pending', 'in_progress', 'submitted_to_institution', 'completed', 'rejected'];
        if (!in_array($status, $validStatuses)) {
            return false;
        }

        $fields = [
            "institution_{$institutionNumber}_status = ?",
            "updated_at = CURRENT_TIMESTAMP"
        ];
        $params = [$status];

        if ($notes !== null) {
            $fields[] = "institution_{$institutionNumber}_notes = ?";
            $params[] = $notes;
        }

        if ($response !== null) {
            $fields[] = "institution_{$institutionNumber}_response = ?";
            $params[] = $response;
        }

        // Set timestamp for submitted_to_institution status
        if ($status === 'submitted_to_institution') {
            $fields[] = "institution_{$institutionNumber}_submitted_at = CURRENT_TIMESTAMP";
        }

        // Set timestamp for completed status
        if ($status === 'completed') {
            $fields[] = "institution_{$institutionNumber}_completed_at = CURRENT_TIMESTAMP";
        }

        $params[] = $submissionId;

        $sql = "UPDATE apply_for_me_submissions SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
}
