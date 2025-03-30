<?php
/** @var SergiX44\Nutgram\Nutgram $bot */

use App\Enums\CallBackDataEnum;
use App\Enums\CommandsEnum;
use App\Helpers\TelegramHelper;
use App\Models\User;
use App\Telegram\Commands\AccountStatsCommand;
use App\Telegram\Commands\HelpCommand;
use App\Telegram\Commands\StartCommand;
use App\Telegram\Commands\SuperStatisticsCommand;
use App\Telegram\Conversations\AdvertiseHereConversation;
use App\Telegram\Conversations\BroadcastMessageConversation;
use App\Telegram\Conversations\CreatePostConversation;
use App\Telegram\Handlers\OnInlineQueryHandler;
use App\Telegram\Handlers\OnMyChatMember;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Exceptions\TelegramException;

/*
|--------------------------------------------------------------------------
| Nutgram Handlers
|--------------------------------------------------------------------------
|
| Here is where you can register telegram handlers for Nutgram. These
| handlers are loaded by the NutgramServiceProvider. Enjoy!
|
*/

$bot->middleware(function (Nutgram $bot, $next) {
    if($bot->chat()?->isPrivate() && !($bot->user()?->is_bot ?? false)) {
        $user = User::updateOrCreate(
            ['tid' => $bot->userId()],
            [
                'name' => $bot->user()?->first_name ?? 'Unknown',
                'full_name' => trim(($bot->user()?->first_name ?? '') . ' ' . ($bot->user()?->last_name ?? '')),
                'user_id' => $bot->userId(),
                'channel_from' => $bot->message()?->chat?->id . ':' . ($bot->message()?->from?->username ?? 'unknown'),
                'message' => true,
                'chat_id' => $bot?->chat()?->id ?? $bot->message()?->chat?->id,
                'updated_at' => Carbon::now()
            ]
        );
        $bot->set('user', $user);
    }

    $next($bot);
});

// Register command handlers
$bot->onCommand( CommandsEnum::START.' {param}', StartCommand::class );
$bot->onCommand( CommandsEnum::START, StartCommand::class )->description('Start the bot.');
$bot->onCommand( CommandsEnum::CREATE_POST, CreatePostConversation::class )->description('Start a new post');
$bot->onCommand( CommandsEnum::CREATE_BROADCAST, BroadcastMessageConversation::class )
->middleware(function(Nutgram $bot, $next){
    if(!in_array($bot->message()->from->id, TelegramHelper::getAdmins())){
        $bot->message()?->delete();
        return;
    }
    $next($bot);
})->description('Start a new broadcaast');
$bot->onCommand( CommandsEnum::HELP, HelpCommand::class );
$bot->onCommand( CommandsEnum::ACCOUNT, AccountStatsCommand::class );
$bot->onCommand( CommandsEnum::SUPER_STATS, SuperStatisticsCommand::class );

// Register callback query handlers
$bot->onCallbackQueryData( CallBackDataEnum::CREATE_POST, CreatePostConversation::class );
$bot->onCallbackQueryData( CallBackDataEnum::ACCOUNT_STATS, AccountStatsCommand::class );
$bot->onCallbackQueryData( CallBackDataEnum::ADVERTISE_HERE, AdvertiseHereConversation::class );

$bot->onCallbackQueryData( CallBackDataEnum::OK, function (Nutgram $bot){
    $bot->message()?->delete();
});

// Handle create post actions

$bot->onInlineQuery(OnInlineQueryHandler::class);
$bot->onMyChatMember(OnMyChatMember::class);
$bot->onCallbackQueryData('refresh_chat_list', function(Nutgram $bot) {
    $bot->answerCallbackQuery(text: "Refreshing...");
    return;
});
$bot->onApiError(function (Nutgram $bot, TelegramException $exception) {
    Log::error('Telegram API Error', [
        'message' => $exception->getMessage(),
        'code' => $exception->getCode(),
        'chat_id' => $bot->chatId(),
        'user_id' => $bot->userId(),
        'trace' => $exception->getTraceAsString(),
        'request' => $exception->getParameters() ?? null,
    ]);
});
