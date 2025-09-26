<?php

use App\Enums\MessageType;
use App\Models\ChatRoom;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->onDelete('restrict');
            $table->foreignIdFor(ChatRoom::class)->constrained()->onDelete('cascade');
            $table->text('content');
            $table->enum('type', MessageType::values())->default(MessageType::Text);
            $table->index(['chat_room_id', 'created_at']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
