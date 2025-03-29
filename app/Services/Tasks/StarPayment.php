<?php
namespace App\Services\Tasks;

use App\Exceptions\TaskNotCompletedException;
use SergiX44\Nutgram\Telegram\Types\Payment\LabeledPrice;
use App\Helpers\BunzillaHelper;
use App\Interfaces\ChallengeHandlerInterface;
use App\Models\Challenge;
use App\Models\Activity;

class StarPayment implements ChallengeHandlerInterface {
    public static function handle( Challenge $challenge, Activity $task, $userId ) {
        try {

            $title = $challenge->name;
            $payload = "$userId::$task->id::$challenge->stars_required";
            $description = $challenge->description ;
            $labedPrice = [ LabeledPrice::make( $description, ( int ) $challenge->stars_required ) ];

            $bot = BunzillaHelper::bot();
            $invoiceLink = $bot->createInvoiceLink(
                $title,
                $description,
                $payload,
                '',
                'XTR',
                $labedPrice
            );

            return [ [
                'invoice_link' => $invoiceLink,
            ], '' ];
        } catch ( \Throwable $th ) {
            throw new TaskNotCompletedException( 'Try again later!', details:$th->getMessage() );
        }
    }
}
