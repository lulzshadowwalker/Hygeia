<!-- Active Chat View -->
<div class="flex flex-col h-full bg-white w-full">
    <!-- Chat Header -->
    <div class="border-b border-gray-200 p-4 sm:p-6">
        <div class="flex items-center gap-4">
            <!-- User Avatar -->
            <div class="relative">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold text-lg shadow-lg">
                    @if($chatRoom->participants->where('isAdmin', '!=', true)->first())
                        {{ strtoupper(substr($chatRoom->participants->where('isAdmin', '!=', true)->first()->name, 0, 1)) }}
                    @else
                        C
                    @endif
                </div>
            </div>
            
            <div>
                <h2 class="text-xl font-bold text-gray-900">
                    @if($chatRoom->participants->where('isAdmin', '!=', true)->first())
                        {{ $chatRoom->participants->where('isAdmin', '!=', true)->first()->name }}
                    @else
                        Chat Room #{{ $chatRoom->id }}
                    @endif
                </h2>
                <div class="flex items-center gap-3 mt-1">
                    <p class="text-sm text-gray-500">
                        {{ $messages->count() }} messages
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages Area -->
    <div class="flex-1 overflow-y-auto bg-gray-50 chat-scroll" id="messages-container">
        <div id="messages-list" class="p-4 sm:p-6 space-y-4 sm:space-y-6">
            @if($messages->isEmpty())
                <!-- Empty State -->
                <div class="text-center py-12">
                    <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.955 8.955 0 01-4.126-.98L3 20l1.98-5.874A8.955 8.955 0 013 12c0-4.418 3.582-8 8-8s8 3.582 8 8z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Start the conversation</h3>
                    <p class="text-gray-500">Send a message to begin helping this customer.</p>
                </div>
            @else
                @foreach($messages as $message)
                    <div class="message-item flex {{ $message->user->isAdmin ? 'justify-end' : 'justify-start' }}" data-message-id="{{ $message->id }}">
                        @if(!$message->user->isAdmin)
                            <!-- Customer Message -->
                            <div class="flex items-start space-x-3 max-w-sm sm:max-w-lg">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-semibold text-sm shrink-0">
                                    {{ strtoupper(substr($message->user->name, 0, 1)) }}
                                </div>
                                <div class="flex flex-col">
                                    <div class="bg-white rounded-2xl rounded-tl-md px-4 py-3 shadow-sm border border-gray-100">
                                        <p class="text-gray-900 text-sm leading-relaxed">{{ $message->content }}</p>
                                    </div>
                                    <div class="flex items-center mt-1 ml-3">
                                        <span class="text-xs text-gray-500">{{ $message->user->name }}</span>
                                        <span class="text-xs text-gray-400 mx-2">•</span>
                                        <span class="text-xs text-gray-500">{{ $message->created_at->format('g:i A') }}</span>
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- Admin Message -->
                            <div class="flex items-start space-x-3 max-w-sm sm:max-w-lg">
                                <div class="flex flex-col items-end">
                                    <div class="bg-green-600 rounded-2xl rounded-tr-md px-4 py-3 shadow-sm">
                                        <p class="text-white text-sm leading-relaxed">{{ $message->content }}</p>
                                    </div>
                                    <div class="flex items-center mt-1 mr-3">
                                        <span class="text-xs text-gray-500">{{ $message->created_at->format('g:i A') }}</span>
                                        <span class="text-xs text-gray-400 mx-2">•</span>
                                        <span class="text-xs text-gray-500">{{ $message->user->name }}</span>
                                        <span class="inline-flex items-center ml-2 px-1.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">
                                            Admin
                                        </span>
                                    </div>
                                </div>
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center text-white font-semibold text-sm shrink-0">
                                    {{ strtoupper(substr($message->user->name, 0, 1)) }}
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <!-- Message Composer -->
    <div class="border-t border-gray-200 bg-white p-4 sm:p-6 relative">
        <form id="message-form" class="flex items-end space-x-4" onsubmit="return false;">
            @csrf
            <div class="flex-1">
                <div class="relative">
                    <textarea
                        id="message-input"
                        rows="1"
                        class="outline-none block w-full rounded-2xl border-gray-300 shadow-sm focus:ring-2 focus:ring-green-600 focus:ring-opacity-50 resize-none py-3 px-4 text-sm placeholder-gray-500 bg-gray-50 focus:bg-white transition-all duration-200 ring-offset-2 border"
                        placeholder="Type your message..."
                        style="min-height: 44px; max-height: 120px;"
                    ></textarea>
                </div>
            </div>
            
            <!-- Send Button -->
            <div class="flex-shrink-0">
                <button
                    id="send-button"
                    type="submit"
                    class="inline-flex items-center justify-center w-12 h-12 rounded-full text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 transform hover:scale-105"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>
