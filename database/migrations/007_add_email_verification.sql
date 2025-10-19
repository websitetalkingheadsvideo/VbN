-- Migration: Add email verification columns to users table
-- Date: 2025-10-12
-- Description: Adds email verification support for user registration

ALTER TABLE users 
ADD COLUMN email_verified BOOLEAN DEFAULT FALSE,
ADD COLUMN verification_token VARCHAR(64) NULL,
ADD COLUMN verification_expires TIMESTAMP NULL;

-- Add index for faster token lookups
CREATE INDEX idx_verification_token ON users(verification_token);

-- Update existing users to be verified (grandfathered in)
UPDATE users SET email_verified = TRUE WHERE email_verified = FALSE;

