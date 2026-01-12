# 📋 Use Cases Documentation
## Smart Inventory Management System

This document describes the main use cases and scenarios of the AI-Powered Smart Inventory Management System. Use cases help understand how different users interact with the system to accomplish their goals.

---

## 👥 Actors (System Users)

### 1. **Administrator (Admin)**
- Full system access
- Manages users and permissions
- Views all reports and analytics
- System configuration access

### 2. **Staff Member**
- Limited access (product management, viewing)
- Cannot manage users
- Can add/edit products
- Receives alerts and notifications

---

## 📝 Use Cases

### UC-1: User Authentication

**Actor:** Admin, Staff

**Description:** Users log into the system to access inventory management features.

**Preconditions:**
- User has a valid account
- System is running and accessible

**Main Flow:**
1. User opens the login page
2. User enters username and password
3. System validates credentials
4. System checks user role (admin/staff)
5. System generates authentication token
6. User is redirected to dashboard
7. System stores authentication token in browser

**Alternative Flows:**
- **3a.** Invalid credentials → System shows error message
- **3b.** Account doesn't exist → System shows error message

**Postconditions:**
- User is authenticated
- User can access authorized features

---

### UC-2: Register New User (Admin Only)

**Actor:** Admin

**Description:** Administrator creates a new user account (Admin or Staff).

**Preconditions:**
- Admin is logged in
- Admin has user management permissions

**Main Flow:**
1. Admin navigates to Users page
2. Admin clicks "Add New User" button
3. Admin fills in user form:
   - Username (unique)
   - Email (unique)
   - Password
   - Full Name
   - Role (Admin/Staff)
4. System validates input data
5. System hashes password
6. System creates user account
7. System confirms user creation
8. New user appears in users list

**Alternative Flows:**
- **4a.** Username/email already exists → System shows error
- **4b.** Invalid data format → System shows validation errors

**Postconditions:**
- New user account is created
- User can log in with new credentials

---

### UC-3: Add New Product

**Actor:** Admin, Staff

**Description:** User adds a new product to the inventory system.

**Preconditions:**
- User is logged in
- Categories exist in the system

**Main Flow:**
1. User navigates to Products page
2. User clicks "Add Product" button
3. User fills product form:
   - Product Name
   - Category (select from list)
   - Quantity
   - Minimum Stock Level
   - Expiry Date (optional)
   - Supplier Name
   - Unit Price
   - Product Image (optional)
   - Description (optional)
4. System validates input data
5. System uploads product image (if provided)
6. System creates product record
7. System logs stock movement (initial stock addition)
8. System checks for alerts (low stock, expiry)
9. System confirms product creation
10. User is redirected to products list

**Alternative Flows:**
- **4a.** Invalid data → System shows validation errors
- **5a.** Image upload fails → System shows error (product still created)
- **8a.** Quantity below minimum → System generates low stock alert

**Postconditions:**
- New product is added to inventory
- Stock log entry is created
- Alerts are generated if applicable

---

### UC-4: Update Product Information

**Actor:** Admin, Staff

**Description:** User modifies existing product details or updates stock quantity.

**Preconditions:**
- User is logged in
- Product exists in the system

**Main Flow:**
1. User navigates to Products page
2. User clicks "Edit" button on a product
3. System loads product details
4. User modifies product information:
   - Updates quantity
   - Changes expiry date
   - Updates price, supplier, etc.
5. User saves changes
6. System validates updated data
7. System updates product record
8. System logs stock movement (if quantity changed)
9. System checks for alerts
10. System confirms update

**Alternative Flows:**
- **4a.** User decreases quantity significantly → System generates low stock alert
- **4b.** User updates expiry date to near date → System generates near-expiry alert

**Postconditions:**
- Product information is updated
- Stock log reflects changes
- New alerts generated if conditions met

---

### UC-5: Delete Product

**Actor:** Admin, Staff

**Description:** User removes a product from the inventory system.

**Preconditions:**
- User is logged in
- Product exists in the system

**Main Flow:**
1. User navigates to Products page
2. User clicks "Delete" button on a product
3. System shows confirmation dialog
4. User confirms deletion
5. System checks for dependencies (stock logs, alerts)
6. System deletes product record (cascades to logs/alerts)
7. System confirms deletion
8. Product is removed from list

**Alternative Flows:**
- **3a.** User cancels → No action taken

**Postconditions:**
- Product is removed from inventory
- Related records (logs, alerts) are deleted (cascade)

---

### UC-6: View Inventory List

**Actor:** Admin, Staff

**Description:** User views the complete inventory list with filtering and search options.

**Preconditions:**
- User is logged in

**Main Flow:**
1. User navigates to Products page
2. System loads all products
3. User can apply filters:
   - By category
   - Low stock only
   - Near expiry only
   - Expired products only
