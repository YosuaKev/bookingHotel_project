#!/usr/bin/env python3
"""
Comprehensive booking flow test:
1. Register user
2. Login and get token
3. Create booking
4. Create payment
5. Verify data in database
"""

import requests
import json
import time
from datetime import datetime, timedelta

BASE_URL = 'http://127.0.0.1:8000'
API_URL = f'{BASE_URL}/api'

# Colors for output
GREEN = '\033[92m'
RED = '\033[91m'
YELLOW = '\033[93m'
BLUE = '\033[94m'
END = '\033[0m'

def print_header(msg):
    print(f"\n{BLUE}{'='*60}")
    print(f"{msg}")
    print(f"{'='*60}{END}\n")

def print_success(msg):
    print(f"{GREEN}✓ {msg}{END}")

def print_error(msg):
    print(f"{RED}✗ {msg}{END}")

def print_info(msg):
    print(f"{YELLOW}ℹ {msg}{END}")

def test_user_registration():
    """Test user registration"""
    print_header("STEP 1: USER REGISTRATION")
    
    email = f'testuser{int(time.time())}@example.com'
    
    payload = {
        'firstName': 'Test',
        'lastName': 'User',
        'email': email,
        'phone': '0812345678',
        'password': 'password123',
        'provider': 'local'
    }
    
    try:
        response = requests.post(f'{API_URL}/users/register', json=payload)
        result = response.json()
        
        if result.get('success'):
            print_success(f"User registered: {email}")
            print_info(f"Response: {json.dumps(result, indent=2)}")
            return email, payload['password']
        else:
            print_error(f"Registration failed: {result.get('message')}")
            return None, None
    except Exception as e:
        print_error(f"Registration error: {str(e)}")
        return None, None

def test_user_login(email, password):
    """Test user login and get token"""
    print_header("STEP 2: USER LOGIN")
    
    payload = {
        'email': email,
        'password': password
    }
    
    try:
        response = requests.post(f'{API_URL}/users/login', json=payload)
        result = response.json()
        
        if result.get('success') and result.get('token'):
            token = result['token']
            print_success(f"Login successful")
            print_success(f"Token: {token[:50]}...")
            print_info(f"User: {result['user']['name']} ({result['user']['email']})")
            return token
        else:
            print_error(f"Login failed: {result.get('message')}")
            return None
    except Exception as e:
        print_error(f"Login error: {str(e)}")
        return None

def test_create_booking(token, email):
    """Test booking creation"""
    print_header("STEP 3: CREATE BOOKING")
    
    # Calculate dates
    checkin = datetime.now() + timedelta(days=5)
    checkout = checkin + timedelta(days=3)
    
    booking_id = f'BK{int(time.time())}'
    
    payload = {
        'id': booking_id,
        'firstName': 'Test',
        'lastName': 'User',
        'email': email,
        'phone': '0812345678',
        'roomType': 'Deluxe Suite',
        'checkin': checkin.strftime('%Y-%m-%d'),
        'checkout': checkout.strftime('%Y-%m-%d'),
        'guests': 2,
        'nights': 3,
        'rate': 299.99,
        'total': 959.97,
        'userEmail': email
    }
    
    headers = {
        'Content-Type': 'application/json',
        'Authorization': f'Bearer {token}'
    }
    
    try:
        response = requests.post(f'{API_URL}/bookings/create', json=payload, headers=headers)
        result = response.json()
        
        if result.get('success'):
            booking = result.get('booking')
            print_success(f"Booking created successfully")
            print_info(f"Booking ID: {booking['booking_id']}")
            print_info(f"Room: {booking['room_type']}")
            print_info(f"Check-in: {booking['check_in']}")
            print_info(f"Total: ${booking['total']}")
            return booking['booking_id'], payload['total']
        else:
            print_error(f"Booking failed: {result.get('message')}")
            print_error(f"Response: {json.dumps(result, indent=2)}")
            return None, None
    except Exception as e:
        print_error(f"Booking error: {str(e)}")
        return None, None

