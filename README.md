# Team Tasker - Premium AI-Enabled Task & Support Platform

**Team Tasker** is a state-of-the-art, Laravel-based project management and support platform designed for high-performance teams. It combines robust task management with a unified support ticket system and real-time communication, all wrapped in a premium, highly-customizable user interface.

![Team Tasker Header](/.gemini/antigravity/brain/d150379a-367d-4d48-95b5-5a4bf5eab78a/header_redesign_verification_1772736739659.png)

## 🚀 Key Modules & Features

### 💻 Admin Dashboard
- **Analytics at a Glance**: Real-time statistics on task completion, ticket volume, and team performance.
- **Dynamic Widgets**: Interactive charts and cards providing a 360-degree view of your operations.

### 📝 Strategic Task Management
- **Multiple Views**: Manage tasks via professional **List**, **Kanban Board**, **Calendar**, and **Gantt Chart** views.
- **Smart Logic**: Recurring tasks, status tracking with customizable colors, and priority-based sorting.
- **Detailed Insights**: Extensive logging, time tracking, and attachment support for every task.

### 🎫 Advanced Support Ticket System
- **Omnichannel Support**: Centralized ticket management with priority levels and custom status flows.
- **SLA Tracking**: Automated notifications for upcoming deadlines and SLA breaches.
- **Client Collaboration**: Convert tickets directly into tasks and collaborate seamlessly.

### 💬 Unified Team Chat
- **Real-Time Communication**: Integrated chat system for instant collaboration.
- **Attachment Support**: Share files, images, and documents directly within conversations.
- **Privacy Controls**: Secure messaging with blocking and presence features.

### 🔐 Unified Authentication & Roles
- **Single Portal Entry**: Consolidated login system seamlessly routing Master Admins, Staff, and Clients to their respective workspaces.
- **Air-Tight Security**: Granular Role-Based Access Control (RBAC) securely isolates client portals from internal operations.

### ✅ Personal To-Do & Productivity
- **Private Task Lists**: Livewire-powered, dynamic to-do lists to keep individual users focused.
- **Interactive Modals**: Seamlessly add, edit, and safely delete personal tasks directly from the dashboard.
- **Automated Logging**: System-wide activity tracking that quietly records project and task changes in the background.

### 🚪 Dedicated Client Portal
- **White-Label Experience**: Custom branding for clients to submit tickets and track their task progress.
- **Simplified Interface**: Focused dashboard for clients to manage their interactions without complexity.

### 🛠️ Premium UI & SEO
- **Glassmorphism Design**: A stunning, modern interface with smooth transitions and interactive elements.
- **Adaptive Theming**: Full support for **Dark Mode** and **Light Mode**.
- **SEO Optimized**: Built-in best practices for metadata, semantic HTML, and fast performance.

---

## 📂 Project Hierarchy & Workflow

Understanding how data flows in Team Tasker helps teams stay organized and highly productive:

1. **Projects (The Core)**: The highest level of organization. You create a Project to represent a major goal, client deliverable, or internal system.
2. **Tasks (The Execution)**: Under each Project, you create multiple Tasks. Tasks break down the massive project into actionable steps assigned to specific users.
3. **Execution & Tracking (The Daily Grind)**: 
   - **Start Timer**: Inside any task, team members can click "Start Timer" to begin tracking their billed or spent time.
   - **Activity Logs**: As you work, you can add logs, notes, and file attachments directly to the task, ensuring a complete historical record of the work performed.

---

## 🔮 Upcoming Features

We are constantly pushing the boundaries of what Team Tasker can do. Here is a sneak peek at what is currently in development:

- **AI-Powered Task Summaries**: Automatically generate daily summaries of all task logs using generative AI.
- **Advanced Push Notifications**: Real-time Firebase Cloud Messaging (FCM) integration for instant mobile and desktop alerts.
- **Expanded Client Ticketing**: A robust visual builder for custom client request forms.
- **Drag-and-Drop Task Board Enhancements**: Swimlanes and WIP (Work In Progress) limits for advanced agile teams.

---

## 🛠️ Technical Stack

- **Framework**: Laravel 11 (PHP 8.2+)
- **Frontend**: Livewire 3, Alpine.js, Bootstrap 5.3
- **Styling**: Vanilla CSS (Premium Design System)
- **Database**: MySQL / PostgreSQL
- **Build Tool**: Vite
- **Real-Time**: Reverb / Firebase Integration

---

## 🔧 Installation & Setup

### Prerequisites
- PHP >= 8.2
- Composer
- Node.js & NPM
- MySQL

### Step-by-Step Installation

1. **Clone & Install Dependencies:**
   ```bash
   git clone https://github.com/softlancerIn/team-tasker.git
   cd team-tasker
   composer install
   npm install
   ```

2. **Environment Configuration:**
   Copy the sample environment file and update your database credentials:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database Setup:**
   ```bash
   php artisan migrate --seed
   ```

4. **Asset Compilation:**
   ```bash
   npm run build
   ```

5. **Start the Application:**
   ```bash
   php artisan serve
   ```

### Firebase Cloud Messaging (FCM) Setup

To enable real-time push notifications across the application, you must configure Firebase Cloud Messaging:

1. **Create a Firebase Project:**
   - Go to the [Firebase Console](https://console.firebase.google.com/) and create a new project.
   - Add a "Web App" to your project to generate your configuration keys.

2. **Configure Environment Variables:**
   - Open your `.env` file and populate the `FIREBASE_*` variables using the keys provided by Firebase (e.g., `FIREBASE_API_KEY`, `FIREBASE_PROJECT_ID`, etc.).

3. **Generate a VAPID Key:**
   - In the Firebase Console, go to **Project Settings > Cloud Messaging**.
   - Under the "Web configuration" section, generate a new **Web Push certificate** (VAPID key).
   - Copy this key and set it as `FIREBASE_VAPID_KEY=` in your `.env` file.

4. **Add Service Account Credentials (For Backend HTTP v1 API):**
   - In the Firebase Console, go to **Project Settings > Service accounts**.
   - Click **Generate new private key**.
   - Rename the downloaded JSON file to `firebase-service-account.json` and place it inside the `storage/app/firebase/` directory of your Laravel project.

---

## 🎨 Branding & Customization
Team Tasker includes a powerful **Settings Module** localized in the Admin Panel.
- **Dynamic Logo & Name**: Change the application logo and title across the platform instantly.
- **Email Configuration**: Built-in Mailpit/SMTP setup for system-wide notifications.
- **Status Management**: Define custom lifecycles for your tasks and tickets.

---

## 🤝 Contributing
We welcome contributions that push the boundaries of productivity tools. Please refer to our contribution guidelines for more details on pull requests and code standards.

## 📄 License
Team Tasker is open-source software licensed under the [MIT License](LICENSE).

## 📮 Contact
**Softlancer Pvt Ltd** - [softlancer.in@gmail.com](mailto:softlancer.in@gmail.com)  
Noida, Uttar Pradesh, India