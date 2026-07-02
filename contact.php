<?php
header('Content-Type: application/json');

function fail($msg, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $msg]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail('Method not allowed', 405);
}

// Honeypot: real visitors never fill this hidden field.
if (!empty($_POST['website'])) {
    echo json_encode(['success' => true]);
    exit;
}

function clean_line($value) {
    $value = trim((string) $value);
    return preg_replace('/[\r\n]+/', ' ', $value);
}

$name    = clean_line($_POST['name'] ?? '');
$email   = clean_line($_POST['email'] ?? '');
$company = clean_line($_POST['company'] ?? '');
$message = trim((string) ($_POST['message'] ?? ''));

if ($name === '' || $email === '') {
    fail('Name and email are required.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fail('Please provide a valid email address.');
}

$to      = 'info@tadbiradvisory.com';
$subject = 'New website enquiry from ' . $name;

$body  = "New enquiry submitted via tadbiradvisory.com\n\n";
$body .= "Name: $name\n";
$body .= "Email: $email\n";
$body .= 'Company: ' . ($company !== '' ? $company : '-') . "\n\n";
$body .= "Message:\n" . ($message !== '' ? $message : '-') . "\n";

$headers   = [];
$headers[] = 'From: Tadbir Advisory Website <noreply@tadbiradvisory.com>';
$headers[] = 'Reply-To: ' . $name . ' <' . $email . '>';
$headers[] = 'Content-Type: text/plain; charset=UTF-8';
$headers[] = 'X-Mailer: PHP/' . phpversion();

$sent = mail($to, $subject, $body, implode("\r\n", $headers));

if (!$sent) {
    fail('Could not send message. Please try again later.', 500);
}

echo json_encode(['success' => true]);
