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

    {{-- TomSelect - JS only, we own all CSS --}}
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

    {{-- TomSelect Full Custom CSS - fully owned, matches text box design --}}
    <style>
        /* ======= TomSelect Required Base Styles ======= */
        .ts-hidden-accessible {
            clip: rect(0 0 0 0) !important;
            border: 0 !important;
            clip-path: inset(50%) !important;
            overflow: hidden !important;
            padding: 0 !important;
            position: absolute !important;
            white-space: nowrap !important;
            width: 1px !important;
        }

        .ts-wrapper {
            position: relative;
            display: block;
        }

        .ts-control {
            position: relative;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            width: 100%;
            box-sizing: border-box;
            overflow: hidden;
            z-index: 1;
        }

        .ts-control>* {
            display: inline-block;
            vertical-align: baseline;
        }

        .ts-control>input {
            flex: 1 1 auto;
            min-width: 7rem;
            max-width: 100%;
            border: 0 !important;
            box-shadow: none !important;
            background: none !important;
            outline: none !important;
            padding: 0 !important;
            margin: 0 !important;
            min-height: 0 !important;
            max-height: none !important;
            line-height: inherit !important;
            color: inherit !important;
        }

        .ts-control>input:focus {
            outline: none !important;
        }

        .ts-wrapper.single .ts-control,
        .ts-wrapper.single .ts-control>input {
            cursor: pointer;
        }

        .ts-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            box-sizing: border-box;
        }

        .ts-dropdown-content {
            overflow-y: auto;
            max-height: 220px;
            overflow-x: hidden;
        }

        .ts-dropdown [data-selectable] {
            cursor: pointer;
            overflow: hidden;
        }

        .ts-dropdown [data-selectable].option {
            cursor: pointer;
        }

        /* Plugin: clear_button */
        .plugin-clear_button .clear-button {
            background: transparent !important;
            cursor: pointer;
            opacity: 0;
            position: absolute;
            right: 2.2rem;
            top: 50%;
            transform: translateY(-50%);
            transition: opacity .3s;
            border: none;
            font-size: 1rem;
            line-height: 1;
            padding: 0;
        }

        .plugin-clear_button.focus.has-items .clear-button,
        .plugin-clear_button:not(.disabled):hover.has-items .clear-button {
            opacity: 0.6;
        }

        /* Plugin: remove_button */
        .ts-wrapper.plugin-remove_button .item {
            align-items: center;
            display: inline-flex;
            padding-right: 0 !important;
        }

        .ts-wrapper.plugin-remove_button .item .remove {
            display: inline-block;
            padding: 0 5px;
            text-decoration: none;
            vertical-align: middle;
            cursor: pointer;
        }

        .input-hidden .ts-control>input {
            left: -10000px;
            opacity: 0;
            position: absolute;
        }

        .ts-control,
        .ts-wrapper.single .ts-control,
        .ts-wrapper.multi .ts-control {
            background-color: var(--bg-input) !important;
            background-image: var(--ts-arrow-bg, var(--ts-arrow-dark)) !important;
            background-repeat: no-repeat !important;
            background-position: right 0.85rem center !important;
            background-size: 12px 12px !important;
            border: 1px solid var(--border-main) !important;
            border-radius: var(--radius-md) !important;
            color: var(--text-high) !important;
            padding: 0.6rem 2.5rem 0.6rem 1rem !important;
            min-height: 42px !important;
            box-shadow: none !important;
            cursor: pointer !important;
            font-family: 'Outfit', sans-serif !important;
            font-size: 0.95rem !important;
        }

        .ts-wrapper.focus .ts-control,
        .ts-wrapper.focus.single .ts-control,
        .ts-wrapper.focus.multi .ts-control {
            border-color: var(--primary) !important;
            background-color: var(--bg-surface) !important;
            box-shadow: none !important;
            outline: none !important;
        }

        .ts-wrapper .ts-control input,
        .ts-control input {
            color: var(--text-high) !important;
            background: transparent !important;
            caret-color: var(--text-high) !important;
        }

        .ts-dropdown,
        .ts-dropdown.single,
        .ts-dropdown.multi {
            background: #1a2436 !important;
            border: 1px solid var(--border-main) !important;
            border-radius: var(--radius-md) !important;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5) !important;
            backdrop-filter: blur(20px) !important;
            color: var(--text-high) !important;
            z-index: 2000 !important;
        }

        .ts-dropdown .option,
        .ts-dropdown .ts-dropdown-content .option {
            color: var(--text-high) !important;
            background: transparent !important;
            padding: 0.6rem 1rem !important;
        }

        .ts-dropdown .option:hover,
        .ts-dropdown .option.active {
            background: rgba(var(--primary-rgb), 0.12) !important;
            color: var(--primary) !important;
        }

        .ts-dropdown .optgroup-header {
            color: var(--text-low) !important;
            background: transparent !important;
        }

        .ts-control .item .remove {
            color: var(--primary) !important;
            border-left: 1px solid rgba(var(--primary-rgb), 0.3) !important;
        }

        /* Light mode overrides */
        [data-theme="light"] .ts-control,
        [data-theme="light"] .ts-wrapper.single .ts-control,
        [data-theme="light"] .ts-wrapper.multi .ts-control {
            background: #f8fafc !important;
            border-color: #e2e8f0 !important;
            color: #0f172a !important;
        }

        [data-theme="light"] .ts-wrapper.focus .ts-control,
        [data-theme="light"] .ts-wrapper.focus.single .ts-control {
            background: #ffffff !important;
            border-color: var(--primary) !important;
        }

        [data-theme="light"] .ts-wrapper .ts-control input {
            color: #0f172a !important;
        }

        [data-theme="light"] .ts-dropdown,
        [data-theme="light"] .ts-dropdown.single {
            background: #ffffff !important;
            border-color: #e2e8f0 !important;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1) !important;
            color: #0f172a !important;
        }

        [data-theme="light"] .ts-dropdown .option,
        [data-theme="light"] .ts-dropdown .ts-dropdown-content .option {
            color: #0f172a !important;
        }

        [data-theme="light"] .ts-control .item {
            background: rgba(var(--primary-rgb), 0.1) !important;
            color: var(--primary) !important;
        }
    </style>

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

            // Remove existing instances from elements with rich-editor class
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
                placeholder: 'Describe the task in detail...',
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
                    editor.on('change keyup', function() {
                        const content = editor.getContent();
                        const textarea = editor.getElement();
                        textarea.value = content;
                        textarea.dispatchEvent(new Event('input', {
                            bubbles: true
                        }));
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
                        if (data.action === 'delete') {
                            if (wire) wire.call('loadConversation', this.conversationId);
                        } else if (data.user_id != this.userId) {
                            if (wire) wire.call('loadConversation', this.conversationId);
                        }

                        window.dispatchEvent(new CustomEvent('global-message-received'));
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
        /* Mobile Navigation Fixes */
        .mobile-toggle-premium {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-md);
            background: var(--bg-input);
            border: 1px solid var(--border-main);
            color: var(--text-medium);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all var(--transition-base);
            margin-right: var(--space-3);
        }

        .mobile-toggle-premium:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: all var(--transition-base);
        }

        .sidebar-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        @media (max-width: 991px) {
            .sidebar-premium {
                transform: translateX(-100%);
                transition: transform var(--transition-base);
            }

            .sidebar-premium.mobile-open {
                transform: translateX(0) !important;
                box-shadow: 20px 0 50px rgba(0, 0, 0, 0.5);
            }

            .top-bar-premium {
                padding-left: var(--space-3) !important;
                padding-right: var(--space-3) !important;
            }

            .header-search {
                width: 100% !important;
                max-width: none !important;
                margin-right: var(--space-2);
            }

            .main-content-premium {
                margin-left: 0 !important;
                padding-top: 80px !important;
            }

            .layout-header-premium {
                left: 0 !important;
            }
        }

        /* NEW GLOBAL HEADER STYLES */
        .layout-header-premium {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 70px;
            background: var(--bg-surface);
            border-bottom: 1px solid var(--border-main);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 24px;
            z-index: 1050;
            backdrop-filter: blur(10px);
        }

        .sidebar-premium {
            top: 70px !important;
            height: calc(100vh - 70px) !important;
            border-top: none !important;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: var(--border-subtle) transparent;
        }

        .sidebar-premium::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar-premium::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-premium::-webkit-scrollbar-thumb {
            background: var(--border-subtle);
            border-radius: 10px;
        }

        .main-content-premium {
            margin-top: 70px;
            padding: 24px;
        }

        .header-utils {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .header-icon-btn {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-medium);
            background: var(--bg-input);
            border: 1px solid var(--border-subtle);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .header-icon-btn:hover {
            color: var(--primary);
            border-color: var(--primary);
            background: var(--bg-surface);
        }

        .header-search-premium {
            flex-grow: 1;
            max-width: 500px;
            margin: 0 40px;
            position: relative;
        }

        .header-search-premium i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-low);
            font-size: 0.9rem;
            pointer-events: none;
        }

        .header-search-premium input {
            width: 100%;
            padding: 9px 16px 9px 40px;
            background: var(--bg-input);
            border: 1px solid var(--border-subtle);
            border-radius: 12px;
            color: var(--text-high);
            font-size: 0.85rem;
            outline: none;
            transition: all 0.2s ease;
        }

        .header-search-premium input:focus {
            border-color: var(--primary);
            background: var(--bg-surface);
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
        }

        @media (max-width: 768px) {
            .header-search-premium {
                display: none;
            }
        }
    </style>
