<?php
// Set headers for JSON response
header('Content-Type: application/json');

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Database configuration
// IMPORTANT: Update these with your actual database credentials
$db_host = 'localhost';
$db_name = 'dss_volunteers';
$db_user = 'your_username';
$db_pass = 'your_password';

// Email configuration
$admin_email = 'admin@joinDSSteam.com'; // Change to your email
$from_email = 'noreply@joinDSSteam.com';

// Response array
$response = array();

try {
    // Check if form was submitted
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Validate and sanitize input
    $fullName = filter_input(INPUT_POST, 'fullName', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING);
    $experience = filter_input(INPUT_POST, 'experience', FILTER_SANITIZE_STRING);
    $availability = filter_input(INPUT_POST, 'availability', FILTER_SANITIZE_STRING);
    
    // Get interests array
    $interests = isset($_POST['interests']) ? $_POST['interests'] : array();
    $interests = array_map(function($interest) {
        return filter_var($interest, FILTER_SANITIZE_STRING);
    }, $interests);

    // Validate required fields
    if (empty($fullName) || empty($email) || empty($phone) || empty($location)) {
        throw new Exception('Please fill in all required fields');
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Please enter a valid email address');
    }

    // Validate at least one interest selected
    if (empty($interests)) {
        throw new Exception('Please select at least one area of interest');
    }

    // Connect to database
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare interests as JSON
    $interests_json = json_encode($interests);

    // Insert into database
    $sql = "INSERT INTO volunteers (full_name, email, phone, location, interests, experience, availability, created_at) 
            VALUES (:full_name, :email, :phone, :location, :interests, :experience, :availability, NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':full_name' => $fullName,
        ':email' => $email,
        ':phone' => $phone,
        ':location' => $location,
        ':interests' => $interests_json,
        ':experience' => $experience,
        ':availability' => $availability
    ]);

    // Send confirmation email to volunteer
    $volunteer_subject = "Welcome to DSS Digital Saturation Team!";
    $volunteer_message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .header { background: #f4a460; color: #fff; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .footer { background: #0a0e27; color: #fff; padding: 15px; text-align: center; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h1>Welcome to DSS!</h1>
        </div>
        <div class='content'>
            <h2>Thank You for Joining, $fullName!</h2>
            <p>We're excited to have you join our Digital Saturation Team. Your dedication to spreading <em>Rhapsody of Realities</em> will make a significant impact.</p>
            
            <h3>Your Registration Details:</h3>
            <ul>
                <li><strong>Name:</strong> $fullName</li>
                <li><strong>Email:</strong> $email</li>
                <li><strong>Phone:</strong> $phone</li>
                <li><strong>Location:</strong> $location</li>
                <li><strong>Areas of Interest:</strong> " . implode(', ', $interests) . "</li>
            </ul>
            
            <p>A team member will contact you within 24-48 hours with next steps and training information.</p>
            
            <p>If you have any questions, feel free to reach out to us at info@joinDSSteam.com or call 123-456-7890.</p>
            
            <p><strong>Together, we're spreading God's Word to the world!</strong></p>
        </div>
        <div class='footer'>
            <p>DSS Technologies Unit | www.joinDSSteam.com</p>
        </div>
    </body>
    </html>
    ";

    $volunteer_headers = "MIME-Version: 1.0" . "\r\n";
    $volunteer_headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $volunteer_headers .= "From: DSS Team <$from_email>" . "\r\n";

    mail($email, $volunteer_subject, $volunteer_message, $volunteer_headers);

    // Send notification email to admin
    $admin_subject = "New DSS Volunteer Registration: $fullName";
    $admin_message = "
    <html>
    <body>
        <h2>New Volunteer Registration</h2>
        <p><strong>Name:</strong> $fullName</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Phone:</strong> $phone</p>
        <p><strong>Location:</strong> $location</p>
        <p><strong>Areas of Interest:</strong> " . implode(', ', $interests) . "</p>
        <p><strong>Experience:</strong> $experience</p>
        <p><strong>Availability:</strong> $availability</p>
        <p><strong>Registration Time:</strong> " . date('Y-m-d H:i:s') . "</p>
    </body>
    </html>
    ";

    $admin_headers = "MIME-Version: 1.0" . "\r\n";
    $admin_headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $admin_headers .= "From: DSS System <$from_email>" . "\r\n";
    $admin_headers .= "Reply-To: $email" . "\r\n";

    mail($admin_email, $admin_subject, $admin_message, $admin_headers);

    // Success response
    $response['success'] = true;
    $response['message'] = 'Thank you for signing up! Check your email for confirmation.';

} catch (PDOException $e) {
    // Database error
    $response['success'] = false;
    $response['message'] = 'Database error. Please try again later.';
    error_log('Database Error: ' . $e->getMessage());
} catch (Exception $e) {
    // General error
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

// Send JSON response
echo json_encode($response);
?>