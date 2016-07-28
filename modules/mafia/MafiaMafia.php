<?php

/**
 * Slap Module
 * Slaps the user given
 *
 * Copyright (c) 2011, Jack Harley
 * All Rights Reserved
 */

namespace modules\mafia;

use awesomeircbot\module\Module;
use awesomeircbot\server\Server;
use modules\mafia\MafiaGame;
use config\Config;

class MafiaMafia extends Module {

    public static $requiredUserLevel = 0;

    public function run() {
        $server = Server::getInstance();
        $game = MafiaGame::getInstance();

        $I = $this->senderNick;
        if (!$game->isIn($I)) {
            $server->message($I, _("You are not in game ;)"));
            return;
        }
        
        if (! MafiaGame::$DEAD_IS_TALKING)
        {
            if ($game->isAlive($I))
            {
                $server->message($I, _("You are alive, In dead IS dead mode, you can not use emots if you are alive. say it your self!!"));
                return;
            }
        }        

        $target = $this->parameters(1);
        if (!$game->isIn($target)) {
            $server->message($I, sprintf(_("%s is not in game ;) so why hate him/her this much?"), $target));
            return;
        }

        $cleareString = $this->ParseString($this->parameters(2, true));
        $server->message(Config::$lobbyRoom, sprintf(_("%s thinks %s is Mafia %s"), $I, $target, $cleareString));
    }

    public function ParseString($str) {
        $str = trim($str);
        if (!preg_match('/^because/i', $str) && strlen($str) > 0) {
            $str = _('because ') . $str;
        }
        return $str;
    }

}

?>
