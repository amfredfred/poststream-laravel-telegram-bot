<?php

namespace App\Telegram\Conversations;

use App\Enums\CallBackDataEnum;
use App\Helpers\TelegramHelper;
use App\Models\BotChatMembership;
use App\Models\Post;
use App\Types\PostData;
// Import the PostData class
use App\Services\PostService;
use Exception;
use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use Illuminate\Support\Str;

class CreatePostConversation extends Conversation {
    public PostData $data;
    public array $session_message_ids = [];

    public function __construct() {
        $this->data = new PostData();
    }

    public function start( Nutgram $bot ) {
        $this->data->postId = Str::upper( Str::random( 10 ) );
        // Set the post_id here
        $bot->sendMessage(
            PostService::startNewPostMessage(),
            parse_mode: ParseMode::HTML
        );
        $this->askForCaption( $bot );
    }

    public function askForCaption( Nutgram $bot ) {
        $bot->sendMessage(
            "Please send a caption for your post (or press 'skip' to skip this step):",
            reply_markup: InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make( 'Skip caption', callback_data: CallBackDataEnum::SKIP_CAPTION ),
                InlineKeyboardButton::make( '❌ Cancel Post', callback_data: CallBackDataEnum::CANCEL_CREATE_POST )
            )
        );

        $this->next( 'processCaption' );
    }

    public function processCaption( Nutgram $bot ) {
        if ( $bot->isCallbackQuery() ) return $this->handleCallback( $bot );
        if ( !is_string( $bot->message()->text ) ) {
            $bot->message()->delete();
            $bot->sendMessage( 'Invalid input. Please enter a valid caption string' );
            return;
        }
        $this->data->caption = $bot->message()->text;
        $this->data->captionEntities = $bot->message()->entities ?? [];
        $this->askForFile( $bot );
    }

    public function askForFile( Nutgram $bot ) {
        $bot->sendMessage(
            'Now, please upload your file [photo, video, etc.].' ,
            reply_markup: InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make( 'Add Buttons', callback_data: CallBackDataEnum::ADD_BUTTON )
            )
            ->addRow(
                InlineKeyboardButton::make( '✅ Done', callback_data: CallBackDataEnum::PREVIEW_POST ),
                InlineKeyboardButton::make( '❌ Cancel', callback_data: CallBackDataEnum::CANCEL_CREATE_POST )
            )
        );
        $this->next( 'processFile' );
    }

    public function processFile( Nutgram $bot ) {
        if ( $bot->isCallbackQuery() ) return $this->handleCallback( $bot );

        $message = $bot->message();
        $media = $message->photo ?? $message->video;

        if ( !$media ) {
            $bot->sendMessage( 'Please upload a photo or video.' );
            return;
        }

        $this->data->mediaType = $message->photo ? 'photo' : 'video';
        $this->data->mediaId = $media->file_id ?? end( $message->photo )->file_id;
        $this->data->caption = $message->caption ?? $this->data->caption;
        $this->data->captionEntities = $message->caption_entities ?? $this->data->captionEntities;

        $bot->sendMessage(
            "{$this->data->mediaType} received! You can change it by uploading a different one.",
            reply_markup: InlineKeyboardMarkup::make() ->addRow(
                InlineKeyboardButton::make( ( $this->data->inline_keyboard_markup ? 'Change':'Add' ).' Buttons', callback_data: CallBackDataEnum::ADD_BUTTON )
            )
            ->addRow(
                InlineKeyboardButton::make( '✅ Done', callback_data: CallBackDataEnum::PREVIEW_POST ),
                InlineKeyboardButton::make( '❌ Cancel', callback_data: CallBackDataEnum::CANCEL_CREATE_POST )
            )
        );
        $this->next( 'processFile' );
    }

    public function askForButtons( Nutgram $bot ) {
        $message_text = $bot->message()->text;
        if ( $bot->isCallbackQuery() ) return $this->handleCallback( $bot );
        if ( !is_string( $message_text ) ) {
            $bot->sendMessage( 'Invalid input. Please enter a valid button format string.' );
            return;
        }
        $lines = preg_split( '/\r\n|\r|\n/', $message_text );
        try {
            // Process each line as a row of buttons
            $inline_keyboard = array_map( function ( $line ) {
                // Separate each button in the line and remove the outer brackets
                $buttonData = array_map( function ( $item ) {
                    [ $text, $url, $booleanStr ] = array_pad(
                        preg_split( '/,\s*/', trim( $item, '[]' ) ),
                        3,
                        null
                    );

                    // Validate text and URL
                    if ( empty( $text ) || empty( $url ) ) {
                        throw new Exception( 'Invalid button data. Text and URL are required for each button.' );
                    }

                    // Check if the link is protected ( monetized )
                    $url = ( $booleanStr && strtolower( $booleanStr ) === 'true' )
                    ? TelegramHelper::getBotWebappLink() . TelegramHelper::encodeString( json_encode( [
                        'redirect' => $url,
                        'postUid' => $this->data->postId, // Use postId from data object
                    ] ) ) : $url;

                    return [ 'text' => $text, 'url' => $url ];
                }
                , preg_split( '/\]\s*\[/', trim( $line, '[]' ) ) );

                return $buttonData;
            }
            , $lines );

            // Build the inline keyboard markup
            $inlineMarkup = InlineKeyboardMarkup::make();
            foreach ( $inline_keyboard as $row ) {
                // Create a row for each line of buttons
                $buttons = array_map( fn( $btn ) => InlineKeyboardButton::make( $btn[ 'text' ], $btn[ 'url' ] ), $row );
                $inlineMarkup->addRow( ...$buttons );
            }

            $this->data->inline_keyboard_markup = $inlineMarkup;
        } catch ( Exception $error ) {
            Log::error( "Button formatting error: {$error->getMessage()}" );
            $bot->sendMessage( $error->getMessage(), parse_mode: ParseMode::HTML );
        }

        $this->next( 'askForButtons' );

        $bot->sendMessage(
            'Buttons added! You can preview or publish the post.',
            reply_markup: InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make( ( $this->data->inline_keyboard_markup ? 'Change':'Add' ).' Buttons', callback_data: CallBackDataEnum::ADD_BUTTON )
            )
            ->addRow(
                InlineKeyboardButton::make( '✅ Done', callback_data: CallBackDataEnum::PREVIEW_POST ),
                InlineKeyboardButton::make( '❌ Cancel', callback_data: CallBackDataEnum::CANCEL_CREATE_POST )
            )
        );

    }

    public function sendPostPreview( Nutgram $bot ) {
        if ( $bot->isCallbackQuery() && $bot->callbackQuery()->data !== CallBackDataEnum::PREVIEW_POST ) {
            return $this->handleCallback( $bot );
        }

        if ( $this->data->mediaType === 'video' ) {
            $msg = $bot->sendVideo(
                $this->data->mediaId,
                caption: $this->data->caption,
                caption_entities: $this->data->captionEntities,
                reply_markup: $this->data->inline_keyboard_markup
            );
        } elseif ( $this->data->mediaType === 'photo' ) {
            $msg = $bot->sendPhoto(
                $this->data->mediaId,
                caption: $this->data->caption,
                caption_entities: $this->data->captionEntities,
                reply_markup: $this->data->inline_keyboard_markup
            );
        } elseif ( $this->data->caption ) {
            $msg = $bot->sendMessage(
                $this->data->caption ,
                entities: $this->data->captionEntities,
                reply_markup: $this->data->inline_keyboard_markup
            );
        } else {
            return;
        }

        $this->data->postMessageId = $msg->message_id;

        $bot->sendMessage(
            '__You can publish your post now. or make changes',
            reply_markup: $this->previePostButtons()
        );

        $this->next( 'sendPostPreview' );
    }

    public function sendPostToChat( Nutgram $bot ) {
        if ( $bot->isCallbackQuery() ) return $this->handleCallback( $bot );
    }

    public function handleCallback( Nutgram $bot ) {
        $bot->message()->delete();
        switch ( $bot?->callbackQuery()?->data ) {
            case CallBackDataEnum::SKIP_CAPTION:
            $this->data->caption = '';
            $this->askForFile( $bot );
            break;

            case CallBackDataEnum::CANCEL_CREATE_POST:
            $bot->sendMessage( 'Post creation cancelled.' );
            $this->end();
            break;

            case CallBackDataEnum::ADD_BUTTON:
            $bot->sendMessage( PostService::addButtonText(), parse_mode: ParseMode::HTML );
            $this->next( 'askForButtons' );
            break;

            case CallBackDataEnum::PREVIEW_POST:
            $this->sendPostPreview( $bot );
            break;

            case CallBackDataEnum::REMOVE_MEDIA:
            $this->data->mediaId = '';
            $this->data->mediaType = '';
            $this->askForFile( $bot );
            break;

            case CallBackDataEnum::ADD_MEDIA:
            $this->data->mediaId = '';
            $this->data->mediaType = '';
            $this->askForFile( $bot );
            break;

            case CallBackDataEnum::BACK_TO_PREVIEW:
            $bot->sendMessage(
                '__You can publish your post now. or make changes',
                reply_markup: $this->previePostButtons()
            );
            $this->next( 'handleCallback' );
            break;

            case CallBackDataEnum::SEND_TO_CHAT:
            [ 'message' => $message, 'buttons' => $buttons ] = PostService::userChatBotMember( $bot->user()->id );
            $bot->sendMessage( $message, parse_mode: ParseMode::HTML, reply_markup:$buttons );
            $this->next( 'sendPostToChat' );
            break;

            default:
            if ( preg_match( '/^type:FORWARD_TO_CHAT_ID(?:[_\-][a-zA-Z0-9]+)*$/', $bot?->callbackQuery()?->data ) ) {
                // Extract the suffix ( e.g., '123' from 'FORWARD_TO_CHAT_ID_123' )
                $chatIdSuffix = str_replace( 'type:FORWARD_TO_CHAT_ID', '', $bot->callbackQuery()->data );
                $chatInfo = BotChatMembership::where( 'chat_id', $chatIdSuffix )->firstOrFail();
                $copiedMessage = $bot->copyMessage(
                    chat_id:$chatIdSuffix,
                    from_chat_id:$bot->chat()->id,
                    message_id:( int )$this->data->postMessageId,
                    caption:$this->data->caption,
                    caption_entities:$this->data->captionEntities,
                    reply_markup:$this->data->inline_keyboard_markup
                );
                $messageLink = "https://t.me/$chatInfo->chat_username/$copiedMessage->message_id";
                if($chatInfo->chat_username){
                    $chatIdSuffix = "@$chatInfo->chat_username";
                }
                $message = '✅ Post sent to '.$chatInfo->chat_title ?? $chatIdSuffix.' successfuly... ';
                if ( $chatInfo->chat_username ) {
                    $message .= " <a href='$messageLink'>View</a>";
                }
                $bot->answerCallbackQuery( text: 'Post sent to '.$chatIdSuffix.' successfuly...' );
                $bot->sendMessage(
                    $message,
                    parse_mode:ParseMode::HTML,
                    reply_markup: $this->previePostButtons()
                );
            } else {
                $bot->sendMessage( 'Invalid option. Please try again.' );
                if ( $this->data->caption || $this->data->mediaId ) {
                    $bot->sendMessage(
                        '__You can publish your post now. or make changes',
                        reply_markup: $this->previePostButtons()
                    );
                }
            }
            $this->next( 'handleCallback' );
            break;
        }
    }

    public function previePostButtons() {
        $replyMarkup = InlineKeyboardMarkup::make()
        ->addRow(
            InlineKeyboardButton::make( '📢 Publish', callback_data: CallBackDataEnum::SEND_TO_CHAT ),
            InlineKeyboardButton::make( '❌ Cancel', callback_data: CallBackDataEnum::CANCEL_CREATE_POST )
        )->addRow(
            InlineKeyboardButton::make( ( $this->data->inline_keyboard_markup ? 'Change':'Add' ).' Buttons', callback_data: CallBackDataEnum::ADD_BUTTON )
        );

        if ( $this->data->mediaId ) {
            $replyMarkup->addRow(
                InlineKeyboardButton::make( "Remove {$this->data->mediaType}", callback_data: CallBackDataEnum::REMOVE_MEDIA ),
                InlineKeyboardButton::make( 'Change file', callback_data: CallBackDataEnum::ADD_MEDIA )
            );
        } else {
            $replyMarkup->addRow(
                InlineKeyboardButton::make( 'Upload file', callback_data: CallBackDataEnum::ADD_MEDIA )
            );
        }

        return $replyMarkup;
    }
}
