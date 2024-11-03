<?php

namespace App\Telegram\Conversations;

use App\Enums\CallBackDataEnum;
use App\Enums\StatusEnum;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class RequestWithdrawalConversation extends Conversation {
    protected $balance;
    protected $withdrawalAmount;
    protected $walletAddress;

    public function start( Nutgram $bot ) {
        $this->balance = ( int ) $bot->get( 'user' )->balance;
        $min_withdrawal_amount = ( int ) config( 'telegram.min_withdrawal_amount' );

        if ( $this->balance > $min_withdrawal_amount ) {
            $this->withdrawalAmount = $this->balance;
            $this->askForAddress( $bot );
        } else {
            $bot->editMessageText(
                'Your balance is too low to withdraw. Please try again later.',
                reply_markup: InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make( '🛑 Ok :)', callback_data: CallBackDataEnum::OK )
                )
            );
            $this->end();
        }
    }

    public function askForAddress( Nutgram $bot ) {
        $bot->editMessageText(
            'Please send your TON wallet address to receive your USDT:',
            reply_markup: InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make( '❌ Cancel Withdrawal', callback_data: CallBackDataEnum::OK )
            )
        );
        $this->next( 'requestWalletAddress' );
    }

    public function requestWalletAddress( Nutgram $bot ) {
        $walletAddress = $bot->message()?->text;

        if ( $bot->isCallbackQuery() && $bot->callbackQuery()->data === CallBackDataEnum::SUBMIT_REQUEST ) {
            $this->submitWithdrawal( $bot );
            return;
        }

        if ( $this->isValidTONAddress( $walletAddress ) ) {
            $this->walletAddress = $walletAddress;
            $bot->sendMessage(
                "Your withdrawal of {$this->withdrawalAmount} USDT will be sent to {$walletAddress}. ",
                reply_markup: InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make( '💸 Submit Withdrawal', callback_data: CallBackDataEnum::SUBMIT_REQUEST ),
                    InlineKeyboardButton::make( '❌ Cancel Withdrawal', callback_data: CallBackDataEnum::OK )
                )
            );
            $this->next( 'requestWalletAddress' );
        } else {
            $bot->sendMessage(
                'Invalid TON wallet address. Please provide a valid address:',
                reply_markup: InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make( '❌ Cancel Withdrawal', callback_data: CallBackDataEnum::OK )
                )
            );
            $this->next( 'requestWalletAddress' );
        }
    }

    public function submitWithdrawal(Nutgram $bot) {
        $user = $bot->get('user');

        try {
            DB::transaction(function () use ($bot, $user) {

                $user->decrement('balance', $this->withdrawalAmount);
                $transaction = new Transaction();
                $transaction->amount = $this->withdrawalAmount;
                $transaction->user_id = $user->id;
                $transaction->wallet_address = $this->walletAddress;
                $transaction->save();
                $bot->sendMessage(
                    "✅✅✅ Withdrawal submitted successfully.\n" .
                    "Transaction ID: <code>{$transaction->unique_id}</code>\n" .
                    "💰 Amount: <code>{$this->withdrawalAmount}</code> USDT\n" .
                    "Please wait for confirmation.\n".
                    "YOu WIll BE NOtified WHen IT IS PRocessed",
                    parse_mode: ParseMode::HTML
                );
                $adminChatId = config('telegram.finance_channel_id');
                $adminMessage =
                    "📢 New Withdrawal Request:\n\n" .
                    "👤 User ID: {$user->user_id}\n" .
                    "👤 Full Name: {$user->full_name}\n" .
                    "👤 Channel From: {$user->channel_from}\n" .
                    "💰 Amount: <code>{$this->withdrawalAmount}</code> USDT\n" .
                    "🏦 Wallet Address: <code>{$this->walletAddress}</code>\n" .
                    "🆔 Transaction ID: <code>{$transaction->unique_id}</code>\n\n" .
                    "Please review and confirm the withdrawal.";

                $adminMarkup = InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make('✅ Mark Paid',  callback_data: CallBackDataEnum::TRANSACTION_STATUS . ':' . $transaction->unique_id . ':' . StatusEnum::APPROVED),
                    InlineKeyboardButton::make('❌ Decline',  callback_data: CallBackDataEnum::TRANSACTION_STATUS . ':' . $transaction->unique_id . ':' . StatusEnum::DECLINED),
                );
                $bot->sendMessage($adminMessage, chat_id: $adminChatId, parse_mode: ParseMode::HTML, reply_markup:$adminMarkup);
            });
            $this->end();
        } catch (\Exception $e) {
            $bot->sendMessage('❌ An error occurred while processing your withdrawal. Please try again later.');
            Log::error('Withdrawal failed: ' . $e->getMessage());
        }
    }


    protected function isValidTONAddress( $address ) {
        return strlen( $address ) > 10;
    }
}
