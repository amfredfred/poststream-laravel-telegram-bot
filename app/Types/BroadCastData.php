<?php
namespace App\Types;

use App\Models\User;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class BroadCastData {
    public string $caption;
    public string $mediaType;
    public string $mediaId;
    public array $captionEntities;
    public array $userChatIds; // Corrected to use array
    public string $broadcastId;
    public ?InlineKeyboardMarkup $inline_keyboard_markup;

    public function __construct() {
        $this->caption = '';
        $this->mediaType = '';
        $this->mediaId = '';
        $this->captionEntities = [];
        $this->broadcastId = '';
        $this->userChatIds = []; // Added missing semicolon
        $this->inline_keyboard_markup = null;
    }
}
