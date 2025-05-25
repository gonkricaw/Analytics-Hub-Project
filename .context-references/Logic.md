# Logical System Design: Indonet Analytics Hub

## 1. Introduction

This document outlines the logical system design for the "Indonet Analytics Hub." The system is a web-based platform designed to provide controlled user access to dynamically managed content, an informative dashboard, and system-wide notifications, all configurable by administrators. The architecture prioritizes security, modularity, and a rich, responsive user experience delivered exclusively in a dark mode theme.

## 2. System Architecture

### 2.1. Architectural Style

The Indonet Analytics Hub will employ a **Server-Client architecture**, specifically:

* **Backend:** A robust API-centric application built with the **Laravel (PHP)** framework. It will handle all business logic, data processing, database interactions, authentication, and authorization.
* **Frontend:** A **Single Page Application (SPA)** developed using **Vue.js** (specifically the Vuexy admin template). The frontend will be responsible for rendering the user interface, managing client-side state, user interactions, and communicating with the backend via API calls.

This decoupled approach allows for independent development and scaling of the frontend and backend.

### 2.2. Core Technology Stack Summary

* **Backend Framework:** Laravel (PHP)
* **Frontend Framework/Template:** Vue.js with Vuexy Admin Dashboard Template
* **Database Management System:** PostgreSQL
* **API Communication:** RESTful APIs using JSON
* **Authentication:** Laravel Sanctum (for SPA authentication)
* **Styling:** CSS/SASS with a "Dark Mode Only" global theme.
* **Iconography:** Font Awesome

## 3. Core Logical Components

The system is logically divided into three main tiers: Frontend, Backend, and Database.

### 3.1. Frontend (Vue.js - Vuexy)

The frontend is responsible for the presentation layer and user interaction.

* **UI Components:** Leverages the pre-built and custom components from the Vuexy template, ensuring a consistent look and feel. This includes forms, tables, modals, charts, cards, and navigation elements (Navbar/Horizontal Navbar). All UI elements will be responsive and feature rich animations and transitions.
* **Client-Side Routing:** Vue Router will manage navigation within the SPA, providing seamless page transitions without full-page reloads. Routes will be protected based on user authentication status and roles.
* **State Management:** A centralized state management solution (like Vuex or Pinia, typically provided with Vuexy) will manage global application state, such as user information, authentication status, and shared data across components.
* **API Client:** Axios (or a similar HTTP client) will be configured to make asynchronous requests to the Laravel backend API, handling data exchange (sending and receiving JSON). It will include interceptors for error handling and attaching authentication tokens.

### 3.2. Backend (Laravel)

The backend serves as the application's core, handling all business logic and data management.

* **API Layer (RESTful Endpoints):** Exposes a set of secure RESTful API endpoints that the frontend consumes. These endpoints will handle CRUD operations for all resources (users, roles, content, menus, notifications, etc.) and other system functionalities.
* **Application Logic/Business Rules:** Contains the core business logic for each module, including data validation, processing, and orchestration of services.
* **Database Abstraction (Eloquent ORM):** Laravel's Eloquent ORM will be used for all database interactions with PostgreSQL, providing an expressive and convenient way to work with data and manage relationships.
* **Authentication Service (Laravel Sanctum):** Manages user authentication for the SPA. It handles login requests, session management (via secure cookies or tokens), and protects API routes.
* **Authorization Service (Laravel Gates & Policies):** Controls what actions authenticated users can perform. Gates and Policies will be defined based on user roles and permissions to restrict access to specific resources and functionalities.
* **Email Service:** Integrates with an email provider (configurable via `.env`) to send transactional emails (invitations, password resets, warnings) using Laravel's mail facilities and manageable email templates.

### 3.3. Database (PostgreSQL)

The PostgreSQL database stores all persistent application data.

* **Schema Design:** The database schema will be designed with normalized tables to ensure data integrity and efficiency. Key tables will include `users`, `roles`, `permissions`, `role_user`, `permission_role`, `menus`, `content`, `notifications`, `email_templates`, `system_configurations`, `terms_and_conditions`, `failed_login_attempts`, `ip_blocks`, etc.
* **Data Integrity:** Foreign key constraints, validation rules at the application layer, and potentially database-level checks will be used to maintain data integrity.
* **Migrations & Seeding:** Laravel migrations will manage schema evolution. Seeders will populate the database with initial default data (e.g., admin user, default roles, default T&C, default email templates) and essential lookup data.

