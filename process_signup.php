<?php
/**
 * DSS Recruitment Form - Secured submission handler
 * Expects POST with CSRF token and form fields; returns JSON.
 */
session_start();
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Disable error output in response (log only)
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

require_once __DIR__ . '/config.php';

// Allowed values (whitelist)
$allowed_interests = ['tech_tools', 'content_distribution', 'data_reports', 'training_support'];
$allowed_availability = ['1-5', '5-10', '10-20', '20+'];

function jsonResponse($success, $message, $code = 200) {
    http_response_code($code);
    echo json_encode(['success' => (bool) $success, 'message' => $message]);
    exit;
}

function sanitizeString($value, $maxLen = 1000) {
    $value = is_string($value) ? trim($value) : '';
    $value = strip_tags($value);
    return mb_substr($value, 0, $maxLen, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method.', 405);
}

// CSRF
$token = isset($_POST['csrf_token']) ? trim($_POST['csrf_token']) : '';
if (empty($_SESSION['csrf_token']) || !hash_equals((string) $_SESSION['csrf_token'], $token)) {
    jsonResponse(false, 'Invalid security token. Please refresh the page and try again.', 403);
}

// Required fields
$fullName = isset($_POST['fullName']) ? sanitizeString($_POST['fullName'], 255) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone = isset($_POST['phone']) ? sanitizeString($_POST['phone'], 50) : '';
$location = isset($_POST['location']) ? sanitizeString($_POST['location'], 255) : '';

if ($fullName === '') {
    jsonResponse(false, 'Full name is required.');
}
if ($email === '') {
    jsonResponse(false, 'Email address is required.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(false, 'Please enter a valid email address.');
}
if ($phone === '') {
    jsonResponse(false, 'Phone number is required.');
}
if ($location === '') {
    jsonResponse(false, 'Location is required.');
}

// Interests (array, at least one)
$raw_interests = isset($_POST['interests']) && is_array($_POST['interests']) ? $_POST['interests'] : [];
$interests = [];
foreach ($raw_interests as $v) {
    $v = trim((string) $v);
    if (in_array($v, $allowed_interests, true)) {
        $interests[] = $v;
    }
}
if (count($interests) === 0) {
    jsonResponse(false, 'Please select at least one area of interest.');
}

$experience = isset($_POST['experience']) ? sanitizeString($_POST['experience'], 5000) : null;
$availability = isset($_POST['availability']) ? trim((string) $_POST['availability']) : null;
if ($availability !== '' && !in_array($availability, $allowed_availability, true)) {
    $availability = null;
}

$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
$userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? mb_substr($_SERVER['HTTP_USER_AGENT'], 0, 500, 'UTF-8') : null;

try {
    $dsn = 'mysql:host=' . DSS_DB_HOST . ';dbname=' . DSS_DB_NAME . ';charset=' . DSS_DB_CHARSET;
    $pdo = new PDO($dsn, DSS_DB_USER, DSS_DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $interestsJson = json_encode(array_values($interests));

    $sql = "INSERT INTO recruitment_applications
            (full_name, email, phone, location, interests, experience, availability, ip_address, user_agent)
            VALUES
            (:full_name, :email, :phone, :location, :interests, :experience, :availability, :ip_address, :user_agent)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':full_name'   => $fullName,
        ':email'       => $email,
        ':phone'       => $phone,
        ':location'    => $location,
        ':interests'   => $interestsJson,
        ':experience'  => $experience ?: null,
        ':availability'=> $availability ?: null,
        ':ip_address'  => $ip,
        ':user_agent'  => $userAgent,
    ]);

    // Regenerate CSRF token after successful submit
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    jsonResponse(true, 'Thank you for your application! We\'ll review your submission and contact you soon.');
} catch (PDOException $e) {
    error_log('DSS recruitment DB error: ' . $e->getMessage());
    jsonResponse(false, 'Unable to save your application. Please try again later.', 500);
} catch (Throwable $e) {
    error_log('DSS recruitment error: ' . $e->getMessage());
    jsonResponse(false, 'An error occurred. Please try again later.', 500);
}
