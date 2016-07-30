<?php

namespace modules\mafia;

use awesomeircbot\module\Module;
use awesomeircbot\server\Server;
use modules\mafia\MafiaGame;
use config\Config;

class MafiaOpt extends Module {

    public static $requiredUserLevel = 7;

    private function showIdentityState() {
        $server = Server::getInstance();
        if (MafiaGame::$SHOW_MAFIA_COUNT)
            $server->message(Config::$lobbyRoom, _("Show identity on day punish is ON (show-mafia 1)"));
        else
            $server->message(Config::$lobbyRoom, _("Show identity on day punish is OFF (show-mafia 0)"));
    }

    private function showWinState() {
        $server = Server::getInstance();
        if (MafiaGame::$WON_STATE_NORMAL)
            $server->message(Config::$lobbyRoom, _("Mafia win state is when mafia cnt = ppl cnt (mafia-state 0)"));
        else
            $server->message(Config::$lobbyRoom, _("Mafia win state is when ppl cnt = 0 (mafia-state 1)"));
    }

    private function showDeadTalkState() {
        $server = Server::getInstance();
        if (MafiaGame::$DEAD_IS_TALKING)
            $server->message(Config::$lobbyRoom, _("Dead people can talk (dead-talk 1)"));
        else
            $server->message(Config::$lobbyRoom, _("Dead people can not talk (sorry) (dead-talk 0)"));
    }

    private function showVerboseState() {
        $server = Server::getInstance();
        if (MafiaGame::$VERBOSE)
            $server->message(Config::$lobbyRoom, _("Verbose mode in ON (verbose 1)"));
        else
            $server->message(Config::$lobbyRoom, _("Verbose mode in OFF (verbose 0)"));
    }

    private function showDayTimeout() {
        $server = Server::getInstance();
        $server->message(Config::$lobbyRoom, sprintf(_("Day timeout is %d secound (day-time %d)"), MafiaGame::$DAY_TIMEOUT, MafiaGame::$DAY_TIMEOUT));
    }

    private function showNightTimeout() {
        $server = Server::getInstance();
        $server->message(Config::$lobbyRoom, sprintf(_("Night timeout is %d secound (night-time %d)"), MafiaGame::$NIGHT_TIMEOUT, MafiaGame::$NIGHT_TIMEOUT));
    }

    private function showPunishTimeout() {
        $server = Server::getInstance();
        $server->message(Config::$lobbyRoom, sprintf(_("Last defense timeout is %d secound(s) (punish-time %d)"), MafiaGame::$PUNISH_TIMEOUT, MafiaGame::$PUNISH_TIMEOUT));
    }

    private function showStates() {
        $this->showIdentityState();
        $this->showWinState();
        $this->showDeadTalkState();
        $this->showVerboseState();
        $this->showDayTimeout();
        $this->showNightTimeout();
        $this->showPunishTimeout();
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
                if ($value !== false)
                    MafiaGame::$SHOW_MAFIA_COUNT = intval($value);
                $this->showIdentityState();
                break;
            case "MAFIA-STATE":
                if ($value !== false)
                    MafiaGame::$WON_STATE_NORMAL = intval($value);
                $this->showWinState();
                break;
            case "DEAD-TALK":
                if ($value !== false)
                    MafiaGame::$DEAD_IS_TALKING = intval($value);
                $this->showDeadTalkState();
                break;
            case "VERBOSE":
                if ($value !== false)
                    MafiaGame::$VERBOSE = intval($value);
                $this->showVerboseState();
                break;
            case "NIGHT-TIME":
                if ($value !== false) {
                    MafiaGame::$NIGHT_TIMEOUT = intval($value);
                    if (MafiaGame::$NIGHT_TIMEOUT < 100)
                        MafiaGame::$NIGHT_TIMEOUT = 100;
                }
                $this->showNightTimeout();
                break;
            case "DAY-TIME":
                if ($value !== false) {
                    MafiaGame::$DAY_TIMEOUT = intval($value);
                    if (MafiaGame::$DAY_TIMEOUT < 100)
                        MafiaGame::$DAY_TIMEOUT = 100;
                }
                $this->showDayTimeout();
                break;
            case "PUNISH-TIME":
                if ($value !== false)
                    MafiaGame::$PUNISH_TIMEOUT = intval($value);
                $this->showPunishTimeout();
                break;
        }
    }

}
