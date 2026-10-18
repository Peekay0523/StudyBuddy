<?php
/**
 * Tutor Model - Tutors available for students to learn from
 */

class Tutor {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->ensureSchema();
    }

    /**
     * Expose the database connection for controller use.
     */
    public function getDb() {
        return $this->db;
    }

    /**
     * Idempotent schema safety-net for the tutors table: create it if
     * missing and add newer profile columns (teaching details shown on
     * the public "View Tutor" page) if the database file is older.
     */
    private function ensureSchema() {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS tutors (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INTEGER UNIQUE NOT NULL,
                    full_name TEXT,
                    subjects TEXT,
                    bio TEXT,
                    hourly_rate TEXT,
                    phone TEXT,
                    email TEXT,
                    is_available INTEGER DEFAULT 1,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )
            ");

            $cols = db_table_columns($this->db, 'tutors');
            if (is_array($cols)) {
                $columnDdl = [
                    'billing_type'      => "ALTER TABLE tutors ADD COLUMN billing_type TEXT DEFAULT ('hourly')",
                    'company_name'      => "ALTER TABLE tutors ADD COLUMN company_name TEXT",
                    'teaching_mode'     => "ALTER TABLE tutors ADD COLUMN teaching_mode TEXT DEFAULT ('contact')",
                    'teaching_approach' => "ALTER TABLE tutors ADD COLUMN teaching_approach TEXT",
                    'packages'          => "ALTER TABLE tutors ADD COLUMN packages TEXT",
                    'company_team'      => "ALTER TABLE tutors ADD COLUMN company_team TEXT",
                    'profile_image'     => "ALTER TABLE tutors ADD COLUMN profile_image TEXT",
                    'grades_offered'    => "ALTER TABLE tutors ADD COLUMN grades_offered TEXT",
                ];
                foreach ($columnDdl as $name => $ddl) {
                    if (!in_array($name, $cols)) {
                        $this->db->exec($ddl);
                    }
                }
            }
        } catch (Exception $e) {
            // Silently ignore - queries will simply fail later if the table is missing
        }
    }

    /**
     * Register the current user as a tutor (or update their existing profile)
     */
    public function register($userId, $fullName, $subjects, $bio, $hourlyRate, $phone, $email, $billingType = 'hourly', $companyName = '', $isAvailable = 1, $teachingMode = 'contact', $teachingApproach = '', $packages = '', $companyTeam = '', $gradesOffered = '') {
        // Upsert: if a tutor profile already exists for this user, update it
        $existing = $this->findByUserId($userId);
        if ($existing) {
            $stmt = $this->db->prepare("
                UPDATE tutors
                SET full_name = ?, subjects = ?, bio = ?, hourly_rate = ?, phone = ?, email = ?, is_available = ?, billing_type = ?, company_name = ?, teaching_mode = ?, teaching_approach = ?, packages = ?, company_team = ?, grades_offered = ?
                WHERE user_id = ?
            ");
            $stmt->execute([$fullName, $subjects, $bio, $hourlyRate, $phone, $email, $isAvailable, $billingType, $companyName, $teachingMode, $teachingApproach, $packages, $companyTeam, $gradesOffered, $userId]);
            return $existing['id'];
        }

        $stmt = $this->db->prepare("
            INSERT INTO tutors (user_id, full_name, subjects, bio, hourly_rate, phone, email, is_available, billing_type, company_name, teaching_mode, teaching_approach, packages, company_team, grades_offered)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $fullName, $subjects, $bio, $hourlyRate, $phone, $email, $isAvailable, $billingType, $companyName, $teachingMode, $teachingApproach, $packages, $companyTeam, $gradesOffered]);
        return $this->db->lastInsertId();
    }

    /**
     * Get all available tutors (with their username)
     */
    public function getAllAvailable() {
        $stmt = $this->db->query("
            SELECT t.*, u.username
            FROM tutors t
            JOIN users u ON t.user_id = u.id
            WHERE t.is_available = 1
            ORDER BY t.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Find a tutor profile by user id
     */
    public function findByUserId($userId) {
        $stmt = $this->db->prepare("SELECT * FROM tutors WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    /**
     * Find a tutor profile by its own id (tutors.id)
     */
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM tutors WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Find a tutor profile by id, joined with the account username
     * (used by the public tutor profile page)
     */
    public function findByIdWithUser($id) {
        $stmt = $this->db->prepare("
            SELECT t.*, u.username
            FROM tutors t
            JOIN users u ON t.user_id = u.id
            WHERE t.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Save the tutor's profile picture path (relative to public/)
     */
    public function updateProfileImage($userId, $relativePath) {
        $stmt = $this->db->prepare("UPDATE tutors SET profile_image = ? WHERE user_id = ?");
        return $stmt->execute([$relativePath, $userId]);
    }
}
