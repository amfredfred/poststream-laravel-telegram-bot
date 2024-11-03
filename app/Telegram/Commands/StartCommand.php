<?php

namespace App\Telegram\Commands;

use App\Models\Post;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Handlers\Type\Command;

use App\Services\PostService;
use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class StartCommand extends Command {

    protected string $command = 'start';
    protected ?string $description = 'Start the bot.';

    public function handle( Nutgram $bot ): void {
        $text = $bot->message()->text;
        $parts = explode( ' ', $text );
        $user  = $bot->get( 'user', );
        if ( count( $parts ) > 1 ) {
            $commandData = $parts[ 1 ];
            $postId = $commandData;
            /**  @var Post $post */
            $post = Post::where( 'post_id', $postId )->first();
            $inlineMarkup = InlineKeyboardMarkup::make();
            foreach ( $post->inline_keyboard_markup[ 'inline_keyboard' ] ?? [] as $row ) {
                $buttons = array_map( fn( $btn ) => InlineKeyboardButton::make( $btn[ 'text' ], $btn[ 'url' ] ), $row );
                $inlineMarkup->addRow( ...$buttons );
            }
            if ( $post ) {
                if ( $post->media_type === 'video' ) {
                    $bot->sendVideo(
                        $post->media_id,
                        caption: $post->caption,
                        caption_entities: $post->caption_entities,
                        reply_markup: $inlineMarkup
                    );
                } elseif ( $post->media_type === 'photo' ) {
                    $bot->sendPhoto(
                        $post->media_id,
                        caption: $post->caption,
                        caption_entities: $post->caption_entities,
                        reply_markup: $inlineMarkup
                    );
                } elseif ( $post->caption ) {
                    $bot->sendMessage(
                        $post->caption ,
                        entities: $post->caption_entities,
                        reply_markup: $inlineMarkup
                    );
                }

                Log::info( [ 'SEEND BUY ME' => $post->hasUserViewed( $user->id ) ] );

                if ( !$post->hasUserViewed( $user->id ) ) {
                    $post->views()->create( [
                        'user_id' => $user->id,
                    ] );
                }
                return;
            }
        }

        $bot->sendMessage(
            $this->createWelcomeMessage(),
            parse_mode:ParseMode::HTML,
            reply_markup: PostService::createStartMenu()
        );
    }

    // Function to create the welcome message
    protected function createWelcomeMessage(): string {
        return '<b>Hi there!</b>' . PHP_EOL . PHP_EOL .
        '<i>I’m POSTSTREAMBOT, your go-to bot for turning posts into earnings!</i>' . PHP_EOL . PHP_EOL .
        'Here’s how it works: ' .
        '📢 Share your posts and earn money effortlessly. ' .
        '💰 You can also add inline links to monetize your messages! Just use the format:' . PHP_EOL . PHP_EOL .
        '<b>Example:</b> <code>[Text, URL]</code>' . PHP_EOL .
        'Some text... [Read More, https://to-read-more]' . PHP_EOL . PHP_EOL .
        'I’m here to help you make the most out of your posts and start earning today.' . PHP_EOL . PHP_EOL .
        '👉 For more assistance, tap the "Menu" button at the bottom left. ' .
        '💬 You’ll also find the menu there to navigate through various options.' . PHP_EOL . PHP_EOL .
        'Let’s get started! Select an option below to begin your journey with ME';
    }
}
