<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
// Database Configuration for XAMPP
//$dbHost = 'sql307.infinityfree.com';
//$dbUser = 'if0_42240914';
///$dbPassword = 'txLIRFr3egr3';
//$dbName = 'if0_42240914_femiprez_tours';
$dbHost = 'localhost';
$dbUser = 'root';
$dbPassword = '';
$dbName = 'Femiprez_Tours';


// Create connection
$conn = new mysqli($dbHost, $dbUser, $dbPassword, $dbName);

// Check connection
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}


// Select database
if (!$conn->select_db($dbName)) {
    die('Database selection failed: ' . $conn->error);
}

// Set charset to utf8
$conn->set_charset('utf8');

function sanitize($value) {
    return htmlspecialchars(trim((string)$value), ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contact.html');
    exit;
}
//db hosting configuration
/*
 $dbHost = 'localhost';
$dbUser = 'root';
$dbPassword = '';
$dbName = 'femiprez_tours';
/*
/*
$full_name = sanitize($_POST['full_name'] ?? '');
$email_address = filter_var(trim($_POST['email_address'] ?? ''), FILTER_VALIDATE_EMAIL);
$phone_number = sanitize($_POST['phone_number'] ?? '');
$destination = sanitize($_POST['destination'] ?? '');
$number_of_adults = sanitize($_POST['number_of_adults'] ?? '');
$number_of_children = sanitize($_POST['number_of_children'] ?? '0');
$preferred_travel_date = sanitize($_POST['preferred_travel_date'] ?? '');
$duration_days = sanitize($_POST['duration_days'] ?? '');
$accommodation_preference = sanitize($_POST['accommodation_preference'] ?? '');
$special_interests = sanitize($_POST['special_interests'] ?? '');
*/
$full_name = sanitize($_POST['name'] ?? '');
$email_address = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$phone_number = sanitize($_POST['phone'] ?? '');
$destination = sanitize($_POST['destination'] ?? '');
$number_of_adults = sanitize($_POST['adults'] ?? '');
$number_of_children = sanitize($_POST['children'] ?? '0');
$preferred_travel_date = sanitize($_POST['date'] ?? '');
$duration_days = sanitize($_POST['duration'] ?? '');
$accommodation_preference = sanitize($_POST['accommodation'] ?? '');
$special_interests = sanitize($_POST['interests'] ?? '');
$errors = [];

if ($full_name === '') {
    $errors[] = 'Full Name is required.';
}
if ($email_address === false) {
    $errors[] = 'A valid Email Address is required.';
}
if ($phone_number === '') {
    $errors[] = 'Phone Number is required.';
}
if ($destination === '') {
    $errors[] = 'Please select a Destination.';
}
if ($number_of_adults === '' || !ctype_digit($number_of_adults) || (int)$number_of_adults < 1) {
    $errors[] = 'Please enter the number of adults (at least 1).';
}
if ($number_of_children === '' || !ctype_digit($number_of_children) || (int)$number_of_children < 0) {
    $errors[] = 'Please enter a valid number of children (0 or more).';
}
if ($preferred_travel_date === '') {
    $errors[] = 'Preferred Travel Date is required.';
}
if ($duration_days === '') {
    $errors[] = 'Please select a Duration.';
}

$destinationNames = [
    'maasai-mara' => 'Maasai Mara National Reserve',
    'amboseli' => 'Amboseli National Park',
    'tsavo' => 'Tsavo National Park',
    'diani' => 'Diani Beach',
    'malindi' => 'Malindi Beach',
    'watamu' => 'Watamu Beach',
    'mount-kenya' => 'Mount Kenya',
    'combined' => 'Combined Destinations',
    'custom' => 'Custom Itinerary',
];

$destinationLabel = $destinationNames[$destination] ?? ucfirst(str_replace('-', ' ', $destination));

if (empty($errors)) {
    $createTableSQL = "
    CREATE TABLE IF NOT EXISTS bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        destination VARCHAR(100) NOT NULL,
        adults INT NOT NULL,
        children INT DEFAULT 0,
        travel_date DATE NOT NULL,
        duration INT NOT NULL,
        accommodation VARCHAR(50),
        special_interests TEXT,
        submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status VARCHAR(50) DEFAULT 'pending'
    )";
    
    if (!$conn->query($createTableSQL)) {
        die('Error creating table: ' . $conn->error);
    }
    
    // Prepare SQL statement for inserting booking data
    $sql = "INSERT INTO bookings (
    full_name, email_address, phone_number, destination, number_of_adults, number_of_children, travel_date, duration, accommodation, special_interests) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die('Prepare failed: ' . $conn->error);
    }
    
    // Bind parameters
    $stmt->bind_param(
        'ssssiiisss',
        $full_name,
        $email_address,
        $phone_number,
        $destination,
        $number_of_adults,
        $number_of_children,
        $preferred_travel_date,
        $duration_days,
        $accommodation_preference,
        $special_interests
    );
    
    // Execute statement
    if ($stmt->execute()) {
        $bookingId = $conn->insert_id;
        $databaseStored = true;
    } else {
        $databaseStored = false;
        $dbError = 'Error saving to database: ' . $stmt->error;
    }
    
    $stmt->close();
    
    // Send email notification
    $recipient = 'tours.femiprez@gmail.com';
    $subject = 'New Booking Request from ' . $full_name;
    $message = "Name: $full_name\n";
    $message .= "Email: $email_address\n";
    $message .= "Phone: $phone_number\n";
    $message .= "Destination: " . ($destinationNames[$destination] ?? $destination) . "\n";
    $message .= "Adults: $number_of_adults\n";
    $message .= "Children: $number_of_children\n";
    $message .= "Preferred Date: $preferred_travel_date\n";
    $message .= "Duration: $duration_days days\n";
    $message .= "Accommodation: $accommodation_preference\n";
    $message .= "Special Interests: $special_interests\n";
    if (isset($bookingId)) {
        $message .= "\nBooking ID: $bookingId\n";
    }

    $headers = "From: $full_name <$email_address>" . "\r\n";
    $headers .= "Reply-To: $email_address" . "\r\n";

    $sent = false;
    //if (function_exists('mail')) {
    //    $sent = mail($recipient, $subject, $message, $headers);
    //}

    $statusMessage = $databaseStored 
        ? 'Thank you! Your booking request has been saved successfully (Booking ID: ' . $bookingId . '). We will respond within 24 hours.'
        : 'Your booking request was received. We will contact you soon.';
}

