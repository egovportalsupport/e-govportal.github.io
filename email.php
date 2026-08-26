<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize it
    $name = htmlspecialchars(trim($_POST['name']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $message = htmlspecialchars(trim($_POST['message']));

    // Recipient email
    $to = "jelli.ignacio@benilde.edu.ph"; // <-- change to your email

    // Email subject
    $subject = "New Contact Form Submission from $name";

    // Email content
    $body = "Name: $name\n";
    $body .= "Email: $email\n\n";
    $body .= "Message:\n$message\n";

    // Email headers
    $headers = "From: $name <$email>" . "\r\n";
    $headers .= "Reply-To: $email" . "\r\n";

    // Send email
    if (mail($to, $subject, $body, $headers)) {
        echo "<h3>Email sent successfully!</h3>";
    } else {
        echo "<h3>Sorry, email sending failed.</h3>";
    }
} else {
    echo "<h3>Invalid request.</h3>";
}
?>