<?php
function sanitize($value) {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contact.html');
    exit;
}

$name = sanitize($_POST['name'] ?? '');
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$phone = sanitize($_POST['phone'] ?? '');
$destination = sanitize($_POST['destination'] ?? '');
$adults = sanitize($_POST['adults'] ?? '');
$children = sanitize($_POST['children'] ?? '0');
$date = sanitize($_POST['date'] ?? '');
$duration = sanitize($_POST['duration'] ?? '');
$accommodation = sanitize($_POST['accommodation'] ?? '');
$interests = sanitize($_POST['interests'] ?? '');

$errors = [];

if ($name === '') {
    $errors[] = 'Full Name is required.';
}
if ($email === false) {
    $errors[] = 'A valid Email Address is required.';
}
if ($phone === '') {
    $errors[] = 'Phone Number is required.';
}
if ($destination === '') {
    $errors[] = 'Please select a Destination.';
}
if ($adults === '' || !ctype_digit($adults) || (int)$adults < 1) {
    $errors[] = 'Please enter the number of adults (at least 1).';
}
if ($date === '') {
    $errors[] = 'Preferred Travel Date is required.';
}
if ($duration === '') {
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
    $recipient = 'tours.femiprez@gmail.com';
    $subject = 'New Booking Request from ' . $name;
    $message = "Name: $name\n";
    $message .= "Email: $email\n";
    $message .= "Phone: $phone\n";
    $message .= "Destination: $destinationLabel\n";
    $message .= "Adults: $adults\n";
    $message .= "Children: $children\n";
    $message .= "Preferred Date: $date\n";
    $message .= "Duration: $duration days\n";
    $message .= "Accommodation: $accommodation\n";
    $message .= "Special Interests: $interests\n";

    $headers = "From: $name <$email>" . "\r\n";
    $headers .= "Reply-To: $email" . "\r\n";

    $sent = false;
    if (function_exists('mail')) {
        $sent = mail($recipient, $subject, $message, $headers);
    }

    $statusMessage = $sent
        ? 'Thank you! Your booking request has been sent successfully. We will respond within 24 hours.'
        : 'Thank you! Your booking request was received. If email delivery is not enabled on this server, please contact tours.femiprez@gmail.com directly.';
}
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
                        <div class="booking-summary">
                            <h3>Your Booking Details</h3>
                            <p><strong>Name:</strong> <?php echo $name; ?></p>
                            <p><strong>Email:</strong> <?php echo $email; ?></p>
                            <p><strong>Phone:</strong> <?php echo $phone; ?></p>
                            <p><strong>Destination:</strong> <?php echo sanitize($destinationLabel); ?></p>
                            <p><strong>Adults:</strong> <?php echo $adults; ?></p>
                            <p><strong>Children:</strong> <?php echo $children; ?></p>
                            <p><strong>Preferred Travel Date:</strong> <?php echo $date; ?></p>
                            <p><strong>Duration:</strong> <?php echo $duration; ?> days</p>
                            <p><strong>Accommodation:</strong> <?php echo sanitize($accommodation); ?></p>
                            <p><strong>Special Interests:</strong> <?php echo $interests; ?></p>
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
