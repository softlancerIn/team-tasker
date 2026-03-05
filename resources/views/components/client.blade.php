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

    <!-- Tom Select -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

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
                this.themeChangeListener = (e) => {
                    const newTheme = e.detail && e.detail.theme ? e.detail.theme : localStorage.getItem(
                        'theme');
                    this.mountEditor(newTheme);
                };
                window.addEventListener('theme-changed', this.themeChangeListener);
            },

            mountEditor(theme = null) {
                // Ensure TinyMCE is loaded
                if (typeof tinymce === 'undefined') {
                    console.error('TinyMCE not loaded');
                    return;
                }

                const savedTheme = theme || localStorage.getItem('theme') || 'dark';
                const isDark = savedTheme === 'dark';

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
                    entity_encoding: 'raw',
                    remove_trailing_brs: false,
                    valid_children: '+body[style|i]',
                    content_style: `
                        body { 
                            background: transparent !important; 
                            color: ${isDark ? '#f8fafc' : '#0f172a'}; 
                            font-family: 'Outfit', sans-serif; 
                            font-size: 14px; 
                            margin: 0; 
                            padding: 10px; 
                            line-height: 1.5; 
                        } 
                        p { margin: 0; } 
                        i { font-style: italic; } 
                        .mce-content-body[data-mce-placeholder]:not(.mce-visualblocks)::before { 
                            color: ${isDark ? 'rgba(255, 255, 255, 0.4)' : 'rgba(0, 0, 0, 0.3)'} !important; 
                            font-family: 'Outfit', sans-serif;
                            font-style: normal;
                        }
                    `,
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
                                container.style.background = 'transparent';
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

        // Global Initialization for other editors (Task Description, etc.)
        window.initGlobalEditors = (theme = null) => {
            if (typeof tinymce === 'undefined') return;

            const savedTheme = theme || localStorage.getItem('theme') || 'dark';
            const isDark = savedTheme === 'dark';

            // Remove existing instances to avoid duplicates on re-init
            document.querySelectorAll('.rich-editor').forEach(el => {
                if (el.id) {
                    const ed = tinymce.get(el.id);
                    if (ed) ed.remove();
                }
            });

            tinymce.init({
                selector: '.rich-editor',
                height: 400,
                skin: isDark ? 'oxide-dark' : 'oxide',
                content_css: isDark ? 'dark' : 'default',
                branding: false,
                placeholder: 'Describe in detail...',
                plugins: [
                    'advlist', 'autolink', 'link', 'image', 'lists', 'charmap', 'preview', 'anchor',
                    'pagebreak',
                    'searchreplace', 'wordcount', 'visualblocks', 'visualchars', 'code', 'fullscreen',
                    'insertdatetime',
                    'media', 'table', 'emoticons', 'help'
                ],
                menubar: true,
                toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | code | styleselect',
                extended_valid_elements: 'i[class|style],table[class|style],th[class|style],td[class|style],h1[class|style],h2[class|style],h3[class|style],h4[class|style],h5[class|style],h6[class|style]',
                valid_elements: '*[*]',
                content_style: isDark ?
                    'body { background: radial-gradient(circle at top right, rgba(99, 102, 241, 0.05), transparent), #1a2436; color: rgba(255, 255, 255, 0.8); font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; line-height: 1.6; } i { font-style: italic; } body.mce-content-body[data-mce-placeholder]:not(.mce-visualblocks)::before { color: rgba(255, 255, 255, 0.4); }' :
                    'body { background: #ffffff; color: #333; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; line-height: 1.6; } i { font-style: italic; }',
                entity_encoding: 'raw',
                remove_trailing_brs: false,
                valid_children: '+body[style|i]',
                setup: function(editor) {
                    editor.on('init', function() {
                        const container = editor.getContainer();
                        if (container) {
                            container.style.border = isDark ? '1px solid rgba(99, 102, 241, 0.3)' :
                                '1px solid #ced4da';
                            container.style.borderRadius = '8px';
                        }
                    });
                }
            });
        };

        // Initialize on load if editors exist
        document.addEventListener('DOMContentLoaded', () => {
            if (document.querySelector('.rich-editor')) {
                window.initGlobalEditors();
            }
        });

        // Listen for theme changes globally
        window.addEventListener('theme-changed', (e) => {
            const newTheme = e.detail && e.detail.theme ? e.detail.theme : localStorage.getItem('theme');
            window.initGlobalEditors(newTheme);
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
        /* Minimal specific overrides if any needed in future */
    </style>
</head>

<body>

    <aside class="sidebar-premium">
        <div class="sidebar-brand">
            <i class="fas fa-layer-group"></i>
            <span>TeamTasker</span>
        </div>

        <nav>
            <a href="{{ route('client.dashboard') }}"
                class="nav-link-premium {{ request()->routeIs('client.dashboard') ? 'active' : '' }}">
                <i class="fas fa-ticket-alt"></i> My Tickets
            </a>

            <a href="{{ route('client.tickets.create') }}"
                class="nav-link-premium {{ request()->routeIs('client.tickets.create') ? 'active' : '' }}">
                <i class="fas fa-plus-circle"></i> New Ticket
            </a>

            <a href="{{ route('client.chat.index') }}"
                class="nav-link-premium {{ request()->routeIs('client.chat.index') ? 'active' : '' }}">
                <i class="fas fa-comments"></i> Chat
            </a>

            <div style="margin-top: auto; padding-top: 2rem;">
                <a href="{{ route('logout') }}" class="nav-link-premium text-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </nav>
    </aside>

    <main class="main-content-premium">
        <div class="top-bar-premium">
            <form action="{{ route('search.global') }}" method="GET" class="header-search">
                <i class="fas fa-search"></i>
                <input type="text" name="q" placeholder="Search tickets, tasks..."
                    value="{{ request('q') }}">
            </form>

            <div class="d-flex align-items-center gap-3">
                <button class="theme-toggle-premium" id="themeToggle" title="Toggle Theme">
                    <i class="fas fa-moon"></i>
                </button>
                <div class="dropdown">
                    <div class="user-profile-premium dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="avatar-premium" style="border: 2px solid var(--border-main);">
                            @if (Auth::user()->profile_image)
                                <img src="{{ asset('storage/' . Auth::user()->profile_image) }}" alt="Profile">
                            @else
                                {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                            @endif
                        </div>
                        <div class="d-none d-md-block">
                            <div
                                style="font-weight: 600; font-size: 0.9rem; color: var(--text-high); line-height: 1.2;">
                                {{ Auth::user()->name ?? 'User' }}</div>
                            <div style="font-size: 0.72rem; color: var(--text-medium);">
                                {{ Auth::user()->role->name ?? 'Client' }}</div>
                        </div>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end shadow-premium mt-2" style="min-width: 200px;">
                        <li class="px-3 py-2" style="border-bottom: 1px solid var(--border-subtle);">
                            <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-high);">
                                {{ Auth::user()->name }}</div>
                            <div style="font-size: 0.7rem; color: var(--text-low);">{{ Auth::user()->email }}</div>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="#" data-bs-toggle="modal"
                                data-bs-target="#profileModal">
                                <i class="fas fa-user-edit me-2" style="color: var(--primary);"></i> Edit Profile
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider" style="border-color: var(--border-subtle); margin: 4px 0;">
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
        <x-modal id="profileModal" title="Edit Profile" submitText="Save Changes"
            formAction="{{ route('profile.update') }}" enctype="multipart/form-data">
            <div class="text-center mb-4">
                <div class="avatar-premium mx-auto mb-3"
                    style="width: 72px; height: 72px; font-size: 1.75rem; border: 3px solid var(--border-main);">
                    @if (Auth::user()->profile_image)
                        <img src="{{ asset('storage/' . Auth::user()->profile_image) }}" alt="Profile"
                            style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                    @endif
                </div>
                <div class="text-low small mb-2">Update your profile picture</div>
                <input type="file" name="profile_image" class="form-premium-control py-2" style="font-size: 0.8rem;">
            </div>
            <div class="mb-3">
                <label class="heading-label mb-2" style="font-size: 0.7rem;">Full Name</label>
                <input type="text" name="name" value="{{ Auth::user()->name }}" class="form-premium-control"
                    required>
            </div>
            <div class="mb-3">
                <label class="heading-label mb-2" style="font-size: 0.7rem;">Email Address</label>
                <input type="email" name="email" value="{{ Auth::user()->email }}" class="form-premium-control"
                    required>
            </div>
            <div class="mb-3">
                <label class="heading-label mb-2" style="font-size: 0.7rem;">New Password <span class="text-low"
                        style="font-weight: 400;">(leave blank to keep current)</span></label>
                <input type="password" name="password" class="form-premium-control" placeholder="••••••••">
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
