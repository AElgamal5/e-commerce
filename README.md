# e-commerce

Project Description: Simple e-commerce using laravel

## Installation

1. Clone the repository:

   ```bash
   git clone https://github.com/AElgamal5/e-commerce.git
   ```
2. Install dependencies:
    ```bash
     cd e-commerce
     composer install
   ```
3. Configure the environment:
   ```bash
    cp .env.example .env
   ```
4. Run the database migrations with seeders:
   ```bash
    php artisan migrate --seed
   ```
5. Generate the application key:
    ```bash
    php artisan key:generate
   ```
6. Running the Application:
   ```bash
    php artisan serve
   ```
    Open your browser and navigate to http://localhost:8000 to view the application.
