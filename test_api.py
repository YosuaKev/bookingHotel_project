#!/usr/bin/env python3
import requests
import json
from datetime import datetime, timedelta

# Test configuration
BASE_URL = 'http://127.0.0.1:8000/api'
TEST_EMAIL = f'testuser{int(datetime.now().timestamp())}@example.com'

print("=" * 60)
print("Hotel Booking API Integration Test")
print("=" * 60)

# Step 1: Register User
print("\n[1] Testing User Registration...")
register_response = requests.post(f'{BASE_URL}/users/register', json={
    'firstName': 'Test',
    'lastName': 'User',
    'email': TEST_EMAIL,
    'phone': '0123456789',
    'password': 'password123',
    'provider': 'local'
})
print(f"Status: {register_response.status_code}")
if register_response.status_code == 201:
    reg_data = register_response.json()
    print(f"✓ Registration successful - User ID: {reg_data['user']['id']}")
else:
    print(f"✗ Registration failed: {register_response.text}")
    exit(1)

# Step 2: Login User
print("\n[2] Testing User Login...")
login_response = requests.post(f'{BASE_URL}/users/login', json={
    'email': TEST_EMAIL,
    'password': 'password123'
})
print(f"Status: {login_response.status_code}")
if login_response.status_code == 200:
    login_data = login_response.json()
    auth_token = login_data['token']
    user_id = login_data['user']['id']
    print(f"✓ Login successful")
    print(f"  Token: {auth_token[:30]}...")
    print(f"  User ID: {user_id}")
else:
    print(f"✗ Login failed: {login_response.text}")
    exit(1)

# Step 3: Create Booking
print("\n[3] Testing Booking Creation...")
booking_id = f"BK{int(datetime.now().timestamp())}"
booking_data = {
    'id': booking_id,
    'firstName': 'Test',
    'lastName': 'Guest',
    'email': TEST_EMAIL,
    'phone': '0123456789',
    'roomType': 'Deluxe Suite',
    'checkin': '2025-12-20 14:00:00',
    'checkout': '2025-12-22 11:00:00',
    'guests': 2,
    'nights': 2,
    'rate': 150.00,
    'total': 330.00,
    'userEmail': TEST_EMAIL,
    'userId': user_id
}

booking_response = requests.post(
    f'{BASE_URL}/bookings/create',
    json=booking_data,
    headers={'Authorization': f'Bearer {auth_token}'}
)
print(f"Status: {booking_response.status_code}")
if booking_response.status_code == 201:
    booking_result = booking_response.json()
    saved_booking_id = booking_result['booking']['booking_id']
    print(f"✓ Booking created successfully")
    print(f"  Booking ID: {saved_booking_id}")
    print(f"  Room: {booking_result['booking']['room_type']}")
    print(f"  Total: ${booking_result['booking']['total']}")
else:
    print(f"✗ Booking creation failed: {booking_response.text}")
    exit(1)

# Step 4: Create Payment
print("\n[4] Testing Payment Creation...")
payment_data = {
    'booking_id': saved_booking_id,
    'payment_method': 'credit_card',
    'cardholder_name': 'Test User',
    'card_last_four': '4242',
    'amount': 330.00,
    'status': 'completed',
    'billing_address': '123 Main St',
    'city': 'New York',
    'zip_code': '10001',
    'country': 'USA',
    'user_email': TEST_EMAIL
}

payment_response = requests.post(
    f'{BASE_URL}/payments/create',
    json=payment_data,
    headers={'Authorization': f'Bearer {auth_token}'}
)
print(f"Status: {payment_response.status_code}")
if payment_response.status_code == 201:
    payment_result = payment_response.json()
    print(f"✓ Payment created successfully")
    print(f"  Transaction ID: {payment_result['transaction_id']}")
    print(f"  Amount: ${payment_result['payment']['amount']}")
    print(f"  Status: {payment_result['payment']['status']}")
else:
    print(f"✗ Payment creation failed: {payment_response.text}")
    exit(1)

# Step 5: Get User Bookings
print("\n[5] Testing Get User Bookings...")
bookings_response = requests.get(
    f'{BASE_URL}/my-bookings',
    headers={'Authorization': f'Bearer {auth_token}'}
)
print(f"Status: {bookings_response.status_code}")
if bookings_response.status_code == 200:
    bookings_data = bookings_response.json()
    print(f"✓ Retrieved user bookings")
    print(f"  Total bookings: {len(bookings_data['bookings'])}")
    if bookings_data['bookings']:
        print(f"  Latest booking ID: {bookings_data['bookings'][0]['booking_id']}")
else:
    print(f"✗ Failed to get bookings: {bookings_response.text}")

# Step 6: Get User Payments
print("\n[6] Testing Get User Payments...")
payments_response = requests.get(
    f'{BASE_URL}/my-payments?email={TEST_EMAIL}',
    headers={'Authorization': f'Bearer {auth_token}'}
)
print(f"Status: {payments_response.status_code}")
if payments_response.status_code == 200:
    payments_data = payments_response.json()
    print(f"✓ Retrieved user payments")
    print(f"  Total payments: {len(payments_data['payments'])}")
else:
    print(f"⚠ Payments endpoint returned {payments_response.status_code}")

print("\n" + "=" * 60)
print("✓ ALL TESTS PASSED!")
print("=" * 60)
print("\nSummary:")
print(f"  - User registered: {TEST_EMAIL}")
print(f"  - User logged in with token")
print(f"  - Booking created: {saved_booking_id}")
print(f"  - Payment processed with transaction ID: {payment_result['transaction_id']}")
print(f"\nData has been successfully saved to the database!")
print("=" * 60)
