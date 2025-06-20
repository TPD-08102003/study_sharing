<?php

namespace App;

use PDO;

class Notification
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getNotificationById($notification_id)
    {
        $query = "SELECT * FROM notifications WHERE notification_id = :notification_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':notification_id', $notification_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getNotificationsByUserId($account_id)
    {
        $query = "SELECT * FROM notifications WHERE account_id = :account_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':account_id', $account_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createNotification($account_id, $message, $is_read = false)
    {
        $query = "INSERT INTO notifications (account_id, message, is_read) VALUES (:account_id, :message, :is_read)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':account_id', $account_id, PDO::PARAM_INT);
        $stmt->bindParam(':message', $message, PDO::PARAM_STR);
        $stmt->bindParam(':is_read', $is_read, PDO::PARAM_BOOL);
        return $stmt->execute();
    }
}
