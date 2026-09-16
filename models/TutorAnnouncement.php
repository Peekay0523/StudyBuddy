<?php
/**
 * TutorAnnouncement Model
 * Announcements a tutor posts for their active students, scoped to a grade:
 *  - type 'online': a link for an online session (Zoom/Meet/YouTube etc.)
 *  - type 'contact': a date (+ optional time) for an in-person/contact lesson
 */

class TutorAnnouncement {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->ensureSchema();
    }

    /**
     * Idempotent schema safety-net, in case the app runs against an older database.
     */
    private function ensureSchema() {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS tutor_announcements (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    tutor_id INTEGER NOT NULL,
                    grade TEXT DEFAULT '',
                    type TEXT DEFAULT 'online',
                    title TEXT NOT NULL,
                    description TEXT,
                    link TEXT,
                    session_date TEXT,
                    session_time TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (tutor_id) REFERENCES tutors(id) ON DELETE CASCADE
                )
            ");
            $this->db->exec("CREATE INDEX IF NOT EXISTS idx_tutor_announcements_tutor ON tutor_announcements(tutor_id)");
        } catch (Exception $e) {
            // Silently ignore - queries will simply fail later if the table is missing
        }
    }

    /**
     * Create an announcement. $type is 'online' (with $link) or 'contact' (with $sessionDate).
     * $grade === '' means the announcement applies to all grades.
     */
    public function create($tutorId, $grade, $type, $title, $description = '', $link = '', $sessionDate = '', $sessionTime = '') {
        $stmt = $this->db->prepare("
            INSERT INTO tutor_announcements (tutor_id, grade, type, title, description, link, session_date, session_time)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $tutorId,
            $grade,
            $type === 'contact' ? 'contact' : 'online',
            $title,
            $description,
            $link,
            $sessionDate,
            $sessionTime,
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * All announcements a tutor has posted (newest first).
     */
    public function getForTutor($tutorId) {
        $stmt = $this->db->prepare("
            SELECT * FROM tutor_announcements
            WHERE tutor_id = ?
            ORDER BY created_at DESC, id DESC
        ");
        $stmt->execute([$tutorId]);
        return $stmt->fetchAll();
    }

    /**
     * Announcements from a single tutor (optionally only for one grade).
     * $grade === '' returns only general (grade = '') announcements.
     * Used when rendering per-grade cards on the tutor's "My Students" page
     * and the student-facing "Lessons & Materials" page.
     */
    public function getForTutorAndGrade($tutorId, $grade) {
        if ($grade === null || trim($grade) === '') {
            $stmt = $this->db->prepare("
                SELECT * FROM tutor_announcements
                WHERE tutor_id = ? AND (grade = '' OR grade IS NULL)
                ORDER BY created_at DESC, id DESC
            ");
            $stmt->execute([$tutorId]);
            return $stmt->fetchAll();
        }
        $stmt = $this->db->prepare("
            SELECT * FROM tutor_announcements
            WHERE tutor_id = ? AND (grade = ? OR grade = '' OR grade IS NULL)
            ORDER BY created_at DESC, id DESC
        ");
        $stmt->execute([$tutorId, $grade]);
        return $stmt->fetchAll();
    }

    /**
     * All announcements from the tutors currently teaching this student,
     * with the tutor's name attached. Only announcements whose grade matches
     * the student's grade (or general, grade = '') are returned.
     */
    public function getForStudent($studentUserId) {
        $stmt = $this->db->prepare("
            SELECT DISTINCT a.*, t.full_name AS tutor_name, t.user_id AS tutor_user_id,
                   tu.username AS tutor_username, ts.grade AS student_grade
            FROM tutor_announcements a
            JOIN tutor_students ts ON ts.tutor_id = a.tutor_id AND ts.status = 'active'
            JOIN tutors t ON t.id = a.tutor_id
            JOIN users tu ON tu.id = t.user_id
            WHERE ts.student_user_id = ?
              AND (a.grade = '' OR a.grade = ts.grade)
            ORDER BY a.created_at DESC, a.id DESC
        ");
        $stmt->execute([$studentUserId]);
        return $stmt->fetchAll();
    }

    /**
     * Find a single announcement scoped to a tutor (ownership check).
     */
    public function findByIdForTutor($id, $tutorId) {
        $stmt = $this->db->prepare("
            SELECT * FROM tutor_announcements WHERE id = ? AND tutor_id = ?
        ");
        $stmt->execute([$id, $tutorId]);
        return $stmt->fetch();
    }

    /**
     * Delete an announcement (ownership enforced via tutor_id).
     */
    public function delete($id, $tutorId) {
        $stmt = $this->db->prepare("
            DELETE FROM tutor_announcements WHERE id = ? AND tutor_id = ?
        ");
        $stmt->execute([$id, $tutorId]);
        return $stmt->rowCount() > 0;
    }
}