</head>

<body>

    <header class="layout-header-premium">
        @php
            $appSettings = \App\Models\Setting::whereIn('key', ['app_name', 'app_logo'])->pluck('value', 'key');
            $appName = $appSettings['app_name'] ?? 'TeamTasker';
            $appLogo = $appSettings['app_logo'] ?? null;
        @endphp
        <div class="d-flex align-items-center gap-3">
            <button class="mobile-toggle-premium d-lg-none" id="mobileSidebarToggle" style="margin-right: 0;">
                <i class="fas fa-bars"></i>
            </button>
            <a href="{{ route('index') }}" class="sidebar-brand text-decoration-none p-0 border-0 bg-transparent">
                @if ($appLogo)
                    <img src="{{ asset('storage/' . $appLogo) }}" alt="Logo"
                        style="width: 32px; height: 32px; object-fit: contain; border-radius: 6px;">
                @else
                    <i class="fas fa-layer-group text-primary" style="font-size: 1.5rem;"></i>
                @endif
                <span class="text-high fw-bold"
                    style="font-size: 1.25rem; letter-spacing: -0.5px;">{{ $appName }}</span>
            </a>
        </div>

        <!-- Global Search -->
        <form action="{{ route('search.global') }}" method="GET" class="header-search-premium">
            <i class="fas fa-search"></i>
            <input type="text" name="q" placeholder="Search tasks, tickets, users..."
                value="{{ request('q') }}">
        </form>

        <div class="header-utils">
            <!-- Theme Toggle -->
            <button class="header-icon-btn" id="themeToggle" title="Toggle Theme">
                <i class="fas fa-moon"></i>
            </button>

            <!-- Notification Placeholder -->
            @php
                $notificationCount = Auth::user()->unreadNotifications->count();
            @endphp
            <button class="header-icon-btn position-relative" title="Notifications" data-bs-toggle="modal"
                data-bs-target="#notificationsModal">
                <i class="far fa-bell"></i>
                @if ($notificationCount > 0)
                    <span class="notification-badge">{{ $notificationCount > 99 ? '99+' : $notificationCount }}</span>
                @endif
            </button>

            <!-- User Profile -->
            <div class="dropdown">
                <div class="user-profile-premium dropdown-toggle p-0 bg-transparent border-0" data-bs-toggle="dropdown"
                    style="cursor: pointer; display: flex; align-items: center; gap: 10px;">
                    <div class="avatar-premium"
                        style="width: 38px; height: 38px; border: 1px solid var(--border-main);">
                        @if (Auth::user()->profile_image)
                            <img src="{{ asset('storage/' . Auth::user()->profile_image) }}" alt="Profile">
                        @else
                            {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                        @endif
                    </div>
                </div>
                <ul class="dropdown-menu dropdown-menu-end shadow-premium mt-3"
                    style="min-width: 200px; border-radius: 12px; border: 1px solid var(--border-subtle);">
                    <li class="px-3 py-3"
                        style="border-bottom: 1px solid var(--border-subtle); background: var(--bg-input); border-radius: 12px 12px 0 0;">
                        <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-high);">
                            {{ Auth::user()->name }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-low);">{{ Auth::user()->email }}</div>
                    </li>
                    <li><a class="dropdown-item py-2 mt-1" href="#" data-bs-toggle="modal"
                            data-bs-target="#profileModal"><i class="fas fa-user-edit me-2 text-primary"></i> Edit
                            Profile</a></li>
                    <li>
                        <hr class="dropdown-divider" style="opacity: 0.1;">
                    </li>
                    <li><a class="dropdown-item text-danger py-2 mb-1" href="{{ route('logout') }}"><i
                                class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </header>

    <aside class="sidebar-premium">

        <nav>
            @if (Auth::user()->hasPermission('dashboard.view'))
                <a href="{{ route('dashboard') }}"
                    class="nav-link-premium {{ request()->is('admin/tasks/dashboard') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a>
            @endif

            @if (Auth::user()->hasPermission('tasks.view') || Auth::user()->hasPermission('tasks.create'))
                <div class="nav-item">
                    <a href="javascript:void(0)"
                        class="nav-link-premium nav-dropdown {{ request()->is('admin/tasks*') && !request()->is('admin/tasks/dashboard') ? 'active' : '' }}"
                        onclick="toggleSubmenu(this)">
                        <i class="fas fa-tasks"></i>
                        <span>Tasks</span>
                        <i
                            class="fas fa-chevron-right ms-auto toggle-icon-premium {{ request()->is('admin/tasks*') && !request()->is('admin/tasks/dashboard') ? 'rotate' : '' }}"></i>
                    </a>
                    <div
                        class="nav-submenu-premium {{ request()->is('admin/tasks*') && !request()->is('admin/tasks/dashboard') ? 'show' : '' }}">

                        @if (Auth::user()->hasPermission('tasks.view'))
                            <a href="{{ route('index') }}"
                                class="nav-link-premium sub-link-premium {{ request()->routeIs('index') ? 'active' : '' }}">
                                <i class="fas fa-list"></i> My Tasks
                            </a>
                            <a href="{{ route('tasks.board') }}"
                                class="nav-link-premium sub-link-premium {{ request()->routeIs('tasks.board') ? 'active' : '' }}">
                                <i class="fas fa-columns"></i> Task Board
                            </a>
                            <a href="{{ route('tasks.calendar') }}"
                                class="nav-link-premium sub-link-premium {{ request()->routeIs('tasks.calendar') ? 'active' : '' }}">
                                <i class="fas fa-calendar-alt"></i> Calendar
                            </a>
                            <a href="{{ route('tasks.gantt') }}"
                                class="nav-link-premium sub-link-premium {{ request()->routeIs('tasks.gantt') ? 'active' : '' }}">
                                <i class="fas fa-chart-bar"></i> Gantt Chart
                            </a>
                        @endif

                        @if (Auth::user()->hasPermission('tasks.create'))
                            <a href="{{ route('create') }}"
                                class="nav-link-premium sub-link-premium {{ request()->routeIs('create') ? 'active' : '' }}">
                                <i class="fas fa-plus-circle"></i> New Task
                            </a>
                        @endif
                    </div>
                </div>
            @endif


            @if (Auth::user()->hasPermission('chat.view'))
                <a href="{{ route('admin.chat.index') }}"
                    class="nav-link-premium {{ request()->routeIs('admin.chat.*') ? 'active' : '' }}">
                    <i class="fas fa-comments"></i> Team Chat
                </a>
            @endif

            @if (Auth::user()->hasPermission('tickets.view'))
                <a href="{{ route('admin.tickets.index') }}"
                    class="nav-link-premium {{ request()->routeIs('admin.tickets.*') ? 'active' : '' }}">
                    <i class="fas fa-ticket-alt"></i> Tickets
                </a>
            @endif

            @if (Auth::user()->hasPermission('clients.view'))
                <a href="{{ route('admin.clients.index') }}"
                    class="nav-link-premium {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}">
                    <i class="fas fa-user-tie"></i> Clients
                </a>
            @endif

            @if (Auth::user()->hasPermission('settings.view'))
                <div class="nav-item">
                    <a href="javascript:void(0)"
                        class="nav-link-premium nav-dropdown {{ request()->routeIs('admin.settings.*') || request()->routeIs('admin.users.*') || request()->routeIs('admin.roles.*') ? 'active' : '' }}"
                        onclick="toggleSubmenu(this)">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                        <i
                            class="fas fa-chevron-right ms-auto toggle-icon-premium {{ request()->routeIs('admin.settings.*') || request()->routeIs('admin.users.*') || request()->routeIs('admin.roles.*') ? 'rotate' : '' }}"></i>
                    </a>
                    <div
                        class="nav-submenu-premium {{ request()->routeIs('admin.settings.*') || request()->routeIs('admin.users.*') || request()->routeIs('admin.roles.*') ? 'show' : '' }}">
                        <a href="{{ route('admin.settings.general') }}"
                            class="nav-link-premium sub-link-premium {{ request()->routeIs('admin.settings.general') ? 'active' : '' }}">
                            <i class="fas fa-sliders-h"></i> General
                        </a>
                        <a href="{{ route('admin.settings.statuses') }}"
                            class="nav-link-premium sub-link-premium {{ request()->routeIs('admin.settings.statuses') ? 'active' : '' }}">
                            <i class="fas fa-check-circle"></i> Task Statuses
                        </a>
                        <a href="{{ route('admin.settings.tags') }}"
                            class="nav-link-premium sub-link-premium {{ request()->routeIs('admin.settings.tags') ? 'active' : '' }}">
                            <i class="fas fa-tags"></i> Tags
                        </a>
                        <a href="{{ route('admin.settings.email') }}"
                            class="nav-link-premium sub-link-premium {{ request()->routeIs('admin.settings.email') ? 'active' : '' }}">
                            <i class="fas fa-envelope"></i> Email Integration
                        </a>
                        @if (Auth::user()->hasPermission('users.view'))
                            <a href="{{ route('admin.users.index') }}"
                                class="nav-link-premium sub-link-premium {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                                <i class="fas fa-users"></i> Users
                            </a>
                        @endif
                        @if (Auth::user()->hasPermission('roles.view'))
                            <a href="{{ route('admin.roles.index') }}"
                                class="nav-link-premium sub-link-premium {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                                <i class="fas fa-shield-halved"></i> Roles
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            <div style="margin-top: auto; padding-top: 2rem;">
                <a href="{{ route('logout') }}" class="nav-link-premium text-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </nav>
    </aside>

    <main class="main-content-premium">
        {{ $slot }}
    </main>

    <!-- Notifications Modal -->
    <div class="modal fade" id="notificationsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content shadow-premium"
                style="border-radius: 20px; border: 1px solid var(--border-subtle); background: var(--bg-surface); backdrop-filter: blur(20px);">
                <div class="modal-header border-0 px-4 pt-4 pb-0">
                    <h5 class="modal-title fw-bold text-high">Notifications</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="px-4 py-2 d-flex justify-content-between align-items-center">
                        <span class="text-low small">{{ $notificationCount }} Unread</span>
                        @if ($notificationCount > 0)
                            <form action="{{ route('notifications.markAsRead') }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="btn btn-link text-primary text-decoration-none p-0 small"
                                    style="font-size: 0.75rem;">Mark all as read</button>
                            </form>
                        @endif
                    </div>
                    <div class="notification-list-wrapper" style="max-height: 400px; overflow-y: auto;">
                        @forelse (Auth::user()->notifications()->latest()->take(20)->get() as $notification)
                            @php
                                $type = 'task';
                                $icon = 'fa-tasks';
                                if (str_contains($notification->type, 'Ticket')) {
                                    $type = 'ticket';
                                    $icon = 'fa-ticket-alt';
                                } elseif (str_contains($notification->type, 'Sla')) {
                                    $type = 'alert';
                                    $icon = 'fa-exclamation-triangle';
                                }

                                $url = '#';
                                if (isset($notification->data['ticket_id'])) {
                                    $url = route('admin.tickets.show', $notification->data['ticket_id']);
                                } elseif (isset($notification->data['task_id'])) {
                                    $url = route('details', $notification->data['task_id']);
                                }
                            @endphp
                            <a href="{{ $url }}"
                                class="notification-item-premium {{ $notification->unread() ? 'unread' : '' }}">
                                <div class="notification-icon-wrapper notification-icon-{{ $type }}">
                                    <i class="fas {{ $icon }}"></i>
                                </div>
                                <div class="notification-content">
                                    <div class="notification-title">
                                        {{ $notification->data['message'] ?? 'New Notification' }}
                                    </div>
                                    <div class="notification-description">
                                        {{ $notification->data['title'] ?? ($notification->data['description'] ?? 'No additional details') }}
                                    </div>
                                    <div class="notification-time">{{ $notification->created_at->diffForHumans() }}
                                    </div>
                                </div>
                                @if ($notification->unread())
                                    <div class="unread-indicator-dot"></div>
                                @endif
                            </a>
                        @empty
                            <div class="p-5 text-center">
                                <i class="far fa-bell-slash text-low mb-3" style="font-size: 2rem;"></i>
                                <div class="text-low small">No notifications found</div>
                            </div>
                        @endforelse
                    </div>
                </div>
                <div class="modal-footer border-0 p-3">
                    <button type="button" class="btn btn-premium-secondary btn-sm w-100"
                        data-bs-dismiss="modal">Close</button>
                </div>
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

            // Submenu Toggle JS
            window.toggleSubmenu = function(el) {
                const submenu = el.nextElementSibling;
                const icon = el.querySelector('.toggle-icon');

                if (submenu.classList.contains('show')) {
                    submenu.classList.remove('show');
                    icon.classList.remove('rotate');
                } else {
                    submenu.classList.add('show');
                    icon.classList.add('rotate');
                }
            };
        });
    </script>
    <div id="sidebarOverlay" class="sidebar-overlay d-lg-none"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar-premium');
            const mobileToggle = document.getElementById('mobileSidebarToggle');
            const overlay = document.getElementById('sidebarOverlay');

            if (mobileToggle) {
                mobileToggle.addEventListener('click', function() {
                    sidebar.classList.add('mobile-open');
                    overlay.classList.add('show');
                });
            }

            if (overlay) {
                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('mobile-open');
                    overlay.classList.remove('show');
                });
            }
        });
    </script>
    {{-- Page-specific scripts pushed via @push('scripts') --}}
    @stack('scripts')
    @livewireScripts
</body>

</html>
