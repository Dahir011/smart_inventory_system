# 📦 AI-Powered Smart Inventory Management System

A comprehensive inventory management system with AI-inspired intelligent features, built using PHP, React, and MySQL.

## 🎯 Project Overview

This system addresses common inventory management challenges:
- Poor inventory tracking
- Stock shortages
- Product expiration losses
- Lack of insights for restocking decisions
- Manual and error-prone record keeping

### Key Features

✅ **User Management** - Role-based access (Admin/Staff)  
✅ **Product Management** - Full CRUD operations with image support  
✅ **Smart Alerts** - Automatic notifications for low stock, near expiry, and expired products  
✅ **AI-Powered Analytics** - Stock prediction, usage analysis, and restock recommendations  
✅ **Dashboard** - Real-time statistics and visual charts  
✅ **Modern UI** - Responsive React-based interface  

## 🛠️ Technology Stack

### Backend
- **Language:** PHP 7.4+
- **Architecture:** REST API (MVC pattern)
- **Server:** Apache (XAMPP)
- **Database:** MySQL

### Frontend
- **Framework:** React.js 18
- **Routing:** React Router v6
- **HTTP Client:** Axios
- **Charts:** Chart.js with React-Chartjs-2
- **Icons:** React Icons

### Development Environment
- **Local Server:** XAMPP
- **Database Tool:** phpMyAdmin

## 📁 Project Structure

```
Smart Inventory Management System/
│
├── inventory-backend/          # PHP Backend
│   ├── api/                    # API router
│   │   └── index.php
│   ├── config/                 # Configuration files
│   │   ├── database.php
│   │   └── cors.php
│   ├── controllers/            # API controllers
│   │   ├── AuthController.php
│   │   ├── UserController.php
│   │   ├── ProductController.php
│   │   ├── CategoryController.php
│   │   ├── AlertController.php
│   │   ├── DashboardController.php
│   │   └── AIAnalyticsController.php
│   ├── models/                 # Data models
│   │   ├── User.php
│   │   ├── Product.php
│   │   ├── Category.php
│   │   ├── Alert.php
│   │   └── AIAnalytics.php
│   ├── database/               # Database files
│   │   └── schema.sql
│   └── uploads/                # File uploads directory
│
├── inventory-frontend/         # React Frontend
│   ├── public/
│   ├── src/
│   │   ├── components/         # Reusable components
│   │   │   ├── Layout.js
│   │   │   └── Layout.css
│   │   ├── pages/              # Page components
│   │   │   ├── Login.js
│   │   │   ├── Dashboard.js
│   │   │   ├── Products.js
│   │   │   ├── ProductForm.js
│   │   │   ├── Alerts.js
│   │   │   └── Users.js
│   │   ├── services/           # API services
│   │   │   ├── api.js
│   │   │   └── auth.js
│   │   ├── App.js
│   │   └── index.js
│   └── package.json
│
└── README.md                   # This file
```

## 🚀 Installation & Setup

### Prerequisites
1. **XAMPP** installed (Apache + MySQL + PHP)
2. **Node.js** and **npm** installed
3. **Code Editor** (VS Code recommended)

### Step 1: Database Setup

1. Start XAMPP and ensure Apache and MySQL are running
2. Open phpMyAdmin: `http://localhost/phpmyadmin`
3. Import the database schema:
   - Click "New" to create a database
   - Name it: `smart_inventory_db`
   - Click "Import"
   - Select `inventory-backend/database/schema.sql`
   - Click "Go"

Alternatively, run the SQL file directly in phpMyAdmin.

### Step 2: Backend Setup

1. Copy the `inventory-backend` folder to:
   ```
   C:\Xampp\htdocs\inventory-backend
   ```

2. Verify database connection in `config/database.php`:
   ```php
   private $host = "localhost";
   private $db_name = "smart_inventory_db";
   private $username = "root";
   private $password = "";  // Default XAMPP password
   ```

3. Test API endpoint:
   - Open: `http://localhost/inventory-backend/api/categories`
   - Should return JSON data

### Step 3: Frontend Setup

1. Open terminal in the `inventory-frontend` directory
2. Install dependencies:
   ```bash
   npm install
   ```

3. Start development server:
   ```bash
   npm start
   ```

4. The app will open at: `http://localhost:3000`

### Step 4: Default Login Credentials

**Admin Account:**
- Username: `admin`
- Password: `admin123`

**Staff Account:**
- Username: `staff1`
- Password: `admin123`

## 📊 Database Schema

### Tables

