@extends('layouts.admin')

@section('title', 'Support Chat Room #' . $chatRoom->id)

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="md:flex md:items-center md:justify-between">
                <div class="flex-1 min-w-0">
                    <nav class="flex" aria-label="Breadcrumb">
                        <ol role="list" class="flex items-center space-x-4">
                            <li>
                                <div>
                                    <a href="{{ route('admin.support.chat.index') }}" class="text-gray-400 hover:text-gray-500">
                                        <svg class="flex-shrink-0 h-5 w-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                                        </svg>
                                        <span class="sr-only">Support Chat</span>
                                    </a>
                                </div>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <svg class="flex-shrink-0 h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="ml-4 text-sm font-medium text-gray-500">Room #{{ $chatRoom->id }}</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                    <h1 class="text-3xl font-bold text-gray-900 mt-2">Support Chat Room #{{ $chatRoom->id }}</h1>
                    <p class="mt-1 text-sm text-gray-500">{{ $chatRoom->participants->count() }} participants • {{ $chatRoom->messages_count }} messages</p>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4">
                    <span class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white">
                        <span class="mr-2 flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-2 w-2 rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                        </span>
                        Live Chat
                    </span>
                </div>
            </div>
        </div>

        <!-- Chat Interface -->
        <div class="bg-white shadow-xl rounded-lg overflow-hidden" style="height: calc(100vh - 200px);">
            <div class="flex h-full">
                <!-- Chat Messages Area -->
                <div class="flex-1 flex flex-col">
                    <!-- Messages Container -->
                    <div id="messages-container" class="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50">
                        <div id="messages-list">
                            @foreach($messages as $message)
                                <div class="message-item flex items-start space-x-3" data-message-id="{{ $message->id }}">
                                    <div class="flex-shrink-0">
                                        <div class="h-8 w-8 rounded-full bg-primary-500 flex items-center justify-center">
                                            <span class="text-sm font-medium text-white">
                                                {{ strtoupper(substr($message->user->name, 0, 1)) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="text-sm">
                                            <span class="font-medium text-gray-900">{{ $message->user->name }}</span>
                                            <span class="text-gray-500 ml-2">{{ $message->created_at->format('M j, g:i A') }}</span>
                                            @if($message->user->hasRole('Admin'))
                                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                    Admin
                                                </span>
                                            @endif
                                        </div>
                                        <div class="mt-1 text-sm text-gray-700">
                                            {{ $message->content }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Message Input -->
                    <div class="border-t border-gray-200 bg-white px-6 py-4">
                        <form id="message-form" class="flex space-x-3">
                            @csrf
                            <div class="flex-1">
                                <textarea id="message-input" 
                                         name="content" 
                                         rows="2" 
                                         class="block w-full border-gray-300 rounded-lg resize-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm" 
                                         placeholder="Type your message..."
                                         required></textarea>
                            </div>
                            <div class="flex-shrink-0">
                                <button type="submit" 
                                        id="send-button"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                    </svg>
                                    Send
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Participants Sidebar -->
                <div class="w-64 border-l border-gray-200 bg-white">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h3 class="text-sm font-medium text-gray-900">Participants ({{ $chatRoom->participants->count() }})</h3>
                    </div>
                    <div class="p-4 space-y-3">
                        @foreach($chatRoom->participants as $participant)
                            <div class="flex items-center space-x-3">
                                <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                    <span class="text-sm font-medium text-gray-700">
                                        {{ strtoupper(substr($participant->name, 0, 1)) }}
                                    </span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $participant->name }}</p>
                                    <p class="text-xs text-gray-500">
                                        @if($participant->hasRole('Admin'))
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                Admin
                                            </span>
                                        @elseif($participant->hasRole('Client'))
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                Client
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ $participant->roles->first()->name ?? 'User' }}
                                            </span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Real-time Chat JavaScript -->
<script>
/**
 * Chat Entities Converter
 * Converts JSON API resource responses into clean entity objects
 */
class ChatEntities {
    static convertMessage(messageResource) {
        if (!messageResource || !messageResource.id) {
            console.warn('Invalid message resource:', messageResource);
            return null;
        }

        return {
            id: messageResource.id,
            content: messageResource.attributes?.content || '',
            type: messageResource.attributes?.type || 'text',
            mine: messageResource.attributes?.mine || false,
            createdAt: messageResource.attributes?.createdAt || null,
            updatedAt: messageResource.attributes?.updatedAt || null,
            sender: messageResource.relationships?.sender ? 
                this.convertUser(messageResource.relationships.sender) : null
        };
    }

    static convertUser(userResource) {
        if (!userResource || !userResource.id) {
            console.warn('Invalid user resource:', userResource);
            return null;
        }

        return {
            id: userResource.id,
            name: userResource.attributes?.name || 'Unknown User',
            avatar: userResource.attributes?.avatar || null,
            type: userResource.attributes?.type || 'user',
            isAdmin: userResource.attributes?.type === 'admin',
            isClient: userResource.attributes?.type === 'client',
            isCleaner: userResource.attributes?.type === 'cleaner',
            createdAt: userResource.attributes?.createdAt || null,
            updatedAt: userResource.attributes?.updatedAt || null
        };
    }

