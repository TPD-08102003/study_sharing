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

    public function getNotificationsByUserId($user_id)
    {
        $query = "SELECT * FROM notifications WHERE user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createNotification($user_id, $message, $is_read = false)
    {
        $query = "INSERT INTO notifications (user_id, message, is_read) VALUES (:user_id, :message, :is_read)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':message', $message, PDO::PARAM_STR);
        $stmt->bindParam(':is_read', $is_read, PDO::PARAM_BOOL);
        return $stmt->execute();
    }
}
