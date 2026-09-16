<?php
/**
 * TutorLesson Model
 * Lessons a tutor shares with their active students, scoped to a grade:
 *  - type 'lesson': a scheduled lesson (link + date + time)
 *  - type 'recorded': a recorded session (link + date)
 *  - type 'document': a document (link + description)
 */

class TutorLesson {
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
                CREATE TABLE IF NOT EXISTS tutor_lessons (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    tutor_id INTEGER NOT NULL,
                    grade TEXT DEFAULT '',
                    type TEXT DEFAULT 'lesson',
                    title TEXT NOT NULL,
                    description TEXT,
                    link TEXT,
                    lesson_date TEXT,
                    lesson_time TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (tutor_id) REFERENCES tutors(id) ON DELETE CASCADE
                )
            ");
            $this->db->exec("CREATE INDEX IF NOT EXISTS idx_tutor_lessons_tutor ON tutor_lessons(tutor_id)");
        } catch (Exception $e) {
            // Silently ignore - queries will simply fail later if the table is missing
        }
    }

    /**
     * Create a shared lesson. $type is 'lesson', 'recorded' or 'document'.
     * $grade === '' means the lesson applies to all grades.
     */
    public function create($tutorId, $grade, $type, $title, $description = '', $link = '', $lessonDate = '', $lessonTime = '') {
        $stmt = $this->db->prepare("
            INSERT INTO tutor_lessons (tutor_id, grade, type, title, description, link, lesson_date, lesson_time)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $tutorId,
            $grade,
            in_array($type, ['lesson', 'recorded', 'document'], true) ? $type : 'lesson',
            $title,
            $description,
            $link,
            $lessonDate,
            $lessonTime,
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * All lessons a tutor has shared (newest first).
     */
    public function getForTutor($tutorId) {
        $stmt = $this->db->prepare("
            SELECT * FROM tutor_lessons
            WHERE tutor_id = ?
            ORDER BY created_at DESC, id DESC
        ");
        $stmt->execute([$tutorId]);
        return $stmt->fetchAll();
    }

    /**
     * Lessons from a single tutor (optionally only for one grade).
     * $grade === '' returns only general (grade = '') lessons.
     */
    public function getForTutorAndGrade($tutorId, $grade) {
        if ($grade === null || trim($grade) === '') {
            $stmt = $this->db->prepare("
                SELECT * FROM tutor_lessons
                WHERE tutor_id = ? AND (grade = '' OR grade IS NULL)
                ORDER BY created_at DESC, id DESC
            ");
            $stmt->execute([$tutorId]);
            return $stmt->fetchAll();
        }
        $stmt = $this->db->prepare("
            SELECT * FROM tutor_lessons
            WHERE tutor_id = ? AND (grade = ? OR grade = '' OR grade IS NULL)
            ORDER BY created_at DESC, id DESC
        ");
        $stmt->execute([$tutorId, $grade]);
        return $stmt->fetchAll();
    }

    /**
     * All lessons from the tutors currently teaching this student,
     * with the tutor's name attached. Only lessons whose grade matches
     * the student's grade (or general, grade = '') are returned.
     */
    public function getForStudent($studentUserId) {
        $stmt = $this->db->prepare("
            SELECT DISTINCT l.*, t.full_name AS tutor_name, t.user_id AS tutor_user_id,
                   tu.username AS tutor_username, ts.grade AS student_grade
            FROM tutor_lessons l
            JOIN tutor_students ts ON ts.tutor_id = l.tutor_id AND ts.status = 'active'
            JOIN tutors t ON t.id = l.tutor_id
            JOIN users tu ON tu.id = t.user_id
            WHERE ts.student_user_id = ?
              AND (l.grade = '' OR l.grade = ts.grade)
            ORDER BY l.created_at DESC, l.id DESC
        ");
        $stmt->execute([$studentUserId]);
        return $stmt->fetchAll();
    }

    /**
     * Find a single lesson scoped to a tutor (ownership check).
     */
    public function findByIdForTutor($id, $tutorId) {
        $stmt = $this->db->prepare("
            SELECT * FROM tutor_lessons WHERE id = ? AND tutor_id = ?
        ");
        $stmt->execute([$id, $tutorId]);
        return $stmt->fetch();
    }

    /**
     * Delete a lesson (ownership enforced via tutor_id).
     */
    public function delete($id, $tutorId) {
        $stmt = $this->db->prepare("
            DELETE FROM tutor_lessons WHERE id = ? AND tutor_id = ?
        ");
        $stmt->execute([$id, $tutorId]);
        return $stmt->rowCount() > 0;
    }
}
