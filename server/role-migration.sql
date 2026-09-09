ALTER TABLE users
  ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'user' AFTER password;

-- Run this separately for the account that should manage the system:
-- UPDATE users SET role = 'admin' WHERE username = 'your-admin-username';