## 4. Key System Modules & Functionality Breakdown

The system's functionality is organized into logical modules:

### 4.1. User & Authentication Management Module

* **Logical Flow:**
    * **Invitation:** Admin creates a user -> System generates temporary password & unique link -> Email sent to user.
    * **Initial Login:** User clicks link -> Enters email & temporary password -> Forced to change password (policy enforced) -> T&C popup.
    * **Standard Login:** User enters credentials -> Backend validates -> Sanctum establishes session -> T&C popup.
    * **Failed Logins & IP Blocking:** System tracks failed attempts -> Blocks IP after 15 failures -> Admin can unblock.
    * **Logout:** User initiates logout (with confirmation) or session times out (15 mins inactivity) -> Session terminated.
    * **Password Reset:** User requests reset -> Enters email -> System sends one-time link -> User sets new password.
    * **Profile Update:** User updates photo/password -> Backend validates and updates.
    * **T&C Acceptance:** On each login, user must accept T&C via modal; declining logs them out.

### 4.2. Role-Based Access Control (RBAC) Module

* **Logical Flow:**
    * Admin defines Permissions (e.g., `create-content`, `edit-users`).
    * Admin defines Roles (e.g., "Manager," "Editor") and assigns multiple Permissions to each Role.
    * Admin assigns one or more Roles to each User.
    * When a user attempts an action or accesses a resource, Laravel Gates/Policies (associated with backend API routes and potentially frontend routes/UI elements) check the user's Roles and associated Permissions to allow or deny the action.

### 4.3. Menu Management Module

* **Logical Flow:**
    * Privileged users access the menu management interface.
    * They can define parent/child menu items, link them to content or list views, and assign Font Awesome icons.
    * The frontend dynamically renders the Navbar/Horizontal Navbar based on the user's role and the menu configuration fetched from the backend.

### 4.4. Content Management Module

* **Logical Flow:**
    * Privileged users access content creation/editing interfaces.
    * **Custom Content:** User utilizes a rich text editor to create content (HTML, text, images, YouTube embeds, document links, buttons). Data is saved to the backend.
    * **Embedded URL Content:** User provides an external URL. The system generates a unique internal UUID-based path, encrypts the original URL for storage/display, and renders the external content in a full-width iframe. Access to this internal path is protected by authentication.
    * When content is requested, the backend checks user permissions (via RBAC) before serving the data.

### 4.5. Notification Module

* **Logical Flow:**
    * Privileged users create notifications with rich content.
    * New notifications are flagged for all users.
    * The frontend polls or receives updates (e.g., via a dedicated API call upon certain actions) for new notifications.
    * The bell icon in the navbar indicates unread notifications (with a pulse animation). Clicking it shows a list of notifications; clicking a specific notification navigates to its detailed view and marks it as read.

### 4.6. Email Management Module

* **Logical Flow:**
    * Privileged users manage email templates (for invitations, password resets, warnings etc.) using an interface that allows HTML and dynamic placeholders.
    * When a system event triggers an email (e.g., user invitation), the backend retrieves the relevant template (custom or system default), populates dynamic values, and dispatches the email via the configured mail service.

### 4.7. Dashboard & Reporting Module (Home/Welcome Page)

* **Logical Flow:**
    * Upon login and T&C acceptance, the user is directed to the Home/Welcome page.
    * The frontend makes multiple API calls to fetch data for various widgets (Jumbotron content, marquee text, login stats, notification list, user activity lists, frequently visited content).
    * The backend aggregates and processes this data (e.g., querying login logs, user sessions, content access patterns).
    * The frontend renders the widgets, including charts (e.g., Line Area Chart for logins) and lists, based on the received data. The digital clock updates client-side.

### 4.8. System Configuration Module

