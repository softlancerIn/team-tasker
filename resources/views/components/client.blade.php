<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Admin Dashboard | Team Tasker' }}</title>
    @livewireStyles

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

    <script>
        // Global Chat Editor Component for Alpine.js
        // Global Chat Editor Component for Alpine.js
        window.chatEditor = (wire, props) => ({
            editorStr: props && props.editorId ? props.editorId : 'message-editor',
            conversationId: props ? props.conversationId : null,
            userId: props ? props.userId : null,
            userName: props ? props.userName : null,
            themeChangeListener: null,

            init() {
                this.mountEditor();

                // Listen for theme changes
                this.themeChangeListener = () => {
                    this.mountEditor();
                };
                window.addEventListener('theme-changed', this.themeChangeListener);
            },

            mountEditor() {
                // Ensure TinyMCE is loaded
                if (typeof tinymce === 'undefined') {
                    console.error('TinyMCE not loaded');
                    return;
                }

                // Remove existing instance if any (safety check)
                let existingEditor = tinymce.get(this.editorStr);
                let currentContent = '';

                if (existingEditor) {
                    try {
                        currentContent = existingEditor.getContent();
                        existingEditor.remove();
                    } catch (e) {
                        console.warn('Error removing editor:', e);
                    }
                }

                // Aggressively clean up stale editors
                if (tinymce.editors) {
                    for (let i = tinymce.editors.length - 1; i >= 0; i--) {
                        let ed = tinymce.editors[i];
                        if (!document.getElementById(ed.id)) {
                            try {
                                ed.remove();
                            } catch (e) {}
                        }
                    }
                }

                const savedTheme = localStorage.getItem('theme') || 'dark';
                const isDark = savedTheme === 'dark';

                tinymce.init({
                    selector: '#' + this.editorStr,
                    height: 100,
                    skin: isDark ? 'oxide-dark' : 'oxide',
                    content_css: isDark ? 'dark' : 'default',
                    branding: false,
                    placeholder: 'Type a message...',
                    plugins: [
                        'advlist', 'autolink', 'link', 'image', 'lists', 'charmap', 'preview', 'anchor',
                        'pagebreak',
                        'searchreplace', 'wordcount', 'visualblocks', 'visualchars', 'code',
                        'fullscreen',
                        'insertdatetime', 'media', 'table', 'emoticons', 'help'
                    ],
                    menubar: false,
                    statusbar: false,
                    resize: false,
                    toolbar: 'undo redo | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | forecolor backcolor | table | bullist numlist',
                    extended_valid_elements: 'i[class|style],table[class|style],th[class|style],td[class|style],h1[class|style],h2[class|style],h3[class|style],h4[class|style],h5[class|style],h6[class|style]',
                    valid_elements: '*[*]',
                    content_css: false,
                    content_style: isDark ?
                        'body { background: transparent; color: rgba(255, 255, 255, 0.8); font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 14px; margin: 0; padding: 0 10px; line-height: 1.4; height: 100%; display: flex; flex-direction: column; justify-content: center; } p { margin: 0; } i { font-style: italic; } .mce-content-body[data-mce-placeholder]:not(.mce-visualblocks)::before { text-align: left; position: absolute; top: 50%; transform: translateY(-50%); left: 10px; color: rgba(255, 255, 255, 0.5); }' :
                        'body { background: transparent; color: #333; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 14px; margin: 0; padding: 0 10px; line-height: 1.4; height: 100%; display: flex; flex-direction: column; justify-content: center; } p { margin: 0; } i { font-style: italic; } .mce-content-body[data-mce-placeholder]:not(.mce-visualblocks)::before { text-align: left; position: absolute; top: 50%; transform: translateY(-50%); left: 10px; color: #aaa; }',
                    entity_encoding: 'raw',
                    remove_trailing_brs: false,
                    valid_children: '+body[style|i]',
                    setup: (editor) => {
                        editor.on('change keyup', () => {
                            let content = editor.getContent();
                            if (wire) wire.set('body', content);
                            // Emit typing event to socket
                            if (window.socket && this.conversationId) {
                                window.socket.emit('typing', {
                                    room: 'chat.' + this.conversationId,
                                    userId: this.userId,
                                    userName: this.userName
                                });
                            }
                        });
                        editor.on('keydown', async (e) => {
                            if (e.key === 'Enter' && !e.shiftKey) {
                                e.preventDefault();
                                const content = editor.getContent();
                                // Check if the preview element exists (indicates attachment is present)
                                const hasAttachment = document.querySelector(
                                    '.chat-media-preview') !== null;

                                // Send if text exists OR attachment exists
                                if ((content && content.trim() !== '') || hasAttachment) {
                                    if (wire) {
                                        await wire.sendMessage(content);
                                    }
                                    editor.resetContent();
                                }
                            }
                        });
                        editor.on('init', function() {
                            const container = editor.getContainer();
                            if (container) {
                                container.style.border = 'none';
                            }
                            // Restore content if reloading
                            if (currentContent) {
                                editor.setContent(currentContent);
                            }
                        });
                    }
                });
            }
        });

        // Global Chat Messages Component
        window.chatMessages = (wire, props) => ({
            conversationId: props.conversationId,
            userId: props.userId,
            receiverId: props.receiverId,
            isTyping: false,
            typingUser: '',
            typingTimeout: null,
            socket: null,

            init() {
                this.scrollToBottom();
                if (wire && wire.messages) {
                    this.$watch('$wire.messages', () => {
                        setTimeout(() => this.scrollToBottom(), 100)
                    });
                }

                if (typeof io !== 'undefined') {
                    const host = window.location.hostname;
                    this.socket = window.socket || io(`http://${host}:3000`);
                    const roomId = `chat.${this.conversationId}`;

                    this.socket.emit('join_room', roomId);

                    const onReceiveMessage = (data) => {
                        if (data.user_id != this.userId) {
                            if (wire) wire.call('loadConversation', this.conversationId);
                        }
                    };

                    const onUserTyping = (data) => {
                        if (data.userId != this.userId) {
                            this.isTyping = true;
                            this.typingUser = data.userName;
                            clearTimeout(this.typingTimeout);
                            this.typingTimeout = setTimeout(() => {
                                this.isTyping = false;
                            }, 3000);
                        }
                    };

                    const onUserStopTyping = (data) => {
                        if (data.userId != this.userId) {
                            this.isTyping = false;
                        }
                    };

                    this.socket.on('receive_message', onReceiveMessage);
                    this.socket.on('user_typing', onUserTyping);
                    this.socket.on('user_stop_typing', onUserStopTyping);

                    const onSendMessageToNode = (event) => {
                        const data = event.detail[0] || event.detail;
                        if (data && data.room && data.message) {
                            this.socket.emit('send_message', data);
                            this.socket.emit('stop_typing', {
                                room: roomId,
                                userId: this.userId
                            });
                        }
                    };
                    window.addEventListener('send-message-to-node', onSendMessageToNode);

                    // Cleanup function
                    return () => {
                        this.socket.off('receive_message', onReceiveMessage);
                        this.socket.off('user_typing', onUserTyping);
                        this.socket.off('user_stop_typing', onUserStopTyping);
                        window.removeEventListener('send-message-to-node', onSendMessageToNode);
                        this.socket.emit('leave_room', roomId);
                    };
                }
            },

            scrollToBottom() {
                const el = document.getElementById('chat-messages');
                if (el) el.scrollTop = el.scrollHeight;
            }
        });
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            /* Common Base Colors */
            --primary: #6366f1;
            --primary-rgb: 99, 102, 241;
            --primary-dark: #4f46e5;
            --secondary: #64748b;
            --secondary-rgb: 100, 116, 139;
            --accent: #10b981;
            --accent-rgb: 16, 185, 129;

            /* Dark Mode Defaults */
            --bg-dark: #0f172a;
            --sidebar-bg: #1e293b;
            --card-bg: rgba(30, 41, 59, 0.7);
            --input-bg: rgba(255, 255, 255, 0.05);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: rgba(255, 255, 255, 0.1);
        }

        [data-theme="light"] {
            --bg-dark: #f1f5f9;
            --sidebar-bg: #ffffff;
            --card-bg: rgba(255, 255, 255, 0.95);
            --input-bg: #f8fafc;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: rgba(0, 0, 0, 0.15);
            --secondary: #94a3b8;
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

        .user-profile {
            transition: all 0.3s ease;
            padding: 5px 10px;
            border-radius: 12px;
        }

        .user-profile:hover {
            background: rgba(255, 255, 255, 0.05);
            cursor: pointer;
        }

        [data-theme="light"] .user-profile:hover {
            background: rgba(0, 0, 0, 0.05);
        }

        /* Light Mode Overrides */
        [data-theme="light"] .text-white,
        [data-theme="light"] .text-white-50 {
            color: var(--text-main) !important;
        }

        [data-theme="light"] .bg-dark {
            background-color: #ffffff !important;
            color: var(--text-main) !important;
        }

        [data-theme="light"] .table-dark {
            --bs-table-bg: transparent;
            --bs-table-color: var(--text-main);
            color: var(--text-main);
            background-color: transparent;
        }

        [data-theme="light"] .table-dark th,
        [data-theme="light"] .table-dark td {
            color: var(--text-main);
            border-color: var(--border-color);
        }

        [data-theme="light"] .form-control,
        [data-theme="light"] .form-select {
            background-color: #ffffff;
            color: var(--text-main);
            border-color: #ced4da;
        }

        [data-theme="light"] .table tbody tr:hover td {
            background-color: rgba(0, 0, 0, 0.03) !important;
        }

        [data-theme="light"] .form-control::placeholder {
            color: #6c757d !important;
            /* Bootstrap text-muted color for visibility */
            opacity: 1;
        }

        .theme-toggle:hover {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
        }

        /* Global Table Fix */
        .table {
            color: var(--text-main);
            --bs-table-color: var(--text-main);
        }

        [data-theme="light"] .text-main-50 {
            color: rgba(255, 255, 255, 0.7) !important;
        }

        [data-theme="light"] .search-container input {
            background: #ffffff;
            border-color: #ced4da;
            color: var(--text-main);
        }

        [data-theme="light"] .search-container i {
            color: #6c757d;
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
            <a href="{{ route('client.dashboard') }}"
                class="nav-link {{ request()->routeIs('client.dashboard') ? 'active' : '' }}">
                <i class="fas fa-ticket-alt"></i> My Tickets
            </a>

            <a href="{{ route('client.tickets.create') }}"
                class="nav-link {{ request()->routeIs('client.tickets.create') ? 'active' : '' }}">
                <i class="fas fa-plus-circle"></i> New Ticket
            </a>

            <a href="{{ route('client.chat.index') }}"
                class="nav-link {{ request()->routeIs('client.chat.index') ? 'active' : '' }}">
                <i class="fas fa-comments"></i> Chat
            </a>

            <div style="margin-top: auto; padding-top: 2rem;">
                <a href="{{ route('logout') }}" class="nav-link text-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </nav>
    </aside>

    <main class="main-content">
        <div class="top-bar">
            <form action="{{ route('search.global') }}" method="GET" class="search-container">
                <i class="fas fa-search"></i>
                <input type="text" name="q" placeholder="Search tasks..." value="{{ request('q') }}">
            </form>

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
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg mt-2"
                        style="border-radius: 12px; min-width: 200px; background: var(--card-bg); border: 1px solid var(--border-color);">
                        <li>
                            <a class="dropdown-item py-2" href="#" data-bs-toggle="modal"
                                data-bs-target="#profileModal" style="color: var(--text-main);">
                                <i class="fas fa-user-edit me-2 text-primary"></i> Edit Profile
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider" style="border-color: var(--border-color);">
                        </li>
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

                // Dispatch event for components like TinyMCE to react
                window.dispatchEvent(new CustomEvent('theme-changed', {
                    detail: {
                        theme: newTheme
                    }
                }));
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

            // Request Notification Permission on user interaction
            document.addEventListener('click', function() {
                if (Notification.permission === 'default') {
                    Notification.requestPermission();
                }
            }, {
                once: true
            });

            // Socket.IO Connection
            if (typeof io !== 'undefined') {
                const userId = {{ auth()->id() }};
                const host = window.location.hostname;
                const socket = io(`http://${host}:3000`);
                window.socket = socket; // Make available globally

                socket.on('connect', () => {
                    console.log('Connected to Socket.IO server');
                    socket.emit('user_connected', userId);
                });

                // Online Users Tracking
                socket.on('online_users', (users) => {
                    // Update UI for online users
                    document.querySelectorAll('[data-user-id]').forEach(el => {
                        const uid = parseInt(el.getAttribute('data-user-id'));
                        const dot = el.querySelector('.status-dot');
                        if (users.includes(uid)) {
                            if (dot) dot.classList.remove('bg-secondary');
                            if (dot) dot.classList.add('bg-success');
                        } else {
                            if (dot) dot.classList.remove('bg-success');
                            if (dot) dot.classList.add('bg-secondary');
                        }
                    });
                });

            }
        });
    </script>
    @livewireScripts
</body>

</html>
