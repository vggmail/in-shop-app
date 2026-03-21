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

## 📄 License
This project is open-source and available under the MIT License.
