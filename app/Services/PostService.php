<?php

namespace App\Services;

use App\Enums\CallBackDataEnum;
use App\Helpers\TelegramHelper;
use Carbon\Carbon;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class PostService {

    public static function createStartMenu(): mixed {
        return InlineKeyboardMarkup::make()->addRow(
            InlineKeyboardButton::make( "📝 Create Post", callback_data  : CallBackDataEnum::CREATE_POST ),
            InlineKeyboardButton::make( "🔎 Search", switch_inline_query_current_chat  : (string) Carbon::now()->year ),
        );
    }

    public static function createPostPublishedMenu( string $postUId ): InlineKeyboardMarkup {
        return InlineKeyboardMarkup::make()->addRow(
            InlineKeyboardButton::make( "📢 Send", switch_inline_query : $postUId )
        );
    }

    public static function createPostMenu( array $additionalButton = [] ): InlineKeyboardMarkup {
        $buttons = [
            InlineKeyboardButton::make( "❌ Cancel Post", callback_data:CallBackDataEnum::CANCEL_CREATE_POST )
        ];

        if ( !empty( $additionalButton ) ) {
            $buttons = array_merge( $additionalButton, $buttons );
        }
        return InlineKeyboardMarkup::make()->addRow( ...$buttons );
    }

    public static function createPostSettingsMenu(): InlineKeyboardMarkup {
        $additionalButtons = [
            InlineKeyboardButton::make( "🔘 Add Button",  callback_data: CallBackDataEnum::ADD_BUTTON ),
            InlineKeyboardButton::make( "🪞 Preview Post",  callback_data: CallBackDataEnum::PREVIEW_POST ),
            InlineKeyboardButton::make( "📤 Publish Post",  callback_data: CallBackDataEnum::PUBLISH_POST ),
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
}
