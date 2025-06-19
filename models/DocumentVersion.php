<?php

namespace App;

use PDO;

class DocumentVersion
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getVersionById($version_id)
    {
        $query = "SELECT * FROM document_versions WHERE version_id = :version_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':version_id', $version_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getVersionsByDocumentId($document_id)
    {
        $query = "SELECT * FROM document_versions WHERE document_id = :document_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':document_id', $document_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createVersion($document_id, $version_number, $file_path, $change_note)
    {
        $query = "INSERT INTO document_versions (document_id, version_number, file_path, change_note) VALUES (:document_id, :version_number, :file_path, :change_note)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':document_id', $document_id, PDO::PARAM_INT);
        $stmt->bindParam(':version_number', $version_number, PDO::PARAM_INT);
        $stmt->bindParam(':file_path', $file_path, PDO::PARAM_STR);
        $stmt->bindParam(':change_note', $change_note, PDO::PARAM_STR);
        return $stmt->execute();
    }
}
