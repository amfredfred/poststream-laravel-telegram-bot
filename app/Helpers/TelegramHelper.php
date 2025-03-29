<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Nutgram;

class TelegramHelper {

    public static function getBotUrl() {
        return  config( 'telegram.bot_url' );
    }

    public static function getBotUsername() {
        return  config( 'telegram.bot_username' );
    }

    public static function getBotWebappLink() {
        return config( 'telegram.bot_webapp_link' );
    }

    public static function getTelegramShareUrl() {
        return config( 'telegram.telegram_share_url' );
    }

    public static function getPostViewRewardPoint() {
        return config( 'telegram.post_view_rpoint' );
    }

    public static function getPostViewOwnerPoint() {
        return config( 'telegram.post_view_opoint' );
    }

    public static function getBotDeepLink() {
        return config( 'telegram.bot_deeplinking' );
    }

    public static function getForceSubChannel() {
        return config( 'telegram.force_sub_channel' );
    }

    public static function getAdmins() {
        return config( 'telegram.admins' );
    }

    public static function encodeString( string $str ): string {
        return base64_encode( $str );
    }

    public static function decodeUrl( string $encodedUrl ): string {
        return base64_decode( urldecode( $encodedUrl ) );
    }

    public static function encodeUrl( string $url ): string {
        return urlencode( base64_encode( $url ) );
    }

    public static function encode( string $string ): string {
        return rtrim( base64_encode( gzencode( $string ) ), '=' );
    }

    public static function decode( string $base64String ): string {
        return gzdecode( base64_decode( $base64String ) );
    }

    public static function makeId( string $contentType = 'ps' ): string {
        return strtoupper( $contentType . substr( md5( mt_rand() ), 0, 9 ) . 'b' );
    }

    public static function makeShareHandle( string $content, int $ownerId ): array {
        $encodedQuery = self::encode( $content );
        $shareLink = self::getBotUrl() . '?start=' . $content;
        $tgShare = self::getTelegramShareUrl() . $shareLink;

        return [ $shareLink,  $tgShare, $encodedQuery ];
    }

    public static function isSubscribed( $userId ): bool {
        if ( !$userId ) return true;
        if ( empty( self::$forceSubChannel ) ) return true;
        if ( in_array( $userId, self::getAdmins() ) ) return true;

        try {
            $client = new Client();
            $response = $client->get( 'https://api.telegram.org/bot' . env( 'BOT_TOKEN' ) . '/getChatMember', [
                'query' => [
                    'chat_id' => self::getForceSubChannel(),
                    'user_id' => $userId,
                ],
            ] );
            $member = json_decode( $response->getBody(), true );

            if ( in_array( $member[ 'result' ][ 'status' ], [ 'creator', 'administrator', 'member' ] ) ) {
                return true;
            } else {
                return false;
            }
        } catch ( \Exception $e ) {
            Log::error( "Error in isSubscribed: {$e->getMessage()}" );
            return false;
        }
    }

    public static function isValidTonAddress( string $address ): bool {
        $validPrefixes = [ 'EQ', 'UQ', 'Ef', 'Uf' ];
        return in_array( substr( $address, 0, 2 ), $validPrefixes ) && preg_match( '/^[A-Za-z0-9\/+=]+$/', $address ) && strlen( $address ) === 48;
    }

    public static function customLinkParser( string $postUid, string $text ): string {
        return preg_replace_callback( '/\[\s*([^,\]]+)\s*,\s*(https?:\/\/[^\s,]+)\s*\]/', function ( $matches ) use ( $postUid ) {
            $label = $matches[ 1 ];
            $url = $matches[ 2 ];
            $params = [
                'redirect' => $url,
                'postUid' => $postUid,
            ];
            $protectedLink = self::getBotWebappLink() . self::encodeString( json_encode( $params ) );
            return "[{$label}]({$protectedLink})";
        }
        , $text );
    }

    public static function convertToMarkdownLink( string $postUid, string $text ): string {
        return self::customLinkParser( $postUid, $text );
    }

    public static function getTelegramFileUrl( string $fileId ): ?string {
        try {
            $client = new Client();
            $response = $client->get( 'https://api.telegram.org/bot' . env( 'BOT_TOKEN' ) . '/getFile', [
                'query' => [ 'file_id' => $fileId ],
            ] );

            $data = json_decode( $response->getBody(), true );

            if ( !$data[ 'ok' ] ) {
                throw new \Exception( 'Failed to retrieve file path from Telegram API' );
            }

            $filePath = $data[ 'result' ][ 'file_path' ];
            return 'https://api.telegram.org/file/bot' . env( 'BOT_TOKEN' ) . "/{$filePath}";
        } catch ( \Exception $e ) {
            Log::error( "Error fetching file URL: {$e->getMessage()}" );
            return null;
        }
    }

    public static function isAdminOrCreator( Nutgram $bot, int|string $chatId, int $userId ): bool {
        try {
            $chatMember = $bot->getChatMember( $chatId, $userId );
            return in_array( $chatMember->status->value, [ 'administrator', 'creator' ] );
        } catch ( \Throwable $e ) {
            Log::error( 'Failed to check admin status: ' . $e->getMessage() );
            return false;
        }
    }
}