    static convert(data, type = null) {
        if (!data) return null;

        if (Array.isArray(data)) {
            return data.map(item => this.convertMessage(item)).filter(Boolean);
        }

        const resourceType = type || data.type;
        switch (resourceType) {
            case 'message':
                return this.convertMessage(data);
            case 'user':
            case 'admin':
            case 'client':
            case 'cleaner':
                return this.convertUser(data);
            default:
                return data;
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Configuration from controller
    const config = @json($reverbConfig);
    const chatRoomId = {{ $chatRoom->id }};
    const currentUserId = {{ auth()->id() }};
    
    // DOM elements
    const messagesContainer = document.getElementById('messages-container');
    const messagesList = document.getElementById('messages-list');
    const messageForm = document.getElementById('message-form');
    const messageInput = document.getElementById('message-input');
    const sendButton = document.getElementById('send-button');

    // Initialize Echo with configuration
    let echo = null;
    
    try {
        if (typeof window.Echo === 'undefined') {
            // Initialize Echo if not already available
            window.Echo = new Echo({
                broadcaster: 'reverb',
                key: config.key,
                wsHost: config.host,
                wsPort: config.port,
                wssPort: config.port,
                forceTLS: config.scheme === 'https',
                enabledTransports: ['ws', 'wss'],
                authEndpoint: '/broadcasting/auth'
            });
        }
        echo = window.Echo;
    } catch (error) {
        console.error('Failed to initialize Echo:', error);
        // Continue without real-time features
    }

    // Listen for new messages
    if (echo) {
        echo.channel(`chat.room.${chatRoomId}`)
            .listen('.message.sent', (messageResource) => {
                console.log('New message resource received:', messageResource);
                
                // Convert resource to clean entity
                const message = ChatEntities.convertMessage(messageResource);
                console.log('Converted message entity:', message);
                
                if (message) {
                    addMessageToChat(message);
                    scrollToBottom();
                }
            })
            .error((error) => {
                console.error('Echo subscription error:', error);
            });
    }

    // Handle form submission
    messageForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const content = messageInput.value.trim();
        if (!content) return;

        // Disable form while sending
        setSendingState(true);

        const url = `{{ route('admin.support.chat.send-message', $chatRoom) }}`;
        console.log('Sending message to URL:', url);

        // Send message via API
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ content: content })
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                messageInput.value = '';
                // Message will be added via Echo broadcast
                if (!echo) {
                    // Fallback: add message directly if Echo is not working
                    const message = ChatEntities.convertMessage(data.data);
                    if (message) {
                        addMessageToChat(message);
                        scrollToBottom();
                    }
                }
            } else {
                throw new Error(data.message || 'Failed to send message');
            }
        })
        .catch(error => {
            console.error('Failed to send message:', error);
            alert('Failed to send message. Please try again.');
        })
        .finally(() => {
            setSendingState(false);
        });
    });

    // Handle Enter key (Shift+Enter for new line)
    messageInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            messageForm.dispatchEvent(new Event('submit'));
        }
    });

    // Helper functions
    function addMessageToChat(message) {
        const messageElement = createMessageElement(message);
        messagesList.appendChild(messageElement);
    }

    function createMessageElement(message) {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'message-item flex items-start space-x-3';
        messageDiv.setAttribute('data-message-id', message.id);
        
        // Use clean entity properties
        const isAdmin = message.sender && message.sender.isAdmin;
        const adminBadge = isAdmin ? `
            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                Admin
            </span>
        ` : '';

        const senderName = message.sender ? message.sender.name : 'Unknown User';
        const senderInitial = senderName.charAt(0).toUpperCase();

        messageDiv.innerHTML = `
            <div class="flex-shrink-0">
                <div class="h-8 w-8 rounded-full bg-primary-500 flex items-center justify-center">
                    <span class="text-sm font-medium text-white">
                        ${senderInitial}
                    </span>
                </div>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-sm">
                    <span class="font-medium text-gray-900">${escapeHtml(senderName)}</span>
                    <span class="text-gray-500 ml-2">${formatDate(message.createdAt)}</span>
                    ${adminBadge}
                </div>
                <div class="mt-1 text-sm text-gray-700">
                    ${escapeHtml(message.content)}
                </div>
            </div>
        `;
        
        return messageDiv;
    }

    function setSendingState(sending) {
        sendButton.disabled = sending;
        messageInput.disabled = sending;
        
        if (sending) {
            sendButton.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Sending...
            `;
        } else {
            sendButton.innerHTML = `
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                </svg>
                Send
            `;
        }
    }

    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric', 
            hour: 'numeric', 
            minute: '2-digit' 
        });
    }

    // Initial scroll to bottom
    scrollToBottom();

    // Connection status indicator
    if (echo) {
        window.addEventListener('beforeunload', function() {
            echo.disconnect();
        });
    }
});
</script>
@endsection
