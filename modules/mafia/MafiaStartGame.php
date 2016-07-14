<?php
namespace modules\mafia;

use awesomeircbot\module\Module;
use awesomeircbot\server\Server;
use modules\mafia\MafiaGame;

class MafiaStartGame extends Module {
	
	public static $requiredUserLevel = 0;
	
	public function run() {
		if ($this->getLevel($this->senderNick) < 10)
			return;
		$server = Server::getInstance();
		$game = MafiaGame::getInstance();
		if (!$this->parameters(1)) {
			$server->message($this->senderNick, "Mafia count should be specified!");
			return;
		}
		$mafia = intval ($this->parameters(1));
		$dr = intval ($this->parameters(2));
		$det = intval ($this->parameters(3));
		$no = intval ($this->parameters(4));
		$game->start($mafia,$dr,$det,$no);
	}
}
