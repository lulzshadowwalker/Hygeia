<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chat System Playground - Hygeia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#2A7C41',
                        primaryDark: '#1E5A2F',
                        primaryLight: '#4A9B60',
                        secondary: '#03DAC6',
                        secondaryDark: '#018786',
                        surface: '#FFFFFF',
                        background: '#FAFAFA',
                    }
                }
            }
        }
    </script>
    @vite(['resources/js/app.js'])
</head>

<body class="bg-background min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Chat System Playground</h1>
            <p class="text-gray-600">Test your Laravel chat system with real-time messaging</p>

            @if(session('success'))
            <div class="mt-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">
                {{ session('success') }}
            </div>
            @endif

            <!-- Current User -->
            @auth
            <div class="mt-4 p-3 bg-primary/10 border border-primary/20 rounded">
                <strong>Currently logged in as:</strong> {{ Auth::user()->name }}
                <span class="text-sm text-gray-600">({{ Auth::user()->email }})</span>
                @if(Auth::user()->hasRole('admin'))
                <span class="ml-2 px-2 py-1 bg-red-100 text-red-800 text-xs rounded">Admin</span>
                @elseif(Auth::user()->hasRole('client'))
                <span class="ml-2 px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">Client</span>
                @elseif(Auth::user()->hasRole('cleaner'))
                <span class="ml-2 px-2 py-1 bg-green-100 text-green-800 text-xs rounded">Cleaner</span>
                @endif
            </div>
            @else
            <div class="mt-4 p-3 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded">
                <strong>Not logged in.</strong> Select a user below to test the chat system.
            </div>
            @endauth
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Sidebar: User Selection & Chat Rooms -->
            <div class="space-y-6">
                <!-- User Selection -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Test Users</h2>
                    <div class="space-y-2">
                        @foreach($users as $user)
                        <form action="{{ route('chat.playground.login-as') }}" method="POST" class="inline-block w-full">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $user->id }}">
                            <button type="submit"
                                class="w-full text-left p-3 rounded border {{ Auth::id() === $user->id ? 'bg-primary text-white border-primary' : 'bg-gray-50 hover:bg-gray-100 border-gray-200' }} transition-colors">
                                <div class="font-medium">{{ $user->name }}</div>
                                <div class="text-sm opacity-75">{{ $user->email }}</div>
                                <div class="flex gap-1 mt-1">
                                    @if($user->hasRole('admin'))
                                    <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded">Admin</span>
                                    @endif
                                    @if($user->hasRole('client'))
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">Client</span>
                                    @endif
                                    @if($user->hasRole('cleaner'))
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded">Cleaner</span>
                                    @endif
                                </div>
                            </button>
                        </form>
                        @endforeach
                    </div>
                </div>

                <!-- Chat Rooms List -->
                @auth
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Chat Rooms</h2>
                    <div class="space-y-2" id="chat-rooms-list">
                        @forelse($chatRooms as $room)
                        <div class="p-3 border rounded cursor-pointer hover:bg-gray-50 chat-room-item"
                            data-room-id="{{ $room->id }}">
                            <!-- Room Type -->
                            <div class="text-xs text-gray-400">
                                Type: {{ $room->type }}
                            </div>
                            <div class="font-medium">{{ $room->name }} ({{ $room->id }})</div>
                            <div class="text-sm text-gray-500">
                                {{ $room->participants->count() }} participants
                            </div>
                            @if($room->latestMessage->first())
                            <div class="text-xs text-gray-400 mt-1">
                                Last: {{ $room->latestMessage->first()->content ?? 'No messages yet' }}
                            </div>
                            @endif
                        </div>
                        @empty
                        <p class="text-gray-500 text-sm">No chat rooms available</p>
                        @endforelse
                    </div>

                    <button id="create-room-btn" class="mt-4 w-full bg-primary text-white px-4 py-2 rounded hover:bg-primaryDark transition-colors">
                        Create New Chat Room
                    </button>
                </div>
                @endauth
            </div>

            <!-- Main Chat Area -->
            @auth
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md h-[600px] flex flex-col">
                    <!-- Chat Header -->
                    <div class="p-4 border-b bg-gray-50 rounded-t-lg">
                        <h3 class="font-semibold text-gray-800" id="chat-header">
                            Select a chat room to start messaging
                        </h3>
                        <div class="text-sm text-gray-500" id="chat-participants"></div>
                    </div>

                    <!-- Messages Area -->
                    <div class="flex-1 p-4 overflow-y-auto" id="messages-container">
                        <div class="text-center text-gray-500 py-8">
                            Select a chat room to view messages
                        </div>
                    </div>

                    <!-- Message Input -->
                    <div class="p-4 border-t bg-gray-50 rounded-b-lg">
                        <form id="message-form" class="flex gap-2">
                            <input type="hidden" id="current-room-id" value="">
                            <input type="text"
                                id="message-input"
                                placeholder="Type your message..."
                                class="flex-1 px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                disabled>
                            <button type="submit"
                                id="send-button"
                                class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-primaryDark transition-colors disabled:opacity-50"
                                disabled>
                                Send
                            </button>
                        </form>
                    </div>
                </div>

                <!-- API Testing Panel -->
                <div class="mt-6 bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">API Testing</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <button id="test-get-rooms" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition-colors">
                            Test GET /chat/rooms
                        </button>
                        <button id="test-create-room" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition-colors">
                            Test POST /chat/rooms
                        </button>
                        <button id="test-get-messages" class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600 transition-colors">
                            Test GET /messages
                        </button>
                        <button id="test-reverb-config" class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600 transition-colors">
                            Test Reverb Config
                        </button>
                    </div>
                    <div id="api-results" class="mt-4 p-3 bg-gray-100 rounded-lg hidden">
                        <pre class="text-sm overflow-x-auto"></pre>
                    </div>
                </div>
            </div>
            @else
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-8 text-center">
                    <div class="text-gray-500">
                        <svg class="mx-auto h-16 w-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.955 8.955 0 01-4.126-.98L3 20l1.98-5.874A8.955 8.955 0 013 12c0-4.418 3.582-8 8-8s8 3.582 8 8z"></path>
                        </svg>
                        <h3 class="text-lg font-medium mb-2">Please Login to Test Chat</h3>
                        <p>Select a user from the left sidebar to start testing the chat system.</p>
                    </div>
                </div>
            </div>
            @endauth
        </div>
    </div>

    @auth
    <script>
        const API_BASE = '/api/v1';
        const CURRENT_USER_ID = {{ Auth::id() }};
        let currentRoomId = null;
        // let echoConnected = false;
        let echoConnected = true;

        // CSRF Token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        // API Headers
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Authorization': `Bearer {{ Auth::user()->createToken('playground')->plainTextToken ?? '' }}`
        };

        // Initialize Echo for real-time messaging
        function initializeEcho() {
            if (window.Echo && !echoConnected) {
                echoConnected = true;
                return;
            } 

            console.warn('Echo is already initialized or not available.');
        }

        // Format timestamp
        function formatTime(timestamp) {
            return new Date(timestamp).toLocaleTimeString();
        }

        // Display API result
        function showApiResult(data) {
            const resultsDiv = document.getElementById('api-results');
            const pre = resultsDiv.querySelector('pre');
            pre.textContent = JSON.stringify(data, null, 2);
            resultsDiv.classList.remove('hidden');
        }

        // Load chat room
        async function loadChatRoom(roomId) {
            try {
                currentRoomId = roomId;

                // Get room details
                const roomResponse = await fetch(`${API_BASE}/chat/rooms/${roomId}`, {
                    headers
                });
                const roomData = await roomResponse.json();

                // Update header
                // document.getElementById('chat-header').textContent = roomData.chat_room.name;
                // document.getElementById('chat-participants').textContent =
                //     `${roomData.chat_room.participants.length} participants: ${roomData.chat_room.participants.map(p => p.name).join(', ')}`;

                // Get messages
                const messagesResponse = await fetch(`${API_BASE}/chat/rooms/${roomId}/messages`, {
                    headers
                });
                const messagesData = await messagesResponse.json();

                // Display messages
                const container = document.getElementById('messages-container');
                container.innerHTML = '';

                if (messagesData.data.length === 0) {
                    container.innerHTML = '<div class="text-center text-gray-500 py-8">No messages in this room yet</div>';
                } else {
                    messagesData.data.reverse().forEach(message => {
                        addMessageToUI(message);
                    });
                }

                // Enable input
                document.getElementById('message-input').disabled = false;
                document.getElementById('send-button').disabled = false;
                document.getElementById('current-room-id').value = roomId;

                // Listen for new messages
                if (window.Echo && echoConnected) {
                    // window.Echo.private(`chat.room.${roomId}`)
                    window.Echo.channel(`chat.room.${roomId}`)
                        .listen('.message.sent', (data) => {
                            addMessageToUI(data);
                        });
                }

            } catch (error) {
                console.error('Error loading chat room:', error);
                alert('Error loading chat room');
            }
        }

        // Add message to UI
        function addMessageToUI(message) {
            const container = document.getElementById('messages-container');
            
            // when the message comes from Echo, it might be a json string
            if (typeof message === "string") {
                message = JSON.parse(message);
            }

            const isOwnMessage = message.relationships.sender.id == CURRENT_USER_ID;

            const messageDiv = document.createElement('div');
            messageDiv.className = `mb-4 ${isOwnMessage ? 'text-right' : 'text-left'}`;

            messageDiv.innerHTML = `
                <div class="inline-block max-w-xs lg:max-w-md px-4 py-2 rounded-lg ${
                    isOwnMessage 
                        ? 'bg-primary text-white' 
                        : 'bg-gray-200 text-gray-800'
                }">
                    <div class="text-xs opacity-75 mb-1">${message.relationships.sender.attributes.name}</div>
                    <div>${message.attributes.content}</div>
                    <div class="text-xs opacity-75 mt-1">${formatTime(message.attributes.createdAt)}</div>
                </div>
            `;

            container.appendChild(messageDiv);
            container.scrollTop = container.scrollHeight;
        }

        // Send message
        document.getElementById('message-form').addEventListener('submit', async (e) => {
            e.preventDefault();

            const input = document.getElementById('message-input');
            const content = input.value.trim();

            if (!content || !currentRoomId) return;

            try {
                const response = await fetch(`${API_BASE}/chat/rooms/${currentRoomId}/messages`, {
                    method: 'POST',
                    headers,
                    body: JSON.stringify({
                        data: {
                            attributes: {
                        content: content,
                        type: 'text'
                    }
                        }
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    input.value = '';
                    // Message will be added via Echo listener or manual refresh
                    if (!echoConnected) {
                        addMessageToUI(data.data);
                    }
                } else {
                    console.error('Error sending message:', data);
                    alert('Error sending message');
                }
            } catch (error) {
                console.error('Error sending message:', error);
                alert('Error sending message');
            }
        });

        // Chat room selection
        document.querySelectorAll('.chat-room-item').forEach(item => {
            item.addEventListener('click', () => {
                const roomId = item.dataset.roomId;
                loadChatRoom(roomId);

                // Update UI selection
                document.querySelectorAll('.chat-room-item').forEach(i => i.classList.remove('bg-primary', 'text-white'));
                item.classList.add('bg-primary', 'text-white');
            });
        });

        // Create new room
        document.getElementById('create-room-btn').addEventListener('click', async () => {
            try {
                const response = await fetch(`${API_BASE}/chat/rooms`, {
                    method: 'POST',
                    headers
                });

                const data = await response.json();

                if (response.ok) {
                    location.reload(); // Refresh to show new room
                } else {
                    console.error('Error creating room:', data);
                    alert('Error creating room');
                }
            } catch (error) {
                console.error('Error creating room:', error);
                alert('Error creating room');
            }
        });

        // API Testing buttons
        document.getElementById('test-get-rooms').addEventListener('click', async () => {
            try {
                const response = await fetch(`${API_BASE}/chat/rooms`, {
                    headers
                });
                const data = await response.json();
                showApiResult(data);
            } catch (error) {
                showApiResult({
                    error: error.message
                });
            }
        });

        document.getElementById('test-create-room').addEventListener('click', async () => {
            try {
                const response = await fetch(`${API_BASE}/chat/rooms`, {
                    method: 'POST',
                    headers
                });
                const data = await response.json();
                showApiResult(data);
            } catch (error) {
                showApiResult({
                    error: error.message
                });
            }
        });

        document.getElementById('test-get-messages').addEventListener('click', async () => {
            if (!currentRoomId) {
                alert('Please select a chat room first');
                return;
            }

            try {
                const response = await fetch(`${API_BASE}/chat/rooms/${currentRoomId}/messages`, {
                    headers
                });
                const data = await response.json();
                showApiResult(data);
            } catch (error) {
                showApiResult({
                    error: error.message
                });
            }
        });

        document.getElementById('test-reverb-config').addEventListener('click', async () => {
            try {
                const response = await fetch(`${API_BASE}/chat/reverb-config`, {
                    headers
                });
                const data = await response.json();
                showApiResult(data);
            } catch (error) {
                showApiResult({
                    error: error.message
                });
            }
        });

        // Initialize
        initializeEcho();
    </script>
    @endauth
</body>

</html>
