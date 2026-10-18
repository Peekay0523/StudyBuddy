<?php
/**
 * Tutor Controller
 * Handles the tutor dashboard: profile, pending student requests,
 * active students (teaching mode, extra classes, EFT payment), and
 * the student-facing "Request Tutor" action.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Tutor.php';
require_once __DIR__ . '/../models/TutorStudent.php';
require_once __DIR__ . '/../models/TutorAnnouncement.php';

require_once __DIR__ . '/../models/TutorStudentProblem.php';
class TutorController {
    private $tutorModel;
    private $tutorStudentModel;
    private $tutorAnnouncementModel;

    public function __construct() {
        $this->tutorModel = new Tutor();
        $this->tutorStudentModel = new TutorStudent();
        $this->tutorAnnouncementModel = new TutorAnnouncement();
        $this->ensureSchema();
    }

    /**
     * Idempotent schema safety-net for the tutor_students table,
     * in case the app is running against an older database file.
     */
    private function ensureSchema() {
        try {
            $db = Database::getInstance()->getConnection();
            $db->exec("
                CREATE TABLE IF NOT EXISTS tutor_students (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    tutor_id INTEGER NOT NULL,
                    student_user_id INTEGER NOT NULL,
                    status TEXT DEFAULT 'pending',
                    teaching_mode TEXT DEFAULT 'online',
                    extra_classes INTEGER DEFAULT 0,
                    payment_method TEXT DEFAULT 'eft',
                    grade TEXT,
                    subjects TEXT,
                    schedule TEXT,
                    estimated_price TEXT,
                    rejection_reason TEXT,
                    requested_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    approved_at DATETIME,
                    FOREIGN KEY (tutor_id) REFERENCES tutors(id) ON DELETE CASCADE,
                    FOREIGN KEY (student_user_id) REFERENCES users(id) ON DELETE CASCADE,
                    UNIQUE(tutor_id, student_user_id)
                )
            ");

            // Add missing columns to older tutor_students tables
            $cols = $db->query("PRAGMA table_info(tutor_students)")->fetchAll(PDO::FETCH_COLUMN, 1);
            if (is_array($cols)) {
                $missingCols = array_diff(['grade', 'subjects', 'schedule', 'estimated_price', 'rejection_reason'], $cols);
                foreach ($missingCols as $col) {
                    $db->exec("ALTER TABLE tutor_students ADD COLUMN {$col} TEXT");
                }
            }
        } catch (Exception $e) {
            // Silently ignore - queries will simply fail later if the table is missing
        }
    }

    /**
     * Tutor Dashboard: profile + pending requests + active students
     */
    public function dashboard() {
        requireLogin();

        $user = getCurrentUser();

        if (!isTutor()) {
            setFlashMessage('error', 'Only tutors can access the tutor dashboard.');
            header('Location: /study-group');
            exit;
        }

        $tutorProfile = $this->getOrCreateTutorProfile($user);

        $pendingRequests = $this->tutorStudentModel->getPendingForTutor($tutorProfile['id']);
        $activeStudents = $this->tutorStudentModel->getActiveForTutor($tutorProfile['id']);
        $activeCount = count($activeStudents);
        $pendingCount = count($pendingRequests);

        include __DIR__ . '/../templates/pages/tutor_dashboard.php';
    }

    /**
     * My Students page: active students grouped into grade cards, with the
     * ability to post announcements (online session links or contact lesson
     * dates) per grade. Students see these announcements on the Tutors page.
     */
    public function myStudents() {
        requireLogin();

        if (!isTutor()) {
            setFlashMessage('error', 'Only tutors can access the My Students page.');
            header('Location: /study-group');
            exit;
        }

        $user = getCurrentUser();
        $tutorProfile = $this->getOrCreateTutorProfile($user);

        $activeStudents = $this->tutorStudentModel->getActiveForTutor($tutorProfile['id']);
        $allAnnouncements = $this->tutorAnnouncementModel->getForTutor($tutorProfile['id']);

        // Group students into grade cards (ordered Grade 8 GåÆ 12, then custom labels, then unspecified)
        $gradeOrder = ['Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12'];
        $groups = []; // grade label => ['students' => [], 'announcements' => []]
        foreach ($activeStudents as $stu) {
            $grade = trim($stu['grade'] ?? '');
            $normalized = '';
            foreach ($gradeOrder as $g) {
                $num = substr($g, 6);
                if (strcasecmp($grade, $g) === 0 || preg_match('/^(?:grade|gr)?\s*(' . $num . ')\s*$/i', $grade)) {
                    $normalized = $g;
                    break;
                }
            }
            if ($normalized === '') {
                if ($grade === '') continue; // handled below under "unspecified"
                if (!isset($groups[$grade])) $groups[$grade] = ['students' => [], 'announcements' => []];
                $groups[$grade]['students'][] = $stu;
            } else {
                if (!isset($groups[$normalized])) $groups[$normalized] = ['students' => [], 'announcements' => []];
                $groups[$normalized]['students'][] = $stu;
            }
        }
        $unspecified = array_values(array_filter($activeStudents, function ($s) {
            return trim($s['grade'] ?? '') === '';
        }));

        $gradeCards = [];
        foreach ($gradeOrder as $g) {
            if (isset($groups[$g])) $gradeCards[$g] = $groups[$g];
        }
        foreach ($groups as $label => $grp) {
            if (!isset($gradeCards[$label])) $gradeCards[$label] = $grp;
        }

        // Attach announcements to their grade card ('' = all grades GåÆ show on every card)
        $generalAnnouncements = [];
        foreach ($allAnnouncements as $ann) {
            $grade = trim($ann['grade'] ?? '');
            if ($grade === '') {
                $generalAnnouncements[] = $ann;
            } else {
                if (!isset($gradeCards[$grade])) $gradeCards[$grade] = ['students' => [], 'announcements' => []];
                $gradeCards[$grade]['announcements'][] = $ann;
            }
        }

        include __DIR__ . '/../templates/pages/tutor_my_students.php';
    }

    /**
     * Tutor posts an announcement for a grade: an online session link or a
     * contact lesson date. Visible to active students of that grade.
     */
    public function createAnnouncement() {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /tutor/my-students');
            exit;
        }

        $tutorProfile = $this->getOwnTutorProfileOrRedirect();

        $type = ($_POST['type'] ?? '') === 'contact' ? 'contact' : 'online';
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $grade = trim($_POST['grade'] ?? '');
        $link = trim($_POST['link'] ?? '');
        $sessionDate = trim($_POST['session_date'] ?? '');
        $sessionTime = trim($_POST['session_time'] ?? '');

        if ($title === '') {
            setFlashMessage('error', 'Please give your announcement a title.');
            header('Location: /tutor/my-students');
            exit;
        }

        if ($type === 'online') {
            if ($link === '' || !filter_var($link, FILTER_VALIDATE_URL)) {
                setFlashMessage('error', 'Please provide a valid session link (starting with http:// or https://).');
                header('Location: /tutor/my-students');
                exit;
            }
            $sessionDate = '';
            $sessionTime = '';
        } else {
            if ($sessionDate === '') {
                setFlashMessage('error', 'Please provide the date for the contact lesson.');
                header('Location: /tutor/my-students');
                exit;
            }
            $link = '';
        }

        $this->tutorAnnouncementModel->create(
            $tutorProfile['id'],
            $grade, // '' = all grades
            $type,
            $title,
            $description,
            $link,
            $sessionDate,
            $sessionTime
        );

        setFlashMessage('success', $type === 'online'
            ? 'Online session announcement posted. Your students can now see the link.'
            : 'Contact lesson announcement posted. Your students can now see the date.');

        header('Location: /tutor/my-students');
        exit;
    }

    /**
     * Tutor deletes one of their own announcements.
     */
    public function deleteAnnouncement($id) {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /tutor/my-students');
            exit;
        }

        $tutorProfile = $this->getOwnTutorProfileOrRedirect();

        if ($this->tutorAnnouncementModel->delete((int)$id, $tutorProfile['id'])) {
            setFlashMessage('success', 'Announcement deleted.');
        } else {
            setFlashMessage('error', 'Unable to delete that announcement.');
        }

        header('Location: /tutor/my-students');
        exit;
    }

    /**
     * Tutor shares a lesson, recorded session or document with a grade.
     * Students see these on the tutor's "Lessons & Materials" page.
     */
    public function createLesson() {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /tutor/my-students');
            exit;
        }

        $tutorProfile = $this->getOwnTutorProfileOrRedirect();

        $kind = trim($_POST['kind'] ?? 'lesson');
        if (!in_array($kind, ['lesson', 'recorded', 'document'], true)) {
            $kind = 'lesson';
        }
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $grade = trim($_POST['grade'] ?? '');
        $link = trim($_POST['link'] ?? '');

        if ($title === '') {
            setFlashMessage('error', 'Please give the item a title.');
            header('Location: /tutor/my-students');
            exit;
        }

        if ($link === '' || !filter_var($link, FILTER_VALIDATE_URL)) {
            setFlashMessage('error', 'Please provide a valid link (starting with http:// or https://).');
            header('Location: /tutor/my-students');
            exit;
        }

        require_once __DIR__ . '/../models/TutorLesson.php';
        $lessonModel = new TutorLesson();
        $lessonModel->create($tutorProfile['id'], $grade, $kind, $title, $description, $link);

        $kindLabels = [
            'lesson' => 'Lesson shared. Your students can open it from your Lessons & Materials page.',
            'recorded' => 'Recorded session shared. Your students can watch it from your Lessons & Materials page.',
            'document' => 'Document shared. Your students can download it from your Lessons & Materials page.',
        ];
        setFlashMessage('success', $kindLabels[$kind]);

        header('Location: /tutor/my-students');
        exit;
    }

    /**
     * Tutor deletes a lesson/recorded session/document they shared.
     */
    public function deleteLesson($id) {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /tutor/my-students');
            exit;
        }

        $tutorProfile = $this->getOwnTutorProfileOrRedirect();

        require_once __DIR__ . '/../models/TutorLesson.php';
        $lessonModel = new TutorLesson();

        if ($lessonModel->delete((int)$id, $tutorProfile['id'])) {
            setFlashMessage('success', 'Item deleted.');
        } else {
            setFlashMessage('error', 'Unable to delete that item.');
        }

        header('Location: /tutor/my-students');
        exit;
    }

    /**
     * Validate + save an uploaded tutor profile picture.
     * Stores the file in public/uploads/tutor_profiles/ and records the
     * path (relative to public/) in the tutors table. Replaces (and deletes)
     * any previous picture.
     *
     * @param array|null $file $_FILES['profile_image'] entry
     * @param int        $userId owner user id
     * @return bool true if a new picture was saved, false otherwise
     */
    private function processProfileImageUpload($file, $userId) {
        // No picture chosen GÇö nothing to do (not an error)
        if (!$file || !is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return true;
        }

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            setFlashMessage('error', 'The picture upload failed. Please try again.');
            return false;
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            setFlashMessage('error', 'Profile picture must be less than 5MB.');
            return false;
        }

        $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!in_array($ext, $allowed, true)) {
            setFlashMessage('error', 'Invalid picture type. Allowed: JPG, PNG, WEBP, GIF.');
            return false;
        }

        // Make sure it really is an image
        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            setFlashMessage('error', 'That file does not look like a valid image.');
            return false;
        }

        $uploadDir = __DIR__ . '/../public/uploads/tutor_profiles/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $relativePath = 'uploads/tutor_profiles/tutor' . (int)$userId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $absolutePath = __DIR__ . '/../public/' . $relativePath;

        if (!move_uploaded_file($file['tmp_name'], $absolutePath)) {
            setFlashMessage('error', 'Failed to save the profile picture. Please try again.');
            return false;
        }

        // Delete the previous picture (only files inside tutor_profiles)
        $existing = $this->tutorModel->findByUserId($userId);
        $old = $existing['profile_image'] ?? null;
        if ($old && strpos($old, 'uploads/tutor_profiles/') === 0) {
            $oldAbsolute = __DIR__ . '/../public/' . $old;
            if (is_file($oldAbsolute)) {
                @unlink($oldAbsolute);
            }
        }

        $this->tutorModel->updateProfileImage($userId, $relativePath);
        setFlashMessage('success', 'Profile picture updated.');
        return true;
    }

    /**
     * Tutor updates their own profile from the tutor dashboard
     */
    public function updateProfile() {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /tutor-dashboard');
            exit;
        }

        $user = getCurrentUser();

        if (!isTutor()) {
            setFlashMessage('error', 'Only tutors can update a tutor profile.');
            header('Location: /study-group');
            exit;
        }

        $fullName = trim($_POST['full_name'] ?? '');
        $subjects = trim($_POST['subjects'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $hourlyRate = trim($_POST['rate'] ?? '');
        $billingType = trim($_POST['billing_type'] ?? 'hourly');
        if ($billingType !== 'monthly') { $billingType = 'hourly'; }
        $companyName = trim($_POST['company_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');

        // Lesson delivery: fully contact (in-person), hybrid or fully online
        $teachingMode = trim($_POST['teaching_mode'] ?? 'contact');
        if (!in_array($teachingMode, ['contact', 'hybrid', 'online'], true)) {
            $teachingMode = 'contact';
        }
        $teachingApproach = trim($_POST['teaching_approach'] ?? '');
        $packages = trim($_POST['packages'] ?? '');
        $companyTeam = trim($_POST['company_team'] ?? '');
        $gradesOffered = trim($_POST['grades_offered'] ?? '');

        if (empty($fullName) || empty($subjects)) {
            setFlashMessage('error', 'Please provide at least your name and the subjects you teach.');
            header('Location: /tutor-dashboard');
            exit;
        }

        // Optional profile picture upload (submitted together with the profile form)
        $imageOk = $this->processProfileImageUpload($_FILES['profile_image'] ?? null, $user['id']);

        // Upsert the profile; saving marks it as publicly available so
        // students can see it in the Available Tutors list
        $this->tutorModel->register($user['id'], $fullName, $subjects, $bio, $hourlyRate, $phone, $email, $billingType, $companyName, 1, $teachingMode, $teachingApproach, $packages, $companyTeam, $gradesOffered);

        if ($imageOk) {
            setFlashMessage('success', 'Your tutor profile has been updated.');
        }
        // When the picture failed validation, its specific error message is kept
        header('Location: /tutor-dashboard');
        exit;
    }

    /**
     * Lessons page for a tutor-student pair (opened via "View Tutor" on
     * the student's "My Tutors" list). Shows the announcements, lessons,
     * recorded sessions and documents the tutor shares with the student.
     */
    public function lessons($tutorId) {
        requireLogin();

        $user = getCurrentUser();

        $tutor = $this->tutorModel->findByIdWithUser($tutorId);
        if (!$tutor) {
            setFlashMessage('error', 'Tutor not found.');
            header('Location: /study-group');
            exit;
        }

        // Only the tutor themselves or students they actively teach may view this
        $isOwnProfile = (int)$tutor['user_id'] === (int)$user['id'];

        // The student's grade for THIS tutor comes from the tutor_students link
        $link = $this->tutorStudentModel->findLink($tutorId, $user['id']);
        $studentGrade = trim($link['grade'] ?? '');

        // Announcements from this tutor scoped to the student's grade (or general)
        $tutorAnnouncements = $this->tutorAnnouncementModel->getForTutorAndGrade($tutorId, $studentGrade);

        // Shared lessons from this tutor, grouped by kind for the cards
        require_once __DIR__ . '/../models/TutorLesson.php';
        $lessonModel = new TutorLesson();
        $tutorLessons = $lessonModel->getForTutorAndGrade($tutorId, $studentGrade);
        $lessonsByKind = ['lessons' => [], 'recorded' => [], 'documents' => []];
        foreach ($tutorLessons as $lesson) {
            $kind = $lesson['type'] ?? 'lesson';
            if ($kind === 'recorded') {
                $lessonsByKind['recorded'][] = $lesson;
            } elseif ($kind === 'document') {
                $lessonsByKind['documents'][] = $lesson;
            } else {
                $lessonsByKind['lessons'][] = $lesson;
            }
        }

        // Lesson mode for the header pill
        $teachingMode = $tutor['teaching_mode'] ?? 'contact';
        if (!in_array($teachingMode, ['contact', 'hybrid', 'online'], true)) {
            $teachingMode = 'contact';
        }

        // Avatar initials
        $initials = '';
        $nameParts = preg_split('/\s+/', trim($tutor['full_name'] ?: ($tutor['username'] ?? '')));
        foreach ($nameParts as $p) {
            if ($p !== '') $initials .= strtoupper(mb_substr($p, 0, 1));
            if (mb_strlen($initials) >= 2) break;
        }
        if ($initials === '') $initials = 'T';

        include __DIR__ . '/../templates/pages/tutor_lessons.php';
    }
    public function view($tutorId) {
        requireLogin();

        $user = getCurrentUser();

        $tutor = $this->tutorModel->findByIdWithUser($tutorId);
        if (!$tutor) {
            setFlashMessage('error', 'Tutor not found.');
            header('Location: /study-group');
            exit;
        }

        // Draft/unavailable profiles are only visible to the tutor who owns them
        $isOwnProfile = (int)$tutor['user_id'] === (int)$user['id'];
        if (empty($tutor['is_available']) && !$isOwnProfile) {
            setFlashMessage('error', 'This tutor is not available right now.');
            header('Location: /study-group');
            exit;
        }

        // Avatar initials
        $initials = '';
        $nameParts = preg_split('/\s+/', trim($tutor['full_name'] ?: ($tutor['username'] ?? '')));
        foreach ($nameParts as $p) {
            if ($p !== '') $initials .= strtoupper(mb_substr($p, 0, 1));
            if (mb_strlen($initials) >= 2) break;
        }
        if ($initials === '') $initials = 'T';

        // Subjects
        $subjects = array_values(array_filter(array_map('trim', explode(',', $tutor['subjects'] ?? ''))));

        // Lesson mode: contact (fully in-person) / hybrid / online
        $teachingMode = $tutor['teaching_mode'] ?? 'contact';
        if (!in_array($teachingMode, ['contact', 'hybrid', 'online'], true)) {
            $teachingMode = 'contact';
        }

        // Tutoring packages: one per line GÇö "Name | Price | Description"
        $packages = [];
        foreach (preg_split('/\r\n|\r|\n/', $tutor['packages'] ?? '') as $line) {
            $line = trim($line);
            if ($line === '') continue;
            $parts = array_map('trim', explode('|', $line));
            $pkg = [
                'name' => $parts[0] ?? '',
                'price' => $parts[1] ?? '',
                'description' => $parts[2] ?? '',
            ];
            if ($pkg['name'] === '') continue;
            if (stripos($pkg['name'], 'group') !== false) {
                $pkg['type'] = 'Group Session';
            } elseif (stripos($pkg['name'], 'single') !== false || stripos($pkg['name'], 'one-on-one') !== false || stripos($pkg['name'], 'individual') !== false) {
                $pkg['type'] = 'Single Session';
            } else {
                $pkg['type'] = 'Package';
            }
            $packages[] = $pkg;
        }

        // Company tutors (who teaches what): one per line GÇö "Name | Qualifications | Subjects | Grades"
        // Backwards compatible with the older "Name | Subjects" and "Name | Qualifications | Subjects" formats.
        $teamMembers = [];
        foreach (preg_split('/\r\n|\r|\n/', $tutor['company_team'] ?? '') as $line) {
            $line = trim($line);
            if ($line === '') continue;
            $parts = array_map('trim', explode('|', $line));
            $member = [
                'name' => $parts[0] ?? '',
                'qualifications' => '',
                'subjects' => [],
                'grades' => '',
            ];
            if (count($parts) >= 4) {
                $member['qualifications'] = $parts[1] ?? '';
                $member['subjects'] = array_values(array_filter(array_map('trim', explode(',', $parts[2] ?? ''))));
                $member['grades'] = $parts[3] ?? '';
            } elseif (count($parts) === 3) {
                $member['qualifications'] = $parts[1] ?? '';
                $member['subjects'] = array_values(array_filter(array_map('trim', explode(',', $parts[2] ?? ''))));
            } else {
                $member['subjects'] = array_values(array_filter(array_map('trim', explode(',', $parts[1] ?? ''))));
            }
            if ($member['name'] === '') continue;
            $teamMembers[] = $member;
        }

        $gradesOffered = $tutor['grades_offered'] ?? '';

        include __DIR__ . '/../templates/pages/tutor_detail.php';
    }

    /**
     * Quick profile-picture upload (e.g. clicking the avatar on the
     * tutor profile page). Tutor-only; redirects back afterwards.
     */
    public function uploadImage() {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /tutor-dashboard');
            exit;
        }

        $user = getCurrentUser();

        if (!isTutor()) {
            setFlashMessage('error', 'Only tutors can update a tutor profile picture.');
            header('Location: /study-group');
            exit;
        }

        $this->processProfileImageUpload($_FILES['profile_image'] ?? null, $user['id']);

        // Redirect back to where the upload came from (same-site path only)
        $redirect = $_POST['redirect'] ?? '/tutor-dashboard';
        if (!is_string($redirect) || $redirect === '' || $redirect[0] !== '/' || strpos($redirect, '//') === 0) {
            $redirect = '/tutor-dashboard';
        }
        header('Location: ' . $redirect);
        exit;
    }

    /**
     * Serve a tutor's profile picture. Routing it through PHP keeps the
     * URLs working in both the dev server and production, keeps the real
     * file path private, and allows a login check.
     */
    public function profileImage($tutorId) {
        requireLogin();

        $tutor = $this->tutorModel->findById($tutorId);
        $relative = $tutor['profile_image'] ?? null;

        if (!$tutor || !$relative || strpos($relative, 'uploads/tutor_profiles/') !== 0) {
            http_response_code(404);
            exit;
        }

        $baseDir = realpath(__DIR__ . '/../public/uploads/tutor_profiles');
        $file = realpath(__DIR__ . '/../public/' . $relative);

        // Path traversal guard: file must live inside tutor_profiles
        if (!$file || !$baseDir || strpos($file, $baseDir) !== 0 || !is_file($file)) {
            http_response_code(404);
            exit;
        }

        $mimes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
        ];
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (!isset($mimes[$ext])) {
            http_response_code(404);
            exit;
        }

        header('Content-Type: ' . $mimes[$ext]);
        header('Content-Length: ' . filesize($file));
        header('Cache-Control: private, max-age=86400');
        readfile($file);
        exit;
    }

    /**
     * A student requests to be taught by a tutor (from the Available Tutors list or tutor detail page)
     */
    public function requestTutor($tutorId) {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /study-group');
            exit;
        }

        $user = getCurrentUser();

        $tutor = $this->tutorModel->findById($tutorId);
        if (!$tutor) {
            setFlashMessage('error', 'Tutor not found.');
            header('Location: /study-group');
            exit;
        }

        if ((int)$tutor['user_id'] === (int)$user['id']) {
            setFlashMessage('error', 'You cannot request yourself as a tutor.');
            header('Location: /study-group');
            exit;
        }

        $grade = trim($_POST['grade'] ?? '');
        $teachingMode = in_array($_POST['teaching_mode'] ?? '', ['online', 'contact'], true)
            ? $_POST['teaching_mode']
            : 'online';
        $schedule = in_array($_POST['schedule'] ?? '', ['weekdays', 'weekends', 'both'], true)
            ? $_POST['schedule']
            : 'both';
        $requestedSubjects = is_array($_POST['subjects'] ?? null) ? $_POST['subjects'] : [];
        $requestedSubjects = array_values(array_filter(array_map('trim', $requestedSubjects)));

        // Parse the tutor's rate and estimate a price based on number of subjects selected.
        $baseRate = $this->parseTutorRate($tutor['hourly_rate'] ?? '');
        $subjectCount = max(1, count($requestedSubjects));
        $estimatedPrice = '';
        if ($baseRate > 0) {
            $total = $baseRate * $subjectCount;
            $currencySymbol = $this->extractCurrencySymbol($tutor['hourly_rate'] ?? '');
            $billingType = ($tutor['billing_type'] ?? 'hourly') === 'monthly' ? '/month' : '/hour';
            $estimatedPrice = $currencySymbol . number_format($total, 2) . $billingType;
        }

        $subjectsString = implode(', ', $requestedSubjects);

        $result = $this->tutorStudentModel->request(
            $tutorId,
            $user['id'],
            $grade,
            $teachingMode,
            $schedule,
            $subjectsString,
            $estimatedPrice
        );

        if ($result) {
            setFlashMessage('success', 'Request sent! ' . htmlspecialchars($tutor['full_name']) . ' will review your request.');
        } else {
            setFlashMessage('info', 'You have already requested this tutor.');
        }

        header('Location: /study-group');
        exit;
    }

    /**
     * Parse a numeric rate from a tutor's hourly_rate string.
     * Examples: "R150", "150", "R 150/hour", "$200.50".
     */
    private function parseTutorRate($rateString) {
        $clean = preg_replace('/[^\d.,]/', '', $rateString ?? '');
        // Normalise thousands separator and decimal point
        if (preg_match('/^(\d{1,3}(,\d{3})*)(\.\d+)?$/', $clean)) {
            $clean = str_replace(',', '', $clean);
        } elseif (substr_count($clean, ',') === 1 && substr_count($clean, '.') === 0) {
            $clean = str_replace(',', '.', $clean);
        }
        return is_numeric($clean) ? (float) $clean : 0;
    }

    /**
     * Extract the currency symbol/prefix from a rate string.
     */
    private function extractCurrencySymbol($rateString) {
        if (preg_match('/^\s*([Rr]\$?|\$|Gé¼|-ú)\s*/', $rateString, $m)) {
            return $m[1];
        }
        return 'R';
    }

    /**
     * Tutor approves a pending student request
     */
    public function approveStudent($id) {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /tutor-dashboard');
            exit;
        }

        $tutorProfile = $this->getOwnTutorProfileOrRedirect();

        if ($this->tutorStudentModel->approve($id, $tutorProfile['id'])) {
            setFlashMessage('success', 'Student approved and added to your active students.');
        } else {
            setFlashMessage('error', 'Unable to approve this student.');
        }

        header('Location: /tutor-dashboard');
        exit;
    }

    /**
     * Tutor rejects a pending student request
     */
    public function rejectStudent($id) {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /tutor-dashboard');
            exit;
        }

        $tutorProfile = $this->getOwnTutorProfileOrRedirect();

        $reason = trim($_POST['rejection_reason'] ?? '');
        if ($reason === '') {
            setFlashMessage('error', 'Please provide a reason for declining the request.');
            header('Location: /tutor-dashboard');
            exit;
        }

        if ($this->tutorStudentModel->reject($id, $tutorProfile['id'], $reason)) {
            setFlashMessage('success', 'Request declined. The student will be notified of the reason.');
        } else {
            setFlashMessage('error', 'Unable to decline this request.');
        }

        header('Location: /tutor-dashboard');
        exit;
    }

    /**
     * Tutor removes an active student
     */
    public function removeStudent($id) {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /tutor-dashboard');
            exit;
        }

        $tutorProfile = $this->getOwnTutorProfileOrRedirect();

        if ($this->tutorStudentModel->remove($id, $tutorProfile['id'])) {
            setFlashMessage('success', 'Student removed from your roster.');
        } else {
            setFlashMessage('error', 'Unable to remove this student.');
        }

        header('Location: /tutor-dashboard');
        exit;
    }

    /**
     * Tutor updates a student's teaching mode (online/contact) and extra classes flag
     */
    public function updateStudentSettings($id) {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /tutor-dashboard');
            exit;
        }

        $tutorProfile = $this->getOwnTutorProfileOrRedirect();

        $teachingMode = $_POST['teaching_mode'] ?? 'online';
        if (!in_array($teachingMode, ['online', 'contact'], true)) {
            $teachingMode = 'online';
        }
        $extraClasses = !empty($_POST['extra_classes']);

        if ($this->tutorStudentModel->updateSettings($id, $tutorProfile['id'], $teachingMode, $extraClasses)) {
            setFlashMessage('success', 'Student settings updated.');
        } else {
            setFlashMessage('error', 'Unable to update this student.');
        }

        header('Location: /tutor-dashboard');
        exit;
    }

    /**
     * Tutor admin updates the list of tutors in their company.
     * Accepts a JSON array of {name, qualifications, subjects} and stores
     * it as pipe-delimited text in the company_team column.
     */
    public function updateCompanyTeam() {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /tutor-dashboard');
            exit;
        }

        $tutorProfile = $this->getOwnTutorProfileOrRedirect();

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if (!is_array($data) || !isset($data['team']) || !is_array($data['team'])) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid team data.']);
            exit;
        }

        $lines = [];
        foreach ($data['team'] as $member) {
            if (!is_array($member)) continue;
            $name = trim($member['name'] ?? '');
            if ($name === '') continue;
            $qualifications = trim($member['qualifications'] ?? '');
            $subjects = trim($member['subjects'] ?? '');
            $grades = trim($member['grades'] ?? '');
            // Store as: Name | Qualifications | Subjects | Grades
            $lines[] = $name . ' | ' . $qualifications . ' | ' . $subjects . ' | ' . $grades;
        }

        $companyTeam = implode("\n", $lines);

        try {
            $stmt = $this->tutorModel->getDb()->prepare("UPDATE tutors SET company_team = ? WHERE id = ?");
            $stmt->execute([$companyTeam, $tutorProfile['id']]);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Our Tutors list updated.']);
        } catch (Exception $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unable to save the tutor list.']);
        }
        exit;
    }

    /**
     * Update just the subjects and grades offered from the dashboard.
     * Accepts JSON: { subjects: [ {name, grade}, ... ], gradesOffered: string }
     */
    public function updateSubjects() {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /tutor-dashboard');
            exit;
        }

        $tutorProfile = $this->getOwnTutorProfileOrRedirect();

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if (!is_array($data) || !isset($data['subjects']) || !is_array($data['subjects'])) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid subjects data.']);
            exit;
        }

        $subjectParts = [];
        foreach ($data['subjects'] as $item) {
            if (!is_array($item)) continue;
            $name = trim($item['name'] ?? '');
            if ($name === '') continue;
            $grade = trim($item['grade'] ?? '');
            if ($grade !== '' && strtolower($grade) !== 'all grades') {
                $subjectParts[] = $name . ' (' . $grade . ')';
            } else {
                $subjectParts[] = $name;
            }
        }
        $subjects = implode(', ', $subjectParts);
        $gradesOffered = trim($data['gradesOffered'] ?? '');

        try {
            $stmt = $this->tutorModel->getDb()->prepare("UPDATE tutors SET subjects = ?, grades_offered = ? WHERE id = ?");
            $stmt->execute([$subjects, $gradesOffered, $tutorProfile['id']]);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Subjects and grades updated.']);
        } catch (Exception $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unable to save subjects.']);
        }
        exit;
    }

    /**
     * Helper: get the logged-in user's own tutor profile, or redirect away
     */
    private function getOwnTutorProfileOrRedirect() {
        $user = getCurrentUser();

        if (!isTutor()) {
            setFlashMessage('error', 'Only tutors can perform this action.');
            header('Location: /study-group');
            exit;
        }

        return $this->getOrCreateTutorProfile($user);
    }

    /**
     * Helper: get the logged-in user's tutor profile, auto-creating a draft
     * profile if none exists yet (e.g. the account was flagged as a tutor
     * before its profile row was created). Drafts are hidden from the
     * Available Tutors list (is_available = 0) until the tutor completes
     * their profile via "Update Tutor Profile" on the Study Groups page.
     */
    private function getOrCreateTutorProfile($user) {
        $tutorProfile = $this->tutorModel->findByUserId($user['id']);
        if ($tutorProfile) {
            return $tutorProfile;
        }

        // Auto-provision a draft profile from the account data
        $this->tutorModel->register(
            $user['id'],
            $user['username'] ?? 'Tutor',
            'General',
            '',
            '',
            $user['phone'] ?? '',
            $user['email'] ?? '',
            'hourly',
            '',
            0 // is_available = 0: not publicly listed until profile is completed
        );

        setFlashMessage('info', 'A draft tutor profile was created for you. Click "Edit Profile" below to add your subjects, rate, and bio.');

        return $this->tutorModel->findByUserId($user['id']);
    }

    public function createProblem() {
        requireLogin();
        $user = getCurrentUser();
        if (!$user) {
            header("Location: /auth/login");
            exit;
        }
        $tutorId = (int)($_POST["tutor_id"] ?? 0);
        $subject = trim($_POST["subject"] ?? "");
        $problemText = trim($_POST["problem_text"] ?? "");
        if (!$tutorId || $problemText === "") {
            setFlashMessage("error", "Please state your problem.");
            header("Location: /tutor/lessons/" . $tutorId);
            exit;
        }
        $link = $this->tutorStudentModel->findActiveLink($tutorId, $user["id"]);
        $grade = $link["grade"] ?? null;
        $studentUserId = (int)($link["student_user_id"] ?? $user["id"]);
        $problemModel = new TutorStudentProblem();
        $problemModel->create($tutorId, $studentUserId, $grade, $subject, $problemText);
        setFlashMessage("success", "Your problem has been submitted.");
        header("Location: /tutor/lessons/" . $tutorId);
        exit;
    }

    public function resolveProblem($id) {
        requireLogin();
        $user = getCurrentUser();
        if (!$user) {
            header("Location: /auth/login");
            exit;
        }
        $problemId = (int)$id;
        $problemModel = new TutorStudentProblem();
        $problem = $problemModel->findById($problemId);
        if (!$problem) {
            setFlashMessage("error", "Problem not found.");
            header("Location: /tutor/my-students");
            exit;
        }
        $tutor = $this->tutorModel->findByUserId($user["id"]);
        if (!$tutor || (int)$tutor["id"] !== (int)$problem["tutor_id"]) {
            setFlashMessage("error", "You are not authorized to resolve this problem.");
            header("Location: /tutor/my-students");
            exit;
        }
        $problemModel->resolve($problemId);
        setFlashMessage("success", "Problem marked as resolved.");
        header("Location: /tutor/my-students");
        exit;
    }
}

