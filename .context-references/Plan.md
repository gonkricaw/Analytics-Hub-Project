# Application Development Plan: Indonet Analytics Hub

**Base Template:** Vuexy - Laravel + Vuejs Admin Dashboard Template

## I. Project Vision
To build a secure, intuitive, and feature-rich "Indonet Analytics Hub" platform, enabling controlled user management, dynamic and diverse content delivery, and flexible system configuration, with a modern and engaging user experience. The application will be designed exclusively with a dark mode theme and will be fully responsive across all devices.

## II. Development Strategy
Development will be carried out поэтапno (phased), starting with core and fundamental features, then progressing to supporting features and refinements. Leveraging the `Vuexy - Laravel + Vuejs Admin Dashboard Template` will accelerate basic UI development and frontend-backend integration. Adherence to the technical specifications and development standards outlined below is crucial for project success. All UI elements must feature rich animations and transitions for a visually engaging experience.

## III. Key Technical Specifications & Development Standards

* **Database Technology:**
    * The primary database for this project will be **PostgreSQL**. All development and deployment environments must be configured accordingly.

* **Database Migrations & Seeding:**
    * For any additions or modifications to table columns, the corresponding `Create Table` or `Alter Table` migration file in Laravel Artisan must be updated. After updating, migrations must be re-run to apply changes to the database schema.
    * **Comprehensive seeders** must be created for all generated tables. Seeders should populate tables with necessary initial data (e.g., default roles, permissions, system configurations) and sample data suitable for development and testing environments.

* **Configuration File Consistency & Management:**
    * It is imperative to consistently check and maintain the following project configuration files. They must align with the base Vuexy template structure and be appropriately updated to reflect new additions or changes during implementation:
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
    * Any deviations or new configurations must be documented and justified.

* **UI Implementation Standard:**
    * **All User Interface (UI) elements must be well-implemented**. This includes adherence to the design specifications (Dark Mode Only, responsive full-width design, smooth animations for marquee, content transitions, hovers, clicks, and loading processes), usability, and overall visual polish. Pop-up confirmations will be required for all update, delete, and logout actions. All icons used must be from **Font Awesome**.

* **Project Folder Structure (Reference: Vuexy Vuejs Laravel Template):**
    ```
    vuexy-vuejs-laravel-template/
    ├── app                      # Controllers and Models
    ├── bootstrap                # Contains cache and app.php
    ├── config                   # Application's configuration files
    ├── database                 # Migrations, model factories, & seeds
    ├── public                   # index.php ,static folder & Build
    │   ├── images/              # Public images
    │   ├── favicon.ico          # Favicon
    │   └── index.php            # Main php file
    ├── resources                # Views, Layouts, store and vue.js components
    │   ├── images/              # Include all images
    │   ├── styles/              # Include all styles files
    │   ├── {js/ts}/             # Include all vue files
    │   └── views/               # Contain Blade templates
    ├── routes/                  # Include Routes Web.php
    ├── storage/                 # Contains compile blade templates
    ├── tests/                   # For testing
    ├── .editorconfig            # Related with your editor
    ├── .env.example             # Include Database credentials and other environment variables
    ├── .gitattributes           # Give attributes to path names
    ├── .gitignore               # Files and Directories to ignore
    ├── .stylelintrc.json        # Style related file
    ├── .eslintrc.js             # ESLint Configuration
    ├── auto-imports.d.ts        # Unplugin auto import file
    ├── components.d.ts          # Unplugin vue components
    ├── artisan                  # Include artisans commands
    ├── shims.d.ts               # Typescript only
    ├── composer.json            # Dependencies used by composer
    ├── package.json             # Dependencies used by node
    ├── env.d.ts                 # Typescript only
    ├── themeConfig.ts           # Theme Customizer
    ├── tsconfig.json            # Typescript only file
    ├── jsconfig.json            # Javascript only file
    ├── phpunit.xml              # Related With testing
    ├── server.php               # For php's internal web server
    └── vite.config.ts           # Laravel's vite file
    ```
    *Developers must adhere to this structure for consistency and maintainability.*

---

## IV. Phased Development Plan

### Phase 1: Application Foundation & Core User Management

