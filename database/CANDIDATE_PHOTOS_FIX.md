# Fix Candidate Profile Photo Display

## The Problem

When you submit candidacy filing with a profile photo, the photo doesn't display on:

- Admin dashboard candidate list
- Admin candidate applications page
- Student ballot

Instead, it shows initials (e.g., "EJ" for "Elbert John").

## Root Cause

The `candidates` table in your database is missing the `image_url` column to store the photo URL.

## Solution

### Step 1: Add the Column to Your Database

You have two options:

#### Option A: Run the Migration Script (Recommended)

1. Go to [Supabase Dashboard](https://supabase.com)
2. Open your project
3. Go to SQL Editor
4. Copy and paste the contents of `database/add-candidate-image-url.sql`
5. Click "Run"

OR copy this SQL directly:

```sql
ALTER TABLE candidates
ADD COLUMN IF NOT EXISTS image_url VARCHAR(500);
```

#### Option B: Update Schema via Supabase UI

1. Open Supabase Dashboard → SQL Editor
2. Run:

```sql
ALTER TABLE candidates ADD COLUMN IF NOT EXISTS image_url VARCHAR(500);
```

### Step 2: Verify the Column Exists

Run this query in Supabase SQL Editor:

```sql
SELECT column_name FROM information_schema.columns
WHERE table_name = 'candidates' AND column_name = 'image_url';
```

If you see `image_url` in the results, you're good to go!

### Step 3: Test

1. Have a candidate submit a new filing with a profile photo
2. Go to Admin → Candidate Applications
3. The photo should now display instead of initials

## What This Does

- Enables the filing handler to store candidate profile photo URLs
- Allows admin to see candidate photos in lists and modals
- Enables students to see candidate photos when voting
- Enables candidate photos in election results

## Files Modified

- `database/schema.sql` - Updated with image_url column
- `database/add-candidate-image-url.sql` - Migration script
- Code already supports it:
  - `candidate/filing_handler.php` - Stores photo URL
  - `admin/candidate.php` - Displays photos
  - `student/ballot.php` - Shows photos on ballot
  - `student/results.php` - Shows photos in results

## Troubleshooting

If photos still don't appear after adding the column:

1. **Check the file exists:**
   - Navigate to `/uploads/candidate_images/` in your server
   - You should see files like `candidate-{id}-{timestamp}.jpg`

2. **Check database storage:**
   - In Supabase, query: `SELECT id, image_url FROM candidates LIMIT 5;`
   - You should see paths like `uploads/candidate_images/candidate-xxx.jpg`

3. **Check file permissions:**
   - Ensure `/uploads/candidate_images/` directory is readable by web server
   - Permissions should be 755 or 775

4. **Check browser cache:**
   - Clear browser cache and refresh page
   - Images might be cached with old data

## Need Help?

If photos still don't show after adding the column, check:

1. The upload directory exists: `/uploads/candidate_images/`
2. Photos were uploaded (check file timestamps)
3. Database has the image_url values (check Supabase)
4. Web server can read the files (check permissions)
