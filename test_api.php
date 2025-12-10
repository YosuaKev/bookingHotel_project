<?php

// Test User Registration
echo "=== Testing User Registration ===\n";
$ch = curl_init('http://127.0.0.1:8000/api/users/register');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

$email = 'testuser' . time() . '@example.com';
$data = json_encode([
    'firstName' => 'Test',
    'lastName' => 'User',
    'email' => $email,
    'phone' => '0123456789',
    'password' => 'password123',
    'provider' => 'local'
]);

curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
$result = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "HTTP Code: " . $httpcode . "\n";
echo "Response: " . $result . "\n";
curl_close($ch);

// Parse response and get token
$response = json_decode($result, true);
$token = null;

if (isset($response['success']) && $response['success']) {
    echo "✓ Registration successful!\n";
}

// Test User Login
echo "\n=== Testing User Login ===\n";
$ch = curl_init('http://127.0.0.1:8000/api/users/login');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

$loginData = json_encode([
    'email' => $email,
    'password' => 'password123'
]);

curl_setopt($ch, CURLOPT_POSTFIELDS, $loginData);
$result = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "HTTP Code: " . $httpcode . "\n";
echo "Response: " . $result . "\n";

$response = json_decode($result, true);
if (isset($response['token'])) {
    $token = $response['token'];
    echo "✓ Login successful! Token: " . substr($token, 0, 20) . "...\n";
}
curl_close($ch);

// Test Booking Creation
echo "\n=== Testing Booking Creation ===\n";
$ch = curl_init('http://127.0.0.1:8000/api/bookings/create');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$headers = array('Content-Type: application/json');
if ($token) {
    $headers[] = 'Authorization: Bearer ' . $token;
}
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$bookingData = json_encode([
    'id' => 'BK' . time(),
    'firstName' => 'Test',
    'lastName' => 'Booking',
    'email' => $email,
    'phone' => '0123456789',
    'roomType' => 'Deluxe Suite',
    'checkin' => '2025-12-20 14:00:00',
    'checkout' => '2025-12-22 11:00:00',
    'guests' => 2,
    'nights' => 2,
    'rate' => 150,
    'total' => 330,
    'userEmail' => $email
]);

curl_setopt($ch, CURLOPT_POSTFIELDS, $bookingData);
$result = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "HTTP Code: " . $httpcode . "\n";
echo "Response: " . $result . "\n";

$response = json_decode($result, true);
if (isset($response['success']) && $response['success']) {
    echo "✓ Booking created successfully!\n";
    $bookingId = $response['booking']['booking_id'] ?? null;
} else {
    echo "✗ Booking creation failed\n";
}
curl_close($ch);

// Test Payment Creation
if (isset($bookingId)) {
    echo "\n=== Testing Payment Creation ===\n";
    $ch = curl_init('http://127.0.0.1:8000/api/payments/create');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
    $headers = array('Content-Type: application/json');
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $paymentData = json_encode([
        'booking_id' => $bookingId,
        'payment_method' => 'credit_card',
        'cardholder_name' => 'Test User',
        'card_last_four' => '4242',
        'amount' => 330,
        'status' => 'completed',
        'billing_address' => '123 Main St',
        'city' => 'New York',
        'zip_code' => '10001',
        'country' => 'USA',
        'user_email' => $email
    ]);

    curl_setopt($ch, CURLOPT_POSTFIELDS, $paymentData);
    $result = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    echo "HTTP Code: " . $httpcode . "\n";
    echo "Response: " . $result . "\n";
    
    $response = json_decode($result, true);
    if (isset($response['success']) && $response['success']) {
        echo "✓ Payment created successfully!\n";
    } else {
        echo "✗ Payment creation failed\n";
    }
    curl_close($ch);
}

echo "\n=== Checking Database ===\n";
// Check if data saved to database
$mysqli = new mysqli('127.0.0.1', 'root', '', 'hotel');
if ($mysqli->connect_error) {
    echo "Database connection failed: " . $mysqli->connect_error . "\n";
} else {
    $result = $mysqli->query("SELECT COUNT(*) as count FROM bookings");
    $row = $result->fetch_assoc();
    echo "Total bookings in database: " . $row['count'] . "\n";
    
    $result = $mysqli->query("SELECT COUNT(*) as count FROM payments");
    $row = $result->fetch_assoc();
    echo "Total payments in database: " . $row['count'] . "\n";
    
    $mysqli->close();
}

echo "\n=== Test Complete ===\n";
?>
