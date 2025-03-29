<?php

namespace App\Helpers;

class ResponseMessageHelper {
    public static function StartBotMessage() {
        return "<b>Welcome,\nI’m Bunzilla, the meme-devouring legend!</b> 🌟🔥\n\n"
            . "Embark on an epic journey through the meme coin universe, where your mission is to help Bunzilla rise to unstoppable greatness! 🌐💥\n\n"
            . "👉 Type <code>/invite</code> to discover how to bring your frens along for the ride.";
    }

    public static function InviteMessage(string $invite_url) {
        $referral_bonus = BunzillaHelper::getReferralBonus();

        return "🌟 <b>Invite Your Frens and Earn Rewards!</b> 🌟\n\n"
            . "🎁 Earn <b>$referral_bonus Points</b> for both <code>YOU</code> and <code>YOUR FRIEND</code> when they join!\n\n"
            . "Together, you can unlock amazing opportunities, grow your network, and achieve greatness! 💪\n\n"
            . "Share Bunzilla with your squad using the link below: ⤵️\n\n"
            . "🔗 Your Invite Link:\n<code>$invite_url</code>";
    }
}
