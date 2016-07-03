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
        $server->notify($I, MafiaGame::bold("Day-only Commands:"));
        $server->notify($I, "!punish <nick>  : To vote to punish <nick>");
        $server->notify($I, "!punish -  : To remove your vote");
        $server->notify($I, "!vote  : To see other people votes");
        $server->notify($I, "!timeout  : To end the day after " . MafiaGame::$DAY_TIMEOUT . " and 60% of player cast their vote.");
        sleep(1);

        $server->notify($I, MafiaGame::bold("Night-only Commands:"));
        $server->notify($I, "!timeout  : To end the night after " . MafiaGame::$NIGHT_TIMEOUT);

        $server->notify($I, MafiaGame::bold("Doctor Command:"));
        $server->notify($I, "!heal <nick>  : To heal <nick>");

        $server->notify($I, MafiaGame::bold("Detector Command:"));
        $server->notify($I, "!whois <nick>  : To investigate <nick>");
        sleep(1);

        $server->notify($I, MafiaGame::bold("Mafia Commands:"));
        $server->notify($I, "!kill <nick>  : To kill <nick>");
        $server->notify($I, "!kill *  : To kill nobody");
        $server->notify($I, "!kill -  : To remove your vote");
        $server->notify($I, "!vote  : To see other mafias (and their votes)");
        sleep(1);

        $server->notify($I, MafiaGame::bold("Global Commands:"));
        $server->notify($I, "!list  : To see list of all players");
        $server->notify($I, "!count : To see the number of players");
        $server->notify($I, "!whoami: To see who you are!");
        $server->notify($I, "!voice : If you must have voice and you have no voice, (mostly reconnect problems)");
        sleep(1);

        $server->notify($I, MafiaGame::bold("Dead Commands:"));
        $server->notify($I, "!wish <wish> : To say your last wish after being killed at night!");
        $server->notify($I, "!slap <nick> [<reason>] : To slap somebody!");
        $server->notify($I, "!mafia <nick> [<reason>] : To say you think someone is mafia!");
        sleep(1);

        $server->notify($I, MafiaGame::bold("Other Commands:"));
        $server->notify($I, "register, leave, start, restart, validate, opt, name, save, load, kick, drop, raw");
    }

}
