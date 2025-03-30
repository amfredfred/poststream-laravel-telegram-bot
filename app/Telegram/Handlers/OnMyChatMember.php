<?php

namespace App\Telegram\Handlers;

use App\Helpers\TelegramHelper;
use App\Models\BotChatMembership;
use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Chat\ChatMemberUpdated;
use SergiX44\Nutgram\Telegram\Exceptions\TelegramException;

class OnMyChatMember {
    public function __invoke(Nutgram $bot): void {
        try {
            /** @var ChatMemberUpdated $myChatMember */
            $myChatMember = $bot->update()->my_chat_member;

            if (!$myChatMember) {
                Log::error('my_chat_member data missing in update');
                return;
            }

            $chat = $myChatMember->chat;
            $actor = $myChatMember->from;
            $newStatus = $myChatMember->new_chat_member->status->value;

            // Check if bot can send messages in this chat
            $canSendMessages = TelegramHelper::isAdminOrCreator($bot, $chat->id, $bot->getMe()->id);

            // Always try to notify actor privately first
            $this->notifyActor($bot, $actor->id,
                "ℹ️ Bot status update in {$chat->title}:\n" .
                "New status: " . ucfirst($newStatus) .
                ($canSendMessages ? "" : "\n\n⚠️ Note: I don't have message permissions in this ".$chat->type->value." ")
            );

            // Check authorization if being added (not left/kicked)
            if ($newStatus !== 'left' && $newStatus !== 'kicked') {
                if (!TelegramHelper::isAdminOrCreator($bot, $chat->id, $actor->id)) {
                    $this->notifyActor($bot, $actor->id,
                        "❌ You must be an admin/creator of {$chat->title} to add this bot."
                    );
                    $bot->leaveChat($chat->id);
                    return;
                }

                if (!in_array($newStatus, ['administrator', 'creator'])) {
                    $this->notifyActor($bot, $actor->id,
                        "⚠️ I need admin privileges to function properly in {$chat->title}.\n" .
                        "Current status: " . ucfirst($newStatus) .
                        ($canSendMessages ? "" : "\n\n❌ Cannot function without message permissions")
                    );
                    return;
                }
            }

            // Success case - bot was made admin
            if (in_array($newStatus, ['administrator', 'creator'])) {
                if ($canSendMessages) {
                    $this->notifyActor(
                        $bot,
                        $actor->id,
                        "✅ Bot activated in {$chat->title}!\n" .
                        "Admin: @{$actor->username}\n" .
                        "Bot status: " . ucfirst($newStatus)
                    );
                } else {
                    $this->notifyActor($bot, $actor->id,
                        "⚠️ Admin rights granted in {$chat->title} but I can't send messages.\n" .
                        "Please enable 'Send Messages' permission for me to function properly."
                    );
                }
            }

            if (in_array($newStatus, ['administrator', 'creator', 'member', 'restricted'])) {
                BotChatMembership::updateOrCreate(
                    ['chat_id' => $chat->id],
                    [
                        'chat_title' => $chat->title,
                        'chat_type' => $chat->type,
                        'permissions' => [],
                        'invited_by_username' => $actor->username,
                        'invited_by_id' => $actor->id,
                        'bot_status' => $newStatus,
                        'chat_username' => $chat->username,
                        'last_checked_at' => now(),
                    ]
                );
            } elseif (in_array($newStatus, ['left', 'kicked'])) {
                BotChatMembership::where('chat_id', $chat->id)
                    ->update([
                        'bot_status' => $newStatus,
                        'last_checked_at' => now()
                    ]);
            }

        } catch (\Throwable $e) {
            Log::error('OnMyChatMember handler failed: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            if (isset($actor)) {
                $this->notifyActor($bot, $actor->id,
                    "⚠️ An error occurred while processing your request. Please try again."
                );
            }
        }
    }

    protected function notifyActor(Nutgram $bot, int $actorId, string $message): void {
        try {
            $bot->sendMessage($message, $actorId);
        } catch (TelegramException $e) {
            Log::error("Failed to notify actor {$actorId}: {$e->getMessage()}");
            // Could add fallback notification logic here
        }
    }
}
