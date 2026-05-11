-- Migration: Add image_url column to candidates table
-- This adds support for candidate profile photos

ALTER TABLE candidates
ADD COLUMN IF NOT EXISTS image_url VARCHAR(500);

-- Comment explaining the column
COMMENT ON COLUMN candidates.image_url IS 'URL to candidate profile photo for ballot and admin display';
