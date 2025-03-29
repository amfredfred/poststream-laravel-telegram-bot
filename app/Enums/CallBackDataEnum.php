<?php declare( strict_types = 1 );

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
* Callback data enum for Telegram bot actions.
*
* @method static static CREATE_POST()
* @method static static CANCEL_CREATE_POST()
* @method static static ADD_BUTTON()
* @method static static PREVIEW_POST()
* @method static static PUBLISH_POST()
* @method static static EDIT_POST()
* @method static static DELETE_POST()
* @method static static VIEW_STATISTICS()
* @method static static SKIP_CAPTION()
* @method static static SKIP_MEDIA()
* @method static static CHANGE_CAPTION()
* @method static static REMOVE_MEDIA()
* @method static static ADD_MEDIA()
* @method static static RESET_BUTTONS()
* @method static static ADD_CAPTION()
* @method static static REORDER_BUTTONS()
* @method static static DUPLICATE_POST()
* @method static static SAVE_DRAFT()
* @method static static DISCARD_DRAFT()
* @method static static ADD_TAGS()
* @method static static REMOVE_TAGS()
* @method static static SET_PRIORITY()
* @method static static ADD_IMAGE()
* @method static static REMOVE_IMAGE()
* @method static static ACCOUNT_STATS()
* @method static static SUBMIT_REQUEST()
* @method static static TRANSACTION_STATUS()
* @method static static ADVERTISE_HERE()
* @method static static CANCEL_CREATE_POST()
* @method static static AUDIENCE_LAST_30_DAYS()
* @method static static AUDIENCE_LAST_7_DAYS()
* @method static static AUDIENCE_LAST_24_HOURS()
* @method static static AUDIENCE_LAST_USER()
* @method static static START_BROADCAST()
*/
final class CallBackDataEnum extends Enum {
    // Callback data for creating a post
    const CREATE_POST = 'type:CREATE_POST';

    // Callback data for canceling post creation
    const CANCEL_CREATE_POST = 'type:CANCEL_CREATE_POST';

    // Callback data for adding a link or button to a post
    const ADD_BUTTON = 'type:ADD_BUTTON';

    // Callback data for previewing a post
    const PREVIEW_POST = 'type:PREVIEW_POST';

    // Callback data for publishing a post
    const PUBLISH_POST = 'type:PUBLISH_POST';

    // Callback data for editing a post
    const EDIT_POST = 'type:EDIT_POST';

    // Callback data for deleting a post
    const DELETE_POST = 'type:DELETE_POST';

    // Callback data for viewing post statistics
    const VIEW_STATISTICS = 'type:VIEW_STATISTICS';

    // Callback data for skipping the caption step
    const SKIP_CAPTION = 'type:SKIP_CAPTION';

    // Callback data for skipping the media upload step
    const SKIP_MEDIA = 'type:SKIP_MEDIA';

    // Callback data for changing the caption of a post
    const CHANGE_CAPTION = 'type:CHANGE_CAPTION';

    // Callback data for removing media from a post
    const REMOVE_MEDIA = 'type:REMOVE_MEDIA';

    // Callback data for removing media from a post
    const ADD_MEDIA = 'type:ADD_MEDIA';

    // Callback data for removing media from a post
    const SEND_TO_CHAT = 'type:SEND_TO_CHAT';

    // Callback data for resetting all buttons on a post
    const RESET_BUTTONS = 'type:RESET_BUTTONS';

    // Callback data for adding a caption to a post
    const ADD_CAPTION = 'type:ADD_CAPTION';

    // Callback data for reordering buttons in a post
    const REORDER_BUTTONS = 'type:REORDER_BUTTONS';

    // Callback data for duplicating a post
    const DUPLICATE_POST = 'type:DUPLICATE_POST';

    // Callback data for saving a draft of the post
    const SAVE_DRAFT = 'type:SAVE_DRAFT';

    // Callback data for discarding a draft of the post
    const DISCARD_DRAFT = 'type:DISCARD_DRAFT';

    // Callback data for adding tags to a post
    const ADD_TAGS = 'type:ADD_TAGS';

    // Callback data for removing tags from a post
    const REMOVE_TAGS = 'type:REMOVE_TAGS';

    // Callback data for setting the priority of a post ( high, medium, low )
    const SET_PRIORITY = 'type:SET_PRIORITY';

    // Callback data for adding an image to a post
    const ADD_IMAGE = 'type:ADD_IMAGE';

    // Callback data for removing an image from a post
    const REMOVE_IMAGE = 'type:REMOVE_IMAGE';

    // Callback data for removing an image from a post
    const ACCOUNT_STATS = 'type:ACCOUNT_STATS';

    // Callback data for removing an image from a post
    const OK = 'type:OKAY';

    const SUBMIT_REQUEST = 'type:SUBMIT_REQUEST';

    const ADVERTISE_HERE = 'type:ADVERTISE_HERE';
    const CONNECT_CHAT = 'type:CONNECT_CHAT';

    const AUDIENCE_LAST_30_DAYS = 'audience:last_30_days';
    const AUDIENCE_LAST_7_DAYS = 'audience:last_7_days';
    const AUDIENCE_LAST_24_HOURS = 'audience:last_24_hours';
    const AUDIENCE_LAST_USER = 'audience:last_user';
    const START_BROADCAST = 'type:START_BROADCAST';

}
