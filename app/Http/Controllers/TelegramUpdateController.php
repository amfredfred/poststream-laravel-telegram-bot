<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use SergiX44\Nutgram\Nutgram;

class TelegramUpdateController extends Controller {
    public function __invoke( Nutgram $bot ) {
        $bot->run();
    }
}
