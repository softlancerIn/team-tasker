# Team Tasker - Premium AI-Enabled Task & Support Platform

**Team Tasker** is a state-of-the-art, Laravel-based project management and support platform designed for high-performance teams. It combines robust task management with a unified support ticket system and real-time communication, all wrapped in a premium, highly-customizable user interface.

![Team Tasker Header](/home/rohit-pal/.gemini/antigravity/brain/d150379a-367d-4d48-95b5-5a4bf5eab78a/header_redesign_verification_1772736739659.png)

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

### 🚪 Dedicated Client Portal
- **White-Label Experience**: Custom branding for clients to submit tickets and track their task progress.
- **Simplified Interface**: Focused dashboard for clients to manage their interactions without complexity.

### 🛠️ Premium UI & SEO
- **Glassmorphism Design**: A stunning, modern interface with smooth transitions and interactive elements.
- **Adaptive Theming**: Full support for **Dark Mode** and **Light Mode**.
- **SEO Optimized**: Built-in best practices for metadata, semantic HTML, and fast performance.

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
   git clone https://github.com/iamrohitpal/team-tasker.git
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