* **Logical Flow:**
    * Administrators access a dedicated section to manage global settings.
    * Changes (e.g., new application logo, updated Jumbotron text, new login page background) are submitted to the backend.
    * The backend saves these configurations to the database or configuration files.
    * The frontend fetches and applies these configurations dynamically where appropriate (e.g., displaying the current app logo).

## 5. Data Flow Examples

* **User Login:**
    1.  User submits credentials (email, password) via Vue.js form.
    2.  Axios sends a POST request to Laravel `/login` API endpoint.
    3.  Laravel Sanctum attempts to authenticate the user against the PostgreSQL `users` table.
    4.  If successful, Sanctum establishes a session (e.g., sets a secure HTTP-only cookie).
    5.  Laravel API returns a success response with user data (excluding sensitive info).
    6.  Vue.js updates client-side state (user authenticated, user info) and redirects to the dashboard.
    7.  Frontend displays T&C modal.
* **Fetching Content:**
    1.  User navigates to a content page in the Vue.js SPA.
    2.  Vue Router triggers a component associated with the content.
    3.  The component makes a GET request (via Axios) to a Laravel API endpoint (e.g., `/api/content/{id_or_slug}`).
    4.  Laravel API middleware checks authentication and authorization (user's role/permissions).
    5.  If authorized, the Laravel controller fetches content from PostgreSQL via Eloquent.
    6.  API returns content data as JSON.
    7.  Vue.js component receives data, updates its state, and renders the content.
* **Creating Content (Custom Content):**
    1.  User (with appropriate permissions) uses the rich text editor in the Vue.js frontend.
    2.  User submits the new content.
    3.  Axios sends a POST request to a Laravel API endpoint (e.g., `/api/content`) with the content data (HTML, text, metadata).
    4.  Laravel API middleware checks authentication and authorization.
    5.  The Laravel controller validates the incoming data.
    6.  If valid, Eloquent ORM saves the new content to the PostgreSQL `content` table.
    7.  API returns a success response, possibly with the newly created content object.
    8.  Vue.js updates UI (e.g., navigates to the new content page or shows a success message).

## 6. Security Considerations (Logical View)

* **Authentication:** Secure SPA authentication handled by Laravel Sanctum (cookie-based or token-based if necessary).
* **Authorization:** Granular access control implemented via Laravel Gates and Policies, restricting access to API endpoints and specific data based on user roles and permissions.
* **Input Validation:** Rigorous validation of all incoming data on the backend (Laravel validation) to prevent invalid data entry and common vulnerabilities like XSS and SQL injection. Frontend validation will provide immediate user feedback.
* **Output Encoding:** Proper encoding of data displayed in the frontend to prevent XSS vulnerabilities. Vue.js handles much of this by default with `{{ }}` syntax.
* **CSRF Protection:** Laravel provides built-in CSRF protection, which will be utilized.
* **Secure URL Embedding:** The mechanism for embedding external URLs will use UUIDs for internal paths and encrypt the original URL to prevent direct exposure or manipulation. Access to these embedded views will be authenticated.
* **Password Security:** Passwords will be hashed using strong algorithms (Laravel default - Bcrypt). Password policies enforced. Temporary passwords and one-time reset links.
* **IP Blocking:** Protection against brute-force login attacks.
* **HTTPS:** The application must be deployed over HTTPS to encrypt all communication between the client and server.

## 7. Scalability & Maintainability (Logical Considerations)

* **Modular Design:** The separation into distinct frontend and backend applications, and further breakdown into logical modules within each, promotes easier development, testing, and maintenance.
* **Standardized Technologies:** Use of well-established and widely supported frameworks (Laravel, Vue.js) and databases (PostgreSQL) facilitates finding resources and developers.
* **Adherence to Coding Standards:** Following defined coding standards (PSR for PHP, Vue.js style guides) and the Vuexy template's conventions improves code readability and maintainability.
* **ORM Usage:** Eloquent ORM abstracts database interactions, making it easier to manage schema changes and potentially switch database systems if ever needed (though PostgreSQL is specified).
* **API Versioning (Future Consideration):** If the API is expected to be consumed by other clients or undergoes significant changes, API versioning can be introduced.

This logical system design provides a blueprint for developing the Indonet Analytics Hub, ensuring all specified functionalities are supported by a coherent and robust architecture.
