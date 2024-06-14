-- Remove everything in tables
DELETE FROM admin;
DELETE FROM session_details;
DELETE FROM session_types;
DELETE FROM website_blocking_groups;
DELETE FROM website_blocking_groups_url;

-- Fill tables with test data.
INSERT INTO website_blocking_groups (group_id, group_name) VALUES
('G1', 'Basic Filtering'),
('G2', 'Advanced Security'),
('G3', 'Restricted Access');

INSERT INTO session_types (session_code, group_id, bytes_limit) VALUES
('S001', 'G1', 500000),
('S002', 'G2', 1000000),
('S003', 'G3', 200000);

INSERT INTO session_details (session_start, session_end, group_id, mac_address, bytes_remaining) VALUES
('2024-04-01T08:00:00', NULL, 'G1', '00:1A:2B:3C:4D:5E', 490000),
('2024-04-01T09:00:00', '2024-04-01T11:00:00', 'G2', '00:1A:2B:3C:4D:5F', 950000),
('2024-04-02T10:00:00', NULL, 'G3', '00:1A:2B:3C:4D:60', 1950000);

INSERT INTO website_blocking_groups_url (website_url, group_id) VALUES
('http://example.com', 'G1'),
('http://security.com', 'G2'),
('http://restrictedaccess.com', 'G3');

INSERT INTO admin (username, password) VALUES
('admin', 'password123');