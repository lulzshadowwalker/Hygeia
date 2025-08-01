@extends('layouts.chat')

@section('chat-area')
<div class="text-center">
    <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1"></path>
    </svg>
    <h3 class="mt-2 text-lg font-medium text-gray-900">Select a conversation</h3>
    <p class="mt-1 text-sm text-gray-500">Choose from the list on the left to start chatting.</p>
</div>
@endsection
