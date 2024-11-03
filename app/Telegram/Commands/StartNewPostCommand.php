<?php

namespace App\Telegram\Commands;

use App\Telegram\Conversations\CreatePostConversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Handlers\Type\Command;

class StartNewPostCommand extends Command {
    protected string $command = 'create_post';

    protected ?string $description = 'Start a new post';

    public function handle( Nutgram $bot ): void {
        
    }
}
