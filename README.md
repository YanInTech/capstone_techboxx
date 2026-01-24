# TechBoxx

TechBoxx is a web application built with Laravel for PC building and e-commerce functionality. It includes features for managing hardware components, user builds, shopping carts, orders, and more. The project also incorporates Python scripts for analysis and testing.

## Features

- User authentication and verification
- Hardware component management (CPUs, coolers, cases, etc.)
- PC build creation and compatibility checking
- Shopping cart and checkout system
- Order management and invoicing
- Payment integration (PayPal)
- Reviews and ratings
- Activity logging
- Admin panel for managing brands, suppliers, and stock
- Python-based analysis tools for motherboard analysis and frequent pairs

## Technologies Used

- **Backend**: Laravel (PHP)
- **Frontend**: Vite, Tailwind CSS, PostCSS
- **Database**: MySQL (configured in config/database.php)
- **Testing**: Pest
- **Python Scripts**: For data analysis
- **Other**: Composer, NPM, Artisan

## Installation

### Prerequisites

- PHP 8.1 or higher
- Composer
- Node.js and NPM
- MySQL or another supported database
- Python 3.x (for Python scripts)

### Steps

1. **Clone the repository:**
   ```bash
   git clone <repository-url>
   cd techboxx
   ```

2. **Install PHP dependencies:**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies:**
   ```bash
   npm install
   ```

4. **Set up environment:**
   - Copy `.env.example` to `.env`
   - Configure your database, mail, and other settings in `.env`

5. **Generate application key:**
   ```bash
   php artisan key:generate
   ```

6. **Run migrations:**
   ```bash
   php artisan migrate
   ```

7. **Seed the database (optional):**
   ```bash
   php artisan db:seed
   ```

8. **Build assets:**
   ```bash
   npm run build
   ```

9. **Start the development server:**
   ```bash
   php artisan serve
   ```

10. **For frontend development:**
    ```bash
    npm run dev
    ```

### Python Scripts

The `python_scripts/` directory contains analysis tools. Ensure Python is installed and run them as needed:

- `mba_analysis.py`: Motherboard analysis
- `frequent_pairs.py`: Frequent pairs analysis
- `test_python.py`: Testing script

## Usage

- Access the application at `http://localhost:8000`
- Register/login to create accounts
- Browse and add hardware components to builds
- Manage shopping cart and proceed to checkout
- View orders and invoices
- Admin users can manage inventory and suppliers

## Testing

Run tests using Pest:

```bash
./vendor/bin/pest
```

## License

This project is licensed under the MIT License.
