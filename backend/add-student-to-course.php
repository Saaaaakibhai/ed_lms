<?php
include 'db.php'; // Database connection

if (isset($_POST['user_id']) && isset($_POST['course_id'])) {
    $user_id = $_POST['user_id'];
    $course_id = $_POST['course_id'];

    // Validate if course exists
    $sql = "SELECT COUNT(*) FROM course WHERE Id = :course_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['course_id' => $course_id]);
    $course_exists = $stmt->fetchColumn();

    if (!$course_exists) {
        echo "Course does not exist.";
        exit();
    }

    // Check if student is already enrolled
    $sql = "SELECT COUNT(*) FROM students WHERE user_id = :user_id AND course_id = :course_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $user_id, 'course_id' => $course_id]);
    $already_enrolled = $stmt->fetchColumn();

    if ($already_enrolled) {
        echo "Student is already enrolled in this course.";
        exit();
    }

    // Enroll the student in the course
    $sql = "INSERT INTO students (user_id, course_id) VALUES (:user_id, :course_id)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $user_id, 'course_id' => $course_id]);

    echo "Student added successfully!";
} else {
    echo "Invalid request.";
}
?>
