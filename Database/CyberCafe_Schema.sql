-- Primary "General user" DB. Has no concept of privilege.
CREATE TABLE user_data (
	user_id TEXT NOT NULL,
	name TEXT NOT NULL,
	email TEXT,
	phone TEXT,
	PRIMARY KEY(user_id)
);

-- Will hold the few number of speed "tiers" that are
-- configured.
CREATE TABLE speed_queues (
	sq_id TEXT NOT NULL,
	queue_name TEXT NOT NULL,
	queue_description TEXT,
	upload_speed REAL NOT NULL,
	download_speed REAL NOT NULL,
	PRIMARY KEY (sq_id)
);

-- Payment isn't necessarily money. An entry here would be
-- accompanied by a number of bytes.
CREATE TABLE payment_history (
	ph_id TEXT NOT NULL,
	user_id TEXT NOT NULL,
	pay_datetime TEXT NOT NULL,
	sq_id INTEGER NOT NULL,
	total_bytes INTEGER NOT NULL,
	PRIMARY KEY (ph_id),
	FOREIGN KEY (user_id) REFERENCES user_data(user_id),
	FOREIGN KEY (sq_id) REFERENCES speed_queues(sq_id)
);

-- After each browsing session, the tallied bytes utilized
-- need to be saved (and/or subtracted) from the particular
-- user's account balance.
CREATE TABLE balance_table (
	user_id TEXT NOT NULL,
	sq_id INTEGER NOT NULL,
	last_update TEXT,
	bytes_remaining INTEGER NOT NULL,
	PRIMARY KEY (user_id, sq_id),
	FOREIGN KEY (user_id) REFERENCES user_data(user_id),
	FOREIGN KEY (sq_id) REFERENCES speed_queues(sq_id)
);

-- When a session is created by a user going past the captive
-- portal, it should be logged here. An expired session was
-- imagined to be left here, indicated 'expired' by 'isess_length'
CREATE TABLE internet_sessions (
	isess_id INTEGER NOT NULL,
	user_id TEXT NOT NULL,
	sq_id INTEGER NOT NULL,
	isess_datetime TEXT NOT NULL,
	isess_length INTEGER NULL,
	rx_bytes INTEGER NULL,
	tx_bytes INTEGER NULL,
	PRIMARY KEY (isess_id),
	FOREIGN KEY (user_id) REFERENCES user_data(user_id),
	FOREIGN KEY (sq_id) REFERENCES speed_queue(sq_id)
);

-- This is simply to keep state for the PHP portal pages
CREATE TABLE website_sessions (
	wsess_id TEXT NOT NULL,
	user_id TEXT NOT NULL,
	wsess_datetime TEXT NOT NULL,
	expr_datetime TEXT NOT NULL
);

-- Links "General users" to a particular status. I imagined one
-- entry per user.
CREATE TABLE user_status (
	user_id TEXT NOT NULL,
	user_status TEXT CHECK(user_status IN ('ACTIVE', 'BANNED', 'BULK', 'EXPIRED', 'PAUSED')),
	PRIMARY KEY (user_id),
	FOREIGN KEY (user_id) REFERENCES user_data(user_id)
);

-- When a user's status changes, put the old status here.
CREATE TABLE user_status_history (
	user_id TEXT NOT NULL,
	change_datetime TEXT NOT NULL,
	new_user_status TEXT CHECK(new_user_status IN ('ACTIVE', 'BANNED', 'BULK', 'EXPIRED', 'PAUSED')),
	previous_user_status TEXT CHECK(previous_user_status IN ('ACTIVE', 'BANNED', 'BULK', 'EXPIRED', 'PAUSED')),
	reason TEXT,
	PRIMARY KEY (user_id, change_datetime),
	FOREIGN KEY (user_id) REFERENCES user_data(user_id)
);
