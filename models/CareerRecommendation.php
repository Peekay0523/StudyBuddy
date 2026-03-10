<?php
/**
 * CareerRecommendation Model
 */

class CareerRecommendation {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($studentId, $reportCardId, $careers = [], $strengths = [], $areasForImprovement = [], $courses = [], $bursaries = [], $aps = 0) {
        $stmt = $this->db->prepare("
            INSERT INTO career_recommendations (student_id, report_card_id, recommended_careers, strengths, areas_for_improvement, courses_data, bursaries_data, aps_score)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $studentId,
            $reportCardId,
            json_encode($careers),
            json_encode($strengths),
            json_encode($areasForImprovement),
            $courses,
            $bursaries,
            $aps
        ]);

        return $this->db->lastInsertId();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM career_recommendations WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();

        if ($result) {
            $result['recommended_careers'] = json_decode($result['recommended_careers'], true) ?? [];
            $result['strengths'] = json_decode($result['strengths'], true) ?? [];
            $result['areas_for_improvement'] = json_decode($result['areas_for_improvement'], true) ?? [];
            $result['courses'] = json_decode($result['courses_data'] ?? '[]', true) ?? [];
            $result['bursaries'] = json_decode($result['bursaries_data'] ?? '[]', true) ?? [];
            $result['aps'] = $result['aps_score'] ?? 0;
        }

        return $result;
    }

    public function findByStudentId($studentId) {
        $stmt = $this->db->prepare("SELECT * FROM career_recommendations WHERE student_id = ? ORDER BY created_at DESC");
        $stmt->execute([$studentId]);
        $results = $stmt->fetchAll();

        foreach ($results as &$result) {
            $result['recommended_careers'] = json_decode($result['recommended_careers'], true) ?? [];
            $result['strengths'] = json_decode($result['strengths'], true) ?? [];
            $result['areas_for_improvement'] = json_decode($result['areas_for_improvement'], true) ?? [];
            $result['courses'] = json_decode($result['courses_data'] ?? '[]', true) ?? [];
            $result['bursaries'] = json_decode($result['bursaries_data'] ?? '[]', true) ?? [];
            $result['aps'] = $result['aps_score'] ?? 0;
        }

        return $results;
    }

    public function findByReportCardId($reportCardId) {
        $stmt = $this->db->prepare("SELECT * FROM career_recommendations WHERE report_card_id = ?");
        $stmt->execute([$reportCardId]);
        $result = $stmt->fetch();

        if ($result) {
            $result['recommended_careers'] = json_decode($result['recommended_careers'], true) ?? [];
            $result['strengths'] = json_decode($result['strengths'], true) ?? [];
            $result['areas_for_improvement'] = json_decode($result['areas_for_improvement'], true) ?? [];
            $result['courses'] = json_decode($result['courses_data'] ?? '[]', true) ?? [];
            $result['bursaries'] = json_decode($result['bursaries_data'] ?? '[]', true) ?? [];
            $result['aps'] = $result['aps_score'] ?? 0;
        }

        return $result;
    }
}
