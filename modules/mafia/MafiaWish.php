<?php
namespace modules\mafia;

use awesomeircbot\module\Module;
use awesomeircbot\server\Server;
use modules\mafia\MafiaGame;

class MafiaWish extends Module {
	
	public static $requiredUserLevel = 0;
	
	public function run() {
		$server = Server::getInstance();
		$game = MafiaGame::getInstance();
		
		$I = $this->senderNick;
		
		if ($game->getState() == MAFIA_TURN)
		{
			$server->message($I, _("A wish in night? you are crazy!"));
			return;
		}

		if (!$game->isIn($I))
		{
			$server->message($I, _("You are not in game ;) may be next time"));
			return;
		}
		$wish = $this->parameters(1 , true);
		
		$game->thisIsMyLastWish($I , $wish);
	}
}
