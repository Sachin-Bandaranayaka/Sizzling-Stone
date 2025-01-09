-- Insert sample reviews
INSERT INTO reviews (user_id, rating, review_text, created_at) VALUES 
(1, 5, 'Amazing food and great service! The ambiance was perfect for our anniversary dinner.', NOW()),
(2, 4, 'Really enjoyed the pasta dishes. The garlic bread was exceptional!', NOW()),
(3, 5, 'Best Italian restaurant in town! The pizza was authentic and delicious.', NOW()),
(1, 4, 'Great selection of wines. The staff was very knowledgeable and helpful.', NOW()),
(2, 5, 'The desserts are to die for! Especially loved the Tiramisu.', NOW());

-- Make sure to update the timestamps to be slightly different
UPDATE reviews SET created_at = DATE_SUB(created_at, INTERVAL 1 DAY) WHERE review_id = 2;
UPDATE reviews SET created_at = DATE_SUB(created_at, INTERVAL 2 DAY) WHERE review_id = 3;
UPDATE reviews SET created_at = DATE_SUB(created_at, INTERVAL 3 DAY) WHERE review_id = 4;
UPDATE reviews SET created_at = DATE_SUB(created_at, INTERVAL 4 DAY) WHERE review_id = 5;
