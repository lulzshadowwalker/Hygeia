<!-- Chat Content Area -->
<div class="flex flex-col h-full bg-white">
    <!-- Chat Header -->
    <div class="border-b border-gray-200 p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center text-gray-600 font-semibold">
                    @if($chatRoom->participants->first())
                        {{ strtoupper(substr($chatRoom->participants->first()->name, 0, 1)) }}
                    @else
                        ?
                    @endif
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Chat Room #{{ $chatRoom->id }}</h2>
                    <p class="text-sm text-gray-500">{{ $chatRoom->messages_count ?? 0 }} messages</p>
                </div>
            </div>
            <button type="button" onclick="refreshMessages()" 
                    class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Refresh
            </button>
        </div>
    </div>

    <!-- Messages Area -->
    <div class="flex-1 overflow-y-auto p-4" id="messages-container">
        <div id="messages-list" class="space-y-4">
            @foreach($messages as $message)
                <div class="message-item flex items-start space-x-3" data-message-id="{{ $message->id }}">
                    <!-- User Avatar -->
                    <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center text-gray-600 font-semibold text-sm shrink-0">
                        {{ strtoupper(substr($message->user->name, 0, 1)) }}
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <div class="flex items-baseline space-x-2">
                            <span class="text-sm font-medium text-gray-900">{{ $message->user->name }}</span>
                            @if($message->user->isAdmin)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                    Admin
                                </span>
                            @endif
                            <span class="text-xs text-gray-500">{{ $message->created_at->format('M j, g:i A') }}</span>
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
    <div class="border-t border-gray-200 p-4">
        <form id="message-form" class="flex space-x-3">
            @csrf
            <div class="flex-1">
                <textarea
                    id="message-input"
                    rows="2"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm resize-none"
                    placeholder="Type your message..."
                    required
                ></textarea>
            </div>
            <div class="flex-shrink-0">
                <button
                    id="send-button"
                    type="submit"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                    Send
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Chat JavaScript -->
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
}

// Initialize chat functionality
(function() {
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
        if (typeof window.Echo !== 'undefined') {
            echo = window.Echo;
        }
    } catch (error) {
        console.error('Failed to initialize Echo:', error);
        // Continue without real-time features
    }

    // Listen for new messages
    if (echo) {
        echo.channel(`chat.room.${chatRoomId}`)
            .listen('MessageSent', (e) => {
                console.log('New message received:', e);
                const message = ChatEntities.convertMessage(e.message);
                if (message) {
                    addMessageToChat(message);
                    scrollToBottom();
                }
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

        // Send message via API
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ content: content })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                messageInput.value = '';
                const message = ChatEntities.convertMessage(data.data);
                if (message && !echo) { // Only add if echo is not handling it
                    addMessageToChat(message);
                    scrollToBottom();
                }
            } else {
                throw new Error(data.message || 'Failed to send message');
            }
        })
        .catch(error => {
            console.error('Error sending message:', error);
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
        
        const isAdmin = message.sender && message.sender.isAdmin;
        const adminBadge = isAdmin ? `
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                Admin
            </span>
        ` : '';

        const senderName = message.sender ? message.sender.name : 'Unknown User';
        const senderInitial = senderName.charAt(0).toUpperCase();

        messageDiv.innerHTML = `
            <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center text-gray-600 font-semibold text-sm shrink-0">
                ${senderInitial}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-baseline space-x-2">
                    <span class="text-sm font-medium text-gray-900">${escapeHtml(senderName)}</span>
                    ${adminBadge}
                    <span class="text-xs text-gray-500">${formatDate(message.createdAt)}</span>
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
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
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

    // Make refreshMessages available globally
    window.refreshMessages = function() {
        fetch(`{{ route('admin.support.chat.get-messages', $chatRoom) }}`)
            .then(response => response.json())
            .then(data => {
                messagesList.innerHTML = '';
                if (data.data && data.data.length > 0) {
                    data.data.forEach(messageResource => {
                        const message = ChatEntities.convertMessage(messageResource);
                        if (message) {
                            addMessageToChat(message);
                        }
                    });
                }
                scrollToBottom();
            })
            .catch(error => {
                console.error('Error refreshing messages:', error);
            });
    };

    // Initial scroll to bottom
    scrollToBottom();

    // Connection status indicator
    if (echo) {
        window.addEventListener('beforeunload', function() {
            echo.disconnect();
        });
    }
})();
</script>
