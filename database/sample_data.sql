-- Insert users
INSERT INTO `users` (`username`, `email`, `password`, `role`) VALUES
('admin', 'admin@sizzlingstone.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('john_doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- Insert sample reviews
INSERT INTO `reviews` (`user_id`, `rating`, `comment`) VALUES
(1, 5, 'Amazing food and excellent service! The stone-grilled steak was cooked to perfection.'),
(1, 4, 'Great atmosphere and friendly staff. The portions were generous.'),
(1, 5, 'The best restaurant in town! Love their signature dishes.');

-- Insert sample reservations
INSERT INTO `reservations` (`user_id`, `date`, `time`, `guests`, `special_requests`, `status`) VALUES
(1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '18:00:00', 2, 'Window seat preferred', 'confirmed'),
(1, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '19:30:00', 4, 'Birthday celebration', 'pending');

-- Insert sample menu items
INSERT INTO `menu_items` (`name`, `description`, `price`, `category`, `available`, `image_path`) VALUES
-- Appetizers
('Crispy Calamari', 'Tender calamari rings, lightly breaded and served with marinara sauce', 12.99, 'Appetizers', 1, 'calamari.jpg'),
('Bruschetta', 'Toasted bread topped with fresh tomatoes, garlic, basil, and olive oil', 8.99, 'Appetizers', 1, 'bruschetta.jpg'),
('Spinach Artichoke Dip', 'Creamy blend of spinach, artichokes, and melted cheeses served with tortilla chips', 10.99, 'Appetizers', 1, 'spinach_dip.jpg'),

-- Stone Grill Specialties
('Premium Ribeye', 'Prime cut ribeye served on a hot stone with your choice of sides', 34.99, 'Stone Grill Specialties', 1, 'ribeye.jpg'),
('Surf & Turf', 'Filet mignon and jumbo shrimp served on a sizzling stone', 39.99, 'Stone Grill Specialties', 1, 'surf_turf.jpg'),
('Salmon Fillet', 'Fresh Atlantic salmon served on a hot stone with lemon herb butter', 28.99, 'Stone Grill Specialties', 1, 'salmon.jpg'),

-- Main Courses
('Grilled Chicken Breast', 'Herb-marinated chicken breast with roasted vegetables', 22.99, 'Main Courses', 1, 'chicken.jpg'),
('Vegetable Stir Fry', 'Fresh seasonal vegetables stir-fried in Asian sauce', 18.99, 'Main Courses', 1, 'stirfry.jpg'),
('Pasta Primavera', 'Fresh pasta tossed with seasonal vegetables in light cream sauce', 19.99, 'Main Courses', 1, 'pasta.jpg'),

-- Sides
('Garlic Mashed Potatoes', 'Creamy potatoes with roasted garlic', 5.99, 'Sides', 1, 'mashed_potatoes.jpg'),
('Grilled Asparagus', 'Fresh asparagus spears with olive oil and sea salt', 6.99, 'Sides', 1, 'asparagus.jpg'),
('Sweet Potato Fries', 'Crispy sweet potato fries with chipotle aioli', 5.99, 'Sides', 1, 'sweet_fries.jpg'),

-- Desserts
('Chocolate Lava Cake', 'Warm chocolate cake with molten center and vanilla ice cream', 8.99, 'Desserts', 1, 'lava_cake.jpg'),
('New York Cheesecake', 'Classic cheesecake with berry compote', 7.99, 'Desserts', 1, 'cheesecake.jpg'),
('Crème Brûlée', 'Classic French vanilla custard with caramelized sugar', 7.99, 'Desserts', 1, 'creme_brulee.jpg');
