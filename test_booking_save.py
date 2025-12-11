#!/usr/bin/env python3
"""Test booking creation via API"""
import requests
import json
from datetime import datetime, timedelta

BASE_URL = "http://localhost:8000/api"

# Test data
today = datetime.now().date()
tomorrow = today + timedelta(days=1)
day_after = tomorrow + timedelta(days=1)

booking_data = {
    "bookingId": f"BK{int(datetime.now().timestamp() * 1000)}",
    "firstName": "Test",
    "lastName": "User",
    "email": "test@example.com",
    "phone": "+1234567890",
    "roomType": "101",
    "checkin": today.isoformat() + "T15:00:00",
    "checkout": tomorrow.isoformat() + "T11:00:00",
    "guests": 2,
    "nights": 1,
    "rate": 150.00,
    "total": 165.00,
    "specialRequests": "High floor preferred"
}

print("=" * 60)
print("TEST: Booking API - Save to Database (No Email)")
print("=" * 60)

print("\n1. POST /api/booking")
print(f"   Payload: {json.dumps(booking_data, indent=2)}")

try:
    response = requests.post(
        f"{BASE_URL}/booking",
        json=booking_data,
        headers={"Content-Type": "application/json"}
    )
    
    print(f"\n2. Response Status: {response.status_code}")
    print(f"   Response Body: {json.dumps(response.json(), indent=2)}")
    
    if response.status_code == 201 and response.json().get('success'):
        print("\n✓ SUCCESS: Booking saved to database!")
        print(f"  Booking ID (DB): {response.json()['booking'].get('id')}")
        print(f"  Booking ID (Ref): {response.json()['booking'].get('booking_id')}")
        print(f"  No email sent - notification created instead")
    else:
        print("\n✗ FAILED: Check response above")
        
except Exception as e:
    print(f"\n✗ ERROR: {str(e)}")
    print("  Make sure server is running: php artisan serve")

print("\n" + "=" * 60)
