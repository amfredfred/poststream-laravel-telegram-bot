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
                InlineKeyboardButton::make( 'Last 30 days', callback_data: CallBackDataEnum::AUDIENCE_LAST_30_DAYS ),
                InlineKeyboardButton::make( 'Last 7 days', callback_data: CallBackDataEnum::AUDIENCE_LAST_7_DAYS )
            )
            ->addRow(
                InlineKeyboardButton::make( 'Last 24 hours', callback_data: CallBackDataEnum::AUDIENCE_LAST_24_HOURS ),
                InlineKeyboardButton::make( 'Last user', callback_data: CallBackDataEnum::AUDIENCE_LAST_USER )
            )
        );
        $this->next( 'handleCallback' );
    }

    public function confirmBroadcast( Nutgram $bot ) {
        $userCount = count( $this->data->users );
        $bot->sendMessage(
            "Preview of your broadcast:\n\n{$this->data->caption}\n\n=====\nAudience: {$userCount} user(s)",
            entities: $this->data->captionEntities,
            reply_markup: InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make( '✅ Start Broadcasting', callback_data: CallBackDataEnum::START_BROADCAST )
            )
            ->addRow(
                InlineKeyboardButton::make( '❌ Cancel', callback_data: CallBackDataEnum::CANCEL_CREATE_POST )
            )
        );
    }

    public function handleCallback( Nutgram $bot ) {

        if ( !$bot->isCallbackQuery() ) {
            return $bot->message()?->delete();
        }

        $callbackData = $bot->callbackQuery()->data;

        switch ( $callbackData ) {
            case CallBackDataEnum::AUDIENCE_LAST_30_DAYS:
            $this->data->users = UserService::getUsersLast30Days();
            break;
            case CallBackDataEnum::AUDIENCE_LAST_7_DAYS:
            $this->data->users = UserService::getUsersLast7Days();
            break;
            case CallBackDataEnum::AUDIENCE_LAST_24_HOURS:
            $this->data->users = UserService::getUsersLast24Hours();
            break;
            case CallBackDataEnum::AUDIENCE_LAST_USER:
            $this->data->users = [ UserService::getLastUser() ] ;
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
        Log::info( [ '$user->chat_id' => $this->data->users ] );
        Log::channel( 'telegram' )->info( 'Hello world!', [ 'user' => optional( $this->data->users )->toArray() ] );
        foreach ( $this->data->users as $user ) {
            Log::info( [ '$this->data->users' => $user ] );
            Log::info( [ '$this->data->users' => $user ] );
            Log::info( [ '$this->data->users' => $user ] );
            $bot->sendMessage( text:$this->data->caption, chat_id:$user->chat_id, entities: $this->data->captionEntities );
        }

        $bot->sendMessage( 'Your message has been broadcast!' );
        $this->end();
    }
}