def test_create_payment(token, booking_id, amount, email):
    """Test payment creation"""
    print_header("STEP 4: CREATE PAYMENT")
    
    payload = {
        'booking_id': booking_id,
        'payment_method': 'credit_card',
        'cardholder_name': 'Test User',
        'card_last_four': '4242',
        'amount': amount,
        'status': 'completed',
        'billing_address': '123 Test Street',
        'city': 'Test City',
        'zip_code': '12345',
        'country': 'Indonesia',
        'user_email': email
    }
    
    headers = {
        'Content-Type': 'application/json',
        'Authorization': f'Bearer {token}'
    }
    
    try:
        response = requests.post(f'{API_URL}/payments/create', json=payload, headers=headers)
        result = response.json()
        
        if result.get('success'):
            payment = result.get('payment')
            print_success(f"Payment created successfully")
            print_info(f"Transaction ID: {payment['transaction_id']}")
            print_info(f"Amount: ${payment['amount']}")
            print_info(f"Status: {payment['status']}")
            return payment['transaction_id']
        else:
            print_error(f"Payment failed: {result.get('message')}")
            print_error(f"Response: {json.dumps(result, indent=2)}")
            return None
    except Exception as e:
        print_error(f"Payment error: {str(e)}")
        return None

def test_verify_booking_in_db(booking_id):
    """Verify booking exists in database"""
    print_header("STEP 5: VERIFY BOOKING IN DATABASE")
    
    try:
        import mysql.connector
        conn = mysql.connector.connect(
            host='127.0.0.1',
            user='root',
            password='',
            database='hotel'
        )
        cursor = conn.cursor(dictionary=True)
        
        cursor.execute('SELECT * FROM bookings WHERE booking_id = %s', (booking_id,))
        booking = cursor.fetchone()
        
        if booking:
            print_success(f"Booking found in database!")
            print_info(f"Guest: {booking['first_name']} {booking['last_name']}")
            print_info(f"Email: {booking['email']}")
            print_info(f"Room: {booking['room_type']}")
            print_info(f"Total: ${booking['total']}")
            return True
        else:
            print_error(f"Booking NOT found in database")
            return False
            
        cursor.close()
        conn.close()
    except Exception as e:
        print_error(f"Database query error: {str(e)}")
        return False

def test_verify_payment_in_db(transaction_id):
    """Verify payment exists in database"""
    print_header("STEP 6: VERIFY PAYMENT IN DATABASE")
    
    try:
        import mysql.connector
        conn = mysql.connector.connect(
            host='127.0.0.1',
            user='root',
            password='',
            database='hotel'
        )
        cursor = conn.cursor(dictionary=True)
        
        cursor.execute('SELECT * FROM payments WHERE transaction_id = %s', (transaction_id,))
        payment = cursor.fetchone()
        
        if payment:
            print_success(f"Payment found in database!")
            print_info(f"Booking ID: {payment['booking_id']}")
            print_info(f"Amount: ${payment['amount']}")
            print_info(f"Status: {payment['status']}")
            print_info(f"Card: ****{payment['card_last_four']}")
            return True
        else:
            print_error(f"Payment NOT found in database")
            return False
            
        cursor.close()
        conn.close()
    except Exception as e:
        print_error(f"Database query error: {str(e)}")
        return False

def main():
    """Run complete booking flow test"""
    print(f"{BLUE}")
    print("╔══════════════════════════════════════════════════════════╗")
    print("║          BOOKING SYSTEM INTEGRATION TEST                 ║")
    print("║        Complete Flow: Register → Login → Book → Pay     ║")
    print("╚══════════════════════════════════════════════════════════╝")
    print(f"{END}")
    
    # Test 1: Register
    email, password = test_user_registration()
    if not email:
        print_error("Registration failed - stopping test")
        return
    
    # Test 2: Login
    token = test_user_login(email, password)
    if not token:
        print_error("Login failed - stopping test")
        return
    
    # Test 3: Create Booking
    booking_id, amount = test_create_booking(token, email)
    if not booking_id:
        print_error("Booking creation failed - stopping test")
        return
    
    # Test 4: Create Payment
    transaction_id = test_create_payment(token, booking_id, amount, email)
    if not transaction_id:
        print_error("Payment creation failed - stopping test")
        return
    
    # Test 5: Verify in DB
    booking_ok = test_verify_booking_in_db(booking_id)
    payment_ok = test_verify_payment_in_db(transaction_id)
    
    # Summary
    print_header("TEST SUMMARY")
    print_success("✓ User Registration")
    print_success("✓ User Login")
    print_success("✓ Booking Creation")
    print_success("✓ Payment Creation")
    if booking_ok:
        print_success("✓ Booking verified in database")
    else:
        print_error("✗ Booking NOT in database")
    if payment_ok:
        print_success("✓ Payment verified in database")
    else:
        print_error("✗ Payment NOT in database")
    
    if booking_ok and payment_ok:
        print(f"\n{GREEN}🎉 ALL TESTS PASSED! System working perfectly!{END}\n")
    else:
        print(f"\n{RED}❌ Some tests failed. Check details above.{END}\n")

if __name__ == '__main__':
    main()
