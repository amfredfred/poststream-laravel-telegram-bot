<?php

namespace App\Telegram\Commands;

use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Handlers\Type\Command;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;

class HelpCommand extends Command {
    protected string $command = "help";

    protected ?string $description = "Get some help";

    public function handle( Nutgram $bot ): void {
        $helpMessage = "<b>🤖 Welcome to POSTSTREAMBOT Help!</b>\n\n";
        $helpMessage .= "Here’s how you can make the most out of the bot:\n\n";

        $helpMessage .= "<b>1. Start the Bot:</b>\n";
        $helpMessage .= "Use the <code>/start</code> command to begin interacting with the bot. This will show you the main menu where you can choose your actions.\n\n";

        $helpMessage .= "<b>2. Create and Share Posts:</b>\n";
        $helpMessage .= "Select the <code>Create Post</code> option to craft and share your posts. Add text, media, and inline links to make your posts engaging.\n\n";

        $helpMessage .= "<b>3. Monetize Your Content:</b>\n";
        $helpMessage .= "Earn money by having others interact with your posts. You can monetize your messages by adding clickable links using the format <code>[Text, URL]</code>.\n\n";

        $helpMessage .= "<b>4. Track Your Earnings:</b>\n";
        $helpMessage .= "Check your account details to see your earnings, post views, and total posts. Use the <code>/account</code> command for a detailed summary.\n\n";

        $helpMessage .= "<b>5. Get Help:</b>\n";
        $helpMessage .= "If you have any questions or need assistance, use the <code>/help</code> command to get more information or contact support.\n\n";

        $helpMessage .= "<i>Need more help? Feel free to ask or visit our support chat. @poststreamchat</i>";
        $bot->sendMessage( $helpMessage, parse_mode:ParseMode::HTML );
    }
}
