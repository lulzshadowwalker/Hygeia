@extends('layouts.admin')

@section('title', 'Support Chat')

@push('scripts')
<script src="{{ asset('js/chat-entities.js') }}"></script>
@endpush

@section('content')
<div class="h-screen bg-gray-100 flex overflow-hidden">
    <!-- Conversations Sidebar -->
    <main class="chat-list flex flex-col w-80 md:w-96 shrink-0 bg-white border-r border-gray-300">
        <!-- Header -->
        <div class="p-4 border-b border-gray-300">
            <div class="flex items-start gap-3">
                <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="w-10 h-10 rounded-lg mt-2">
                <div>
                    <h1 class="text-lg font-bold text-gray-900">Support HQ</h1>
                    <p class="text-sm text-gray-600 text-pretty">
                        Answer questions and resolve issues for your customers.
                    </p>
                </div>
            </div>
        </div>

        <!-- Chat Rooms List -->
        <div class="flex-1 overflow-y-auto">
            @if($supportRooms->isEmpty())
                <div class="p-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.955 8.955 0 01-4.126-.98L3 20l1.98-5.874A8.955 8.955 0 013 12c0-4.418 3.582-8 8-8s8 3.582 8 8z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No conversations</h3>
                    <p class="mt-1 text-sm text-gray-500">No support conversations are currently active.</p>
                </div>
            @else
                @foreach($supportRooms as $room)
                    <div class="chat-item border-b border-gray-200 p-4 flex gap-4 cursor-pointer hover:bg-green-50 transition-colors duration-200 {{ request()->route('chatRoom') && request()->route('chatRoom')->id == $room->id ? 'bg-green-100' : '' }}"
                         onclick="selectChatRoom({{ $room->id }})">
                        
                        <!-- Avatar placeholder - using first participant's initial or default -->
                        <div class="w-12 h-12 rounded-full bg-gray-300 flex items-center justify-center text-gray-600 font-semibold shrink-0">
                            @if($room->participants->first())
                                {{ strtoupper(substr($room->participants->first()->name, 0, 1)) }}
                            @else
                                ?
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-baseline">
                                <p class="font-semibold text-gray-900 truncate">
                                    Chat Room {{ $room->id }}
                                </p>
                                <p class="text-xs text-gray-500 shrink-0 ml-2">
                                    @if($room->latestMessage)
                                        {{ $room->latestMessage->created_at->diffForHumans() }}
                                    @else
                                        {{ $room->updated_at->diffForHumans() }}
                                    @endif
                                </p>
                            </div>
                            <p class="text-sm text-gray-600 truncate mt-1">
                                @if($room->latestMessage)
                                    {{ $room->latestMessage->content }}
                                @else
                                    No messages yet
                                @endif
                            </p>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </main>

    <!-- Chat Area -->
    <section class="flex-1 flex items-center justify-center bg-gray-100" id="chat-area">
        <div class="text-center">
            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1"></path>
            </svg>
            <h3 class="mt-2 text-lg font-medium text-gray-900">Select a conversation</h3>
            <p class="mt-1 text-sm text-gray-500">Choose from the list on the left to start chatting.</p>
        </div>
    </section>
</div>

<style>
:root {
    --brand-green-lightest: #F3FAF7;
    --brand-green-lighter: #E6F4EC;
    --brand-green-light: #C1E4D5;
    --brand-green: #2B8C64;
    --brand-green-dark: #227050;
}

.chat-list {
    border-right: 1px solid var(--neutral-300);
    background-color: white;
}

.chat-item {
    transition: background-color 0.2s ease-in-out;
}

.chat-item:hover {
    background-color: var(--brand-green-lightest);
}

.chat-item.active {
    background-color: var(--brand-green-lighter);
}
</style>

<script>
function selectChatRoom(roomId) {
    // Update URL without page reload
    const newUrl = `{{ url('admin/support/chat/main') }}/${roomId}`;
    history.pushState({roomId: roomId}, '', newUrl);
    
    // Update active state
    document.querySelectorAll('.chat-item').forEach(item => {
        item.classList.remove('active', 'bg-green-100');
    });
    event.currentTarget.classList.add('active', 'bg-green-100');
    
    // Load chat content
    loadChatRoom(roomId);
}

function loadChatRoom(roomId) {
    const chatArea = document.getElementById('chat-area');
    chatArea.innerHTML = `
        <div class="flex items-center justify-center h-full">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
        </div>
    `;
    
    // Fetch chat room content via AJAX
    fetch(`/admin/support/chat/${roomId}/content`)
        .then(response => response.text())
        .then(html => {
            chatArea.innerHTML = html;
            // Initialize chat functionality
            initializeChatRoom(roomId);
        })
        .catch(error => {
            console.error('Error loading chat room:', error);
            chatArea.innerHTML = `
                <div class="text-center">
                    <div class="text-red-500 mb-2">
                        <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Error loading conversation</h3>
                    <p class="mt-1 text-sm text-gray-500">Please try again or refresh the page.</p>
                </div>
            `;
        });
}

