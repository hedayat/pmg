<?php

namespace modules\mafia;

use awesomeircbot\module\Module;
use awesomeircbot\server\Server;
use modules\mafia\MafiaGame;
use config\Config;

class MafiaOpt extends Module {

    public static $requiredUserLevel = 7;

    private function showStates() {
        $server = Server::getInstance();
        if (MafiaGame::$SHOW_MAFIA_COUNT)
            $server->message(Config::$lobbyRoom, _("Show identity on day punish is ON (show-mafia 1)"));
        else
            $server->message(Config::$lobbyRoom, _("Show identity on day punish is OFF (show-mafia 0)"));

        if (MafiaGame::$WON_STATE_NORMAL)
            $server->message(Config::$lobbyRoom, _("Mafia win state is when mafia cnt = ppl cnt (mafia-state 0)"));
        else
            $server->message(Config::$lobbyRoom, _("Mafia win state is when ppl cnt = 0 (mafia-state 1)"));


        if (MafiaGame::$DEAD_IS_TALKING)
            $server->message(Config::$lobbyRoom, _("Dead people can talk (dead-talk 1)"));
        else
            $server->message(Config::$lobbyRoom, _("Dead people can not talk (sorry) (dead-talk 0)"));

        if (MafiaGame::$VERBOSE)
            $server->message(Config::$lobbyRoom, _("Verbose mode in ON (verbose 1)"));
        else
            $server->message(Config::$lobbyRoom, _("Verbose mode in OFF (verbose 0)"));

        $server->message(Config::$lobbyRoom, sprintf(_("Day timeout is %d secound (day-time %d)"), MafiaGame::$DAY_TIMEOUT, MafiaGame::$DAY_TIMEOUT));
        $server->message(Config::$lobbyRoom, sprintf(_("Night timeout is %d secound (night-time %d)"), MafiaGame::$NIGHT_TIMEOUT, MafiaGame::$NIGHT_TIMEOUT));
    }

    public function run() {
        $server = Server::getInstance();
        $opt = $this->parameters(1);
        if (!$opt) {
            $this->showStates();
            return;
        }
        $value = $this->parameters(2, true);
        switch (strtoupper($opt)) {
            case "SHOW-MAFIA":
                MafiaGame::$SHOW_MAFIA_COUNT = $value;
                break;
            case "MAFIA-STATE":
                MafiaGame::$WON_STATE_NORMAL = $value;
                break;
            case "DEAD-TALK":
                MafiaGame::$DEAD_IS_TALKING = $value;
                break;
            case "VERBOSE":
                MafiaGame::$VERBOSE = $value;
                break;
            case "NIGHT-TIME":
                MafiaGame::$NIGHT_TIMEOUT = intval($value);
                if (MafiaGame::$NIGHT_TIMEOUT < 100)
                    MafiaGame::$NIGHT_TIMEOUT = 100;
                break;
            case "DAY-TIME":
                MafiaGame::$DAY_TIMEOUT = intval($value);
                if (MafiaGame::$DAY_TIMEOUT < 100)
                    MafiaGame::$DAY_TIMEOUT = 100;
                break;
        }
    }

}
