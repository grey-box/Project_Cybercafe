-- Mapping of session code to download limit and website blocking group.
-- Used to initialise a session 
-- Session code is entered by the user in the captive portal.
CREATE TABLE session_types(
	session_code TEXT NOT NULL,
	group_id TEXT NOT NULL,
	bytes_limit INTEGER NOT NULL,
	PRIMARY KEY (session_code),
	FOREIGN KEY (group_id) REFERENCES website_blocking_groups(group_id)
);

-- Mapping session_id to a mac address (device) and a blocking group.
CREATE TABLE session_details(
	session_id INTEGER PRIMARY_KEY AUTOINCREMENT,
	session_start TEXT NOT NULL,
	session_end TEXT,
	group_id TEXT NOT NULL,
	mac_address TEXT NOT NULL,
	bytes_remaining INTEGER DEFAULT 0,
	PRIMARY KEY (session_id, mac_address),
	FOREIGN KEY (group_id) REFERENCES website_blocking_groups(group_id)
);

-- Website blocking groups
CREATE TABLE website_blocking_groups (
	group_id TEXT NOT NULL,
	group_name TEXT,
	PRIMARY KEY (group_id)
);

-- Maps website blocking groups to URL
CREATE TABLE website_blocking_groups_url(
   website_url TEXT NOT NULL,
   group_id TEXT NOT NULL,
   FOREIGN KEY (group_id) REFERENCES website_blocking_groups(group_id),
   PRIMARY KEY ( website_url, group_id)
);

-- Admin users
CREATE TABLE admin(
	username TEXT NOT NULL,
	password TEXT NOT NULL
)