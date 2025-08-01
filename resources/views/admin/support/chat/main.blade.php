@extends('layouts.admin')

@section('title', 'Support Chat')

@section('content')
<div class="h-screen bg-gray-100 flex overflow-hidden">
    <!-- Conversations Sidebar -->
    <main class="chat-list flex flex-col w-96 shrink-0 bg-white border-r border-gray-300">
        <!-- Header -->
        <div class="p-4 border-b border-gray-300">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-green-600 rounded-lg flex items-center justify-center text-white font-bold text-lg">
                    S
                </div>
                <div>
                    <h1 class="text-lg font-bold text-gray-900">Support HQ</h1>
                    <p class="text-sm text-gray-600">Admin Panel</p>
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
    // This will be implemented when we add the chat functionality
    console.log('Initializing chat room:', roomId);
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
