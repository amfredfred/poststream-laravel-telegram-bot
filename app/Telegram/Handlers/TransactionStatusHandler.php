<?php

namespace App\Telegram\Handlers;

use App\Models\Transaction;
use App\Enums\StatusEnum;
use SergiX44\Nutgram\Nutgram;

class TransactionStatusHandler {
    public function __invoke( Nutgram $bot ): void {
        $callbackData = $bot->callbackQuery()->data;
        // Define regex pattern to match `TRANSACTION_STATUS:<transaction_id>:<status>`
        $pattern = '/TRANSACTION_STATUS:([a-zA-Z0-9]+):([A-Z]+)/';

        if ( preg_match( $pattern, $callbackData, $matches ) ) {
            [ $fullMatch, $transactionId, $status ] = $matches;
            if ( !StatusEnum::fromValue( $status ) ) {
                $bot->sendMessage( '❌ Invalid status provided.' );
                return;
            }

            $transaction = Transaction::where( 'unique_id', $transactionId )->first();

            if ( !$transaction ) {
                $bot->sendMessage( '❌ Transaction not found.' );
                return;
            }

            $transaction->status = $status;
            $transaction->save();

            $confirmationMessage = "✅ Transaction <code>{$transactionId}</code> has been {$status}.\n\n" .
            "💰 Amount: <b>{$transaction->amount}</b> USDT\n" .
            "🏦 Wallet Address: <code>{$transaction->wallet_address}</code>";

            // Edit the original message to reflect the updated status
            $bot->editMessageText(
                "{$confirmationMessage}\n\nThe current status is: {$status}.",
                chat_id: $bot->callbackQuery()->message->chat->id,
                message_id: $bot->callbackQuery()->message->message_id,
                parse_mode: 'HTML'
            );

            $userChatId = $transaction->user->chat_id;

            if ( $status === StatusEnum::DECLINED ) {
                $bot->sendMessage(
                    "Hello! We wanted to inform you that your transaction with ID <code>{$transactionId}</code> has been declined. " .
                    "Unfortunately, the amount of <b>{$transaction->amount}</b> USDT will not be processed at this time. " .
                    'If you have any questions or believe this was a mistake, please reach out for further assistance. ' .
                    "Your wallet address remains on file: <code>{$transaction->wallet_address}</code>.\nThank you for your understanding.",
                    chat_id: $userChatId,
                    parse_mode: 'HTML'
                );
            } else {
                $bot->sendMessage(
                    "Hi there! I'm glad to inform you that your transaction with ID <code>{$transactionId}</code> has been {$status}.\n\n" .
                    "Here's a quick recap:\n" .
                    "💰 Amount: <b>{$transaction->amount}</b> USDT\n" .
                    "🏦 Wallet Address: <code>{$transaction->wallet_address}</code>\n" .
                    'If you have any questions or need further assistance, feel free to reach out!',
                    chat_id: $userChatId,
                    parse_mode: 'HTML'
                );
            }
        } else {
            $bot->sendMessage( '❌ Invalid callback data format.' );
        }
    }
}
