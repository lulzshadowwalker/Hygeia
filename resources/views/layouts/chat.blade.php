@extends('layouts.admin')

@section('title', 'Support Chat')

@section('content')
<div class="h-screen bg-gray-100 flex overflow-hidden">
    <aside class="sidebar" data-side="left" aria-hidden="false">
        <nav aria-label="Sidebar navigation">
            <header>
                <a href="/" class="btn-ghost px-2 h-12 w-full justify-start">
                    <div class="bg-sidebar-primary text-sidebar-primary-foreground flex aspect-square size-8 items-center justify-center rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256" class="h-4 w-4">
                            <rect width="256" height="256" fill="none"></rect>
                            <line x1="208" y1="128" x2="128" y2="208" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32"></line>
                            <line x1="192" y1="40" x2="40" y2="192" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32"></line>
                        </svg>
                    </div>
                    <div class="grid flex-1 text-left text-sm leading-tight">
                        <span class="truncate font-medium">{{ config('app.name') }}</span>
                        <span class="truncate text-xs">Support Chat</span>
                    </div>
                </a>
            </header>

            <section x-data="rooms" class="scrollbar">
                <div role="group" aria-labelledby="group-label-content-1">
                    <h3 id="group-label-content-1">Conversations</h3>

                    <template x-if="rooms.filter(room => room.latestMessage).length === 0">
                        <div class="p-8 flex flex-col items-center justify-center text-center">
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No conversations</h3>
                            <p class="mt-1 text-sm text-gray-500">No support conversations are currently active.</p>
                        </div>
                    </template>

                    <ul>
                        <template x-for="room in rooms" :key="room.id">
                            <template x-if="room.latestMessage">
                                <li>
                                    <a :href="`/admin/support/chat/${room.id}`" class="flex items-center gap-2 p-2 hover:bg-gray-200 rounded">
                                        <img :src="room.user.avatar" alt="" class="size-6 shrink-0 object-cover rounded-full">
                                        <span x-text="room.user.name"></span>
                                        <!-- 13 minutes ago -->
                                        <span class="ms-auto text-xs text-gray-500" x-text="humanReadable(room.latestMessage.createdAt)"></span>
                                    </a>
                                </li>
                            </template>
                        </template>
                    </ul>
                </div>
            </section>

            <footer>
                <div id="demo-dropdown-menu" class="dropdown-menu">
                    <button id="demo-dropdown-menu-trigger" type="button" aria-expanded="false" aria-controls="demo-dropdown-menu-menu" class="btn-ghost p-2 h-12 w-full flex items-center justify-start" data-keep-mobile-sidebar-open="">
                        <img src="{{ auth()->user()->avatar }}" class="rounded-lg shrink-0 size-8">
                        <div class="grid flex-1 text-left text-sm leading-tight">
                            <span class="truncate font-medium">{{ auth()->user()->name }}</span>
                            <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m7 15 5 5 5-5"></path>
                            <path d="m7 9 5-5 5 5"></path>
                        </svg>
                    </button>

                    <div id="demo-dropdown-menu-popover" data-side="top" data-popover aria-hidden="true" class="min-w-56">
                        <div role="menu" id="demo-dropdown-menu-menu" aria-labelledby="demo-dropdown-menu-trigger">
                            <div role="menuitem">
                                <!--  back to admin panel -->
                                <a href="{{ route('filament.admin.pages.dashboard') }}" class="flex items-center justify-start w-full">
                                    Admin Panel
                                    <span class="text-muted-foreground ml-auto text-xs tracking-widest">⇧⌘D</span>
                                </a>
                            </div>
                            <hr role="separator" />
                            <div role="menuitem" class="transition-colors hover:bg-destructive/20 hover:text-destructive">
                                <form action="{{  route('filament.admin.auth.logout') }}" method="post" class="w-full" id="logout-form" @keyup.shift.cmd.c.window="alert('hihi')">
                                    @csrf
                                    <button class="w-full flex items-center justify-start cursor-pointer">
                                        Logout
                                        <span class="text-muted-foreground ml-auto text-xs tracking-widest">⇧⌘C</span>
                                    </button>
                                </form>
                            </div>

                            <script>
                                document.addEventListener('alpine:init', () => {
                                    window.addEventListener('keydown', (e) => {
                                        if (e.shiftKey && (e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'c') {
                                            e.preventDefault();
                                            document.getElementById('logout-form').submit();
                                        }
                                    });

                                    window.addEventListener('keydown', (e) => {
                                        if (e.shiftKey && (e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'd') {
                                            e.preventDefault();
                                            window.location.href = "{{ route('filament.admin.pages.dashboard') }}";
                                        }
                                    });
                                });
                            </script>
                        </div>
                    </div>
                </div>
            </footer>
        </nav>
    </aside>

    <!-- Chat Area -->
    <section class="flex-1 flex items-center justify-center bg-gray-100 relative">
        @yield('chat-area')
    </section>
</div>

<script>
    function humanReadable(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;

        const seconds = Math.floor(diff / 1000);
        const minutes = Math.floor(seconds / 60);
        const hours = Math.floor(minutes / 60);
        const days = Math.floor(hours / 24);

        if (days > 0) return `${days} day${days > 1 ? 's' : ''} ago`;
        if (hours > 0) return `${hours} hour${hours > 1 ? 's' : ''} ago`;
        if (minutes > 0) return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
        return `${seconds} second${seconds > 1 ? 's' : ''} ago`;
    }
</script>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('rooms', () => ({
            rooms: @json($supportRooms).map((room) => ChatEntities.convertChatRoom(room)),

            init() {
                this.initializeSocket('support');
            },

            prependRoom(room) {
                this.rooms.unshift(ChatEntities.convertChatRoom(room));
            },

            initializeSocket(roomId) {
                if (!window.Echo) {
                    console.warn('Echo is not initialized');
                    return;
                }

                window.Echo.channel('chat.rooms.support')
                    .listen('.chat.rooms.support.created', (data) => {
                        this.prependRoom(data)
                        toast.info('New support chat room created', {
                            description: 'A new support chat room has been created.'
                        })
                    })
                    .listen('.chat.rooms.support.updated', (data) => {
                        const index = this.rooms.findIndex(room => room.id === data.id)
                        if (index !== -1) {
                            this.rooms.splice(index, 1)
                            this.rooms.unshift(ChatEntities.convertChatRoom(data))
                            return;
                        }

                        console.warn('Room not found for update:', data.id);
                    })
            }
        }));
    });
</script>
@endsection
