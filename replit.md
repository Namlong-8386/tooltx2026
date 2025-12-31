# TOOLTX2026

## Overview
A Vietnamese web application for a prediction tools platform. Users can register, login, deposit money, and purchase activation keys for various prediction tools (Tài Xỉu, Sicbo, Baccarat, etc.).

## Project Structure
```
├── index.php           # Landing page
├── login.php           # User login
├── register.php        # User registration
├── logout.php          # User logout
├── includes/
│   ├── functions.php   # Core utility functions
│   └── icons.php       # SVG icon helper functions
├── user/
│   ├── dashboard.php   # User dashboard
│   ├── deposit.php     # Deposit money
│   ├── buy-key.php     # Purchase activation keys
│   └── history.php     # Transaction history
├── admin/
│   └── banks.php       # Admin bank management
├── data/
│   ├── users.json      # User data storage
│   ├── banks.json      # Bank account configurations
│   ├── deposits.json   # Deposit transaction records
│   └── keys.json       # Purchased keys records
└── assets/
    └── images/
        └── logo-vip.png  # Site logo
```

## Tech Stack
- PHP 8.2 (built-in server)
- JSON file-based storage (no database required)
- Tailwind CSS (via CDN)
- No external dependencies

## Running the Application
The application runs using PHP's built-in development server:
```bash
php -S 0.0.0.0:5000
```

## Features
- User registration and authentication with password hashing
- User dashboard with balance display
- Deposit system with VietQR integration
- Key purchase system with quantity-based discounts
- Transaction history tracking
- Admin panel for bank management

## Data Storage
All data is stored in JSON files under the `data/` directory:
- `users.json` - User accounts with hashed passwords
- `banks.json` - Configured bank accounts for deposits
- `deposits.json` - Deposit transaction records
- `keys.json` - Purchased activation keys

## Recent Changes
- 2025-12-29: Fixed user deposit page (3 critical issues) ✅
  - ✅ Fixed "timer stuck at 19:59":
    - Disabled debugger in security.js that was interfering with JavaScript execution
    - Added DOMContentLoaded check to ensure DOM is ready before running timer
    - Timer now counts down from 20:00 to 00:00 every second
  - ✅ Fixed "balance showing 0 VND" - Added $currentUser loading at top of deposit.php
  - ✅ Fixed "status not updating when admin approves/rejects":
    - Improved AJAX polling every 3 seconds with proper error handling
    - API returns correct JSON response with status + new_balance
    - JavaScript updates UI real-time when status changes
  
- 2025-12-28: Initial setup for Replit environment
  - Reorganized project structure (includes/, user/, admin/, data/, assets/)
  - Created placeholder logo
  - Configured PHP built-in server on port 5000
