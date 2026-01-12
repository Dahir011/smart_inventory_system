# 🏗️ System Architecture Documentation

## Overview

The Smart Inventory Management System follows a **three-tier architecture** pattern:
1. **Presentation Layer** (React Frontend)
2. **Application Layer** (PHP Backend API)
3. **Data Layer** (MySQL Database)

## Architecture Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    PRESENTATION LAYER                        │
│                      (React Frontend)                        │
│                                                              │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐   │
│  │  Login   │  │Dashboard │  │ Products │  │  Alerts  │   │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘   │
│                                                              │
│                  React Router + Axios                        │
└───────────────────────┬─────────────────────────────────────┘
                        │ HTTP/REST API
                        │ (JSON)
┌───────────────────────▼─────────────────────────────────────┐
│                  APPLICATION LAYER                           │
│                    (PHP Backend)                             │
│                                                              │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │ Controllers  │  │    Models    │  │  AI Analytics│      │
│  │              │  │              │  │              │      │
│  │ - Auth       │  │ - User       │  │ - Predictions│      │
│  │ - Product    │  │ - Product    │  │ - Insights   │      │
│  │ - Alert      │  │ - Category   │  │ - Trends     │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
│                                                              │
│                    REST API Router                           │
└───────────────────────┬─────────────────────────────────────┘
                        │ PDO/MySQL
                        │
┌───────────────────────▼─────────────────────────────────────┐
│                      DATA LAYER                              │
│                    (MySQL Database)                          │
│                                                              │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐   │
│  │  users   │  │ products │  │stock_logs│  │  alerts  │   │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘   │
│                                                              │
│                  Normalized Schema                           │
└─────────────────────────────────────────────────────────────┘
```

## Layer Details

### 1. Presentation Layer (Frontend)

**Technology:** React.js 18

**Components Structure:**
```
src/
├── components/       # Reusable UI components
│   └── Layout.js    # Main layout with sidebar
├── pages/           # Page components
│   ├── Login.js
│   ├── Dashboard.js
│   ├── Products.js
│   ├── ProductForm.js
│   ├── Alerts.js
│   └── Users.js
├── services/        # API communication
│   ├── api.js      # Axios configuration
│   └── auth.js     # Authentication service
└── App.js          # Main router
```

**Key Features:**
- Single Page Application (SPA)
- Client-side routing
- State management via React Hooks
- Real-time data updates
- Responsive design

### 2. Application Layer (Backend)

**Technology:** PHP 7.4+ with PDO

**MVC Pattern Implementation:**

#### Models (`models/`)
- **User.php** - User authentication and management
- **Product.php** - Product CRUD and inventory tracking
- **Category.php** - Category management
- **Alert.php** - Alert generation and management
- **AIAnalytics.php** - AI-powered analytics and predictions

#### Controllers (`controllers/`)
- **AuthController.php** - Handle login/register
- **ProductController.php** - Product operations
- **CategoryController.php** - Category operations
- **AlertController.php** - Alert operations
- **DashboardController.php** - Dashboard statistics
- **AIAnalyticsController.php** - AI insights endpoints
- **UserController.php** - User management (Admin)

#### API Router (`api/index.php`)
- Routes HTTP requests to appropriate controllers
- Handles URL parsing and routing
- Returns JSON responses

**Request Flow:**
```
HTTP Request → .htaccess → api/index.php → Controller → Model → Database
                                                              ↓
Response ← JSON ← Controller ← Model ← Database Results
```

### 3. Data Layer (Database)

**Technology:** MySQL (via XAMPP)

**Schema Design:**
- **Normalized** to 3NF (Third Normal Form)
- **Foreign keys** for referential integrity
- **Indexes** for performance optimization
- **Timestamps** for audit trails

## Design Patterns Used

### 1. MVC (Model-View-Controller)
- **Model:** Data access and business logic
- **View:** React components (frontend)
- **Controller:** Request handling and routing

### 2. RESTful API
- Resource-based URLs
- HTTP methods (GET, POST, PUT, DELETE)
- Stateless communication
- JSON data format

### 3. Repository Pattern (Implicit)
- Models encapsulate database operations
- Controllers use models for data access

### 4. Service Layer (Frontend)
- Centralized API communication
- Separation of concerns

## Data Flow

### Example: Adding a Product

```
1. User fills form in React (ProductForm.js)
   ↓
2. Form submission triggers API call via api.js
   POST /api/products { product data }
   ↓
