<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - Hygeia</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                        },
                        green: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Custom Chat Styles -->
    <style>
        /* Chat-specific animations and styles */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .message-item {
            animation: fadeInUp 0.3s ease-out;
        }
        
        /* Scrollbar styling */
        .chat-scroll::-webkit-scrollbar {
            width: 6px;
        }
        
        .chat-scroll::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        
        .chat-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        
        .chat-scroll::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        /* Textarea auto-resize */
        #message-input {
            transition: height 0.1s ease-out;
        }
        
        /* Quick Reply and Template Dropdown Styles */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        /* Dropdown positioning */
        #quick-reply-dropdown,
        #template-dropdown {
            animation: fadeInUp 0.2s ease-out;
            z-index: 9999 !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        /* Backdrop blur effect for dropdowns */
        .dropdown-backdrop {
            backdrop-filter: blur(2px);
        }
    </style>
    
    <!-- Vite Assets -->
    @vite(['resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen">

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>
</body>
</html>
