<?php

namespace modules\mafia;

use awesomeircbot\module\Module;
use awesomeircbot\server\Server;
use modules\mafia\MafiaGame;

class MafiaHelp extends Module {

    public static $requiredUserLevel = 0;

    public function run() {
        $server = Server::getInstance();
        $I = $this->senderNick;
        $server->message($I, MafiaGame::bold("Day-only Commands:"));
        $server->message($I, "!punish <nick>  : To vote to punish <nick>");
        $server->message($I, "!punish -  : To remove your vote");
        $server->message($I, "!vote  : To see other people votes");
        $server->message($I, "!timeout  : To end the day after " . MafiaGame::$DAY_TIMEOUT . " and 60% of player cast their vote.");

        $server->message($I, MafiaGame::bold("Night-only Commands:"));
        $server->message($I, "!timeout  : To end the night after " . MafiaGame::$NIGHT_TIMEOUT);

        $server->message($I, MafiaGame::bold("Doctor Command:"));
        $server->message($I, "!heal <nick>  : To heal <nick>");

        $server->message($I, MafiaGame::bold("Detector Command:"));
        $server->message($I, "!whois <nick>  : To investigate <nick>");

        $server->message($I, MafiaGame::bold("Mafia Commands:"));
        $server->message($I, "!kill <nick>  : To kill <nick>");
        $server->message($I, "!kill *  : To kill nobody");
        $server->message($I, "!kill -  : To remove your vote");
        $server->message($I, "!vote  : To see other mafias (and their votes)");

        $server->message($I, MafiaGame::bold("Global Commands:"));
        $server->message($I, "!list  : To see list of all players");
        $server->message($I, "!count : To see the number of players");
        $server->message($I, "!whoami: To see who you are!");
        $server->message($I, "!voice : If you must have voice and you have no voice, (mostly reconnect problems)");

        $server->message($I, MafiaGame::bold("Dead Commands:"));
        $server->message($I, "!wish <wish> : To say your last wish after being killed at night!");
        $server->message($I, "!slap <nick> [<reason>] : To slap somebody!");
        $server->message($I, "!mafia <nick> [<reason>] : To say you think someone is mafia!");

        $server->message($I, MafiaGame::bold("Other Commands:"));
        $server->message($I, "register, leave, start, restart, validate, opt, name, save, load, kick, drop, raw");
    }

}
