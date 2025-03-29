<?php

namespace App\Telegram\Handlers;

use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Nutgram;
use App\Models\Post;
// Adjust the model namespace according to your structure
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton as KeyboardInlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class OnInlineQueryHandler {

    public function __invoke( Nutgram $bot ): void {
        $query = $bot->inlineQuery();
        $queryText = trim( $query->query );
        if ( !$queryText ) return;

        try {
            $posts = Post::where( 'caption', 'like', '%' . $queryText . '%' )
            ->orWhere( 'post_id', 'like', '%' . $queryText . '%' )->get();
            $results = array_map( [ $this, 'createInlineResult' ], $posts->toArray() );
            $bot->answerInlineQuery( $results, $query->id );
        } catch ( \Exception $error ) {
            Log::error( 'Error fetching posts: ' . $error->getMessage() );
            $bot->answerInlineQuery( [], $query->id );
        }
    }

    private function createInlineResult( array $post ): array {

        $post = new Post( $post );
        $inlineMarkup = InlineKeyboardMarkup::make();
        foreach ( $post->inline_keyboard_markup[ 'inline_keyboard' ] ?? [] as $row ) {
            $buttons = array_map( fn( $btn ) => KeyboardInlineKeyboardButton::make( $btn[ 'text' ], $btn[ 'url' ] ), $row );
            $inlineMarkup->addRow( ...$buttons );
        }

        if ( $post->media_id && $post->media_type ) {
            $commonFields = [
                'id' => $post->post_id,
                'caption_entities' => $post->caption_entities,
                'caption' =>  $post->caption ?? '-----',
                'reply_markup' => $inlineMarkup,
                'description' =>  $post->caption ?? '-----',
                'title' =>  $post->caption ?? '-----',
            ];

            switch ( $post->media_type ) {
                case 'photo':
                return array_merge( [ 'type' => 'photo', 'photo_file_id' => $post->media_id ], $commonFields );
                case 'video':
                return array_merge( [ 'type' => 'video', 'video_file_id' => $post->media_id ], $commonFields );
                case 'document':
                return array_merge( [ 'type' => 'document', 'document_file_id' => $post->media_id ], $commonFields );
                case 'audio':
                return array_merge( [ 'type' => 'audio', 'audio_file_id' => $post->media_id ], $commonFields );
                default:
                break;
            }
        }

        return [
            'type' => 'article',
            'id' => $post->post_id,
            'title' => mb_strlen( $post->caption ) > 6 ? mb_substr( $post->caption, 0, 6 ) . '...' : $post->caption ?? '-----',
            'description' =>  $post->caption ?? '-----' ,
            'input_message_content' => [
                'message_text' =>   $post->caption ?? '-----' ,
                'entities' => $post->caption_entities,
            ],
            'entities' => $post->caption_entities,
            'reply_markup' => $inlineMarkup,
            'thumbnail_url' => $post->thumbUrl ?? '',
        ];
    }
}
