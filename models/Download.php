<?php

namespace App;

use PDO;

class Download
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getDownloadById($download_id)
    {
        $query = "SELECT * FROM downloads WHERE download_id = :download_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':download_id', $download_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getDownloadsByDocumentId($document_id)
    {
        $query = "SELECT * FROM downloads WHERE document_id = :document_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':document_id', $document_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function recordDownload($document_id, $account_id)
    {
        $query = "INSERT INTO downloads (document_id, account_id, download_date) VALUES (:document_id, :account_id, NOW())";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':document_id', $document_id, PDO::PARAM_INT);
        $stmt->bindParam(':account_id', $account_id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