3. API Router (index.php) routes to ProductController
   ↓
4. ProductController validates and creates Product model
   ↓
5. Product model:
   - Inserts into database
   - Logs stock movement
   - Returns success
   ↓
6. Controller returns JSON response
   ↓
7. React receives response and updates UI
   ↓
8. User is redirected to products list
```

### Example: AI Stock Prediction

```
1. User views product in Products.js
   ↓
2. Component calls aiAnalyticsAPI.getInsights(productId)
   ↓
3. API: GET /api/ai-analytics?action=insights&product_id=1
   ↓
4. AIAnalyticsController routes to AIAnalytics model
   ↓
5. AIAnalytics model:
   - Queries stock_logs for historical data
   - Calculates average daily usage
   - Predicts stockout date
   - Recommends restock quantity
   ↓
6. Returns JSON with insights
   ↓
7. React displays insights in UI
```

## Security Architecture

### Authentication Flow
```
Login Request → AuthController → User Model → Database
                                          ↓
                                 Verify Password
                                          ↓
                             Generate Token (user_id:role)
                                          ↓
                          Store in localStorage (Frontend)
```

### Authorization
- **Role-based:** Admin vs Staff
- **Token-based:** Simple Bearer token (user_id:role)
- **Route protection:** ProtectedRoute component

### Input Validation
- **Frontend:** Form validation
- **Backend:** Controller validation
- **Database:** Prepared statements (SQL injection prevention)

## API Architecture

### Endpoint Structure
```
/api/{resource}?action={action}&{params}
```

**Examples:**
- `/api/auth?action=login`
- `/api/products?id=1`
- `/api/dashboard?action=stats`
- `/api/ai-analytics?action=stockout-prediction&product_id=1`

### Response Format
```json
{
  "success": true/false,
  "message": "Optional message",
  "data": { ... }
}
```

### Error Handling
- HTTP status codes (200, 400, 401, 404, 500)
- JSON error responses
- Frontend error display

## Scalability Considerations

### Current Architecture (Single Server)
- All components on localhost
- Suitable for small-medium businesses
- Easy deployment

### Potential Improvements for Scale
1. **Database:**
   - Connection pooling
   - Read replicas
   - Caching layer (Redis)

2. **Backend:**
   - Load balancing
   - API rate limiting
   - JWT authentication

3. **Frontend:**
   - Code splitting
   - CDN deployment
   - Service workers (PWA)

## File Organization Principles

1. **Separation of Concerns:** Each layer has distinct responsibilities
2. **Single Responsibility:** Each file/class has one purpose
3. **DRY (Don't Repeat Yourself):** Reusable components and functions
4. **Convention over Configuration:** Standard naming and structure

## Technology Choices Rationale

### Why PHP?
- **Widely supported:** XAMPP compatibility
- **No compilation:** Easy development
- **PDO:** Secure database access
- **Mature ecosystem:** Rich libraries

### Why React?
- **Component-based:** Reusable UI
- **Virtual DOM:** Efficient updates
- **Large community:** Extensive resources
- **Modern:** Industry standard

### Why MySQL?
- **XAMPP integration:** Easy setup
- **Relational:** Structured data
- **Performance:** Fast queries
- **Free:** Open source

## Testing Strategy (Academic)

1. **Manual Testing:** Functional testing of features
2. **API Testing:** Using browser DevTools or Postman
3. **Database Testing:** Verify data integrity in phpMyAdmin
4. **UI Testing:** Test responsive design on different screens

## Deployment Architecture (Production Ready)

For production deployment:
```
┌─────────────┐
│   Client    │
│  (Browser)  │
└──────┬──────┘
       │ HTTPS
┌──────▼──────────────────┐
│   Web Server            │
│   (Apache/Nginx)        │
│  ┌──────────────────┐   │
│  │  React Build     │   │
│  │  (Static Files)  │   │
│  └──────────────────┘   │
└──────┬──────────────────┘
       │
┌──────▼──────────────────┐
│   PHP Application       │
│   (Backend API)         │
└──────┬──────────────────┘
       │
┌──────▼──────────────────┐
│   MySQL Database        │
└─────────────────────────┘
```

## Conclusion

This architecture provides:
- ✅ Clear separation of concerns
- ✅ Scalable structure
- ✅ Easy maintenance
- ✅ Academic clarity
- ✅ Real-world applicability

The system is designed to be both academically rigorous and practically useful for small-to-medium businesses.
