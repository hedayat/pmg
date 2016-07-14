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
        $server->notify($I, MafiaGame::bold(_("Day-only Commands:")));
        $server->notify($I, _("!punish <nick>  : To vote to punish <nick>"));
        $server->notify($I, _("!punish -  : To remove your vote"));
        $server->notify($I, _("!vote  : To see other people votes"));
        $server->notify($I, sprintf(_("!timeout  : To end the day after %d seconds when 60% of players cast their vote."), MafiaGame::$DAY_TIMEOUT));
        sleep(1);

        $server->notify($I, MafiaGame::bold(_("Night-only Commands:")));
        $server->notify($I, sprintf(_("!timeout  : To end the night after %d seconds"), MafiaGame::$NIGHT_TIMEOUT));

        $server->notify($I, MafiaGame::bold(_("Doctor Command:")));
        $server->notify($I, _("!heal <nick>  : To heal <nick>"));

        $server->notify($I, MafiaGame::bold(_("Detector Command:")));
        $server->notify($I, _("!whois <nick>  : To investigate <nick>"));
        sleep(1);

        $server->notify($I, MafiaGame::bold(_("Mafia Commands:")));
        $server->notify($I, _("!kill <nick>  : To kill <nick>"));
        $server->notify($I, _("!kill *  : To kill nobody"));
        $server->notify($I, _("!kill -  : To remove your vote"));
        $server->notify($I, _("!vote  : To see other mafias (and their votes)"));
        sleep(1);

        $server->notify($I, MafiaGame::bold(_("Global Commands:")));
        $server->notify($I, _("!list  : To see list of all players"));
        $server->notify($I, _("!count : To see the number of players"));
        $server->notify($I, _("!whoami: To see who you are!"));
        $server->notify($I, _("!voice : If you must have voice and you have no voice, (mostly reconnect problems)"));
        sleep(1);

        $server->notify($I, MafiaGame::bold(_("Dead Commands:")));
        $server->notify($I, _("!wish <wish> : To say your last wish after being killed at night!"));
        $server->notify($I, _("!slap <nick> [<reason>] : To slap somebody!"));
        $server->notify($I, _("!mafia <nick> [<reason>] : To say you think someone is mafia!"));
        sleep(1);

        $server->notify($I, MafiaGame::bold(_("Other Commands:")));
        $server->notify($I, "register, leave, start, restart, validate, opt, name, save, load, kick, drop, raw");
    }

}
