# Task List: Indonet Analytics Hub Development

## Introduction

This document outlines the detailed tasks required for the development of the "Indonet Analytics Hub." It is structured by development phases and aims to provide a clear roadmap for implementation. Adherence to the previously established Development Plan and Logical System Design is crucial.

**IMPORTANT: Before starting any new major feature or phase, developers MUST perform the following "Pre-Implementation Check."**

---

## 🌟 General Pre-Implementation Check (Crucial Note) 🌟

**Before proceeding with tasks in each section, ensure you have thoroughly reviewed and understand:**

1.  **The Overall Development Plan:** Understand the goals and scope of the current phase and how it fits into the larger project.
2.  **The Logical System Design:** Understand the architecture, data flow, and how the components you are building interact with the rest of the system.
3.  **Configuration File Consistency & Management:**
    * Verify that your local setup aligns with the project's standards.
    * Be prepared to update these files as needed during implementation, ensuring consistency with the Vuexy template and project guidelines:
        * `.editorconfig`
        * `.eslintrc.cjs`
        * `.stylelintrc.json`
        * `auto-imports.d.ts`
        * `components.d.ts`
        * `composer.json`
        * `jsconfig.json`
        * `package.json`
        * `themeConfig.js`
        * `typed-router.d.ts`
        * `vite.config.js`
    * Document any significant changes or additions to these configurations.
4.  **Existing Code & Vuexy Template Structure:** Leverage existing components and patterns from the Vuexy template wherever possible to maintain consistency and accelerate development.

---

## Phase 0: Project Setup & Initial Configuration

* **Objective:** Establish the foundational development environment and basic application shell.
* **Pre-Implementation Check:** Review general guidelines above.

