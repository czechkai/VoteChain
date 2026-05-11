# Document Download Implementation Summary

## What Was Fixed

You can now view/download documents when you click "VIEW FILE" in the Candidate Applications admin page.

## How It Works

### 1. **Document Storage** (Existing - Already Working)

- Files uploaded through candidate filing form
- Stored at: `uploads/candidate_documents/{candidateId}/{filename}`
- Paths stored in database (candidate_documents or candidacy_filings table)

### 2. **Download Endpoints Created**

- **Admin**: `/admin/download.php?file=uploads/candidate_documents/{candidateId}/{filename}`
- **Candidate**: `/candidate/download.php?file=uploads/candidate_documents/{candidateId}/{filename}`

### 3. **Security Features**

- ✅ Path traversal protection (prevents ../ attacks)
- ✅ Admin can access any candidate documents
- ✅ Candidates can only access their own documents
- ✅ File ownership verified against database records
- ✅ Only files in candidate_documents directory are served

### 4. **File Display Logic**

- **PDFs & Images**: Opens inline in new tab (\_blank target)
- **Other files**: Downloads as attachment
- Supported types: PDF, JPG, PNG, GIF, WebP (inline)
- All other types: Attachment downloads

## Testing the Feature

### To Test:

1. Open the Admin > Candidate Applications page
2. Click on any candidate with documents
3. In the review modal, you should see document names with "VIEW FILE" links
4. Click "VIEW FILE" - document should open/download
5. If it's an image or PDF, it opens in a new tab
6. If it's another format, it downloads

### Files Changed:

- [admin/candidate.php](admin/candidate.php#L477-L481) - Updated modal to use download endpoint
- [admin/download.php](admin/download.php) - New download handler for admin
- [candidate/download.php](candidate/download.php) - New download handler for candidates

## Technical Details

### Path Resolution

Files are accessed through secure handlers that:

1. Validate the requested file path
2. Prevent directory traversal attacks
3. Verify file ownership (for candidates)
4. Set proper MIME types for display vs download
5. Add cache headers for performance

### Database Lookup

Downloads verify ownership by checking:

- Admin: Any document access allowed
- Candidate: Documents must belong to their candidate record
  - Uses `candidates.profile_id` or `candidates.user_id`
  - Extracts candidate ID from file path
  - Verifies candidate record belongs to current user

## Example File Path Flow

**When candidate uploads:**

```
File saved to: uploads/candidate_documents/cd861dc2-9945-44ae-adc2-f18627255d25/cert_candidacy-6a00d2e1e3e696.88792323.png
DB stores: uploads/candidate_documents/cd861dc2-9945-44ae-adc2-f18627255d25/cert_candidacy-6a00d2e1e3e696.88792323.png
```

**When admin clicks VIEW FILE:**

```
Modal link becomes: download.php?file=uploads/candidate_documents/cd861dc2-9945-44ae-adc2-f18627255d25/cert_candidacy-6a00d2e1e3e696.88792323.png
Download handler:
  1. Decodes URL parameter
  2. Validates path is in allowed directory
  3. Checks file exists
  4. Sets proper MIME type
  5. Serves file with appropriate headers
```

## Troubleshooting

If "VIEW FILE" doesn't work:

1. Check browser console for errors
2. Verify file exists in uploads directory
3. Check Apache/XAMPP error logs
4. Ensure candidate documents directory has read permissions
5. Check that database has correct file paths