4. User can search by product name or supplier
5. System displays filtered results
6. User views product details in table/list format

**Postconditions:**
- User sees relevant products
- Filters are applied to the view

---

### UC-7: View Dashboard Statistics

**Actor:** Admin, Staff

**Description:** User views system overview with statistics and charts.

**Preconditions:**
- User is logged in

**Main Flow:**
1. User navigates to Dashboard (default page)
2. System loads dashboard data:
   - Total products count
   - Low stock count
   - Near expiry count
   - Expired products count
   - Unread alerts count
3. System loads charts:
   - Products by category (pie chart)
   - Monthly inventory changes (line/bar chart)
4. System displays statistics and visualizations
5. User can interact with charts

**Postconditions:**
- User sees system overview
- Charts are displayed correctly

---

### UC-8: View and Manage Alerts

**Actor:** Admin, Staff

**Description:** User views system-generated alerts and marks them as read.

**Preconditions:**
- User is logged in
- System has generated alerts

**Main Flow:**
1. User navigates to Alerts page
2. System loads all alerts (unread first)
3. User views alert details:
   - Alert type (low stock, near expiry, expired)
   - Product name
   - Alert message
   - Date/time created
4. User can mark alert as read
5. System updates alert status
6. Alert moves to read section

**Alternative Flows:**
- **4a.** User clicks on product name → System navigates to product edit page

**Postconditions:**
- User is aware of inventory issues
- Alerts are marked as read

---

### UC-9: Automatic Alert Generation

**Actor:** System (Automatic)

**Description:** System automatically generates alerts based on inventory conditions.

**Preconditions:**
- Products exist in the system
- Alert checking process runs (manual trigger or scheduled)

**Main Flow:**
1. System scans all products
2. System checks each product for:
   - Low stock (quantity ≤ minimum level)
   - Near expiry (expiry date within 7 days)
   - Expired (expiry date < today)
3. For each condition met:
   - System creates alert record
   - System sets alert type and message
   - System links alert to product
4. System marks alerts as unread
5. Alerts appear on Alerts page

**Trigger Events:**
- Product quantity updated
- Product expiry date updated
- New product added
- Manual alert check triggered

**Postconditions:**
- Alerts are created for problematic products
- Users are notified of issues

---

### UC-10: View AI Stock Prediction

**Actor:** Admin, Staff

**Description:** User views AI-powered prediction of when a product will run out of stock.

**Preconditions:**
- User is logged in
- Product exists
- Product has stock movement history

**Main Flow:**
1. User views product details (or products list)
2. User requests AI insights (or system displays automatically)
3. System analyzes stock_logs for product
4. System calculates average daily usage
5. System predicts stockout date:
   - Days remaining = Current Quantity / Daily Usage
   - Predicted Date = Today + Days Remaining
6. System displays prediction to user
7. User sees predicted stockout information

**Alternative Flows:**
- **3a.** Insufficient historical data → System shows "No prediction available"

**Postconditions:**
- User knows when to restock
- Prediction is based on historical patterns

---

### UC-11: View AI Restock Recommendation

**Actor:** Admin, Staff

**Description:** User receives intelligent recommendation on how much to restock.

**Preconditions:**
- User is logged in
- Product exists
- Product has usage history

**Main Flow:**
1. User views product insights
2. System calculates:
   - Average daily usage
   - Usage during lead time (days × daily usage)
   - Safety stock (buffer percentage)
   - Recommended quantity
3. System displays recommendation with reasoning
4. User sees recommended restock quantity
5. User can use this to place orders

**Postconditions:**
- User has data-driven restock quantity
- Recommendation considers usage patterns and lead time

---

### UC-12: View Fast-Moving Products

**Actor:** Admin

**Description:** Administrator identifies products with high usage rates.

**Preconditions:**
- Admin is logged in
- Products have stock movement history

**Main Flow:**
1. Admin views Dashboard or Analytics
2. System analyzes stock_logs for all products
3. System calculates usage rates
4. System identifies fast-moving products (high usage)
5. System displays list of fast-moving products
6. Admin sees which products sell/use quickly
7. Admin can adjust stock levels for these products

**Postconditions:**
- Admin knows which products are popular
- Inventory can be optimized

---

### UC-13: View Slow-Moving Products

**Actor:** Admin

**Description:** Administrator identifies products with low or no usage.

**Preconditions:**
- Admin is logged in
- Products exist in system

**Main Flow:**
1. Admin views Analytics
2. System analyzes stock_logs
3. System identifies slow-moving products:
   - No usage in last 30 days
   - Low usage relative to stock
4. System displays list of slow-moving products
5. Admin sees products that may need promotion or removal
6. Admin can make inventory decisions

**Postconditions:**
- Admin identifies stagnant inventory
- Can reduce stock or run promotions

