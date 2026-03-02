<?php
/**
 * Memorandum Model
 */

class Memorandum {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($scriptId, $content) {
        $stmt = $this->db->prepare("INSERT INTO memorandums (script_id, content) VALUES (?, ?)");
        $stmt->execute([$scriptId, $content]);

        return $this->db->lastInsertId();
    }

    public function findByScriptId($scriptId) {
        $stmt = $this->db->prepare("SELECT * FROM memorandums WHERE script_id = ?");
        $stmt->execute([$scriptId]);
        return $stmt->fetch();
    }

    public function update($id, $content) {
        $stmt = $this->db->prepare("UPDATE memorandums SET content = ? WHERE id = ?");
        $stmt->execute([$content, $id]);
        return true;
    }
}
