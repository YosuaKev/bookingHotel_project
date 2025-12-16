# 🏨 LUXORA Hotel - Quick Start Guide

**Status**: ✅ READY TO USE  
**Date**: December 12, 2024

---

## ⚡ Quick Setup (5 minutes)

### 1. Database Setup
```bash
# Run all migrations
php artisan migrate --force

# Optional: Seed sample data
php artisan db:seed
```

### 2. Create Admin User (for testing)
```bash
php artisan tinker

# In the tinker shell:
User::factory()->create(['email' => 'admin@hotel.com', 'usertype' => 'admin', 'password' => bcrypt('admin123')]);
exit
```

### 3. Start Development Server
```bash
php artisan serve
```

The application will be available at: `http://localhost:8000`

---

## 🔐 Default Test Accounts

After migration, create these accounts manually or via registration:

### Admin Account
- **Email**: admin@hotel.com
- **Password**: admin123
- **Access**: `http://localhost:8000/admin.html`

### Regular User Account
- **Email**: user@hotel.com
- **Password**: password123
- **Access**: `http://localhost:8000/profile.html` (after login)

---

## 📱 Main Pages

### Public Pages (No Login Required)
- **Home**: `http://localhost:8000/`
- **Browse Rooms**: `http://localhost:8000/room.html`
- **Room Details**: `http://localhost:8000/room-detail.html?id=1`
- **Booking**: `http://localhost:8000/booking.html`
- **Payment**: `http://localhost:8000/payment.html`
- **Sign In**: `http://localhost:8000/signin.html`
- **Sign Up**: `http://localhost:8000/signup.html`

### Protected Pages (Login Required)
- **My Profile**: `http://localhost:8000/profile.html`
  - Edit profile info
  - Change password
  - View bookings
  - Cancel bookings
  - View notifications

### Admin Pages (Admin Login Required)
- **Admin Dashboard**: `http://localhost:8000/admin.html`
  - Dashboard overview
  - Manage rooms
  - View bookings
  - Verify payments
  - Manage users
  - View reports

---

## 🎯 Testing Workflow

### Test 1: User Registration & Login
1. Go to `http://localhost:8000/signup.html`
2. Fill registration form
3. Click "Create Account"
4. Login with credentials
5. Token saved to localStorage
6. Redirected to home

### Test 2: Browse & Book a Room
1. View rooms on home page
2. Click a room → `room-detail.html`
3. Check availability for dates
4. Click "Book This Room"
5. Fill booking form → Submit
6. Booking saved to database
7. See booking in `profile.html` → My Bookings

### Test 3: Payment Processing
1. After booking, go to `payment.html`
2. Order summary loads from database
3. Enter payment details (any test card)
4. Submit payment
5. Payment recorded in database
6. Booking marked as "paid"
7. Payment notification created

### Test 4: Payment Proof Upload (Bank Transfer)
1. On payment form, select "Bank Transfer"
2. See option to upload proof
3. Upload image file
4. Submit
5. Proof file saved to `storage/payment-proofs/`
6. Payment status: "pending_verification"

### Test 5: Admin Payment Verification
1. Login as admin → `admin.html`
2. Go to "Verify Payments" section
3. View pending payment proofs
4. Click "View" to see image
5. Click "Approve" or "Reject"
6. User receives notification of decision
7. If approved, booking marked as "paid"

### Test 6: User Profile Management
1. Login as user → `profile.html`
2. **Profile Settings tab**:
   - Update name/email/phone
   - Click "Update Profile"
   - See success message

3. **Change Password tab**:
   - Enter current password
   - Enter new password twice
   - Click "Change Password"
   - Logout and login with new password

4. **My Bookings tab**:
   - See all user's bookings
   - See booking stats (total, completed, upcoming)
   - Click "Cancel" to cancel active booking
   - Confirm cancellation
   - See refund amount (100% if >7 days, 50% if <7 days)

5. **Notifications tab**:
   - View all notifications
   - See notification history

### Test 7: Room Reviews
1. After booking is completed (check-out date passed)
2. User can review the room
3. Go to `room-detail.html?id=1`
4. Click "Write Review"
5. Rate 1-5 stars, add comment
6. Submit review
7. Review appears with "Verified Booking" badge

### Test 8: Admin Room Management
1. Login as admin → `admin.html`
2. Go to "Manage Rooms" section
3. **View rooms**: See table of all rooms
4. **Add Room**:
   - Click "Add New Room"
   - Fill form (title, type, price, capacity, description, image)
   - Click "Add Room"
   - Redirected to rooms list
   - New room appears in list

5. **Edit Room**: Click "Edit", update fields, save
6. **Delete Room**: Click "Delete", confirm

### Test 9: Admin Analytics
1. Login as admin → `admin.html`
2. Dashboard shows:
   - Total bookings
   - Total revenue (verified payments only)
   - Total users
   - Total rooms
   - Pending payment verifications

