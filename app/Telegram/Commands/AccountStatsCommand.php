<?php

namespace App\Telegram\Commands;

use App\Enums\CallBackDataEnum;
use App\Models\Post;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Handlers\Type\Command;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class AccountStatsCommand extends Command {
    protected string $command = 'account';

    protected ?string $description = 'Account Statistics';

    public function handle( Nutgram $bot ): void {
        $user = $bot->get( 'user' );
        $postsCount = Post::where( 'user_id', $user->id )->count();
        $reply_markup = InlineKeyboardMarkup::make()->addRow(
            InlineKeyboardButton::make('💸 Request Withdrawal', callback_data:CallBackDataEnum::REQUEST_WITHDRAWAL)
        );
        $bot->sendMessage(
            $this->buildAccountMessage( $user->balance, $postsCount ),
            parse_mode:ParseMode::HTML,
            reply_markup:$reply_markup
        );
    }

    public function buildAccountMessage( $balance, $totalPosts ): string {
        $message = "<b>Here’s a summary of your account:</b>" . PHP_EOL . PHP_EOL;
        $message .= "<blockquote>";
        $message .= "<b>💰 <u>EARNINGS</u></b> - <code>$" . $balance . "</code>" . PHP_EOL . PHP_EOL;
        // $message .= "<b>👁️ <u>POST VIEWS</u></b> - <code>" . number_format( $totalImpressions ) . "</code>" . PHP_EOL . PHP_EOL;
        $message .= "<b>📝 <u>TOTAL POSTS</u></b> - <code>" . $totalPosts   . "</code>" . PHP_EOL . PHP_EOL;
        $message .= "<b>💸 <u>TOTAL WITHDRAWALS</u></b> - <code>SOON</code>" . PHP_EOL . PHP_EOL;
        $message .= "</blockquote>";
        $message .= "<blockquote>";
        $message .= "<em><b>Important:</b> Make sure to keep track of your earnings, posts, and impressions. Regularly review your stats to stay updated and optimize your account performance.</em>";
        $message .= "</blockquote>";
        $message .= "If you have any questions or need assistance, feel free to ask! @poststreamchat";
        return $message;
    }

}