---

### UC-14: Generate Inventory Report

**Actor:** Admin, Staff

**Description:** User generates a printable report of inventory status.

**Preconditions:**
- User is logged in

**Main Flow:**
1. User navigates to Products page
2. User applies desired filters
3. User clicks "Print Report" or "Export PDF"
4. System formats data for printing
5. System generates report view
6. User prints or saves report

**Alternative Flows:**
- **4a.** PDF generation → System creates PDF file
- **4b.** Print view → System shows print-friendly page

**Postconditions:**
- User has physical/digital inventory report
- Report reflects current filtered view

---

### UC-15: Manage Categories

**Actor:** Admin

**Description:** Administrator manages product categories.

**Preconditions:**
- Admin is logged in

**Main Flow:**
1. Admin navigates to Categories (via products)
2. Admin can:
   - Add new category
   - Edit category name/description
   - Delete category (if no products use it)
3. System validates category operations
4. System updates category records
5. Categories are available for product assignment

**Alternative Flows:**
- **2a.** Delete category with products → System prevents deletion

**Postconditions:**
- Categories are updated
- Products can use new categories

---

## 📊 Use Case Diagram Summary

```
┌─────────────────────────────────────────────────────────────┐
│                    SYSTEM BOUNDARY                           │
│                                                              │
│  ┌──────────┐          ┌──────────┐                         │
│  │  Admin   │          │  Staff   │                         │
│  └────┬─────┘          └────┬─────┘                         │
│       │                     │                                │
│       ├─ Authenticate       ├─ Authenticate                 │
│       ├─ Manage Users       ├─ View Products                │
│       ├─ Add/Edit Products  ├─ Add/Edit Products            │
│       ├─ View Dashboard     ├─ View Dashboard               │
│       ├─ View Analytics     ├─ View Alerts                  │
│       ├─ View AI Insights   │                                │
│       └─ Generate Reports   └─ View Reports                 │
│                                                              │
│  ┌──────────────────────────────────────┐                   │
│  │      Automatic Alert System          │                   │
│  │  - Low Stock Detection               │                   │
│  │  - Expiry Monitoring                 │                   │
│  └──────────────────────────────────────┘                   │
│                                                              │
│  ┌──────────────────────────────────────┐                   │
│  │      AI Analytics Engine             │                   │
│  │  - Stock Prediction                  │                   │
│  │  - Usage Analysis                    │                   │
│  │  - Restock Recommendations           │                   │
│  └──────────────────────────────────────┘                   │
└─────────────────────────────────────────────────────────────┘
```

---

## 🎯 Key Use Case Relationships

### **Includes (Mandatory)**
- UC-1 (Authentication) is included in all other use cases
- UC-6 (View Inventory) is included in UC-4, UC-5

### **Extends (Optional)**
- UC-10 (AI Prediction) extends UC-6 (View Inventory)
- UC-11 (Restock Recommendation) extends UC-10

### **Precedes (Sequential)**
- UC-2 (Register User) precedes UC-1 (Login)
- UC-3 (Add Product) precedes UC-8 (Alerts)
- UC-9 (Auto Alerts) triggers UC-8 (View Alerts)

---

## 📚 Academic Presentation Points

1. **Actor Identification:** Clearly defined Admin and Staff roles
2. **Use Case Completeness:** Covers all major system functions
3. **Flow Clarity:** Step-by-step flows are easy to understand
4. **Alternative Paths:** Handles error cases and exceptions
5. **Relationships:** Shows how use cases relate to each other
6. **Real-World Applicability:** Practical scenarios for businesses

---

## 🔄 System Interaction Summary

| Use Case | Actor | Frequency | Complexity |
|----------|-------|-----------|------------|
| UC-1: Authentication | All | Daily | Simple |
| UC-2: Register User | Admin | Occasional | Medium |
| UC-3: Add Product | All | Frequent | Medium |
| UC-4: Update Product | All | Very Frequent | Medium |
| UC-5: Delete Product | All | Occasional | Simple |
| UC-6: View Inventory | All | Very Frequent | Simple |
| UC-7: View Dashboard | All | Daily | Medium |
| UC-8: View Alerts | All | Frequent | Simple |
| UC-9: Auto Alerts | System | Continuous | Complex |
| UC-10: AI Prediction | All | Frequent | Complex |
| UC-11: Restock Recommendation | All | Frequent | Complex |
| UC-12: Fast-Moving | Admin | Occasional | Complex |
| UC-13: Slow-Moving | Admin | Occasional | Complex |
| UC-14: Generate Report | All | Occasional | Simple |
| UC-15: Manage Categories | Admin | Rare | Simple |

---

This use case documentation provides a comprehensive view of how users interact with the Smart Inventory Management System, making it easier to understand, present, and defend the system in academic contexts.