function initializeChatRoom(roomId) {
    // DOM elements - wait a moment for them to be available
    setTimeout(() => {
        const messagesContainer = document.getElementById('messages-container');
        const messagesList = document.getElementById('messages-list');
        const messageForm = document.getElementById('message-form');
        const messageInput = document.getElementById('message-input');
        const sendButton = document.getElementById('send-button');
        
        // Initialize form submission if form exists
        if (messageForm && messageInput && sendButton) {
            initializeMessageForm();
        }
        
        // Initialize Echo for real-time messaging
        if (window.Echo) {
            console.log('Initializing Echo for room:', roomId);
            window.Echo.channel(`chat.room.${roomId}`)
                .listen('.message.sent', (data) => {
                    console.log('New message received via Echo:', data);
                    addMessageToUI(data);
                });
        }
        
        // Add message to UI function
        function addMessageToUI(messageData) {
            console.log('Adding message to UI:', messageData);
            
            const messagesList = document.getElementById('messages-list');
            if (!messagesList) return;
            
            
            // Convert message data using ChatEntities
            const message = window.ChatEntities ? 
                window.ChatEntities.convertMessage(messageData) : 
                messageData;
            
            if (!message) {
                console.error('Failed to convert message data:', messageData);
                return;
            }
            
            console.log('Converted message:', message);
            
            // Get current user ID from auth
            const currentUserId = {{ Auth::id() ?? 'null' }};
            const isAdmin = message.sender?.isAdmin || false;
            const isOwnMessage = message.sender?.id == currentUserId;
            
            // Create message element
            const messageDiv = document.createElement('div');
            messageDiv.className = `message-item flex ${isAdmin ? 'justify-end' : 'justify-start'}`;
            messageDiv.setAttribute('data-message-id', message.id);
            
            const userName = message.sender?.name || 'Unknown';
            const messageContent = message.content || '';
            const createdAt = message.createdAt || new Date().toISOString();
            
            if (!isAdmin) {
                // Customer Message
                messageDiv.innerHTML = `
                    <div class="flex items-start space-x-3 max-w-sm sm:max-w-lg">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-semibold text-sm shrink-0">
                            ${userName.charAt(0).toUpperCase()}
                        </div>
                        <div class="flex flex-col">
                            <div class="bg-white rounded-2xl rounded-tl-md px-4 py-3 shadow-sm border border-gray-100">
                                <p class="text-gray-900 text-sm leading-relaxed">${messageContent}</p>
                            </div>
                            <div class="flex items-center mt-1 ml-3">
                                <span class="text-xs text-gray-500">${userName}</span>
                                <span class="text-xs text-gray-400 mx-2">•</span>
                                <span class="text-xs text-gray-500">${new Date(createdAt).toLocaleTimeString()}</span>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                // Admin Message
                messageDiv.innerHTML = `
                    <div class="flex items-start space-x-3 max-w-sm sm:max-w-lg">
                        <div class="flex flex-col items-end">
                            <div class="bg-green-600 rounded-2xl rounded-tr-md px-4 py-3 shadow-sm">
                                <p class="text-white text-sm leading-relaxed">${messageContent}</p>
                            </div>
                            <div class="flex items-center mt-1 mr-3">
                                <span class="text-xs text-gray-500">${new Date(createdAt).toLocaleTimeString()}</span>
                                <span class="text-xs text-gray-400 mx-2">•</span>
                                <span class="text-xs text-gray-500">${userName}</span>
                                <span class="inline-flex items-center ml-2 px-1.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">
                                    Admin
                                </span>
                            </div>
                        </div>
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center text-white font-semibold text-sm shrink-0">
                            ${userName.charAt(0).toUpperCase()}
                        </div>
                    </div>
                `;
            }
            
            messagesList.appendChild(messageDiv);
            
            // Scroll to bottom
            const messagesContainer = document.getElementById('messages-container');
            if (messagesContainer) {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        }
        
        function insertTextIntoInput(text) {
            if (!messageInput) return;
            
            const cursorPos = messageInput.selectionStart;
            const currentValue = messageInput.value;
            const newValue = currentValue.slice(0, cursorPos) + text + currentValue.slice(messageInput.selectionEnd);
            
            messageInput.value = newValue;
            messageInput.focus();
            
            // Set cursor position after inserted text
            const newCursorPos = cursorPos + text.length;
            messageInput.setSelectionRange(newCursorPos, newCursorPos);
        }

        function initializeMessageForm() {
            // Handle form submission
            messageForm.addEventListener('submit', async (e) => {
                console.log('Form submit event triggered');
                e.preventDefault();
                
                const messageContent = messageInput.value.trim();
                if (!messageContent) {
                    console.log('Empty message, not sending');
                    return;
                }
                
                console.log('Sending message:', messageContent);
                
                // Disable form while sending
                sendButton.disabled = true;
                messageInput.disabled = true;
                
                try {
                    const response = await fetch(`/admin/support/chat/${roomId}/messages`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            content: messageContent
                        })
                    });
                    
                    if (response.ok) {
                        // Clear the input
                        messageInput.value = '';
                        // Message will be added via Echo real-time listener
                        console.log('Message sent successfully, waiting for Echo update');
                    } else {
                        console.error('Failed to send message');
                        alert('Failed to send message. Please try again.');
                    }
                } catch (error) {
                    console.error('Error sending message:', error);
                    alert('Error sending message. Please try again.');
                } finally {
                    // Re-enable form
                    sendButton.disabled = false;
                    messageInput.disabled = false;
                    messageInput.focus();
                }
            });
            
            // Handle enter key (submit on Enter, new line on Shift+Enter)
            messageInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    messageForm.dispatchEvent(new Event('submit'));
                }
            });
        }
    }, 100); // Small delay to ensure DOM elements are available
}

// Handle browser back/forward
window.addEventListener('popstate', function(event) {
    if (event.state && event.state.roomId) {
        loadChatRoom(event.state.roomId);
    } else {
        // Show default state
        document.getElementById('chat-area').innerHTML = `
            <div class="text-center">
                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1"></path>
                </svg>
                <h3 class="mt-2 text-lg font-medium text-gray-900">Select a conversation</h3>
                <p class="mt-1 text-sm text-gray-500">Choose from the list on the left to start chatting.</p>
            </div>
        `;
    }
});
</script>
@endsection
