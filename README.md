# Indonet Analytics Hub Platform

Indonet Analytics Hub is a role-based web application designed to deliver dynamic analytical content, system notifications, and comprehensive user management. Built with a modern tech stack, it prioritizes efficiency, security, and an optimal user experience. The application is structured to provide a seamless interface for both administrators and users, allowing for easy content management, user role assignments, and system notifications.

<div align="center">
  
[![GitHub](https://img.shields.io/badge/GitHub-IndonetAnalyticsHub-blue.svg)](https://github.com/IndonetAnalyticsHub)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](https://opensource.org/licenses/MIT)
[![Version](https://img.shields.io/badge/version-1.0.0-orange.svg)](https://github.com/IndonetAnalyticsHub)
[![Last Updated](https://img.shields.io/badge/last__updated-2025--05--15-red.svg)](https://github.com/IndonetAnalyticsHub)
[![Contributors](https://img.shields.io/badge/contributors-5-yellow.svg)](https://github.com/IndonetAnalyticsHub)
[![Documentation](https://img.shields.io/badge/documentation-available-brightgreen.svg)](https://github.com/IndonetAnalyticsHub)
[![Issues](https://img.shields.io/badge/issues-welcome-red.svg)](https://github.com/IndonetAnalyticsHub)
[![Pull Requests](https://img.shields.io/badge/pull_requests-welcome-brightgreen.svg)](https://github.com/IndonetAnalyticsHub)
[![Build Status](https://img.shields.io/badge/build-passing-brightgreen.svg)](https://github.com/IndonetAnalyticsHub)

</div>




[![Indonet Analytics Hub](https://cdn.qwenlm.ai/output/144093b4-3007-49f6-89bc-fbdcce557939/t2i/b034206f-939d-4f2b-8017-708399706cc7/7cdd18cf-5ca1-45a1-975b-71e3b515d40c.png?key=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJyZXNvdXJjZV91c2VyX2lkIjoiMTQ0MDkzYjQtMzAwNy00OWY2LTg5YmMtZmJkY2NlNTU3OTM5IiwicmVzb3VyY2VfaWQiOiI3Y2RkMThjZi01Y2ExLTQ1YTEtOTc1Yi03MWUzYjUxNWQ0MGMiLCJyZXNvdXJjZV9jaGF0X2lkIjpudWxsfQ.7hSdx7Llz_DLdBoI3YH5q2v4n8MKkC8iOFBCB_n8Tb0)](https://github.com/IndonetAnalyticsHub)

---

## Table of Contents

- [Project Overview](#project-overview)
- [Key Features](#key-features)
- [Technology Stack](#technology-stack)
  - [Backend](#backend)
  - [Frontend](#frontend)
  - [Database](#database)
  - [Development Tools](#development-tools)
- [Responsive Design](#responsive-design)
- [Documentation](#documentation)
  - [Frontend](#frontend)
  - [Security & Performance](#security--performance)
- [Design & UI Highlights](#design--ui-highlights)
- [Core System Logic](#core-system-logic)
- [Project Architecture Overview](#project-architecture-overview)
- [Credits](#credits)
- [License](#license)

---

## Project Overview

The Indonet Analytics Hub is a web application designed to provide a centralized platform for managing and delivering analytical content, notifications, and user management. The application is built using the Laravel framework for the backend and Vue.js for the frontend, ensuring a modern and responsive user experience.

The application is designed to be role-based, allowing for different levels of access and functionality based on user roles. This ensures that sensitive information and critical functionalities are only accessible to authorized users, enhancing security and data integrity.

The application features a dynamic content management system, allowing administrators to create and manage various types of content, including HTML, text, images, videos, and documents. Additionally, the application includes a robust user management system, enabling administrators to control user access and permissions effectively.

The application also includes a notification system, allowing administrators to create and manage system-wide notifications that are visible to all users. This ensures that users are always informed about important updates and changes within the system.

The Indonet Analytics Hub is designed to be user-friendly and intuitive, with a focus on providing a seamless experience for both administrators and users. The application is built with modern technologies and follows best practices in web development, ensuring that it is maintainable, scalable, and secure.

The application is designed to be responsive and user-friendly, ensuring that users can access the information they need quickly and efficiently. The use of modern technologies like Vue.js and Laravel ensures that the application is not only powerful but also maintainable and scalable.

---

## Key Features

* **Role-Based Access Control (RBAC):** Granular control over user permissions and access to specific content, menus, and functionalities based on assigned roles (e.g., Admin, Manager, Sales).
* **Dynamic Content Management:** Admins and authorized users can create, manage, and display various content types, including HTML, text, images, embedded videos (YouTube), documents (PDF, Word, Excel, PPT), and secure external URL embeds.
* **User Management:**
    * Admin-controlled user creation (via email invitation with temporary password).
    * Profile management (users can update password and photo).
    * Secure login with IP blocking after multiple failed attempts.
    * "Forgot Password" functionality with email-based reset.
    * Auto-logout after a period of inactivity.
* **System Notifications:** Admins can create and manage system-wide notifications (text, images, videos, documents, hyperlinks) visible to all users via a navbar indicator.
* **Menu Management:** Dynamic, multi-level menu creation and management, with visibility controlled by user roles.
* **Terms & Conditions Management:** Admins can manage T&C, which users must accept upon each login.
* **Email Template Management:** Customizable HTML email templates for invitations, password resets, warnings, etc.
* **Configurable Home Page:** Features a marquee, jumbotron (carousel), digital clock, and various dynamic widgets (Top Users, Notifications, Login Charts, Popular Menus).
* **System Configuration:** Admins can customize application logo, name, login background, default profile photo, and footer.

---

## Technology Stack

### Backend

| Technology | Description/Version |
|------------|---------------------|
| Framework  | Laravel 11.x        |
| Language   | PHP 8.2 (or higher) |
| Database   | PostgreSQL          |
| Cache & Queues | Redis           |
| API        | RESTful APIs for frontend communication |
| Testing    | PHPUnit             |

### Frontend

| Technology | Description/Version |
|------------|---------------------|
| Framework | Vue.js 3.5.13 |
| UI Component Library | Vuetify 3.7.5 |
| State Management | Pinia 2.3.0 |
| Routing | Vue Router 4.5.0 |
| Build Tool | Vite 5.4.11 (via `laravel-vite-plugin`) |
| Animation | GSAP (GreenSock Animation Platform) |
| CSS Pre-processor | SASS |
| Data Visualization | ApexCharts |
| Internationalization | Vue I18n (setup available) |

### Security & Performance

* **Security:** Built-in security features of Laravel, including CSRF protection, XSS protection, and SQL injection prevention.
* **Performance:** Optimized for performance with caching, queue management, and efficient database queries.
* **Testing:** Comprehensive testing suite using PHPUnit for backend and Jest for frontend, ensuring code quality and reliability.
* **Version Control:** Git for version control, with a structured branching strategy for development and production releases.
* **Deployment:** Docker support for containerized deployment, ensuring consistency across development, testing, and production environments.
* **Monitoring & Logging:** Integrated logging and monitoring tools for tracking application performance and errors.
* **Documentation:** Comprehensive documentation for both backend and frontend, including API documentation, setup instructions, and user guides.
* **Testing & Development Tools:**
    * MSW (Mock Service Worker) for API mocking during frontend development.
    * ESLint and Stylelint for code quality and consistency.
    * PHPUnit for backend testing.
* **Deployment Tools:** Vite + Laravel integration via `laravel-vite-plugin` for efficient asset management and deployment.
* **Notable Features:**
    * Comprehensive UI Components: Cards, dialogs, form elements, charts, etc.
    * Theme Customization: Light/dark mode and color themes.
    * Multi-language Support: i18n integration.
    * Mock API: MSW for frontend development without backend.
    * App Demos: Email, Kanban board, etc.
    * Front Pages: Landing page, pricing plans, etc.
* **authentication:** Secure user authentication with email invitations, mandatory first-time password changes, and strict role-based authorization controlling access to all resources and actions. Terms & Conditions must be accepted on each login.
* **Dynamic Content Delivery:** Content and menu visibility are dynamically adjusted based on the logged-in user's roles and permissions, ensuring users only see relevant information.
* **Notification Flow:** System notifications are created by admins and pushed to all users, with an indicator in the navbar. Users can view a list and details of notifications.
* **Admin Control:** Admins have comprehensive control over user management, role/permission assignments, content, menus, system configurations, T&C, and email templates.
* **Responsive Design:** Ensures optimal viewing and interaction across various devices and screen sizes.
* **Animations:** Subtle and purposeful animations (using GSAP and CSS transitions) to enhance user experience, including hover effects, page transitions, and notification indicators (e.g., pulse animation).
* **Iconography:** Font Awesome for clear and consistent visual cues.
* **Design System:** The design system is built using Vuetify, a popular Vue.js UI library, ensuring a consistent and responsive design across the application. The design system includes a comprehensive set of components, styles, and guidelines for building user interfaces that are both visually appealing and functional.

---

## Design & UI Highlights

* **Theme:** Exclusive Dark Theme for a modern and focused user experience.
* **Primary Color:** `#8C3EFF` (Indonet Purple) used for key interactive elements and branding accents.
* **Layout:**
    * Horizontal Navigation (top navbar, no vertical sidebar).
    * Fixed Header for persistent navigation.
    * Wide Content area for optimal information display.
    * Left-to-Right (LTR) direction.
* **Component-Based:** Built using reusable Vue.js components, leveraging the extensive Vuetify component library and custom-built components.
* **Responsive Design:** Ensures optimal viewing and interaction across various devices and screen sizes.
* **Animations:** Subtle and purposeful animations (using GSAP and CSS transitions) to enhance user experience, including hover effects, page transitions, and notification indicators (e.g., pulse animation).
* **Iconography:** Font Awesome for clear and consistent visual cues.

---

## Core System Logic

* **Authentication & Authorization:** Secure user authentication with email invitations, mandatory first-time password changes, and strict role-based authorization controlling access to all resources and actions. Terms & Conditions must be accepted on each login.
* **Dynamic Content Delivery:** Content and menu visibility are dynamically adjusted based on the logged-in user's roles and permissions, ensuring users only see relevant information.
* **Notification Flow:** System notifications are created by admins and pushed to all users, with an indicator in the navbar. Users can view a list and details of notifications.
* **Admin Control:** Admins have comprehensive control over user management, role/permission assignments, content, menus, system configurations, T&C, and email templates.

---

## Project Architecture Overview

* **Backend:** Follows the Model-View-Controller (MVC) pattern provided by Laravel, exposing RESTful APIs for frontend interaction.
* **Frontend:** A Single Page Application (SPA) built with Vue.js, utilizing a component-based architecture for modularity and reusability. State is managed by Pinia, and routing by Vue Router.
* **Integration:** Laravel serves the initial Blade view that bootstraps the Vue.js application. All subsequent interactions are handled via API calls. The `laravel-vite-plugin` manages the integration of Vite-built frontend assets.

---

This README provides a high-level overview of the Indonet Analytics Hub project. For more detailed information, please refer to the specific documentation for Development Plan, System Logic, and Design System.

---

# Credits

* **Project Lead:** Muhammad Ishaq, Business Intelligence Team
* **Contributors:** Muhammad Ishaq, Business Intelligence Team
* **UI/UX Design:** Muhammad Ishaq, Business Intelligence Team
* **Frontend Development:** Muhammad Ishaq, Business Intelligence Team
* **Backend Development:** Muhammad Ishaq, Business Intelligence Team
* **Database Design:** Muhammad Ishaq, Business Intelligence Team
* **Design Inspiration:** Design System/Template Source
* **Special Thanks:** Indonet Team, Management, etc.
* **License:** MIT License
* **Version:** 1.0.0
* **Last Updated:** Date

# License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

---

## Responsive Design

The Indonet Analytics Hub is built with a mobile-first approach to ensure optimal user experience across all device types:

### Responsive Framework

- **Adaptive Layout System**: Uses flexible grids and logical CSS properties
- **Breakpoint System**: Consistent breakpoints for all media queries
- **Touch Optimization**: Minimum 44px touch targets and mobile-specific interactions
- **Conditional Rendering**: Different UI elements for mobile vs desktop

### Mobile-Optimized Components

The application includes several specialized responsive components:

- **ResponsiveContentContainer**: Adapts layout based on screen size
- **ResponsiveDataCard**: Optimized card displays for all devices
- **ResponsiveNavigationDrawer**: Touch-friendly navigation
- **MobileNavigation**: Dedicated mobile menu with hamburger toggle

### Responsive Chart Visualizations

All data visualizations automatically adapt to screen size:

- Simplified legends on mobile
- Adjusted label sizes and rotations
- Touch-optimized tooltips and interactions
- Responsive height and width calculations

### Documentation

For more details about our responsive implementation:

- [Responsive Design Documentation](docs/RESPONSIVE_DESIGN.md)
- [Responsive Design Guide](docs/RESPONSIVE_DESIGN_GUIDE.md)
- [Implementation Summary](docs/RESPONSIVE_IMPLEMENTATION_SUMMARY.md)

---