* **Main Objective:** To build a stable application foundation with a complete and secure user management and authentication system, utilizing PostgreSQL. This phase focuses on establishing how users access and are managed within the system. The application will directly open to a login page, as there will be no public registration page.
* **Key Feature Scope:**
    1.  **Project Setup & Basic UI:**
        * Initialize Laravel and Vuexy project with **PostgreSQL** database configuration.
        * Configure environment (`.env`) for database, mail, etc.
        * Apply global theme: **Dark Mode Only**.
        * Basic navigation structure: A **Sticky Navbar or Horizontal Navbar** will be used for the application logo, menu list, user avatar/profile access, and notification bell. No sidebar will be used for menu navigation after login.
        * Integrate **Font Awesome icons** for all iconographic needs.
        * Implement global **loading process animations** for page loads and data fetching.
    2.  **Comprehensive User Management & Authentication:**
        * **User Addition & Invitations (Admin Functionality):**
            * Administrators will be able to add new users by providing their details (e.g., name, email).
            * Upon addition, the system will automatically send an **email invitation** to the new user. This email will contain their registered email, a **temporary password**, and a unique access link to the application.
        * **Login Process:**
            * The login page will feature a **sidebar layout** (Left: Static Image/Branding, Right: Login Form with email and password fields).
            * Users access the application by entering their email and the provided password.
            * Display clear, animated **flash notifications** for login attempts: "Invalid password" for incorrect credentials, or similar informative messages.
            * Upon successful login, display an animated "Login successful!" flash message and redirect the user to the Home/Welcome page.
            * **IP Blocking:** Implement an automatic IP blocking mechanism if there are **15 consecutive failed login attempts** from the same IP address. Administrators must have an interface to view and manually unblock these IPs.
        * **Initial Login & Password Change:**
            * When a user logs in for the first time using a temporary password (from the invitation email), they will be immediately redirected to a dedicated "Change Password" page.
            * **Password Policy:** The new password must be a minimum of 8 characters and include a combination of uppercase letters, lowercase letters, and numbers.
            * After successfully changing the password, the user is redirected to the Home/Welcome page.
        * **User Profile Management:**
            * Users can access an "Edit Profile" page to change their **password** (adhering to the password policy) and **profile photo**.
            * The user's name and email address will be non-editable through this interface.
            * Profile avatars should be displayed as **circle images without borders**.
        * **Forgot Password Functionality:**
            * If a user forgets their password, they can use a "Forgot Password" link on the login page.
            * They will be prompted to enter their registered email address.
            * Upon confirmation, the system sends an email to the registered address containing a unique, **one-time use link** to reset the password. Clicking this link a second time will render it invalid.
            * The link directs the user to a "Change Password" page where they can set a new password (adhering to the password policy). After a successful password change, they are redirected back to the login page to log in with their new credentials.
        * **Logout:**
            * **Automatic Logout:** Users will be automatically logged out after **15 minutes of inactivity**.
            * **Manual Logout:** A logout option will be available. Before logging out manually, a **popup confirmation** ("Are you sure you want to logout?") will be displayed.
            * Closing the browser/tab will require the user to log in again upon returning.
    3.  **Terms & Conditions (T&C) Management & Acceptance:**
        * **T&C Content Management:** Only administrators can edit the content of the Terms and Conditions.
        * **T&C Acceptance:** Every time a user successfully logs in and lands on the Home/Welcome page, a **modal popup** will appear displaying the Terms and Conditions.
            * If the user clicks "Accept," the modal will close, and they can proceed to use the application.
            * If the user clicks "Decline," they will be automatically logged out and redirected to the login page.

* **Phase 1 Output:** Application with a secure and complete user lifecycle management (invitation, initial login, profile management, password recovery), robust authentication via PostgreSQL, and mandatory T&C acceptance. The basic UI framework (dark mode, navbar, login page style) is established and well-implemented with initial animations.

---

### Phase 2: RBAC Implementation & Fundamental Content Management

