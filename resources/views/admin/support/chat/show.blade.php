@extends('layouts.chat')

@section('chat-area')
<div class="flex flex-col h-full bg-white w-full">
    <header class="flex items-center gap-2 p-4 border-b border-muted">
        <img src="{{ $chatRoom->user->avatar }}" alt="Sofia Davis" class="w-10 h-10 rounded-full">
        <div class="flex flex-col gap-1 mr-auto">
            <h3 class="text-sm font-medium leading-none">{{ $chatRoom->user->name }}</h3>
            <p class="text-sm text-muted-foreground">
                {{ $chatRoom->user->email }}
            </p>
        </div>

        <button type="button" class="btn-icon-outline rounded-full" data-tooltip="Call {{ $chatRoom->user->phone }}" data-side="bottom" data-align="end" @disabled(! $chatRoom->user->phone)>
            <a href="tel:{{ $chatRoom->user->phone }}">
                <i class="fas fa-phone-alt text-gray-500 hover:text-gray-700 rotate-90"></i>
            </a>
        </button>

        <button class="btn-icon-outline rounded-full" @disabled(! $chatRoom->user->email) data-tooltip="Email {{ $chatRoom->user->email }}" data-side="bottom" data-align="end">
            <a href="mailto:{{ $chatRoom->user->email }}">
                <i class="fas fa-envelope text-gray-500 hover:text-gray-700"></i>
            </a>
        </button>
    </header>

    <!-- Messages Area -->
    <div class="flex-1 overflow-y-auto bg-gray-50 chat-scroll" id="js-message-container">
        <div x-data="chat" id="js-message-list" class="p-4 sm:p-6 space-y-4 sm:space-y-6">
            <template x-if="messages.length === 0">
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
            </template>

            <template x-for="message in messages" :key="message.id">
                <div class="message-item flex" :class="{ 'justify-end': message.sender.isAdmin }">
                    <div
                        class="flex w-max max-w-[75%] flex-col gap-2 rounded-lg px-3 py-2 text-sm bg-muted"
                        :class="{ 'bg-primary text-primary-foreground': message.sender.isAdmin }"
                        x-text="message.content">
                    </div>
                </div>
            </template>
        </div>
    </div>

    <form x-target="js-send-button" action="{{ route('admin.support.chat.send', $chatRoom->id) }}" @ajax:success="$el.reset()" method="post" class="flex items-center space-x-2 p-4 border-t border-muted" x-data="{ input: '', pending: false }" @ajax:before="pending = true" @ajax:after="pending = false" @ajax:error="toast.error('Failed to send message. Please try again later.')" @ajax:success="toast.success('Message sent successfully!')">
        @csrf
        <input name="content" class="input" type="text" placeholder="Type your message here..." x-model="input" autofocus>
        <button id="js-send-button" type="submit" class="btn" :disabled="!input">
            <i class="fas fa-paper-plane" :class="{ '!hidden': pending }"></i>
            <span class="fas fa-circle-notch fa-spin" :class="{ '!hidden': !pending }"></span>
        </button>
    </form>
</div>

<script>
    document.addEventListener('alpine:init', function() {
        Alpine.data('chat', () => ({
            messages: @json($messages).map((message) => ChatEntities.convertMessage(message)),

            appendMessage(message) {
                this.messages.push(ChatEntities.convertMessage(message));
                this.scroll();
            },

            init() {
                this.scroll();
                const room = '<?= $chatRoom->id ?>';
                this.initializeSocket(room);

            },

            /// Scroll to the bottom of the messages list
            scroll() {
                this.$nextTick(() => {
                    const container = document.getElementById('js-message-container');
                    if (container) {
                        container.scrollTop = container.scrollHeight;
                    }
                })
            },

            initializeSocket(roomId) {
                console.log('Initializing chat room:', roomId);
                if (window.Echo) {
                    window.Echo.channel(`chat.room.${roomId}`)
                        .listen(
                            '.message.sent',
                            (data) => this.appendMessage(data)
                        );
                    return;
                }

                console.warn('Echo is not initialized');
            }
        }))
    });
</script>
@endsection
