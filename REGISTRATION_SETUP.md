# Registration System Setup - COMPLETE ✓

## What's Been Implemented

### 1. **Updated Configuration** (`includes/config.php`)
- ✓ Loads environment variables from `.env`
- ✓ Connects to Supabase PostgreSQL via PDO
- ✓ Helper functions for profiles table:
  - `createProfile()` - Register new student
  - `getProfileByEmail()` - Find student by email
  - `getProfileByStudentId()` - Find student by ID
  - `authenticateProfile()` - Login verification
  - `recordVote()`, `getCandidates()`, `getElectionResults()` - Voting features

### 2. **Registration Handler** (`auth/register_handler.php`)
- ✓ Validates all required fields
- ✓ Checks for duplicate email and student ID
- ✓ Validates email format
- ✓ Validates password strength (min 8 chars)
- ✓ Checks password match
- ✓ Hashes password with bcrypt
- ✓ Stores profile in Supabase `profiles` table
- ✓ Auto-logs in user after registration
- ✓ Returns JSON response for AJAX

### 3. **Updated Registration Form** (`auth/register.php`)
- ✓ 3-step registration wizard
  - Step 1: Personal info (first name, last name, email)
  - Step 2: Academic info (student ID, faculty, program)
  - Step 3: Account security (password, confirm password)
- ✓ Client-side validation
- ✓ Password strength indicators
- ✓ AJAX form submission
- ✓ Success/error alerts
- ✓ Auto-redirect to dashboard on success

### 4. **Test Page** (`test-registration.php`)
- ✓ Verify database connection
- ✓ Check tables and helper functions
- ✓ Optional test registration

---

## How to Test Registration

### Step 1: Verify Setup
Open: `http://localhost/votechain/test-registration.php`
- Should show all green checkmarks ✓
- If database test fails, check `.env` credentials

### Step 2: Register Student
1. Go to: `http://localhost/votechain/auth/register.php`
2. Fill in student details:
   - Name: John Doe
   - Email: john.doe@dorsu.edu.ph
   - Student ID: 2026-0001 (calculates year level automatically)
   - Faculty: FACET
   - Program: BS Information Technology
   - Password: SecurePassword123!
3. Confirm password
4. Check Terms & Conditions
5. Click "Continue"
6. Should redirect to dashboard

### Step 3: Verify in Supabase
1. Go to Supabase Dashboard
2. Click "Table Editor"
3. Select "profiles" table
4. Should see the new student record with:
   - email: john.doe@dorsu.edu.ph
   - student_id: 2026-0001
   - year_level: 2
   - role: student
   - password_hash: (bcrypt hash, not plain text)

---

## Database Table Structure (profiles)

```
id (UUID)                 - Auto-generated unique ID
first_name (TEXT)         - Student first name
last_name (TEXT)          - Student last name
email (TEXT)              - University email (unique)
student_id (TEXT)         - Student ID (unique)
year_level (TEXT)         - Academic year level
faculty_code (TEXT)       - Faculty code (FACET, FCJE, etc.)
program_code (TEXT)       - Program code (BSIT, BSCS, etc.)
role (TEXT)               - User role (student, candidate, admin)
password_hash (TEXT)      - Bcrypt password hash
is_verified (BOOLEAN)     - Email verification status
created_at (TIMESTAMPTZ)  - Registration timestamp
updated_at (TIMESTAMPTZ)  - Last update timestamp
```

---

## Security Features

✓ **Password Hashing**: Uses `password_hash()` with bcrypt  
✓ **Input Validation**: Email format, password strength  
✓ **SQL Injection Prevention**: Prepared statements with placeholders  
✓ **Duplicate Prevention**: Checks email and student ID uniqueness  
✓ **Session Management**: Automatic login after registration  
✓ **Error Logging**: Errors logged to `logs/errors.log`  

---

## API Endpoints

### Registration
- **File**: `auth/register_handler.php`
- **Method**: POST
- **Parameters**:
  - fname, lname, email, sid, year_level, faculty, program, password, confirm_password
- **Returns**: JSON
  ```json
  {
    "success": true,
    "message": "Registration successful!",
    "redirect": "../student/dashboard.php"
  }
  ```

---

## Next Features to Implement

1. **Login System** - `auth/login.php` handler
2. **Dashboard** - Student dashboard after login
3. **Voting System** - Ballot and vote recording
4. **Results** - Election results display
5. **Blockchain** - Hash-based vote ledger

---

## Files Modified

- `includes/config.php` - Complete rewrite with profile functions
- `auth/register.php` - AJAX form submission added
- `auth/register_handler.php` - Created
- `test-registration.php` - Created
- `logs/` - Directory created for error logging

---

## Troubleshooting

### Registration says "Email already registered"
- Email already exists in `profiles` table
- Use a different email

### "Student ID already registered"
- Student ID already exists
- Use a different student ID

### "Database connection error"
- Check `.env` file credentials
- Verify Supabase is running
- Test with: `npm run test:supabase`

### Password validation errors
- Password must be at least 8 characters
- Must include number or special character for complexity
- Passwords must match

---

**Status**: ✓ Registration system is READY TO USE

Test it now at: `http://localhost/votechain/auth/register.php`