* **Main Objective:** To build a solid Role-Based Access Control (RBAC) system, enabling granular control over application features, and to develop the core capabilities for managing dynamic menus and diverse content types. All UI elements must be well-implemented with appropriate animations.
* **Key Feature Scope:**
    1.  **Full RBAC (Role-Based Access Control) System (Admin Functionality):**
        * **Permissions Management:** Administrators can define and manage individual permissions within the system (e.g., `content-create`, `user-edit`, `menu-delete`). These permissions represent specific actions.
        * **Roles Management:** Administrators can create, edit, and delete roles (e.g., "Manager," "Sales," "Editor"). For each role, administrators will assign a set of the defined permissions.
        * **User Role Assignment:** As part of user management, administrators will assign one or more roles to each user, thereby dictating their access capabilities.
        * **Content Access Control:** Roles will determine what content and features a user can access. For example, a "Manager" might see all content, while a "Sales" role might only see specific sections.
        * **Visual Indication:** User roles or key permissions might be displayed as **badges** in relevant UI sections (e.g., user lists).
    2.  **Dynamic Menu Management (Managed by Admin or Privileged Users):**
        * Users with appropriate permissions can create, edit, and delete menu items.
        * **Menu Types:** Menus can be either a "List Menu" (leading to a list of sub-items or content) or a "Content Menu" (directly leading to a specific piece of content).
        * **Iconography:** Each menu item **must** be associated with an icon from the **Font Awesome** library.
        * **Menu Structure:** The system must support hierarchical menu structures, including:
            * Parent Menu > Child Menu > Content Page
            * Parent Menu > Content Page
            * Multi-level dropdown menus.
        * Menus will be displayed in the main **Navbar or Horizontal Navbar**, not a sidebar.
    3.  **Fundamental Content Management (Managed by Users with Appropriate Permissions):**
        * Users with relevant rights can create, edit, and delete content.
        * **Content Types:**
            * **Custom Content:** Created using a **Free Text Edit Editor**. This editor must support rich text formatting, insertion of static text, images, embedded YouTube videos, links to documents (PDF, Word, Excel, PowerPoint allowing view/download), and creation of hyperlink buttons. It should also allow for inserting code snippets.
            * **Embed Page URL:** Allows embedding an external webpage directly into the application.
                * The embedded page will automatically display in **full-width** within the application's content area. Other content pages will use a **narrow margin** layout.
                * Each embeddable URL configuration will have a system-generated **UUIDv4** for its specific path within the application.
                * A middleware layer will **encrypt/decrypt the original external URL** to obscure it from the end-user in the browser's address bar when viewing the embedded content.
                * If an attempt is made to access the application's embed URL path directly from outside the application (i.e., without an active login session), the user will be redirected to the main login page.
        * Implement **content load animations** for a smoother user experience.

* **Phase 2 Output:** A robust application with a fully functional RBAC system controlling user access. Users can manage a dynamic navigation menu and create/manage diverse content types (rich text, embedded pages) according to their assigned roles and permissions. All UI interactions are enhanced with smooth animations.

---

### Phase 3: Communication Features & User Interaction

