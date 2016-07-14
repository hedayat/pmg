<?php
namespace modules\mafia;

use awesomeircbot\module\Module;
use awesomeircbot\server\Server;
use modules\mafia\MafiaGame;
use config\Config;

class MafiaReStartGame extends Module {
	
	public static $requiredUserLevel = 0;
	
	public function run() {
		if ($this->getLevel($this->senderNick) < 10)
			return;
		$server = Server::getInstance();
		$game = MafiaGame::getInstance(true);
		$server->message(Config::$lobbyRoom, _("Game restarted!"));
	}
}
