# Quick Test Guide - Database Persistence Fixes

## TL;DR - What Was Fixed
1. **booking.html**: Now saves complete API response with `booking_id` to localStorage
2. **payment.html**: Now saves to localStorage ONLY after API confirms success
3. **payment.html**: Now uses `booking_id` field (database name) first, not `id`
4. **signin.html**: Enhanced error handling

## Quick Test (5 minutes)

### Step 1: Open DevTools
Press `F12` in browser, go to Console tab

### Step 2: Sign In
1. Go to `signin.html`
2. Sign in with: `test@example.com` / `password`
3. Check Console for: ✓ "User logged in successfully"
4. Check localStorage has `authToken` (in Application tab)

### Step 3: Make Booking
1. Go to `booking.html`
2. Fill form and submit
3. Check Console for: ✓ "✓ Booking saved to database"
4. Check Application tab → localStorage → `lastBooking` 
   - Should have `booking_id` field (number like 1, 2, 3, etc.)
   - NOT just `id` field with timestamp

### Step 4: Make Payment
1. Go to `payment.html`
2. Fill card form and submit
3. Check Console for: ✓ "✓ Payment saved to database"
4. Should redirect to `history.html` with success message

### Step 5: Verify in Database
Open terminal and run:
```sql
-- Check if booking exists (find your user_id first)
SELECT * FROM bookings ORDER BY created_at DESC LIMIT 1;

-- Check if payment exists
SELECT * FROM payments ORDER BY created_at DESC LIMIT 1;

-- Verify they match
SELECT b.booking_id, b.total, p.booking_id, p.amount FROM bookings b
LEFT JOIN payments p ON b.booking_id = p.booking_id 
ORDER BY b.created_at DESC LIMIT 1;
```

Should see data in all three queries! ✓

## Common Issues & Quick Fixes

### Issue: "No auth token found" warning in Console
**Solution**: Sign in again, make sure localStorage shows `authToken`

### Issue: localStorage['lastBooking'] doesn't have booking_id
**Solution**: Check Console for error message, verify API call succeeded

### Issue: Payment shows error like "Unknown booking"
**Solution**: Clear localStorage, sign in again, create fresh booking first

### Issue: Data still only in localStorage, not database
**Solution**: 
1. Check Network tab for API response status (should be 200-201)
2. Check Laravel logs: `storage/logs/laravel.log`
3. Verify Authorization header is sent: Network tab → payment request → Headers

## Files to Review
- ✅ `public/booking.html` - Lines 720-745 (localStorage update)
- ✅ `public/payment.html` - Line 898 (booking_id priority)
- ✅ `public/payment.html` - Lines 925-975 (savePaymentToDatabase)
- ✅ `public/signin.html` - Enhanced error handling

## Success Indicators
After complete flow:
- [ ] Console shows no red errors
- [ ] Database has booking record
- [ ] Database has payment record
- [ ] `payments.booking_id` = `bookings.booking_id`
- [ ] history.html shows the booking with status "Confirmed"

**Expected Test Time**: 5 minutes
**Files Changed**: 3 (booking.html, payment.html, signin.html)
**Commits**: 1 recent commit with all fixes
