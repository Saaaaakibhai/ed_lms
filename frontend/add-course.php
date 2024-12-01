<?php
// Include necessary files and start the session
require_once '../backend/db.php'; // Adjust the path to your db.php
session_start();

// Get the course ID from the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Course ID is required.");
}

$courseId = intval($_GET['id']);
$course = null;
$enrolledUsers = [];
$students = [];

// Fetch course details
try {
    // Fetch course details from the database
    $courseQuery = "SELECT Title FROM course WHERE Id = :courseId";
    $stmt = $pdo->prepare($courseQuery);
    $stmt->execute(['courseId' => $courseId]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$course) {
        die("Course not found.");
    }

    // Fetch enrolled users
    $enrolledQuery = "
        SELECT users.name, users.email 
        FROM students 
        JOIN users ON students.user_id = users.id 
        WHERE students.course_id = :courseId
    ";
    $stmt = $pdo->prepare($enrolledQuery);
    $stmt->execute(['courseId' => $courseId]);
    $enrolledUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all students for the dropdown
    $studentsQuery = "SELECT Id, Name FROM users WHERE role = 'student'";
    $stmt = $pdo->query($studentsQuery);
    while ($student = $stmt->fetch()) {
        $students[] = $student;
    }
} catch (Exception $e) {
    die("An error occurred while fetching data: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Timeline</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1><?php echo htmlspecialchars($course['Title']); ?></h1>

        <h2>Add Students to Course</h2>
        <form action="../backend/add-student-to-course.php" method="POST">
            <div class="mb-3">
                <label for="student" class="form-label">Select Student</label>
                <select class="form-select" id="student" name="user_id" required>
                    <option value="">Choose a student</option>
                    <?php foreach ($students as $student): ?>
                        <option value="<?php echo htmlspecialchars($student['Id']); ?>"><?php echo htmlspecialchars($student['Name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Hidden field to store course_id -->
            <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($courseId); ?>">

            <button type="submit" class="btn btn-primary">Add Student</button>
        </form>

        <h3 class="mt-4">Students Enrolled in this Course</h3>
        <?php if (empty($enrolledUsers)): ?>
            <p>No students enrolled in this course yet.</p>
        <?php else: ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($enrolledUsers as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
