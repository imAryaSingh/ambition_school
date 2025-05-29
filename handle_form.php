<?php
// DATABASE CONNECTION
$host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "contact_db";

// Create connection
$conn = new mysqli($host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// SANITIZE & VALIDATE INPUT
function clean_input($data) {
  return htmlspecialchars(stripslashes(trim($data)));
}

$name = clean_input($_POST['name']);
$email = clean_input($_POST['email']);
$phone = clean_input($_POST['phone']);
$message = clean_input($_POST['message']);

// VALIDATE FIELDS
if (!preg_match("/^[A-Za-z\s]{3,50}$/", $name)) {
  die("Invalid name.");
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  die("Invalid email.");
}
if (!preg_match("/^[0-9]{10}$/", $phone)) {
  die("Invalid phone number.");
}
if (strlen($message) < 10 || strlen($message) > 500) {
  die("Message length must be between 10 and 500 characters.");
}

// INSERT INTO DATABASE
$sql = "INSERT INTO contact_messages (name, email, phone, message) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $name, $email, $phone, $message);

if (!$stmt->execute()) {
  die("Database error: " . $stmt->error);
}
$stmt->close();

// SEND EMAIL
$to = "70211@cbseshiksha.in";
$subject = "📩 New Contact Message from $name";
$body = "You received a new message:\n\n"
      . "Name: $name\n"
      . "Email: $email\n"
      . "Phone: $phone\n"
      . "Message:\n$message";

$headers = "From: $email";

if (mail($to, $subject, $body, $headers)) {
  echo "Message sent successfully and saved in database!";
} else {
  echo "Saved in database, but email sending failed.";
}

$conn->close();
?>