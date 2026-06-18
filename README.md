## Installation
### 1. Clone the repository
```bash
git clone https://github.com/itsldqs2103/anigal-svelte.git
cd anigal-svelte
```

### 2. Install PHP dependencies
#### The development environment must use PHP 8.3 or later
```bash
composer install
```

### 3. Install Node dependencies
```bash
npm install
```

### 4. Setup environment file
#### Windows
```bash
copy .env.example .env
php artisan key:generate
```
#### Linux / macOS
```bash
cp .env.example .env
php artisan key:generate
```
#### Important configuration
Make sure to update your .env file to avoid issues
```bash
SESSION_DRIVER=file
CACHE_STORE=file
```

### 5. Generate Wayfinder file
```bash
php artisan wayfinder:generate --path=resources/js/wayfinder --skip-routes
```

### 6. Run migrations
```bash
php artisan migrate
```

### 7. Start development server
```bash
php artisan serve
npm run dev
```
