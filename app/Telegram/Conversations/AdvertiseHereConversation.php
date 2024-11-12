<?php

namespace App\Telegram\Conversations;

use App\Helpers\TelegramHelper;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;

class AdvertiseHereConversation extends Conversation {

    public function start( Nutgram $bot ) {

        $bot->sendMessage( 'COMING SOON' );
        $this->end();
        return;

        $bot->sendMessage(
            "📢 Welcome! Let's set up your advertisement.\n\n" .
            "Please provide a brief description of what you'd like to advertise:\n" .
            "<i>Type 'Cancel' anytime to stop.</i>",
            parse_mode: ParseMode::HTML
        );
        $this->next( 'askForDescription' );
    }

    public function askForDescription( Nutgram $bot ) {
        if ( $this->checkForCancel( $bot ) ) return;

        $description = $bot->message()->text;
        $bot->set( 'ad_description', $description );

        $bot->sendMessage(
            'Great! Now, could you specify your target audience? (e.g., age group, interests, location):',
            parse_mode: ParseMode::HTML
        );
        $this->next( 'askForAudience' );
    }

    public function askForAudience( Nutgram $bot ) {
        if ( $this->checkForCancel( $bot ) ) return;

        $audience = $bot->message()->text;
        $bot->set( 'target_audience', $audience );

        $bot->sendMessage(
            'Thank you! Next, please specify your budget for this advertisement campaign (in USD):',
            parse_mode  : ParseMode::HTML
        );
        $this->next( 'askForBudget' );
    }

    public function askForBudget( Nutgram $bot ) {
        if ( $this->checkForCancel( $bot ) ) return;

        $budget = $bot->message()->text;
        $bot->set( 'ad_budget', $budget );

        $bot->sendMessage(
            'Almost done! How long would you like the advertisement to run? (e.g., 1 week, 1 month):',
            parse_mode: ParseMode::HTML
        );
        $this->next( 'askForDuration' );
    }

    public function askForDuration( Nutgram $bot ) {
        if ( $this->checkForCancel( $bot ) ) return;

        $duration = $bot->message()->text;
        $bot->set( 'ad_duration', $duration );

        // Gather all data
        $adDescription = $bot->get( 'ad_description' );
        $targetAudience = $bot->get( 'target_audience' );
        $adBudget = $bot->get( 'ad_budget' );
        $adDuration = $bot->get( 'ad_duration' );

        // Send a summary to the user
        $bot->sendMessage(
            '✅ <b>Here’s a summary of your advertisement request:</b>\n\n' .
            "📄 <b>Description:</b> $adDescription\n" .
            "👥 <b>Target Audience:</b> $targetAudience\n" .
            "💰 <b>Budget:</b> $$adBudget\n" .
            "⏳ <b>Duration:</b> $adDuration\n\n" .
            '<i>We will review your request and get back to you shortly. Thank you for choosing to advertise with us!</i>',
            parse_mode:ParseMode::HTML
        );

        // Forward the summary to the admin
        $adminMessage =
        '📢 <b>New Advertisement Request\n\n\n' .
        "📄 <b>Description:</b> $adDescription\n" .
        "👥 <b>Target Audience:</b> $targetAudience\n" .
        "💰 <b>Budget:</b> $$adBudget\n" .
        "⏳ <b>Duration:</b> $adDuration\n\n" .
        '👤 <b>User ID:</b> ' . $bot->userId();

        $adminId = TelegramHelper::getAdmins()[ 0 ] ?? null;
        if ( $adminId ) {
            $bot->sendMessage( $adminMessage, $adminId, parse_mode:  ParseMode::HTML );
        }

        $this->end();
    }

    /**
    * Check if the user entered 'Cancel' to stop the conversation.
    */

    private function checkForCancel( Nutgram $bot ): bool {
        if ( strtolower( $bot->message()->text ) === 'cancel' ) {
            $bot->sendMessage(
                '🚫 Advertisement setup has been canceled. If you change your mind, you can start again anytime.',
                parse_mode: ParseMode::HTML
            );
            $this->end();
            return true;
        }
        return false;
    }
}
