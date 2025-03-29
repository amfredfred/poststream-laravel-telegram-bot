<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use SergiX44\Nutgram\Telegram\Properties\ChatMemberStatus;
use SergiX44\Nutgram\Telegram\Properties\ChatType;

return new class extends Migration {
    public function up()
    {
        Schema::create('bot_chat_memberships', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('chat_id')->unique()->comment('Telegram chat ID');
            $table->string('chat_title')->nullable();
            $table->enum('chat_type', [
                ChatType::CHANNEL->value,
                ChatType::GROUP->value,
                ChatType::SUPERGROUP->value,
                ChatType::PRIVATE->value
            ])->comment('Type of chat from Telegram');
            $table->json('permissions')->nullable()->comment('Bot permissions in this chat');
            $table->string('invited_by_username')->nullable();
            $table->bigInteger('invited_by_id')->nullable();
            $table->enum('bot_status', [
                ChatMemberStatus::ADMINISTRATOR->value,
                ChatMemberStatus::CREATOR->value,
                ChatMemberStatus::KICKED->value,
                ChatMemberStatus::RESTRICTED->value,
                ChatMemberStatus::MEMBER->value,
                ChatMemberStatus::LEFT->value
            ])->comment('Current bot status in chat');
            $table->timestamp('added_at')->useCurrent();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('chat_id');
            $table->index('chat_type');
            $table->index('bot_status');
            $table->index('invited_by_id');
            $table->index('last_checked_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('bot_chat_memberships');
    }
};
