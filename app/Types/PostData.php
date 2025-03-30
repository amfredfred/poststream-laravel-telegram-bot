<?php
namespace App\Types;

use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class PostData {
    public string $caption;
    public string $mediaType;
    public string $mediaId;
    public array $captionEntities;
    public string $postId;
    public string $postMessageId;
    public ?InlineKeyboardMarkup $inline_keyboard_markup;

    public function __construct() {
        $this->caption = '';
        $this->mediaType = '';
        $this->mediaId = '';
        $this->captionEntities = [];
        $this->postId = '';
        $this->inline_keyboard_markup = null;
        $this->postMessageId = '';
    }
}
