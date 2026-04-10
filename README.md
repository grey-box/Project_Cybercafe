# Project Cybercafe

Source code related to trying to achieve the Project CyberCafe.

## System Overview
Project Cybercafe is an infrastructure project aimed at providing controlled internet access via a captive portal and a hotspot interface. The system acts as a router that intercepts unauthenticated web traffic, redirects users to a captive portal for login, and uses `iptables` and data tracking mechanisms to manage internet sessions based on data balances and speed queues. 

The architecture consists of three main components:
1. **Backend**: A set of Bash scripts managing the core daemon, `iptables` rules (NAT, Mangle, Filter), and `lighttpd` web server configuration.
2. **PHP Portal**: The captive portal frontend built with PHP that authenticates users, checks data balances, and inserts internet session approvals.
3. **Database**: SQLite databases storing user information, data lane balances, and active internet sessions.

## Setup Instructions

### Prerequisites
- A Linux-based environment (e.g., Raspberry Pi, standard Linux server, or Android with Termux as seen in the codebase).
- Required packages:
  - `bash` (for running the scripts)
  - `sqlite3` (for database operations)
  - `lighttpd` (for the captive portal web server)
  - `iptables` (for routing and traffic shaping)
  - `tc` / `iproute2` (for network interface and traffic control)
  - `php` with standard extensions (for the portal)

### Installation
1. Clone the repository to your target machine.
2. Make sure all scripts in `./Backend` are executable (`chmod +x ./Backend/*.sh`).
3. Configure the environment variables in `./Backend/cybercafe.conf` appropriately for your system. Pay special attention to:
    - `HS_INTERFACE` (The network interface your hotspot runs on, e.g., `wlan0`)
    - `DATABASE_PATH` (Absolute path to your SQLite DB)
    - `LIGHTTPD_PATH` and `LIGHTTPD_CONF`
4. The database (`CyberCafe.db` or configured otherwise) should be initialized with valid `users` and `balance_table` entries. The `website_sessions` and `internet_sessions` tables will be dynamically updated by the PHP portal.

## Usage Guide

The primary interface for managing the backend is the `cybercafe.sh` script located in the `Backend` directory.

### Starting the System
To start the daemon and setup the captive portal:
```bash
cd ./Backend
./cybercafe.sh run
```
This starts the Cybercafe daemon, which periodically checks the hotspot status, applies default `iptables` drop rules, redirects unauthenticated users to the captive server via DNAT on port 80, and starts the `lighttpd` web server.

### Checking Status
To view if the daemon is running and check the interface IP:
```bash
cd ./Backend
./cybercafe.sh status
```

### Viewing System Information
You can list active internet sessions, user data, data lanes, and current iptables rules:
```bash
./cybercafe.sh list sessions
./cybercafe.sh list users
./cybercafe.sh list lanes
./cybercafe.sh list rules
```

### Shutting Down
To safely shut down the daemon and clean up the `iptables` routing rules:
```bash
./cybercafe.sh shutdown
```
In an emergency, use `./cybercafe.sh kill` to force stop all scripts and flush the infrastructure (this abandons active sessions and cleans up all related rules).

### Troubleshooting and Errors
Errors caught by the daemon are logged to `./Backend/error.log`. You can view the latest entries easily with:
```bash
./cybercafe.sh errorlog
```

### Interactive Mode
If you run `./cybercafe.sh` without arguments, it opens an interactive prompt where you can run the commands (`run`, `status`, `list <info>`, `shutdown`, `help`) repeatedly without prefixing commands.