3. Go to "Reports" section
   - See revenue statistics
   - Top rooms by bookings
   - Average booking value

---

## 🔧 API Testing (with cURL or Postman)

### Register User
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "firstName": "John",
    "lastName": "Doe",
    "email": "john@example.com",
    "phone": "1234567890",
    "password": "password123"
  }'
```

### Login User
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

### Get Rooms List
```bash
curl -X GET "http://localhost:8000/api/rooms?min_price=100&max_price=500" \
  -H "Accept: application/json"
```

### Get User Profile (Protected)
```bash
curl -X GET http://localhost:8000/api/user/profile \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

### Create Booking
```bash
curl -X POST http://localhost:8000/api/booking \
  -H "Content-Type: application/json" \
  -d '{
    "bookingId": "BK1702381200000",
    "firstName": "John",
    "lastName": "Doe",
    "email": "john@example.com",
    "phone": "1234567890",
    "roomType": "Deluxe",
    "checkin": "2024-12-20",
    "checkout": "2024-12-23",
    "guests": 2,
    "nights": 3,
    "rate": 150.00,
    "total": 450.00
  }'
```

### Create Payment
```bash
curl -X POST http://localhost:8000/api/payments/create \
  -H "Content-Type: application/json" \
  -d '{
    "booking_id": "BK1702381200000",
    "payment_method": "credit_card",
    "cardholder_name": "John Doe",
    "card_last_four": "4242",
    "amount": 450.00,
    "status": "success",
    "billing_address": "123 Main St",
    "city": "New York",
    "zip_code": "10001",
    "country": "USA"
  }'
```

---

## 📊 Database Quick Reference

### Check Bookings
```bash
php artisan tinker
Booking::with('user', 'payments')->get();
exit
```

### Check Payments
```bash
php artisan tinker
Payment::with('booking')->get();
exit
```

### Check Notifications
```bash
php artisan tinker
Notification::with('user')->where('status', 'unread')->get();
exit
```

### Check Reviews
```bash
php artisan tinker
Review::with('user', 'room', 'booking')->get();
exit
```

---

## 🐛 Common Issues & Solutions

### Issue: Token not found in localStorage
**Solution**: Make sure you're on a protected page (profile.html, etc.) and logged in. Check browser console: `localStorage.getItem('token')`

### Issue: Payment form not loading booking data
**Solution**: 
1. Make sure you have an active booking in `my_bookings`
2. Check network tab for API errors
3. Verify token is being sent: `Authorization: Bearer {token}`

### Issue: Admin dashboard shows 403 Forbidden
**Solution**: 
1. Verify user has `usertype = 'admin'` in database
2. Logout and login again
3. Check if AdminMiddleware is properly registered

### Issue: Room image not displaying
**Solution**:
1. Run `php artisan storage:link` to create storage symlink
2. Check image file exists in `storage/app/public/rooms/`
3. Verify file permissions

### Issue: Migrations fail
**Solution**:
```bash
# If columns already exist, migrations will skip them
php artisan migrate --force --step

# Or reset database (WARNING: deletes all data)
php artisan migrate:reset
php artisan migrate
```

---

## 📚 Documentation Files

1. **IMPLEMENTATION_GUIDE.md** - Complete technical documentation
2. **README.md** - Project overview (in root)
3. **API_ENDPOINTS.md** - (Can be created from IMPLEMENTATION_GUIDE.md)

---

## 🚀 What's Working

✅ User registration & authentication  
✅ Room browsing with filters  
✅ Booking creation & database storage  
✅ Payment processing  
✅ Payment proof upload & verification  
✅ User profile management  
✅ Booking cancellation with refunds  
✅ Room reviews (verified booking)  
✅ Admin dashboard & statistics  
✅ Room management (CRUD)  
✅ Bulk price updates  
✅ Booking notifications  
✅ Availability checking  
✅ User notifications system  

---

## 💡 Next Steps (Optional Enhancements)

1. **Email Integration**: Add email notifications alongside DB notifications
2. **Real Payment Gateway**: Integrate Stripe or PayPal
3. **Calendar View**: Interactive availability calendar
4. **Booking Modifications**: Allow users to modify dates
5. **Seasonal Pricing**: Different prices for peak/off-season
6. **Guest Preferences**: Save guest preferences for future bookings
7. **SMS Notifications**: Send booking confirmations via SMS
8. **PDF Reports**: Generate PDF receipts and invoices
9. **Multi-language**: Support multiple languages
10. **Analytics Export**: Export reports to Excel/PDF

---

## 📞 Support

For issues or questions about the implementation:
1. Check IMPLEMENTATION_GUIDE.md for detailed documentation
2. Review API endpoints in the guide
3. Check database schema for data structure
4. Review frontend code for implementation details

---

**Happy Testing! 🎉**

The hotel booking system is fully functional and ready for use.

*Last Updated: December 12, 2024*
