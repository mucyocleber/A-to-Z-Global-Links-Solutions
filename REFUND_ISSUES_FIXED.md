# Refund Request Submission - Issues Found & Fixed

## Issues Identified

### 1. **Column Name Mismatch** ❌
**Problem:** The PHP code was trying to insert into `account_holder_name` but the actual database column is `account_name`

**Location:** Line 88 in submit-refund.php
```php
// WRONG:
account_holder_name

// CORRECT:
account_name
```

### 2. **Data Type Inconsistency** ❌
**Problem:** The CREATE TABLE statement in PHP used `int UNSIGNED` for `application_id` and `user_id`, but the actual database schema uses `int` (without UNSIGNED)

**Impact:** This could cause foreign key constraint issues

### 3. **Missing Application Status Update** ❌
**Problem:** When a refund request is submitted, the `applications.refund_status` field should be updated to 'requested', but this wasn't happening

**Impact:** The application table wouldn't reflect that a refund was requested

### 4. **No Transaction Support** ❌
**Problem:** The code didn't use database transactions, so if the insert succeeded but other operations failed, data could be inconsistent

### 5. **Incorrect Timestamp Column** ❌
**Problem:** PHP code used `created_at` but the actual schema uses `requested_at`

## Fixes Applied

### ✅ Fix 1: Corrected Column Name
Changed `account_holder_name` to `account_name` in the INSERT statement

### ✅ Fix 2: Updated Data Types
Removed `UNSIGNED` from `application_id` and `user_id` in CREATE TABLE statement to match actual schema

### ✅ Fix 3: Added Application Status Update
Added code to update `applications.refund_status = 'requested'` after inserting refund request

### ✅ Fix 4: Implemented Transaction Support
Wrapped the operations in a database transaction:
- Begin transaction
- Insert refund request
- Update application status
- Commit transaction
- Rollback on any error

### ✅ Fix 5: Fixed Timestamp Column
Changed `created_at` to `requested_at` to match the database schema

### ✅ Fix 6: Simplified Table Schema
Removed unnecessary columns from CREATE TABLE:
- Removed `processed_by`
- Removed `updated_at`
- Made bank fields nullable (DEFAULT NULL) to match schema

## Testing Recommendations

1. **Test successful submission:**
   - Submit a refund request with all required fields
   - Verify record is inserted into `refund_requests` table
   - Verify `applications.refund_status` is updated to 'requested'

2. **Test validation:**
   - Try submitting without required fields
   - Verify appropriate error messages

3. **Test duplicate prevention:**
   - Try submitting multiple refund requests for same application
   - Should be rejected with "Refund request already exists" message

4. **Test amount validation:**
   - Try requesting refund amount greater than total_amount
   - Should be rejected

5. **Test transaction rollback:**
   - Simulate a failure scenario to ensure rollback works properly

## Database Schema Reference

```sql
CREATE TABLE `refund_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `application_id` int NOT NULL,
  `user_id` int NOT NULL,
  `refund_amount` decimal(10,2) NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `bank_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','approved','rejected','processed') DEFAULT 'pending',
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `requested_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `processed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Summary

All critical issues have been fixed. The refund submission should now work correctly with:
- Proper column names matching the database
- Transaction support for data consistency
- Application status updates
- Correct data types
- Proper error handling