* **Main Objective:** To enhance user engagement and system communication through an internal notification system and manageable templates for transactional emails. UI interactions should be intuitive and visually responsive.
* **Key Feature Scope:**
    1.  **In-System Notification Management (Managed by Users with Appropriate Permissions):**
        * Users with rights can create, edit, and delete system-wide notifications.
        * **Notification Content:** Notifications can contain HTML, static text, images, embedded YouTube videos, links to documents, and hyperlink buttons.
        * **Distribution:** New notifications are sent to **all active users** within the system.
        * **Navbar Indicator:**
            * A **bell icon** in the main navbar will indicate notifications.
            * A **pulse dot animation** will appear on the bell icon if there are unread notifications.
            * Clicking the bell icon will display a dropdown list of notification titles, visually differentiating between "read" and "unread" notifications.
            * Clicking on a notification title in the dropdown will navigate the user to a detailed view of that specific notification.
    2.  **Email Template Management (Managed by Users with Appropriate Permissions):**
        * Users with rights can create, edit, and delete templates for various system-generated emails.
        * **Template Types:** Examples include "User Invitation," "Password Reset," "System Warning," etc. These are for **system-related functions/modules, not for email marketing campaigns**.
        * **Template Content:** Templates can be built using HTML and include placeholders for **dynamic values** (e.g., user's name, temporary password, reset link), static text, images, embedded YouTube videos, links to documents, and hyperlink buttons.
        * **Default Templates:** If a user-defined template for a specific system action has not been created, the system will use a pre-defined, hard-coded default template for that action.

* **Phase 3 Output:** The application will feature a comprehensive in-system notification system to keep users informed and a flexible email template management system for customizing transactional communications. User interactions with these systems will be clear and visually cued.

---

### Phase 4: Main Page (Dashboard) Development & Global Configuration

* **Main Objective:** To create a dynamic and informative Home/Welcome page (dashboard) that provides users with relevant insights and quick access to key information. Additionally, to provide administrators with tools for global application configuration. All dashboard widgets and configuration interfaces must be well-implemented and visually appealing.
* **Key Feature Scope:**
    1.  **Interactive Home/Welcome Page (Dashboard):**
        * The content displayed on the Home/Welcome page should be generally manageable by administrators or users with specific permissions.
        * **Layout Structure (Single Page, Row-Based):**
            * **Row 1:**
                * **Jumbotron Widget:** A full-width, auto-playing carousel featuring background images and overlay text. Content (images, text, carousel settings) is manageable by admins/privileged users.
                * **Digital Clock Widget:** Displays the current date and time, synchronized with the user's local computer time, updating every minute.
            * **Row 2:**
                * **Line Area Chart Widget:** A free, visually appealing line area chart displaying the number of user logins over the last 15 days.
                * **Notification List Widget:** Displays the titles of the top 5 latest unread or most recent system notifications.
            * **Row 3:**
                * **Top 5 Users Online Widget:** Lists the top 5 users currently active in the system (requires a mechanism to track active sessions).
                * **Top 5 Frequently Logged-in Users Widget:** Lists the top 5 users who have logged in most frequently in the last month.
            * **Row 4:**
                * **Top 5 Frequently Visited Menus/Content Widget:** Lists the top 5 menu items or content pages most frequently accessed by users.
        * **Scrolling Text (Marquee):** A smoothly animated marquee displaying custom messages, manageable by admins/privileged users.
    2.  **System Configuration Management (Admin Functionality):**
        * Interface for managing the content of the **Jumbotron and marquee text** displayed on the Home page.
        * Ability to change the main **application logo** (used in navbar, login page, etc.).
        * Ability to change the **application name** (displayed in titles, headers, etc.).
        * Ability to set/change the **default profile photo** used for new users or users who haven't uploaded their own.
        * Ability to manage the content of the application's **footer**.
        * Ability to change the **background image of the login page**.

* **Phase 4 Output:** A dynamic and informative Home/Welcome dashboard providing key metrics and updates. Administrators have a dedicated section to configure global aspects of the application's appearance and behavior. All elements are visually polished and incorporate smooth animations.

---

### Phase 5: UI/UX Refinement, Comprehensive Testing, & Deployment

* **Main Objective:** To ensure the application is of high quality in terms of user experience, functionality, security, and is ready for launch. This involves a final polish of all UI elements, rigorous testing, and a smooth deployment process.
* **Key Feature Scope/Activities:**
    1.  **UI/UX Refinement:**
        * Thorough review and refinement of all **visual animations and transition effects** across the application (e.g., content loading, menu opening/closing, widget interactions, page switches, button hover/click effects) to ensure they are smooth, intuitive, and enhance the user experience.
        * Finalize **loading process animations** and **content load animations** for consistency and visual appeal.
        * Ensure **flash notification animations** (after all CUD actions, login, etc.) are informative and well-timed.
        * Verify all **popup confirmations** (for update, delete, logout actions) are clear and consistently implemented.
        * Final check for consistent and appropriate use of **Font Awesome icons**.
        * Optimize **responsiveness** and visual fidelity across a range of target devices and screen sizes.
        * Ensure all UI elements are meticulously **well-implemented** according to the overall design language (Dark Mode, full-width where appropriate, narrow margins for standard content).
    2.  **Comprehensive Testing:**
        * **Functional Testing:** Verify every feature and user flow against the defined requirements.
        * **Security Testing:** Conduct vulnerability assessments, focusing on authentication, authorization, data validation (to prevent XSS, SQL injection), session management, and protection of sensitive data, particularly related to PostgreSQL.
        * **Performance Testing:** Evaluate application load times, server response times, database query efficiency, and UI responsiveness under normal and peak load conditions.
        * **Usability Testing:** Gather feedback from test users on the ease of use, intuitiveness, and overall user experience.
        * **User Acceptance Testing (UAT):** Formal testing by stakeholders or client representatives to confirm the application meets their requirements and is fit for purpose.
    3.  **Deployment Preparation and Execution:**
        * Configure the production server environment, including PostgreSQL database setup, web server, PHP, and all necessary dependencies.
        * Develop and test the application deployment process (e.g., using CI/CD pipelines, scripts).
        * Perform final data migration and run seeders for initial production data, if applicable.
        * Deploy the application to the production environment.
    4.  **Post-Deployment:**
        * Conduct initial monitoring of the live application for performance, errors, and unexpected behavior.
        * Establish a process for promptly addressing any bugs or critical issues that arise post-launch.

* **Phase 5 Output:** A stable, secure, thoroughly tested, and high-performance "Indonet Analytics Hub" application with an optimal and meticulously well-implemented user experience, successfully deployed to the production environment and ready for users.

---

## V. Cross-Phase Aspects
* **Security:** Security considerations (input validation, output encoding, proper authentication/authorization checks, secure API endpoints, protection against common web vulnerabilities) will be integral to the design and implementation of every feature throughout all phases, with specific attention to PostgreSQL security practices.
* **Code Quality:** Adherence to Laravel and Vue.js best practices, clean code principles, proper commenting, and maintaining consistency with the Vuexy template's file structure and coding standards will be enforced. Regular code reviews are recommended.
* **Documentation:** Internal technical documentation for code (especially complex logic or APIs), functionalities, and any significant architectural decisions or new configurations will be maintained.
* **Version Control (Git):** Consistent use of Git for source code management, employing meaningful commit messages and potentially a branching strategy (e.g., Gitflow) to manage development, features, and releases.
