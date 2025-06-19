<?php

namespace App;

use PDO;

class CourseMember
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getMemberById($course_member_id)
    {
        $query = "SELECT * FROM course_members WHERE course_member_id = :course_member_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':course_member_id', $course_member_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addMember($course_id, $user_id)
    {
        $query = "INSERT INTO course_members (course_id, user_id) VALUES (:course_id, :user_id)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function removeMember($course_member_id)
    {
        $query = "DELETE FROM course_members WHERE course_member_id = :course_member_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':course_member_id', $course_member_id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