* **Tasks:**
    * [ ] **Environment Setup:**
        * [ ] Set up local development environment (PHP, Node.js, Composer, PostgreSQL).
        * [ ] Clone/Install Vuexy - Laravel + Vuejs Admin Dashboard Template.
        * [ ] Configure Laravel `.env` file:
            * `APP_NAME`, `APP_URL`
            * Database connection (PostgreSQL): `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
            * Mail driver settings (`MAIL_MAILER`, `MAIL_HOST`, etc.)
            * Other necessary environment variables.
        * [ ] Run initial `composer install` and `npm install` (or `yarn install`).
        * [ ] Run initial database migrations: `php artisan migrate`.
        * [ ] Generate Laravel application key: `php artisan key:generate`.
    * [ ] **Basic Application Configuration:**
        * [ ] Configure Laravel `config/app.php` (timezone, locale).
        * [ ] **Global Theme:** Implement "Dark Mode Only" as the exclusive theme.
            * [ ] Ensure Vuexy `themeConfig.js` (or `.ts`) is set for dark mode by default and options for light mode are disabled/removed if applicable.
            * [ ] Verify all base layouts and components render correctly in dark mode.
        * [ ] **Navigation Structure:**
            * [ ] Configure main navigation to use a **Sticky Navbar or Horizontal Navbar**.
            * [ ] Ensure no sidebar is used for menu navigation after login.
            * [ ] The Navbar should be prepared to hold: App Logo, Menu List, User Avatar/Profile Access, Notification Bell.
        * [ ] **Iconography:**
            * [ ] Integrate and test **Font Awesome icons**. Ensure they can be easily used in both Blade (if any) and Vue components.
        * [ ] **Initial UI Shell & Layouts:**
            * [ ] Define main Vue layouts (e.g., `LayoutAuthenticated.vue`, `LayoutGuest.vue`).
            * [ ] Implement global **loading process animations** (e.g., a spinner or progress bar for page transitions and initial data loads).
            * [ ] Set up basic Vue Router configuration (`router/index.js` or `.ts`):
                * [ ] Define initial routes (e.g., `/login`, `/dashboard` or `/home`).
                * [ ] Implement route guards for authentication (redirect to login if not authenticated).
        * [ ] **Version Control:**
            * [ ] Initialize Git repository.
            * [ ] Create initial commit with the base project setup.
            * [ ] Define branching strategy (e.g., Gitflow).
    * [ ] **Testing:**
        * [ ] Ensure basic Laravel and Vue application can run without errors.
        * [ ] Verify PostgreSQL connection.

---

## Phase 1: Core User Management & Authentication

* **Objective:** Implement the complete user lifecycle, authentication, and foundational security features.
* **Pre-Implementation Check:** Review general guidelines, overall plan, logical system, and config files.

* **Tasks:**
    * [ ] **User Model & Database Schema (PostgreSQL):**
        * [ ] Review/Update Laravel `User` model (`app/Models/User.php`).
        * [ ] Review/Update `users` table migration (`database/migrations/..._create_users_table.php`) for necessary fields: `name`, `email`, `password`, `profile_photo_path`, `invited_by` (nullable), `last_active_at`, `temporary_password_used` (boolean), etc. Ensure `email` is unique.
        * [ ] Create migration for `failed_login_attempts` table (user_id, ip_address, timestamp).
        * [ ] Create migration for `ip_blocks` table (ip_address, reason, unblocked_at, unblocked_by).
        * [ ] Run migrations: `php artisan migrate`.
        * [ ] Create User factory for testing/seeding (`database/factories/UserFactory.php`).
    * [ ] **User Invitation System (Admin Functionality):**
        * **Backend:**
            * [ ] Create API endpoint for admin to add a new user (e.g., POST `/api/admin/users/invite`).
            * [ ] Implement controller logic to:
                * Validate input (name, email).
                * Check for existing email.
                * Generate a secure temporary password.
                * Store user in `users` table (hash temporary password, mark `temporary_password_used` as false).
                * Dispatch an email notification (see Email Management Module tasks later).
            * [ ] Implement RBAC: Ensure only admins can access this endpoint.
        * **Frontend (Admin Panel Section - to be built more fully in RBAC phase, placeholder here):**
            * [ ] Design basic UI form for admin to input new user's name and email for invitation.
            * [ ] Implement API call to the invitation endpoint.
    * [ ] **Login Process:**
        * **Backend:**
            * [ ] Implement `/api/login` endpoint using Laravel Sanctum.
            * [ ] Logic to authenticate against `email` and hashed `password` in PostgreSQL.
            * [ ] On successful login:
                * Establish session.
                * Clear `failed_login_attempts` for the user/IP.
                * Update `last_active_at`.
                * Return user data (excluding password) and session status.
            * [ ] On failed login:
                * Log failed attempt in `failed_login_attempts`.
                * Increment failure count for IP. If count reaches 15, add IP to `ip_blocks` table.
                * Return appropriate error response (e.g., 401 or 422).
            * [ ] Middleware to check if IP is blocked before attempting login.
        * **Frontend:**
            * [ ] Create Vue Login page component (`views/pages/auth/Login.vue` or similar).
                * Layout: Static image/branding on the left, Login form (email, password fields, "Forgot Password?" link, Submit button) on the right.
            * [ ] Implement form handling and validation (e.g., using Vuelidate or VeeValidate).
            * [ ] Implement Vuex/Pinia store actions/mutations for login process (API call, storing user data & auth status).
            * [ ] Implement API call to `/api/login`.
            * [ ] On successful login:
                * Store user data and token/session state.
                * Redirect to Home/Welcome page.
                * Display animated success flash message ("Login successful!").
            * [ ] On failed login: Display animated error flash message ("Invalid credentials" or "IP blocked").
            * [ ] Ensure no public registration page/link exists. Application opens directly to login.
    * [ ] **Initial Login & Password Change:**
        * **Backend:**
            * [ ] Create API endpoint for changing password (e.g., POST `/api/user/change-password`).
            * [ ] Middleware to check if `temporary_password_used` is false for authenticated user on certain routes.
            * [ ] Controller logic to validate current password (if applicable, or just new password for initial change), new password (policy: min 8 chars, uppercase, lowercase, number), and confirmation.
            * [ ] Update user's password (hashed) and set `temporary_password_used` to true.
        * **Frontend:**
            * [ ] Create Vue Change Password page component.
            * [ ] After initial login with temporary password, Vue Router redirects to this page.
            * [ ] Form for new password and password confirmation. Display password policy requirements.
            * [ ] API call to change password.
            * [ ] On success, redirect to Home/Welcome page and show success message.
    * [ ] **User Profile Management (Post-Login):**
        * **Backend:**
            * [ ] API endpoint for user to update their profile (e.g., POST `/api/user/profile`).
            * [ ] Allow updating password (re-use change password logic/validation) and profile photo.
            * [ ] Prevent updating name and email via this endpoint.
            * [ ] Handle profile photo upload (store file, update `profile_photo_path` in DB).
        * **Frontend:**
            * [ ] Create "Edit Profile" page/modal.
            * [ ] Form fields for current password (if changing password), new password, password confirmation.
            * [ ] File input for profile photo upload with preview. Display as circle avatar image without borders.
            * [ ] Display non-editable name and email.
            * [ ] API calls to update profile.
    * [ ] **Forgot Password Functionality:**
        * **Backend:**
            * [ ] API endpoint to request password reset (e.g., POST `/api/password/email`). Input: email.
                * Validate email exists. Generate a unique, one-time use token (store hashed token with expiry in `password_resets` table).
                * Send password reset email (see Email Management) with a link containing the token.
            * [ ] API endpoint to reset password (e.g., POST `/api/password/reset`). Input: email, token, new password, password confirmation.
                * Validate token, email, and new password (policy).
                * Update user's password. Delete/invalidate token.
        * **Frontend:**
            * [ ] "Forgot Password?" link on Login page navigates to a "Request Password Reset" page/modal. Form for email.
            * [ ] API call to request password reset. Display confirmation message.
            * [ ] Create "Reset Password" page (accessed via email link). Form for email, new password, password confirmation. Token will be part of the URL.
            * [ ] API call to reset password. On success, redirect to Login page with success message.
    * [ ] **Logout Process:**
        * **Backend:**
            * [ ] API endpoint for logout (e.g., POST `/api/logout`).
            * [ ] Invalidate current user's session/token (Sanctum `logout` method).
        * **Frontend:**
            * [ ] Logout button in Navbar/Profile dropdown.
            * [ ] Display **popup confirmation** ("Are you sure you want to logout?") before proceeding.
            * [ ] On confirmation, call logout API.
            * [ ] Clear client-side user state and redirect to Login page.
    * [ ] **Automatic Logout (Inactivity):**
        * **Backend:**
            * [ ] Middleware to update `last_active_at` on authenticated API requests.
        * **Frontend:**
            * [ ] Implement client-side timer (e.g., 15 minutes).
            * [ ] On timeout, call logout API automatically or prompt user to stay logged in.
            * [ ] If user closes browser/tab, session should ideally be cleared on server based on session lifetime or require re-login.
    * [ ] **Terms & Conditions (T&C) Management & Acceptance:**
        * **Backend:**
            * [ ] Create `terms_and_conditions` table (id, content, version, created_at, updated_at).
            * [ ] API endpoint for admin to update T&C content (e.g., PUT `/api/admin/terms-conditions`).
            * [ ] API endpoint to fetch current T&C (e.g., GET `/api/terms-conditions`).
            * [ ] (Consider logging T&C acceptance: `user_tnc_acceptance` table - user_id, tnc_version, accepted_at).
        * **Frontend:**
            * [ ] After successful login & redirection to Home/Welcome (and after initial password change if applicable), display a **modal popup** with T&C content fetched from API.
            * [ ] "Accept" button: Closes modal, user proceeds. (Log acceptance if implemented).
            * [ ] "Decline" button: Calls logout API, redirects to Login page.
            * [ ] This modal must appear on every successful login session until accepted for that session or based on a "don't show again for this session" cookie after first acceptance.
    * [ ] **Admin IP Unblocking (Placeholder for full Admin Panel):**
        * **Backend:**
            * [ ] API endpoint for admin to list blocked IPs (e.g., GET `/api/admin/blocked-ips`).
            * [ ] API endpoint for admin to unblock an IP (e.g., DELETE `/api/admin/blocked-ips/{ip_address}`).
        * **Frontend (Admin Panel Section):**
            * [ ] UI to display list of blocked IPs with an "Unblock" button.
    * [ ] **Testing:**
        * [ ] Unit tests for all new backend logic (User model, Auth controllers, services).
        * [ ] Feature tests for all API endpoints.
        * [ ] Frontend unit/component tests for Login, Change Password, Profile, T&C modal.
        * [ ] End-to-end tests for all user flows.

---

## Phase 2: Role-Based Access Control (RBAC) Implementation

* **Objective:** Implement a comprehensive RBAC system to control user access to application features and data.
* **Pre-Implementation Check:** Review general guidelines, overall plan, logical system, and config files.

* **Tasks:**
    * [ ] **Database Schema (PostgreSQL):**
        * [ ] Create `roles` table (id, name, display_name, description). Name should be unique (e.g., 'admin', 'manager', 'sales_user').
        * [ ] Create `permissions` table (id, name, display_name, description). Name should be unique (e.g., 'create-users', 'edit-content', 'view-reports').
        * [ ] Create `role_user` pivot table (user_id, role_id).
        * [ ] Create `permission_role` pivot table (permission_id, role_id).
        * [ ] Run migrations.
    * [ ] **Backend (Laravel):**
        * [ ] Create Eloquent models: `Role`, `Permission`.
        * [ ] Define relationships in models (`User` hasMany `Role`, `Role` hasMany `Permission`, etc.).
        * [ ] **Permissions Management (Admin API Endpoints):**
            * [ ] GET `/api/admin/permissions` (List all permissions).
            * [ ] POST `/api/admin/permissions` (Create new permission: name, display_name, description).
            * [ ] PUT `/api/admin/permissions/{id}` (Update permission).
            * [ ] DELETE `/api/admin/permissions/{id}` (Delete permission).
        * [ ] **Roles Management (Admin API Endpoints):**
            * [ ] GET `/api/admin/roles` (List all roles).
            * [ ] POST `/api/admin/roles` (Create new role: name, display_name, description).
            * [ ] PUT `/api/admin/roles/{id}` (Update role).
            * [ ] DELETE `/api/admin/roles/{id}` (Delete role).
            * [ ] POST `/api/admin/roles/{id}/permissions` (Assign/sync permissions to a role).
        * [ ] **User Role Assignment (Admin API Endpoints - extend User Management):**
            * [ ] GET `/api/admin/users/{id}/roles` (Get roles for a user).
            * [ ] PUT `/api/admin/users/{id}/roles` (Assign/sync roles to a user).
        * [ ] **Authorization Logic:**
            * [ ] Implement Laravel Gates and/or Policies for all relevant models and actions.
            * [ ] Examples: `UserPolicy` (can view, create, update, delete users), `ContentPolicy`, `MenuPolicy`.
            * [ ] Use these policies in controllers to authorize actions.
            * [ ] Create middleware for route-level role/permission checks if needed.
        * [ ] **Seeders:**
            * [ ] Create `RolesSeeder` (e.g., 'Admin', 'Manager', 'User').
            * [ ] Create `PermissionsSeeder` for all core permissions.
            * [ ] Seed default role-permission assignments (e.g., Admin gets all permissions).
            * [ ] Assign 'Admin' role to the initial admin user.
    * [ ] **Frontend (Vue.js - Admin Panel Section):**
        * [ ] Create UI sections for managing Permissions:
            * List permissions with options to Create, Edit, Delete.
            * Forms for creating/editing permissions.
        * [ ] Create UI sections for managing Roles:
            * List roles with options to Create, Edit, Delete.
            * Forms for creating/editing roles.
            * Interface to assign permissions to roles (e.g., checklist of permissions).
        * [ ] Extend User Management UI (Admin Panel):
            * Interface to assign roles to users (e.g., checklist or multi-select).
        * [ ] **UI Behavior based on RBAC:**
            * Conditionally render UI elements (buttons, menu items) based on user permissions fetched from backend or determined client-side after login (if permissions are part of user payload).
            * Protect Vue routes using navigation guards that check user roles/permissions.
            * Display access rights/roles as **badges** in user listings or profile views if required.
    * [ ] **Testing:**
        * [ ] Unit tests for Role, Permission models and relationships.
        * [ ] Feature tests for all RBAC API endpoints.
        * [ ] Test authorization logic (Gates/Policies) thoroughly.
        * [ ] Frontend tests for admin panel RBAC UIs.
        * [ ] End-to-end tests: Log in as different roles and verify access restrictions.

---

## Phase 3: Menu & Content Management

* **Objective:** Enable dynamic management of application navigation and content by authorized users.
* **Pre-Implementation Check:** Review general guidelines, overall plan, logical system, and config files.

* **Tasks:**
    * [ ] **Menu Management:**
        * **Database Schema (PostgreSQL):**
            * [ ] Create `menus` table (id, parent_id nullable, name, type ['list_menu', 'content_menu'], icon (Font Awesome class), route_or_url, content_id nullable, order, role_permissions_required (JSON or related table)).
            * [ ] Run migrations.
        * **Backend (Laravel):**
            * [ ] Create `Menu` Eloquent model with self-referencing relationship for parent/child.
            * [ ] API endpoints for Menu Management (CRUD by admin/privileged users):
                * GET `/api/menus` (Fetch menus accessible by the current user, respecting RBAC, for frontend rendering).
                * GET `/api/admin/menus` (Fetch all menus for management).
                * POST `/api/admin/menus` (Create menu item).
                * PUT `/api/admin/menus/{id}` (Update menu item).
                * DELETE `/api/admin/menus/{id}` (Delete menu item).
                * (Optional) Endpoint to reorder menus.
            * [ ] Logic to handle menu hierarchy (parent > child > content, parent > content, multi-level dropdowns).
            * [ ] Ensure menu API respects RBAC for managing and accessing menus.
        * **Frontend (Vue.js):**
            * [ ] **Menu Rendering:**
                * Fetch accessible menus from `/api/menus` after login.
                * Dynamically render the main navigation (Navbar/Horizontal Navbar) based on fetched menu data, including Font Awesome icons and hierarchical structures.
            * [ ] **Menu Management UI (Admin Panel / Privileged User Section):**
                * Interface to list, create, edit, delete menu items.
                * Form fields for: name, parent menu (dropdown), type (list/content), icon (Font Awesome picker/input), route/URL or content link, order, required roles/permissions.
    * [ ] **Content Management:**
        * **Database Schema (PostgreSQL):**
            * [ ] Create `contents` table (id, title, slug (unique), type ['custom', 'embed_url'], custom_content (TEXT type for HTML), embed_url_original (TEXT), embed_url_uuid (UUID, unique), created_by_user_id, updated_by_user_id).
            * [ ] Run migrations.
        * **Backend (Laravel):**
            * [ ] Create `Content` Eloquent model.
            * [ ] API endpoints for Content Management (CRUD by users with rights):
                * GET `/api/content` (List accessible content - respecting RBAC).
                * GET `/api/content/{slug_or_uuid}` (Fetch specific content - respecting RBAC).
                * POST `/api/content` (Create new content).
                * PUT `/api/content/{id}` (Update content).
                * DELETE `/api/content/{id}` (Delete content).
            * [ ] Logic for `custom` content type: Store HTML from text editor.
            * [ ] Logic for `embed_url` content type:
                * Generate UUIDv4 for `embed_url_uuid` on creation.
                * Implement middleware/logic for **encrypting/decrypting `embed_url_original`** (ensure this isn't directly exposed in API responses if sensitive, only use UUID for client-side linking to an internal route).
                * Create a dedicated route like `/app/embed/{uuid}` that loads the embed. This route must be protected by authentication. Accessing it without login redirects to the main login page.
            * [ ] Implement ContentPolicy to control access based on user permissions.
        * **Frontend (Vue.js):**
            * [ ] **Content Display Pages/Components:**
                * Component to render `custom` content (using `v-html` carefully, or a sanitizer if content is from less trusted users). Apply narrow margin styling.
                * Component to render `embed_url` content (iframe filling full width of content area). Use the `/app/embed/{uuid}` route.
            * [ ] **Content Management UI (Accessible by Privileged Users):**
                * Interface to list, create, edit, delete content.
                * Form for content creation/editing:
                    * Fields for title, slug (auto-generate from title, allow edit).
                    * Type selection: "Custom Content" or "Embed Page URL".
                    * If "Custom Content": Integrate **Free Text Edit Editor** (e.g., Quill, TinyMCE, CKEditor) supporting HTML, static text, image uploads/linking, YouTube video embeds, document links (PDF, Word, Excel, PowerPoint), hyperlink buttons, code insertion.
                    * If "Embed Page URL": Field for original external URL.
            * [ ] Implement **content load animations**.
    * [ ] **Testing:**
        * [ ] Unit tests for Menu and Content models.
        * [ ] Feature tests for all Menu and Content API endpoints, including RBAC checks.
        * [ ] Test embed URL encryption/decryption and secure access.
        * [ ] Frontend tests for menu rendering and content management UIs.
        * [ ] Test text editor functionalities.
        * [ ] End-to-end tests for creating, viewing, and managing menus and content with different user roles.

---

## Phase 4: Notifications & Communication

* **Objective:** Implement in-app notifications and manageable email templates for system communications.
* **Pre-Implementation Check:** Review general guidelines, overall plan, logical system, and config files.

* **Tasks:**
    * [ ] **In-App Notification System:**
        * **Database Schema (PostgreSQL):**
            * [ ] Create `notifications` table (id, title, content (TEXT for HTML), created_by_user_id, created_at).
            * [ ] Create `user_notifications` pivot table (user_id, notification_id, read_at nullable, created_at).
        * **Backend (Laravel):**
            * [ ] Create `Notification` Eloquent model.
            * [ ] API endpoints for Notification Management (CRUD by users with rights):
                * POST `/api/notifications` (Create new notification). New notifications are distributed to all current users (populate `user_notifications`).
                * PUT `/api/notifications/{id}` (Update notification).
                * DELETE `/api/notifications/{id}` (Delete notification).
            * [ ] API endpoints for User Notifications:
                * GET `/api/user/notifications` (Fetch notifications for the logged-in user, with read status, perhaps paginated). Include unread count.
                * POST `/api/user/notifications/{id}/mark-read` (Mark specific notification as read).
                * POST `/api/user/notifications/mark-all-read` (Mark all as read).
        * **Frontend (Vue.js):**
            * [ ] **Navbar Integration:**
                * Display **bell icon** in the navbar.
                * Fetch unread notification count and display it (e.g., as a badge on the bell).
                * Implement **pulse dot animation** on the bell icon if there are unread notifications.
                * Clicking bell icon shows a dropdown list of recent notification titles, distinguishing read/unread.
                * Clicking a title navigates to a notification detail page/modal and calls API to mark as read.
            * [ ] **Notification Detail View:** Page or modal to display full notification content.
            * [ ] **Notification Management UI (Admin Panel / Privileged User Section):**
                * Interface to list, create, edit, delete system notifications.
                * Form for notification content (can use a simplified rich text editor or allow basic HTML for text, images, YouTube, docs, buttons).
    * [ ] **Email Template Management:**
        * **Database Schema (PostgreSQL):**
            * [ ] Create `email_templates` table (id, name (unique, e.g., 'user_invitation', 'password_reset'), subject, body (TEXT for HTML), created_by_user_id, type ['system_default_overrideable', 'user_created']).
        * **Backend (Laravel):**
            * [ ] Create `EmailTemplate` Eloquent model.
            * [ ] API endpoints for Email Template Management (CRUD by users with rights):
                * GET `/api/email-templates`
                * POST `/api/email-templates`
                * PUT `/api/email-templates/{id}`
                * DELETE `/api/email-templates/{id}`
            * [ ] Logic in Laravel Mailables to:
                * Check for a user-defined template from the database based on a specific key (e.g., 'user_invitation').
                * If not found, use a hard-coded system default template.
                * Parse template `body` and `subject` for dynamic placeholders (e.g., `{{userName}}`, `{{resetLink}}`) and replace them with actual data before sending.
        * **Frontend (Vue.js - Admin Panel / Privileged User Section):**
            * [ ] Interface to list, create, edit, delete email templates.
            * [ ] Form for template editing: name, subject, body (using a rich text editor or HTML editor that supports dynamic placeholders, static text, images, YouTube, docs, buttons). Explain available placeholders.
        * **Update existing email sending logic (Invitation, Password Reset) to use this template system.**
    * [ ] **Testing:**
        * [ ] Unit tests for Notification and EmailTemplate models.
        * [ ] Feature tests for all related API endpoints.
        * [ ] Test notification delivery and read/unread status updates.
        * [ ] Test email template rendering with dynamic data and fallback to system defaults.
        * [ ] Frontend tests for notification display and email template management UI.

---

## Phase 5: Home/Welcome Page (Dashboard)

* **Objective:** Develop a dynamic and informative dashboard page with various widgets.
* **Pre-Implementation Check:** Review general guidelines, overall plan, logical system, and config files.

* **Tasks:**
    * [ ] **Home/Welcome Page Structure (Vue.js Component):**
        * [ ] Create main component for Home/Welcome page (`views/pages/Dashboard.vue` or similar).
        * [ ] Implement responsive row-based layout as specified.
    * [ ] **Backend API Endpoints for Dashboard Widgets:**
        * [ ] Endpoint for Jumbotron content (GET `/api/dashboard/jumbotron`).
        * [ ] Endpoint for Marquee text (GET `/api/dashboard/marquee`).
        * [ ] Endpoint for User Login Stats (GET `/api/dashboard/login-stats` - e.g., data for last 15 days).
        * [ ] Endpoint for Top 5 Latest Notifications (GET `/api/dashboard/latest-notifications`).
        * [ ] Endpoint for Top 5 Users Online (GET `/api/dashboard/online-users` - requires session tracking).
        * [ ] Endpoint for Top 5 Frequently Logged-in Users (GET `/api/dashboard/frequent-users` - for last month).
        * [ ] Endpoint for Top 5 Frequently Visited Menus/Content (GET `/api/dashboard/frequent-content`).
        * (Note: Some of these may require new tracking mechanisms or tables if not already planned, e.g., for content visit frequency, user online status beyond `last_active_at`).
    * [ ] **Frontend Widget Implementation (Vue.js Components):**
        * [ ] **Jumbotron Widget (Row 1):**
            * Full-width auto-playing carousel with background images and text.
            * Fetch content from API.
        * [ ] **Digital Clock Widget (Row 1):**
            * Display current date and time, synced with user's local computer.
            * Updates every minute. (Client-side implementation).
        * [ ] **Line Area Chart Widget (Row 2):**
            * Use a free charting library (e.g., Chart.js, ApexCharts - Vuexy might include one).
            * Display user login counts for the last 15 days, fetched from API.
        * [ ] **Notification List Widget (Row 2):**
            * Display titles of Top 5 latest notifications, fetched from API.
        * [ ] **Top 5 Users Online Widget (Row 3):**
            * Display list of users, fetched from API.
        * [ ] **Top 5 Frequently Logged-in Users Widget (Row 3):**
            * Display list of users, fetched from API.
        * [ ] **Top 5 Frequently Visited Menus/Content Widget (Row 4):**
            * Display list of menu/content items, fetched from API.
        * [ ] **Scrolling Text (Marquee) Widget:**
            * Display manageable text with smooth animation, fetched from API.
    * [ ] **Manageable Content for Dashboard Elements (Admin Task - see Phase 6):**
        * Ensure Jumbotron and Marquee content can be managed via the System Configuration module.
    * [ ] **Testing:**
        * [ ] Feature tests for all new dashboard API endpoints.
        * [ ] Logic tests for data aggregation on the backend if complex.
        * [ ] Frontend component tests for each widget.
        * [ ] Test responsiveness and data accuracy of the dashboard.

---

## Phase 6: System Configuration (Admin Focus)

* **Objective:** Allow administrators to manage global application settings and dashboard content.
* **Pre-Implementation Check:** Review general guidelines, overall plan, logical system, and config files.

* **Tasks:**
    * [ ] **Database Schema (PostgreSQL):**
        * [ ] Create `system_configurations` table (key (unique), value (TEXT or JSON)).
        * Alternatively, extend existing tables or create specific tables for Jumbotron items, Marquee messages if they are complex.
    * [ ] **Backend (Laravel):**
        * [ ] API endpoints for Admin to Get/Update configurations:
            * GET `/api/admin/configurations/{key}` or group (e.g., `/api/admin/configurations/homepage`).
            * PUT `/api/admin/configurations/{key}` or group.
        * Specific keys/groups for:
            * Jumbotron content (e.g., array of slides with image URLs, text, links).
            * Marquee text.
            * Application Logo URL/path.
            * Login Page Background Image URL/path.
            * Application Name.
            * Default Profile Photo URL/path.
            * Footer content (HTML or text).
        * Logic to handle image uploads for logo, background, default photo, Jumbotron images.
    * [ ] **Frontend (Vue.js - Admin Panel Section):**
        * [ ] Create "System Configuration" page for Admins.
        * [ ] Forms/UI elements for managing each configuration item:
            * **Homepage Config:** Interface to manage Jumbotron slides (add/edit/delete image, text, link) and Marquee text.
            * **Branding Config:** Upload new Application Logo, Login Page Background Image, Default Profile Photo. Input for Application Name.
            * **Layout Config:** Rich text editor or input for Footer content.
        * [ ] Ensure frontend fetches and displays current configurations in forms.
        * [ ] API calls to update configurations.
    * [ ] **Application Integration:**
        * [ ] Modify frontend layouts/components to fetch and display these configurations dynamically (e.g., App Logo in Navbar, App Name in title, Login Background, Footer).
        * [ ] Dashboard widgets (Jumbotron, Marquee) should use data managed here.
    * [ ] **Testing:**
        * [ ] Feature tests for configuration API endpoints.
        * [ ] Frontend tests for the admin configuration UI.
        * [ ] End-to-end tests: Admin changes a config, verify it reflects correctly in the application for users.

---

## Phase 7: UI/UX Enhancements & General Polish (Ongoing & Finalization)

* **Objective:** Refine the user interface and experience, ensuring all animations, transitions, and interactions are smooth and professional.
* **Pre-Implementation Check:** Review general guidelines, overall plan, logical system, and config files. This phase is about iterating and polishing existing implementations.

* **Tasks:** (These tasks are often iterative and applied across components built in earlier phases)
    * [ ] **Animation & Transition Review:**
        * [ ] Review all page transitions – ensure they are smooth and use defined loading animations.
        * [ ] Review animations on hover and click for interactive elements (buttons, cards, menu items, images).
        * [ ] Ensure marquee animation is smooth.
        * [ ] Review content load animations.
        * [ ] Ensure animations for opening/closing list menus, modals, and widgets are consistent and visually appealing.
    * [ ] **Flash Notification Polish:**
        * [ ] Review all flash notifications (success, error, warning after actions) for consistent styling, timing, and animation.
    * [ ] **Popup Confirmation Review:**
        * [ ] Verify all update, delete, and logout actions have clear, consistently styled popup confirmations.
    * [ ] **Responsiveness & Cross-Browser Testing:**
        * [ ] Thoroughly test application responsiveness on various screen sizes (desktop, tablet, mobile).
        * [ ] Test on major supported browsers for consistency.
    * [ ] **Accessibility (A11y) Review (Basic):**
        * [ ] Check for keyboard navigability.
        * [ ] Ensure sufficient color contrast (especially with dark mode).
        * [ ] Alt text for important images.
        * [ ] Semantic HTML where appropriate.
    * [ ] **Performance Optimization (Frontend):**
        * [ ] Review bundle sizes, identify opportunities for code splitting if needed.
        * [ ] Optimize images.
        * [ ] Minimize re-renders in Vue components.
    * [ ] **Consistency Checks:**
        * [ ] Ensure consistent use of Font Awesome icons.
        * [ ] Consistent styling for forms, buttons, typography.
        * [ ] Consistent implementation of dark mode across all custom components.
        * [ ] Verify narrow margins for all standard content pages and full-width for embed URLs.
    * [ ] **User Feedback Incorporation:**
        * [ ] If any user feedback is available from interim reviews, address relevant UI/UX points.

---

## Phase 8: Testing & Deployment

* **Objective:** Conduct final comprehensive testing and deploy the application to the production environment.
* **Pre-Implementation Check:** Review general guidelines, overall plan, logical system, and config files. Ensure all previous phases are complete and signed off.

* **Tasks:**
    * [ ] **Comprehensive Testing (Final Rounds):**
        * [ ] **Functional Testing:** Execute all test cases, ensure all features work as per requirements.
        * [ ] **Security Testing:**
            * Perform vulnerability scanning.
            * Review authentication & authorization logic for loopholes.
            * Test for common web vulnerabilities (XSS, SQLi, CSRF, etc.).
            * Verify data protection measures.
        * [ ] **Performance Testing (Backend & Frontend):**
            * Load testing API endpoints.
            * Stress testing critical functionalities.
            * Analyze database query performance under load (PostgreSQL).
            * Frontend performance profiling (rendering, script execution).
        * [ ] **User Acceptance Testing (UAT):**
            * Prepare UAT environment and test data.
            * Conduct UAT with stakeholders/client.
            * Collect and address UAT feedback.
    * [ ] **Deployment Preparation:**
        * [ ] **Production Environment Setup:**
            * Configure production server (OS, web server - Nginx/Apache, PHP, Node.js).
            * Set up and secure PostgreSQL production database.
            * Configure environment variables (`.env`) for production (database credentials, API keys, mail server, `APP_ENV=production`, `APP_DEBUG=false`).
        * [ ] **Build & Compilation:**
            * Run `composer install --optimize-autoloader --no-dev`.
            * Run `npm run build` (or `yarn build`) to compile frontend assets.
        * [ ] **Deployment Strategy:**
            * Choose deployment method (e.g., manual, Capistrano, Docker, CI/CD pipeline).
            * Create deployment scripts/pipeline.
        * [ ] **Backup Strategy:** Implement database and application file backup procedures.
    * [ ] **Deployment:**
        * [ ] Execute deployment to production server.
        * [ ] Run production database migrations and seeders (for essential initial data).
        * [ ] Configure web server to point to the application.
        * [ ] Set up SSL/TLS certificate (HTTPS).
    * [ ] **Post-Deployment Checks:**
        * [ ] Thoroughly test all critical functionalities on the production environment.
        * [ ] Monitor server logs and application logs for errors.
        * [ ] Perform initial performance checks on production.
    * [ ] **Documentation Finalization:**
        * [ ] Update any deployment guides or operational manuals.
        * [ ] Ensure all technical documentation is complete.
        * [ ] Prepare user guides if required.
