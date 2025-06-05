# 📊 **Statistics App**

The **Statistics App** is a comprehensive business management system built with Laravel, designed to handle **sales operations, financial transactions, customer relationships, and agent management** for retail businesses. The system supports **both cash and installment-based sales**, tracks financial obligations, processes payments, and provides detailed financial reporting with **WhatsApp integration for automated notifications**.

---

## 🔹 **Core Features**

-   **Product Inventory Management** – Supports categorization and stock tracking.
-   **Customer Relationship Management** – Maintains detailed customer records.
-   **Receipt Generation** – Handles both cash and installment sales.
-   **Installment Payment Tracking** – Monitors scheduled payments.
-   **Financial Reporting & Analytics** – Provides insights into business performance.
-   **User Management** – Role-based access control for Admins and Accountants.
-   **Activity Logging** – Tracks system events for auditing purposes.
-   **Agent & Financial Transactions Management** – Tracks purchases from suppliers and financial settlements.
-   **Inventory Control** – Manages stock levels and warehouse transactions.

---

## 🔍 **Detailed System Overviews**

For more details on each subsystem, check the following links:

🔹 **Receipt Management**  
[📜 Receipt System Overview](https://deepwiki.com/MohammedAlmostfa/Statistics-App/2-receipt-management)

🔹 **Product Management**  
[📦 Inventory & Categorization](https://deepwiki.com/MohammedAlmostfa/Statistics-App/3-product-management)

🔹 **Payment & Installment System**  
[💳 Installment Plans & Transactions](https://deepwiki.com/MohammedAlmostfa/Statistics-App/4-payment-and-installment-system)

🔹 **Financial Reporting**  
[📊 Business Analytics](https://deepwiki.com/MohammedAlmostfa/-Statistics-App/6-financial-reporting)

🔹 **Agent Management**  
[📌 Agent Management Overview](https://deepwiki.com/MohammedAlmostfa/Statistics-App/2.4-agent-management)

🔹 **Financial Transactions**  
[💰 Transactions & Settlements](https://deepwiki.com/MohammedAlmostfa/Statistics-App/2.1-financial-transaction-management)

📌 **For a complete system overview, visit:**  
🔗 [Statistics App Overview](https://deepwiki.com/MohammedAlmostfa/Statistics-App/1-overview)

---

## 🚀 **Technology Stack**

The Statistics App is built using **modern technologies** to ensure efficiency and scalability:

-   **Laravel 12** – PHP Framework
-   **JWT Authentication** – Secure user authentication
-   **Cache System** – Performance optimization
-   **Database Transactions** – Ensuring data integrity
-   **Event-Driven Architecture** – Used for inventory updates

---

## 📐 **Project Architecture & Database Design**

-   **Project Structure**: [📁 System Design](https://drive.google.com/file/d/1V8l6mdmPlQwRZu2TiZz44RYAEA7u0LO5/view?usp=sharing)
-   **Database Schema**: [🗄️ Tables & Relationships](https://deepwiki.com/MohammedAlmostfa/Statistics-App/5.2-database-schema-and-migrations)
-   **Postman API Documentation**: [View API Endpoints](https://egmohammed.postman.co/workspace/e.g.mohammed-Workspace~b4e2523d-6246-4fe1-a96f-67892282e04b/collection/37858198-1a8bb936-f78c-4341-a68a-adc3b6ba5a99?action=share&creator=37858198)

---

## 🔐 **User Roles & Permissions**

The system supports **three primary roles**:

1️⃣ **Admin** – Full control over users, finances, and system settings.  
2️⃣ **Accountant** – Limited access to financial data and receipts.

📌 **Each role has specific permissions to ensure security and proper access management.**

---

## 🔔 **Notifications & Integrations**

-   **Automated WhatsApp Notifications** – Installment payment reminders.
-   **Technology Used**: **UltraMessage**

---

## 📦 **Installation Guide**

### **🔹 Steps to Set Up the Project:**

1️⃣ **Clone the Repository**

```sh
git clone https://github.com/MohammedAlmostfa/Statistics-App
```

2️⃣ **Navigate to the Project Directory**

```sh
cd Statistics-App
```

3️⃣ **Install Dependencies**

```sh
composer install
```

4️⃣ **Create Environment File**

```sh
cp .env.example .env
```

5️⃣ **Configure `.env` File**

-   Set **database credentials** (MySQL connection settings).
-   Define **APP_KEY** and **JWT_SECRET** values.

6️⃣ **Generate Application Key**

```sh
php artisan key:generate
```

7️⃣ **Generate JWT Secret Key**

```sh
php artisan jwt:secret
```

8️⃣ **Run Migrations** (to create database tables)

```sh
php artisan migrate
```

9️⃣ **Seed the Database** (to add default data)

```sh
php artisan db:seed
```

🔟 **Start Job Queue** (for background tasks)

```sh
php artisan queue:work
```

1️⃣1️⃣ **Run the Application**

```sh
php artisan serve
```

---

## ⚠️ **Important Notes**

-   Ensure API requests **follow validation rules** before execution.
-   Test API endpoints using **Postman** or similar tools.
-   Follow **best practices** for clean and scalable code.

---

## 👤 **Credits**

Developed by:

-   **[Mohammed Almostfa](https://github.com/MohammedAlmostfa)**

---

## 📞 **Contact**

For inquiries or support, reach out via:

-   📱 **Phone**: +963991851269
-   💻 **GitHub**: [Mohammed Almostfa](https://github.com/MohammedAlmostfa)
-   🔗 **LinkedIn**: [Mohammed Almostfa](https://www.linkedin.com/in/mohammed-almostfa-63b3a7240/)

---

### 🎯 **Thank you for using the Statistics App!**

We welcome feedback and suggestions! 🚀

---
