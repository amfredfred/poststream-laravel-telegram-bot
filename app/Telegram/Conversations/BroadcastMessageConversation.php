<?php

namespace App\Telegram\Conversations;

use App\Enums\CallBackDataEnum;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use App\Services\UserService;
use App\Types\BroadCastData;
use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use Illuminate\Support\Str;

class BroadcastMessageConversation extends Conversation {
    public BroadCastData $data;
    public array $session_message_ids = [];

    public function __construct() {
        $this->data = new BroadCastData();
    }

    public function start( Nutgram $bot ) {
        $this->data->broadcastId = Str::upper( Str::random( 10 ) );

        $this->askForCaption( $bot );
    }

    public function askForCaption( Nutgram $bot ) {
        $bot->sendMessage(
            'Please send a caption for your broadcast',
            reply_markup: InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make( '❌ Cancel', callback_data: CallBackDataEnum::CANCEL_CREATE_POST )
            )
        );
        $this->next( 'processCaption' );
    }

    public function processCaption( Nutgram $bot ) {
        if ( !is_string( $bot->message()->text ) ) {
            $bot->message()->delete();
            $bot->sendMessage( 'Invalid input. Please enter a valid caption string' );
            return;
        }
        $this->data->caption = $bot->message()->text;
        $this->data->captionEntities = $bot->message()->entities ?? [];
        $this->askForAudience( $bot );
    }

    public function askForAudience( Nutgram $bot ) {
        $bot->sendMessage(
            'Who would you like to send the message to?',
            reply_markup: InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make( 'User last 30 days', callback_data: CallBackDataEnum::AUDIENCE_LAST_30_DAYS ),
                InlineKeyboardButton::make( 'User last 7 days', callback_data: CallBackDataEnum::AUDIENCE_LAST_7_DAYS )
            )
            ->addRow(
                InlineKeyboardButton::make( 'User last 24 hours', callback_data: CallBackDataEnum::AUDIENCE_LAST_24_HOURS ),
                InlineKeyboardButton::make( 'User last user', callback_data: CallBackDataEnum::AUDIENCE_LAST_USER )
            )
        );
        $this->next( 'handleCallback' );
    }

    public function confirmBroadcast( Nutgram $bot ) {
        $userCount = count( $this->data->userChatIds );
        $bot->sendMessage(
            "Preview of your broadcast #{$this->data->broadcastId}:\n\n{$this->data->caption}\n\n=====\nAudience: {$userCount} user(s)",
            entities: $this->data->captionEntities,
            reply_markup: InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make( '✅ Start Broadcasting', callback_data: CallBackDataEnum::START_BROADCAST )
            )
            ->addRow(
                InlineKeyboardButton::make( '❌ Cancel', callback_data: CallBackDataEnum::CANCEL_CREATE_POST )
            )
        );
        $this->next( 'handleCallback' );
    }

    public function handleCallback( Nutgram $bot ) {

        if ( !$bot->isCallbackQuery() ) {
            return $bot->message()?->delete();
        }

        $callbackData = $bot->callbackQuery()->data;

        switch ( $callbackData ) {
            case CallBackDataEnum::AUDIENCE_LAST_30_DAYS:
            $this->data->userChatIds = UserService::getUsersLast30DaysChatIds();
            break;
            case CallBackDataEnum::AUDIENCE_LAST_7_DAYS:
            $this->data->userChatIds = UserService::getUsersLast7DaysChatIds();
            break;
            case CallBackDataEnum::AUDIENCE_LAST_24_HOURS:
            $this->data->userChatIds = UserService::getUsersLast24HoursChatIds();
            break;
            case CallBackDataEnum::AUDIENCE_LAST_USER:
            $this->data->userChatIds = [ UserService::getLastUser()->chat_id ] ;
            // Wrap in a collection
            break;
            case CallBackDataEnum::START_BROADCAST:
            return $this->sendToSelectedUsers( $bot );
            case CallBackDataEnum::CANCEL_CREATE_POST:
            $bot->sendMessage( 'Broadcast canceled.' );
            return $this->end();
            default:
            $bot->sendMessage( 'Invalid option.' );
            return $this->end();
        }

        $this->confirmBroadcast( $bot );
    }

    public function sendToSelectedUsers( Nutgram $bot ) {
        try {
            foreach ( $this->data->userChatIds as $key => $chat_id ) {
                try {
                    $bot->sendMessage(
                        text: $this->data->caption,
                        chat_id: $chat_id,
                        entities: $this->data->captionEntities
                    );
                } catch ( \Throwable $userException ) {
                    Log::channel( 'telegram' )->error( "Failed to send message to user {$chat_id}: {$userException->getMessage()}" );
                }
            }
            $bot->sendMessage( 'Your message has been broadcast!' );
        } catch ( \Throwable $broadcastException ) {
            Log::channel( 'telegram' )->error( "Braodcast #{$this->data->broadcastId}\nFailed to complete broadcast: {$broadcastException->getMessage()}" );
            $bot->sendMessage( 'There was an error with the broadcast. Please try again.' );
        }
        $this->end();
    }

}
