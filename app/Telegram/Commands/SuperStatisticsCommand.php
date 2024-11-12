<?php

namespace App\Telegram\Commands;

use App\Enums\CallBackDataEnum;
use App\Enums\CommandsEnum;
use App\Models\Post;
use App\Models\PostViews;
use App\Models\User;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Handlers\Type\Command;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class SuperStatisticsCommand extends Command {
    protected string $command = CommandsEnum::SUPER_STATS;

    protected ?string $description = 'View bot statistics.';

    public function handle(Nutgram $bot): void {
        // Fetch statistics
        $total_post_views = $this->getTotalPostViews();
        $total_posts = $this->getTotalPosts();
        $total_users = $this->getTotalUsers();

        $total_post_views_24h = $this->getTotalPostViewsOver24h();
        $total_post_views_7d = $this->getTotalPostViewsOver7d();
        $total_post_views_30d = $this->getTotalPostViewsOver30d();

        $total_users_24h = $this->getTotalUsersUpdatedOver24h();
        $total_users_7d = $this->getTotalUsersUpdatedOver7d();
        $total_users_30d = $this->getTotalUsersUpdatedOver30d();

        // Format the message with HTML
        $statsMessage = sprintf(
            "<b>📊 Bot Statistics</b>\n\n" .
            "=====================\n" .
            "👁️ <b>Total Post Views:</b> %d\n" .
            "🔹 <b>Post Views (24h):</b> %d\n" .
            "🔹 <b>Post Views (7d):</b> %d\n" .
            "🔹 <b>Post Views (30d):</b> %d\n\n" .
            "📝 <b>Total Posts:</b> %d\n\n" .
            "👤 <b>Total Users:</b> %d\n" .
            "🔸 <b>Users Updated in Last 24h:</b> %d\n" .
            "🔸 <b>Users Updated in Last 7d:</b> %d\n" .
            "🔸 <b>Users Updated in Last 30d:</b> %d\n" .
            "=====================\n\n" .
            "🌟 Want to reach more people? Tap below to advertise with us!",
            $total_post_views,
            $total_post_views_24h,
            $total_post_views_7d,
            $total_post_views_30d,
            $total_posts,
            $total_users,
            $total_users_24h,
            $total_users_7d,
            $total_users_30d
        );

        // Add "Advertise Here" button
        $bot->sendMessage($statsMessage, parse_mode:ParseMode::HTML,
            reply_markup: InlineKeyboardMarkup::make()->addRow(
                InlineKeyboardButton::make('📢 Advertise Here', callback_data: CallBackDataEnum::ADVERTISE_HERE)
            )
        );
    }

    private function getTotalPostViews(): int {
        return PostViews::count();
    }

    private function getTotalPostViewsOver24h(): int {
        return PostViews::where('created_at', '>=', now()->subDay())->count();
    }

    private function getTotalPostViewsOver7d(): int {
        return PostViews::where('created_at', '>=', now()->subDays(7))->count();
    }

    private function getTotalPostViewsOver30d(): int {
        return PostViews::where('created_at', '>=', now()->subDays(30))->count();
    }

    private function getTotalPosts(): int {
        return Post::count();
    }

    private function getTotalUsers(): int {
        return User::count();
    }

    private function getTotalUsersUpdatedOver24h(): int {
        return User::where('updated_at', '>=', now()->subDay())->count();
    }

    private function getTotalUsersUpdatedOver7d(): int {
        return User::where('updated_at', '>=', now()->subDays(7))->count();
    }

    private function getTotalUsersUpdatedOver30d(): int {
        return User::where('updated_at', '>=', now()->subDays(30))->count();
    }
}