// Close database connection
// if ($conn->query($sql)===TRUE) {
//     echo "Booking submitted successfully.";
// } else {
//     echo "Error: " . $sql . "<br>" . $conn->error;
// }
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Request Submitted</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="logo">
                <h1>Femiprez Tours</h1>
            </div>
            <ul class="nav-links">
                <li><a href="index.html">Home</a></li>
                <li><a href="destinations.html">Destinations</a></li>
                <li><a href="packages.html">Packages</a></li>
                <li><a href="safari.html">Safari</a></li>
                <li><a href="contact.html" class="active">Contact</a></li>
            </ul>
        </div>
    </nav>
    <section class="page-header">
        <h1>Booking Request Status</h1>
        <p>Your adventure request details are below.</p>
    </section>
    <section class="contact-section">
        <div class="container">
            <div class="booking-form-container">
                <?php if (!empty($errors)): ?>
                    <div class="form-errors">
                        <h2>There were some problems with your submission:</h2>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo sanitize($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <a class="btn btn-primary" href="contact.html">Return to the form</a>
                    </div>
                <?php else: ?>
                    <div class="form-success">
                        <h2>Booking Request Submitted</h2>
                        <p><?php echo sanitize($statusMessage); ?></p>
                        <?php if (isset($bookingId)): ?>
                            <p style="background-color: #e8f5e9; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
                                <strong>Your Booking Reference ID:</strong> <span style="font-family: monospace; font-weight: bold;"><?php echo $bookingId; ?></span>
                            </p>
                        <?php endif; ?>
                        <div class="booking-summary">
                            <h3>Your Booking Details</h3>
                            <p><strong>Name:</strong> <?php echo $full_name; ?></p>
                            <p><strong>Email:</strong> <?php echo $email_address; ?></p>
                            <p><strong>Phone:</strong> <?php echo $phone_number; ?></p>
                            <p><strong>Destination:</strong> <?php echo sanitize($destinationLabel); ?></p>
                            <p><strong>Adults:</strong> <?php echo $number_of_adults; ?></p>
                            <p><strong>Children:</strong> <?php echo $number_of_children; ?></p>
                            <p><strong>Preferred Travel Date:</strong> <?php echo $preferred_travel_date; ?></p>
                            <p><strong>Duration:</strong> <?php echo $duration_days; ?> days</p>
                            <p><strong>Accommodation:</strong> <?php echo sanitize($accommodation_preference); ?></p>
                            <p><strong>Special Interests:</strong> <?php echo $special_interests; ?></p>
                        </div>
                        <a class="btn btn-primary" href="index.html">Continue Browsing</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <footer>
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2026 Femiprez Tours. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
