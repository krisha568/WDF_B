<?php
require_once "db.php";

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Get form data
    $name = trim($_POST["name"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirmPassword"] ?? "";
    $course = trim($_POST["course"] ?? "");
    $year = trim($_POST["year"] ?? "");

    // Backend validation
    if (!preg_match("/^[A-Za-z ]{2,100}$/", $name)) {
        $message = "Please enter a valid name.";

    } elseif (!preg_match("/^[A-Za-z0-9_]{3,50}$/", $username)) {
        $message = "Username must be 3-50 letters, numbers or underscores.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";

    } elseif (strlen($password) < 8) {
        $message = "Password must contain at least 8 characters.";

    } elseif ($password !== $confirmPassword) {
        $message = "Passwords do not match.";

    } elseif (!in_array($course, [
        "B.Tech IT", "B.Tech CSE", "B.Tech CE", "B.Tech ME"
    ], true)) {
        $message = "Please select a valid course.";

    } elseif (!in_array($year, [
        "First Year", "Second Year", "Third Year", "Fourth Year"
    ], true)) {
        $message = "Please select a valid year.";

    } else {

        // Check duplicate username or email
        $sql = "SELECT student_id FROM students
                WHERE username = ? OR email = ?
                LIMIT 1";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
    $message = "Database error: " . $conn->error;
}
         else {

            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {

                $message = "Username or email already exists.";

            } else {

                // Hash password before saving
                $hashedPassword = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                // Insert student using prepared statement
                $insert = $conn->prepare(
                    "INSERT INTO students
                    (name, username, email, password, course, year)
                    VALUES (?, ?, ?, ?, ?, ?)"
                );
                if (!$insert) {
    die("SQL Error: " . $conn->error);
}

                if (!$insert) {
    $message = "Database error: " . $conn->error;
}
                else {

                    $insert->bind_param(
                        "ssssss",
                        $name,
                        $username,
                        $email,
                        $hashedPassword,
                        $course,
                        $year
                    );

                    if ($insert->execute()) {

                        $insert->close();
                        $stmt->close();
                        $conn->close();

                        // Redirect after successful registration
                        header("Location: index.html?registered=1");
                        exit();

                    } elseif ($insert->errno === 1062) {

                        $message = "Username or email already exists.";

                    }  else {
    $message = "Registration failed: " . $insert->error;
}

                    }

                    if ($insert instanceof mysqli_stmt) {
    $insert->close();
}
                }
            }

            if ($stmt instanceof mysqli_stmt) {
    $stmt->close();
}
        }
    }

    if ($message !== "") {
        $messageType = "error";
    }

?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>StudentHub Registration</title>

<style>




body {
    font-family: Arial, sans-serif;
    margin: 0;
    background-color: #F0F8FF;
    color: #333;
}

header {
    background-color: #2c6e9e;
    color: white;
    text-align: center;
    padding: 20px;
}

nav {
    background-color: #164a70;
    text-align: center;
    padding: 12px;
}

nav a {
    color: white;
    text-decoration: none;
    margin: 8px;
}

main {
    width: 70%;
    max-width: 850px;
    margin: 30px auto;
}

section {