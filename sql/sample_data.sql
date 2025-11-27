-- Sample food entries for Calories DB
INSERT INTO food_database (name, typical_portion_grams, calories_per_100g, protein_per_100g, carb_per_100g, fat_per_100g)
VALUES
('Grilled Chicken Breast', 120, 110, 24, 0, 1),
('Steamed Broccoli', 100, 34, 2.8, 7, 0.4),
('Rice (cooked)', 150, 130, 2.5, 28, 0.3),
('Olive Oil', 15, 884, 0, 0, 100);

-- Three sample users
INSERT INTO users (username, email, password)
VALUES
('james', 'james@demo.local', '$2y$10$y8CYQFn0X1/mVptjTwr/pOHRfOChy31Tzm.QbGYvdrzJu.Z9xUpli'),
('lucy', 'lucy@demo.local', '$2y$10$y8CYQFn0X1/mVptjTwr/pOHRfOChy31Tzm.QbGYvdrzJu.Z9xUpli'),
('mona', 'mona@demo.local', '$2y$10$y8CYQFn0X1/mVptjTwr/pOHRfOChy31Tzm.QbGYvdrzJu.Z9xUpli');

-- Three sample meals (with items and macros as JSON)
INSERT INTO meals (user_id, image_path, detected_items, calories, macros, confidence, portion_grams, suggestion, waste_score, is_leftover, created_at)
VALUES
(1, 'assets/sample1.jpg', '[{"name":"Grilled Chicken Breast","grams":120},{"name":"Steamed Broccoli","grams":100}]',
 256, '{"protein":26,"carb":8,"fat":2}', 0.94, 220, "Tonight: add 150g Brown rice for energy balance.", 78, 0, NOW()),
(2, 'assets/sample2.jpg', '[{"name":"Rice (cooked)","grams":150},{"name":"Olive Oil","grams":14}]',
 140, '{"protein":3,"carb":29,"fat":14}', 0.89, 170, "Try a handful of beans for extra fiber.", 85, 0, NOW()),
(3, 'assets/sample3.jpg', '[{"name":"Rice (cooked)","grams":100},{"name":"Steamed Broccoli","grams":90}]',
 98, '{"protein":3,"carb":20,"fat":0.8}', 0.88, 190, "Add 50g tofu for balanced protein.", 94, 1, NOW());
