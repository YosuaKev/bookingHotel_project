#!/usr/bin/env python3
"""
Test notification system integration
"""
import requests
import json

BASE_URL = "http://localhost:8000/api"

def test_booking_with_notification():
    """Test creating booking which triggers notification"""
    
    print("\n=== Testing Notification System ===\n")
    
    # 1. Register a test user
    print("1. Registering test user...")
    user_data = {
        "name": "Notification Test User",
        "email": "notif_test@example.com",
        "password": "password123",
        "password_confirmation": "password123"
    }
    
    reg_response = requests.post(f"{BASE_URL}/users/register", json=user_data)
    print(f"   Status: {reg_response.status_code}")
    print(f"   Response: {reg_response.json()}")
    
    # 2. Login to get token
    print("\n2. Logging in...")
    login_data = {
        "email": "notif_test@example.com",
        "password": "password123"
    }
    
    login_response = requests.post(f"{BASE_URL}/users/login", json=login_data)
    print(f"   Status: {login_response.status_code}")
    login_json = login_response.json()
    print(f"   Response: {login_json}")
    
    if not login_json.get('success'):
        print("   ERROR: Login failed!")
        return False
    
    token = login_json.get('token')
    print(f"   Token: {token[:20]}...")
    
    # 3. Create booking (should trigger notification)
    print("\n3. Creating booking (should create notification)...")
    booking_data = {
        "id": "BK" + str(int(1702156800000)),
        "firstName": "Test",
        "lastName": "User",
        "email": "notif_test@example.com",
        "phone": "+1234567890",
        "roomType": "Deluxe Suite",
        "checkin": "2025-12-20",
        "checkout": "2025-12-22",
        "guests": 2,
        "nights": 2,
        "rate": 250,
        "total": 500,
        "userEmail": "notif_test@example.com",
        "userId": None
    }
    
    headers = {
        "Authorization": f"Bearer {token}",
        "Content-Type": "application/json"
    }
    
    booking_response = requests.post(f"{BASE_URL}/bookings/create", json=booking_data)
    print(f"   Status: {booking_response.status_code}")
    booking_json = booking_response.json()
    print(f"   Response: {booking_json}")
    
    # 4. Check notifications
    print("\n4. Fetching notifications...")
    notif_response = requests.get(
        f"{BASE_URL}/notifications",
        headers=headers
    )
    print(f"   Status: {notif_response.status_code}")
    notif_json = notif_response.json()
    print(f"   Response: {json.dumps(notif_json, indent=2)}")
    
    if notif_json.get('success'):
        print(f"\n   ✓ Found {len(notif_json.get('notifications', []))} notifications")
        print(f"   ✓ Unread count: {notif_json.get('unread_count')}")
        
        # 5. Mark first notification as read
        if notif_json.get('notifications'):
            first_notif = notif_json['notifications'][0]
            print(f"\n5. Marking notification as read...")
            read_response = requests.post(
                f"{BASE_URL}/notifications/{first_notif['id']}/read",
                headers=headers
            )
            print(f"   Status: {read_response.status_code}")
            print(f"   Response: {read_response.json()}")
    
    return True

if __name__ == "__main__":
    test_booking_with_notification()
