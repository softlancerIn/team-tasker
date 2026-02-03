<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Admin Dashboard | Team Tasker' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Bootstrap & Chart.js -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/tinymce@5/tinymce.min.js' referrerpolicy="origin"></script>
    <script src="https://unpkg.com/feather-icons"></script>

    <style>
        :root {
            /* Common Base Colors */
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #64748b;
            --accent: #10b981;

            /* Dark Mode Defaults */
            --bg-dark: #0f172a;
            --sidebar-bg: #1e293b;
            --card-bg: rgba(30, 41, 59, 0.7);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: rgba(255, 255, 255, 0.1);
        }

        [data-theme="light"] {
            --bg-dark: #f1f5f9;
            --sidebar-bg: #ffffff;
            --card-bg: rgba(255, 255, 255, 0.8);
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: rgba(0, 0, 0, 0.1);
            --secondary: #94a3b8;
            /* Lighter secondary for light mode */
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-main);
            margin: 0;
            overflow-x: hidden;
        }

        /* Glassmorphism Sidebar */
        .sidebar {
            width: 260px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--border-color);
            padding: 1.5rem;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .sidebar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--primary);
        }

        .nav-link {
            color: var(--text-muted);
            padding: 0.8rem 1rem;
            border-radius: 12px;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
        }

        .nav-link i {
            width: 20px;
            font-size: 1.1rem;
        }

        /* Main Content area */
        .main-content {
            margin-left: 260px;
            padding: 2rem;
            min-height: 100vh;
            background: radial-gradient(circle at top right, rgba(99, 102, 241, 0.05), transparent);
        }

        /* Glass Cards */
        .glass-card {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 1.5rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .icon-primary {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
        }

        .icon-accent {
            background: rgba(16, 185, 129, 0.1);
            color: var(--accent);
        }

        .icon-warning {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .btn {
            font-weight: 400;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--primary);
            border: none;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(99, 102, 241, 0.4);
        }

        .form-control,
        .form-select {
            font-weight: 400;
            border-radius: 12px;
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            color: var(--text-main);
        }

        .form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='white' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        }

        .form-select option {
            background-color: #1a1b1e;
            color: white;
        }

        .form-control:focus,
        .form-select:focus {
            background-color: #25262b;
            border-color: var(--primary);
            color: white;
            box-shadow: 0 0 0 0.25rem rgba(99, 102, 241, 0.1);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.4) !important;
        }

        /* Top Bar */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
        }

        .search-container {
            position: relative;
            width: 300px;
        }

        .search-container input {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 0.6rem 1rem 0.6rem 2.5rem;
            color: white;
            width: 100%;
        }

        .search-container i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .extra-small {
            font-size: 0.7rem;
        }

        .activity-timeline {
            position: relative;
            padding-left: 0.5rem;
        }

        .activity-timeline::before {
            content: '';
            position: absolute;
            left: 20px;
            top: 0;
            bottom: 0;
            width: 1px;
            background: rgba(255, 255, 255, 0.1);
        }

        .text-main {
            color: var(--text-main) !important;
        }

        .text-main-50 {
            color: var(--text-muted) !important;
            /* text-muted matches text-white-50 intent better in light mode */
        }

        .light-mode .form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%231e293b' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        }

        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .main-content {
                margin-left: 0;
            }
        }

        .theme-toggle {
            cursor: pointer;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            color: var(--text-muted);
            background: transparent;
            border: 1px solid var(--border-color);
        }

        .theme-toggle:hover {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
        }
    </style>
</head>

