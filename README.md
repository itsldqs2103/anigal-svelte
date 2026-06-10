## Installation
### 1. Clone the repository
```bash
git clone https://github.com/itsldqs2103/anigal-svelte.git
cd anigal-svelte
```

### 2. Install PHP dependencies

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

### 5. Start development server
```bash
php artisan serve
npm run dev
```
