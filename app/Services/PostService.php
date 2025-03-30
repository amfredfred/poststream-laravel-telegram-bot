<?php

namespace App\Services;

use App\Enums\CallBackDataEnum;
use App\Helpers\TelegramHelper;
use App\Models\BotChatMembership;
use Carbon\Carbon;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class PostService {

    public static function createStartMenu(): mixed {
        return InlineKeyboardMarkup::make()->addRow(
            InlineKeyboardButton::make( '📝 Create Post', callback_data  : CallBackDataEnum::CREATE_POST ),
            InlineKeyboardButton::make( '🔎 Search', switch_inline_query_current_chat  : ( string ) Carbon::now()->year ),
        );
    }

    public static function createPostPublishedMenu( string $postUId ): InlineKeyboardMarkup {
        return InlineKeyboardMarkup::make()->addRow(
            InlineKeyboardButton::make( '📢 Send', switch_inline_query : $postUId )
        );
    }

    public static function createPostMenu(array $additionalButtons = [], $showBack = true): InlineKeyboardMarkup {
        $menu = InlineKeyboardMarkup::make();

        // Add additional buttons in rows of 2 (or single if odd number)
        if (!empty($additionalButtons)) {
             foreach ($additionalButtons as $buttonOrGroup) {
                is_array($buttonOrGroup)
                    ? $menu->addRow(...$buttonOrGroup)
                    : $menu->addRow($buttonOrGroup);
            }
        }
        // Always add Cancel button as last row
        if($showBack)
        $menu->addRow(
            InlineKeyboardButton::make(
                '❌ Cancel Post',
                callback_data: CallBackDataEnum::CANCEL_CREATE_POST
            )
        );
        else
        $menu->addRow(
            InlineKeyboardButton::make(
                '🔙 Back',
                callback_data: CallBackDataEnum::BACK_TO_PREVIEW
             ),
        );
        return $menu;
    }

    public static function createPostSettingsMenu(): InlineKeyboardMarkup {
        $additionalButtons = [
            InlineKeyboardButton::make( '🔘 Add Button',  callback_data: CallBackDataEnum::ADD_BUTTON ),
            InlineKeyboardButton::make( '🪞 Preview Post',  callback_data: CallBackDataEnum::PREVIEW_POST ),
        ];
        return self::createPostMenu( $additionalButtons );
    }

    public static function addButtonText() {
        $addLinkMessage = "<strong>🔗 Button Formatting Guide:</strong>\n\n";

        $addLinkMessage .= "<strong>📌 Basic Button:</strong>\n";
        $addLinkMessage .= "<code>[Title, URL]</code>\n";
        $addLinkMessage .= "Example: <code>[Post Stream Bot, https://t.me/poststreambot]</code>\n\n";

        $addLinkMessage .= "<strong>📋 Multiple Buttons in a Row:</strong>\n";
        $addLinkMessage .= "<code>[First, URL_1] [Second, URL_2]</code>\n";
        $addLinkMessage .= "Example: <code>[Home, https://example.com] [Contact, https://example.com/contact]</code>\n\n";

        $addLinkMessage .= "<strong>📄 Buttons on Separate Lines:</strong>\n";
        $addLinkMessage .= "<code>[First, URL_1]</code>\n";
        $addLinkMessage .= "<code>[Second, URL_2]</code>\n";
        $addLinkMessage .= "Example:\n<code>[Blog, https://example.com/blog]</code>\n";
        $addLinkMessage .= "<code>[Support, https://example.com/support]</code>\n\n";

        $addLinkMessage .= "<strong>🔒 Protected (Monetized) Links:</strong>\n";
        $addLinkMessage .= "To create a button that links to a monetized or protected page, use the following format:\n";
        $addLinkMessage .= "<code>[Title, URL, true]</code>\n";
        $addLinkMessage .= "or\n";
        $addLinkMessage .= "<code>[Title, URL, yes]</code>\n";
        $addLinkMessage .= "Example: <code>[Exclusive Content, https://example.com/vip, true]</code>\n\n";

        $addLinkMessage .= "<em>Use this guide to format buttons properly in your posts!</em>";

        return $addLinkMessage;
    }

    public static function postPublishedText( string $uid, string $shareLink ): string {
        $usernmae = TelegramHelper::getBotUsername();
        $postPublishedMessage = "<strong>🎉 Your post has been published!</strong>\n\n";
        $postPublishedMessage .= "📤 <strong>Share it with others using the link below:</strong>\n";
        $postPublishedMessage .= "🔗 <code>{$shareLink}</code>\n\n";
        $postPublishedMessage .= "📱 <strong>In any chat, just type:</strong>\n";
        $postPublishedMessage .= "@".$usernmae." <code>{$uid}</code>\n";
        return $postPublishedMessage;
    }

    public static function startNewPostMessage(): string {
        return <<<HTML
        ⚠️ <strong>NOTICE NOTICE NOTICE</strong>⚠️
        <blockquote>
        By uploading any content, you <strong>ACKNOWLEDGE AND AGREE</strong> that you possess all necessary rights to use, share, or resell the material. Ensure that all content adheres to Telegram’s community guidelines. Any content deemed inappropriate or prohibited will be removed.
        </blockquote>
        HTML;
    }

    public static function userChatBotMember(int $userTid): array {
        $chats = BotChatMembership::active()
            ->where('invited_by_id', $userTid)
            ->select(['chat_id', 'chat_title', 'chat_type'])
            ->get();

        // Messages
        $noChatsMessage = <<<MSG
        🚫 I'm not in any of your channels/groups yet!

        To let me post there:
        1. Add me as ADMIN
        2. Grant post permissions
        3. Try again
        MSG;

        $selectChatMessage = <<<MSG
        📌 Available channels/groups:

        Your post will be published in the selected channel or group. Please choose where you would like to share it.
        MSG;

        $botUsername = env('BOT_USERNAME');

        // No chats case - show full add options
        if ($chats->isEmpty()) {
            return [
                'message' => $noChatsMessage,
                'buttons' => self::createPostMenu([
                    [ InlineKeyboardButton::make(
                        text: "👇Add me to your channel/group👇",
                        callback_data: 'refresh_chat_list'
                    )], [
                         InlineKeyboardButton::make(
                            text: "➕ Group",
                            url: "https://t.me/$botUsername?startgroup=added"
                        ),
                        InlineKeyboardButton::make(
                            text: "➕ Channel",
                            url: "https://t.me/$botUsername?startchannel=added"
                        )
                    ] ], false)
            ];
        }

        // Chats available - show existing + add options
        $chatButtons = $chats->map(function ($chat) {
            return InlineKeyboardButton::make(
                text: match($chat->chat_type) {
                    'channel' => "📢 " . $chat->chat_title,
                    default => "👥 " . $chat->chat_title
                },
                callback_data: CallBackDataEnum::FORWARD_TO_CHAT_ID . $chat->chat_id
            );
        })->toArray();

        // Add action buttons at the end
        $actionButtons = [
            [ InlineKeyboardButton::make(
                text: "👇Need to add me elsewhere?👇",
                callback_data: 'refresh_chat_list'
            )],[
             InlineKeyboardButton::make(
                    text: "➕ Group",
                    url: "https://t.me/$botUsername?startgroup=added"
                ),
                InlineKeyboardButton::make(
                    text: "➕ Channel",
                    url: "https://t.me/$botUsername?startchannel=added"
            )
        ]];

        return [
            'message' => $selectChatMessage,
            'buttons' => self::createPostMenu([...$chatButtons, ...$actionButtons], false)
        ];
    }
}
