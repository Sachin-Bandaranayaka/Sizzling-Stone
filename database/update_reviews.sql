USE sizzling_stone;

-- Add is_reported column to reviews table
ALTER TABLE reviews
ADD COLUMN is_reported BOOLEAN DEFAULT FALSE;
