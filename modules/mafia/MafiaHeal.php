<?php

namespace modules\mafia;

use awesomeircbot\module\Module;
use awesomeircbot\server\Server;
use modules\mafia\MafiaGame;

class MafiaHeal extends Module {

    public static $requiredUserLevel = 0;

    public function run() {
        $server = Server::getInstance();
        $game = MafiaGame::getInstance();

        $I = $this->senderNick;
        if ($game->getState() != MAFIA_TURN) {
            $server->message($I, _("Not healing time!"));
            return;
        }

        if (!$game->isIn($I)) {
            $server->message($I, _("You are not in game ;) may be next time"));
            return;
        }
        $you = $this->parameters(1);
        if (!$game->isIn($you) && $you != "*" && $you != "-") {
            $server->message($I, sprintf(_("%s is not in game ;) are you in love with him/her?"), $you));
            return;
        }
        $game->iSayHealYou($I, $you);
    }

}
