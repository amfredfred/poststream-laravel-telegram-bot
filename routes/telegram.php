<?php
/** @var SergiX44\Nutgram\Nutgram $bot */

use App\Enums\CallBackDataEnum;
use App\Enums\CommandsEnum;
use App\Models\User;
use App\Telegram\Commands\AccountStatsCommand;
use App\Telegram\Commands\HelpCommand;
use App\Telegram\Commands\StartCommand;
use App\Telegram\Commands\SuperStatisticsCommand;
use App\Telegram\Conversations\AdvertiseHereConversation;
use App\Telegram\Conversations\CreatePostConversation;
use App\Telegram\Conversations\RequestWithdrawalConversation;
use App\Telegram\Handlers\OnInlineQueryHandler;
use App\Telegram\Handlers\TransactionStatusHandler;
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
   if(!$bot->chat()?->isChannel){
      $user = User::firstOrCreate(
        ['tid' => $bot->userId()],
        [
            'name' => $bot->user()?->first_name ?? 'Unknown',
            'full_name' => $bot->user()?->first_name . ' ' . $bot->user()?->last_name,
            'user_id' => $bot->userId(),
            'channel_from' => $bot->message()?->chat?->id.':'.$bot->message()?->from?->username,
            'message' => true,
            'balance' => 0,
            'chat_id' => $bot?->chat()?->id ?? $bot->message()?->chat?->id,
            'tid' => $bot->userId()
        ]
    );

    $bot->set('user', $user);
  }


    $CRYPTO_BOT_APP_API = env('CRYPTO_BOT_APP_API');
    if($CRYPTO_BOT_APP_API){
        $api = new \Klev\CryptoPayApi\CryptoPay($CRYPTO_BOT_APP_API);
        $bot->set('cryptobot', $api);
    }else{

    }

    $next($bot);
});

// Register command handlers
$bot->onCommand( CommandsEnum::START.' {param}', StartCommand::class );
$bot->onCommand( CommandsEnum::START, StartCommand::class )->description('Start the bot.');
$bot->onCommand( CommandsEnum::CREATE_POST, CreatePostConversation::class )->description('Start a new post');
$bot->onCommand( CommandsEnum::HELP, HelpCommand::class );
$bot->onCommand( CommandsEnum::ACCOUNT, AccountStatsCommand::class );
$bot->onCommand( CommandsEnum::SUPER_STATS, SuperStatisticsCommand::class );

// Register callback query handlers
$bot->onCallbackQueryData( CallBackDataEnum::CREATE_POST, CreatePostConversation::class );
$bot->onCallbackQueryData( CallBackDataEnum::REQUEST_WITHDRAWAL, RequestWithdrawalConversation::class );
$bot->onCallbackQueryData( CallBackDataEnum::ACCOUNT_STATS, AccountStatsCommand::class );
$bot->onCallbackQueryData( CallBackDataEnum::ADVERTISE_HERE, AdvertiseHereConversation::class );
$bot->onCallbackQueryData( CallBackDataEnum::TRANSACTION_STATUS.':[a-zA-Z0-9]+:[A-Z]+', TransactionStatusHandler::class);
$bot->onCallbackQueryData( CallBackDataEnum::OK, function (Nutgram $bot){
    $bot->message()?->delete();
});

// Handle create post actions

$bot->onInlineQuery(OnInlineQueryHandler::class);


$bot->onApiError(function (Nutgram $bot, TelegramException $exception) {
    echo $exception->getMessage(); // Bad Request: chat not found
    echo $exception->getCode(); // 400
    error_log($exception);
});
