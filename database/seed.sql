USE recruitment_system;

DELETE FROM role_permissions;
DELETE FROM user_roles;
DELETE FROM permissions;
DELETE FROM users;
DELETE FROM roles;

INSERT INTO roles (id, name, description) VALUES
(1, 'admin', 'Super user with all access'),
(2, 'recruiter', 'Full recruitment workflow access'),
(3, 'manager', 'Can review assigned candidates and add remarks');

INSERT INTO permissions (resource, action_name, description) VALUES
('dashboard', 'view', 'View dashboard'),
('users', 'manage', 'Manage users'),
('candidates', 'create', 'Create candidate'),
('candidates', 'view', 'View candidates'),
('candidates', 'edit', 'Edit candidate'),
('candidates', 'assign_manager', 'Assign manager'),
('candidates', 'reject', 'Reject candidate with reason'),
('candidates', 'select', 'Select candidate'),
('feedback', 'add', 'Add feedback'),
('timeline', 'view', 'View timeline');

INSERT INTO role_permissions (role_id, permission_id)
SELECT 1, id FROM permissions;
INSERT INTO role_permissions (role_id, permission_id)
SELECT 2, id FROM permissions WHERE resource IN ('dashboard','candidates','feedback','timeline');
INSERT INTO role_permissions (role_id, permission_id)
SELECT 3, id FROM permissions WHERE resource IN ('dashboard','feedback','timeline') OR (resource='candidates' AND action_name='view');

INSERT INTO users (id, full_name, email, phone, password_hash, status, password_changed_at) VALUES
(1, 'admin', 'admin@example.com', '9999999999', '$2y$10$1Wg9f0iN6J4iPj0a4oP6cOWm8iODlYw0nTHrD0M2P6hK2cYjB8W0G', 'active'),
(2, 'abhay', 'abhay@example.com', '8888888888', '$2y$10$Q7fYh1qCjKmD0wqQnIbKXeDqv2eI9RH7mGk3OP2iED8q1JcyiHFui', 'active', NOW());

INSERT INTO user_roles (user_id, role_id) VALUES
(1,1),
(2,3);
