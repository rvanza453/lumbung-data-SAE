-- Update password untuk user corrector
USE lubung_data_sae;

UPDATE users 
SET password = '$2y$10$18ofXy4wWYpztzJ6Vyaos.ctC1N9p59iGkfz05tY2Te7.i4rxQdxS'
WHERE username = 'corrector';

SELECT username, LEFT(password, 20) as password_hash, full_name, role 
FROM users 
WHERE username = 'corrector';