1. **users** - User accounts (Admin/Staff)
2. **categories** - Product categories
3. **products** - Inventory products
4. **stock_logs** - Inventory movement history (for AI analysis)
5. **alerts** - System-generated alerts

### Key Relationships

- `products.category_id` → `categories.id`
- `stock_logs.product_id` → `products.id`
- `stock_logs.user_id` → `users.id`
- `alerts.product_id` → `products.id`

## 🤖 AI Features Explanation

The system implements AI-inspired intelligence without external APIs:

### 1. **Average Daily Usage Calculation**
- Analyzes `stock_logs` table
- Calculates average daily removal/sales
- Formula: `Total Removed / Active Days`

### 2. **Stockout Prediction**
- Uses current quantity and average daily usage
- Predicts: `Current Quantity / Daily Usage = Days Until Stockout`
- Provides predicted stockout date

### 3. **Restock Recommendation**
- Considers: Average usage, lead time, safety stock percentage
- Formula: `(Daily Usage × Lead Time) + Safety Stock - Current Quantity`
- Intelligent buffer calculation

### 4. **Fast-Moving Products**
- Products with high usage rate
- Identified by analyzing stock movement frequency

### 5. **Slow-Moving Products**
- Products with low or no usage
- Identified by days since last movement

## 🔐 Security Features

1. **Password Hashing** - Using PHP `password_hash()` function
2. **Input Validation** - Server-side validation in controllers
3. **SQL Injection Protection** - Using PDO prepared statements
4. **CORS Configuration** - Restricted to localhost
5. **Role-Based Access** - Admin vs Staff permissions

## 📝 API Endpoints

### Authentication
- `POST /api/auth?action=login` - User login
- `POST /api/auth?action=register` - User registration

### Products
- `GET /api/products` - Get all products (with filters)
- `GET /api/products?id={id}` - Get single product
- `POST /api/products` - Create product
- `PUT /api/products?id={id}` - Update product
- `DELETE /api/products?id={id}` - Delete product

### Dashboard
- `GET /api/dashboard?action=stats` - Get statistics
- `GET /api/dashboard?action=categories` - Get category distribution
- `GET /api/dashboard?action=monthly-changes` - Get monthly changes

### Alerts
- `GET /api/alerts` - Get all alerts
- `GET /api/alerts?action=check` - Check and generate alerts
- `PUT /api/alerts?action=read&id={id}` - Mark alert as read

### AI Analytics
- `GET /api/ai-analytics?action=daily-usage&product_id={id}` - Get daily usage
- `GET /api/ai-analytics?action=stockout-prediction&product_id={id}` - Predict stockout
- `GET /api/ai-analytics?action=restock-recommendation&product_id={id}` - Get restock recommendation
- `GET /api/ai-analytics?action=insights&product_id={id}` - Get all insights

## 🎓 Academic Use

This project is designed for:
- Final-year university projects
- Academic presentations
- System documentation
- Technical demonstrations

### Presentation Points

1. **Problem Statement** - Inventory management challenges
2. **Solution Architecture** - MVC pattern, REST API
3. **AI Implementation** - Statistical analysis and pattern recognition
4. **Database Design** - Normalized schema with relationships
5. **Security Measures** - Authentication, authorization, validation
6. **User Interface** - Modern, responsive React UI

## 📚 Documentation Files

- `README.md` - This file (overview and setup)
- `SYSTEM_ARCHITECTURE.md` - Detailed system architecture
- `AI_LOGIC_EXPLANATION.md` - AI features deep dive

## 🐛 Troubleshooting

### Backend Issues

**Problem:** API returns 404
- **Solution:** Ensure `.htaccess` is in `inventory-backend` folder
- **Solution:** Check Apache `mod_rewrite` is enabled

**Problem:** Database connection error
- **Solution:** Verify MySQL is running in XAMPP
- **Solution:** Check database credentials in `config/database.php`

### Frontend Issues

**Problem:** CORS errors
- **Solution:** Verify `config/cors.php` is included in controllers
- **Solution:** Check API base URL in `src/services/api.js`

**Problem:** Cannot login
- **Solution:** Verify database has default users (run schema.sql)
- **Solution:** Check browser console for API errors

## 📄 License

This project is created for educational purposes.

## 👨‍💻 Development

### Adding New Features

1. **Backend:** Add model → controller → API endpoint
2. **Frontend:** Add service → component → route
3. **Database:** Update schema if needed

### Testing

- Use browser DevTools (F12) to monitor API calls
- Check Network tab for request/response
- Verify database changes in phpMyAdmin

## 🙏 Acknowledgments

Built for academic and learning purposes.

---

**Developed with ❤️ for Smart Inventory Management**
