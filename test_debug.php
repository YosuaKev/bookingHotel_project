<?php
// Direct database test for debugging

echo "=== TESTING BOOKING AND PAYMENT SAVE ===\n\n";

// 1. Create test user
$email = 'debug' . time() . '@test.com';
$password = 'password123';

echo "[1] Creating test user...\n";
$ch = curl_init('http://127.0.0.1:8000/api/users/register');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
$userData = json_encode(array(
    'firstName' => 'Debug',
    'lastName' => 'User',
    'email' => $email,
    'phone' => '0123456789',
    'password' => $password,
    'provider' => 'local'
));
curl_setopt($ch, CURLOPT_POSTFIELDS, $userData);
$result = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "HTTP $http_code\n";
echo "Response: $result\n\n";
curl_close($ch);

// 2. Login user
echo "[2] Logging in user...\n";
$ch = curl_init('http://127.0.0.1:8000/api/users/login');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'email' => $email,
    'password' => $password
]));
$result = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "HTTP $http_code\n";
$login_response = json_decode($result, true);
echo "Response: " . json_encode($login_response, JSON_PRETTY_PRINT) . "\n\n";

if (!isset($login_response['token'])) {
    echo "ERROR: No token in response!\n";
    exit(1);
}
$token = $login_response['token'];
echo "✓ Token received: " . substr($token, 0, 30) . "...\n\n";
curl_close($ch);

// 3. Create booking
echo "[3] Creating booking...\n";
$booking_id = 'BK' . time();
$ch = curl_init('http://127.0.0.1:8000/api/bookings/create');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
));
$booking_data = [
    'id' => $booking_id,
    'firstName' => 'Debug',
    'lastName' => 'Guest',
    'email' => $email,
    'phone' => '0123456789',
    'roomType' => 'Deluxe Suite',
    'checkin' => '2025-12-20T14:00:00',
    'checkout' => '2025-12-22T11:00:00',
    'guests' => 2,
    'nights' => 2,
    'rate' => 150.00,
    'total' => 330.00,
    'userEmail' => $email
];
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($booking_data));
$result = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "HTTP $http_code\n";
$booking_response = json_decode($result, true);
echo "Response: " . json_encode($booking_response, JSON_PRETTY_PRINT) . "\n\n";

if (!isset($booking_response['success']) || !$booking_response['success']) {
    echo "ERROR: Booking creation failed!\n";
    exit(1);
}
echo "✓ Booking created: " . $booking_response['booking']['booking_id'] . "\n\n";
curl_close($ch);

// 4. Create payment
echo "[4] Creating payment...\n";
$ch = curl_init('http://127.0.0.1:8000/api/payments/create');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
));
$payment_data = [
    'booking_id' => $booking_id,
    'payment_method' => 'credit_card',
    'cardholder_name' => 'Debug User',
    'card_last_four' => '4242',
    'amount' => 330.00,
    'status' => 'completed',
    'billing_address' => '123 Test St',
    'city' => 'Test City',
    'zip_code' => '12345',
    'country' => 'Indonesia',
    'user_email' => $email
];
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payment_data));
$result = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "HTTP $http_code\n";
$payment_response = json_decode($result, true);
echo "Response: " . json_encode($payment_response, JSON_PRETTY_PRINT) . "\n\n";

if (!isset($payment_response['success']) || !$payment_response['success']) {
    echo "ERROR: Payment creation failed!\n";
    echo "Message: " . ($payment_response['message'] ?? 'Unknown error') . "\n";
    exit(1);
}
echo "✓ Payment created: " . $payment_response['transaction_id'] . "\n\n";
curl_close($ch);

// 5. Verify in database
echo "[5] Verifying in database...\n";
$mysqli = new mysqli('127.0.0.1', 'root', '', 'hotel');

// Check booking
$result = $mysqli->query("SELECT * FROM bookings WHERE booking_id = '$booking_id'");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo "✓ Booking found in database:\n";
    echo "  - ID: " . $row['booking_id'] . "\n";
    echo "  - Guest: " . $row['first_name'] . " " . $row['last_name'] . "\n";
    echo "  - Room: " . $row['room_type'] . "\n";
    echo "  - Total: $" . $row['total'] . "\n";
} else {
    echo "✗ Booking NOT found in database!\n";
}

// Check payment
$result = $mysqli->query("SELECT * FROM payments WHERE booking_id = '$booking_id'");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo "✓ Payment found in database:\n";
    echo "  - Transaction: " . $row['transaction_id'] . "\n";
    echo "  - Amount: $" . $row['amount'] . "\n";
    echo "  - Status: " . $row['status'] . "\n";
} else {
    echo "✗ Payment NOT found in database!\n";
}

$mysqli->close();

echo "\n=== TEST COMPLETE ===\n";
?>