<body>

    <aside class="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-layer-group"></i>
            <span>TeamTasker</span>
        </div>

        <nav>
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
                <i class="fas fa-chart-line"></i> Dashboard
            </a>
            <a href="{{ route('index') }}" class="nav-link {{ request()->is('index') ? 'active' : '' }}">
                <i class="fas fa-tasks"></i> My Tasks
            </a>
            <a href="{{ route('create') }}" class="nav-link {{ request()->is('create') ? 'active' : '' }}">
                <i class="fas fa-plus-circle"></i> New Task
            </a>
            <div class="nav-item">
                <a href="{{ route('admin.users.index') }}"
                    class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i> Users
                </a>
            </div>
            <div class="nav-item">
                <a href="{{ route('admin.roles.index') }}"
                    class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                    <i class="fas fa-shield-halved"></i> Roles
                </a>
            </div>
            <div class="nav-link" data-bs-toggle="collapse" href="#settingsSubmenu" role="button" aria-expanded="false"
                aria-controls="settingsSubmenu">
                <i class="fas fa-cog"></i> Settings
            </div>
            <div class="collapse {{ request()->routeIs('admin.statuses.*') ? 'show' : '' }}" id="settingsSubmenu">
                <ul class="list-unstyled ps-3">
                    <li>
                        <a href="{{ route('admin.statuses.index') }}"
                            class="nav-link {{ request()->routeIs('admin.statuses.*') ? 'active' : '' }}">
                            <i class="fas fa-tags"></i> Task Statuses
                        </a>
                    </li>
                </ul>
            </div>

            <div style="margin-top: auto; padding-top: 2rem;">
                <a href="{{ route('logout') }}" class="nav-link text-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </nav>
    </aside>

    <main class="main-content">
        <div class="top-bar">
            <div class="search-container">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search tasks...">
            </div>

            <div class="d-flex align-items-center gap-3">
                <button class="theme-toggle" id="themeToggle" title="Toggle Theme">
                    <i class="fas fa-moon"></i>
                </button>
                <div class="dropdown">
                    <div class="user-profile dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"
                        style="cursor: pointer;">
                        <div class="avatar">
                            @if (Auth::user()->profile_image)
                                <img src="{{ asset('storage/' . Auth::user()->profile_image) }}" alt="Profile"
                                    style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                            @else
                                {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                            @endif
                        </div>
                        <div>
                            <div style="font-weight: 500;">{{ Auth::user()->name ?? 'User' }}</div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">
                                {{ Auth::user()->role->name ?? 'User' }}</div>
                        </div>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end bg-dark border-secondary shadow-lg mt-2"
                        style="border-radius: 12px; min-width: 200px;">
                        <li>
                            <a class="dropdown-item text-white py-2" href="#" data-bs-toggle="modal"
                                data-bs-target="#profileModal">
                                <i class="fas fa-user-edit me-2 text-primary"></i> Edit Profile
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider border-secondary">
                        </li>
                        <li>
                        <li>
                            <a class="dropdown-item text-danger py-2" href="{{ route('logout') }}">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Profile Edit Modal -->
        <x-modal id="profileModal" title="My Profile" submitText="Update Profile"
            formAction="{{ route('profile.update') }}" enctype="multipart/form-data">
            <div class="text-center mb-4">
                <div class="avatar mx-auto mb-2" style="width: 80px; height: 80px; font-size: 2rem;">
                    @if (Auth::user()->profile_image)
                        <img src="{{ asset('storage/' . Auth::user()->profile_image) }}" alt="Profile"
                            style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                    @else
                        {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                    @endif
                </div>
                <div class="text-muted small">Update your profile picture</div>
                <input type="file" name="profile_image" class="form-control form-control-sm mt-2">
            </div>
            <div class="mb-3">
                <label class="form-label text-white">Full Name</label>
                <input type="text" name="name" value="{{ Auth::user()->name }}" class="form-control"
                    required>
            </div>
            <div class="mb-3">
                <label class="form-label text-white">Email Address</label>
                <input type="email" name="email" value="{{ Auth::user()->email }}" class="form-control"
                    required>
            </div>
            <div class="mb-3">
                <label class="form-label text-white">New Password (Empty to keep current)</label>
                <input type="password" name="password" class="form-control">
            </div>
        </x-modal>

        <!-- Toast Container -->
        <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
            @if (session('success'))
                <div id="successToast" class="toast align-items-center text-white bg-success border-0 shadow-lg"
                    role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-toast="toast"
                            aria-label="Close"></button>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div id="errorToast" class="toast align-items-center text-white bg-danger border-0 shadow-lg"
                    role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                            aria-label="Close"></button>
                    </div>
                </div>
            @endif
        </div>

        {{ $slot }}
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Theme Toggle Logic
            const themeToggle = document.getElementById('themeToggle');
            const icon = themeToggle.querySelector('i');
            const html = document.documentElement;

            // Check saved theme or default to dark
            const savedTheme = localStorage.getItem('theme') || 'dark';
            html.setAttribute('data-theme', savedTheme);
            updateIcon(savedTheme);

            themeToggle.addEventListener('click', () => {
                const currentTheme = html.getAttribute('data-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

                html.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                updateIcon(newTheme);
            });

            function updateIcon(theme) {
                if (theme === 'dark') {
                    icon.classList.remove('fa-sun');
                    icon.classList.add('fa-moon');
                } else {
                    icon.classList.remove('fa-moon');
                    icon.classList.add('fa-sun');
                }
            }

            if (document.getElementById('successToast')) {
                new bootstrap.Toast(document.getElementById('successToast')).show();
            }
            if (document.getElementById('errorToast')) {
                new bootstrap.Toast(document.getElementById('errorToast')).show();
            }
        });
    </script>
</body>

</html>
