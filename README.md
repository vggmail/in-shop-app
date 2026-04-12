# Fast Food Hub - Smart POS & Online Ordering System

Fast Food Hub is a modern, high-performance web application designed for restaurants, cafes, and fast-food outlets. It combines a powerful **Point of Sale (POS)** admin panel with a customer-facing **Online Ordering** platform.

![Preview](https://images.unsplash.com/photo-1513104890138-7c749659a591?w=1200&h=400&fit=crop)

## 🚀 Key Features

### 🛒 Customer Ordering (Front-end)
- **Live Menu**: Categorized menu with variants (sizes) and extras (toppings).
- **Persistent Cart**: Shopping cart data is saved in LocalStorage, surviving page refreshes.
- **Smart Coupons**: Public coupons shown on home page with "Copy to Apply" functionality.
- **SEO Optimized**: Meta tags and Schema.org structured data for high Google ranking.

### 🛡️ Admin Management (Back-end)
- **Real-time POS**: Instant order placement for walk-in customers.
- **Smart Notifications**: Instant alerts (bell sound + toast) for new online orders using reliable ID tracking.
- **Coupon Management**: Create internal or public coupons with expiry and minimum bill limits.
- **Activity Tracker**: Full audit logs to monitor staff actions and IP addresses.
- **Reports & Analytics**: Dashboard with sales trends and top-performing items.

## 🛠️ Technology Stack
- **Framework**: Laravel 10+
- **Styling**: Bootstrap 5, FontAwesome 6, Custom Vanilla CSS
- **Logic**: PHP 8.1+, jQuery, LocalStorage API
- **Database**: MySQL / MariaDB

## 📦 Installation & Setup

1. **Clone the Repo**
   ```bash
   git clone https://github.com/yourusername/in-shop.git
   cd in-shop
   ```

2. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database Migration**
   ```bash
   php artisan migrate --seed
   ```

5. **Run Locally**
   ```bash
   php artisan serve
   ```

6. **Create Storage Link** (Crucial for Images)
   ```bash
   php artisan storage:link
   ```

## 🌐 Production Requirements & Server Setup

To ensure the best performance and security in a production environment, please verified the following:

### 1. PHP Configuration
- **GD Library**: Required for high-quality thumbnail generation. Ensure `extension=gd` is enabled in your `php.ini`.
- **WebP Support**: Ensure your GD installation supports WebP for optimized image delivery.
- **Upload Limits**: Adjust `post_max_size` and `upload_max_filesize` (recommended 10M+) in `php.ini` for item image uploads.

### 2. File Permissions
Ensure the following directories are writable by the web server (e.g., `www-data`):
- `storage`
- `bootstrap/cache`

### 3. Optimization Commands
Run these commands when deploying updates:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 4. Security Notes
- **Honey Pot**: The system uses a hidden honey pot field to block bots. Do not remove the `honey_pot_field` inputs from forms.
- **Multi-Tenant Security**: Admins are restricted from viewing or modifying Super Admin accounts for infrastructure safety.


## 📄 License
This project is open-source and available under the MIT License.
