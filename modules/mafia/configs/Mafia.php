<?php
/**
 * Log Module Config
 *
 * Copyright (c) 2011, Jack Harley
 * All Rights Reserved
 */
namespace modules\mafia\configs;
use awesomeircbot\module\ModuleConfig;
use awesomeircbot\line\ReceivedLineTypes;

class Mafia implements ModuleConfig {

	public static $mappedCommands = array(
		"list"	=>  "modules\mafia\MafiaList",
		"vote"	=>  "modules\mafia\MafiaVote",
		"heal"	=>  "modules\mafia\MafiaHeal",
		"whois"	=>  "modules\mafia\MafiaWhois",
		"opt"	=>  "modules\mafia\MafiaOpt",
		"raw"     => "modules\mafia\MafiaRaw",
		"kick"     => "modules\mafia\MafiaKick",
		"drop"     => "modules\mafia\MafiaDrop",
		"register" => "modules\mafia\MafiaJoinGame",
		"leave" => "modules\mafia\MafiaLeaveGame",
		"kill" => "modules\mafia\MafiaKill",
		"punish" => "modules\mafia\MafiaPunish",
		"start" => "modules\mafia\MafiaStartGame",
		"restart" => "modules\mafia\MafiaReStartGame",
		"validate" => "modules\mafia\MafiaValidate",
		"timeout" => "modules\mafia\MafiaTimeout",
		"count" => "modules\mafia\MafiaCount",
		"wish" => "modules\mafia\MafiaWish",
		"voice" => "modules\mafia\MafiaVoice",
		"whoami" => "modules\mafia\MafiaWhoami",
		"save" => "modules\mafia\MafiaSave",
		"load" => "modules\mafia\MafiaLoad",
		"name" => "modules\mafia\MafiaName",
		"slap" => "modules\mafia\MafiaSlap",
		"mafia" => "modules\mafia\MafiaMafia",
	);

	public static $mappedEvents = array(
		ReceivedLineTypes::JOIN => "modules\mafia\MafiaJoinChanel",
		ReceivedLineTypes::NICK => "modules\mafia\MafiaNick",
	);

	public static $mappedTriggers = array(
	);

	public static $help = array(
		"MAFIA COMMANDS: (" => array(
			"BASE" => array(
				"description" => "Not a command itself!",
				"parameters" => false
			)
		),
		"register" => array(
			"BASE" => array(
				"description" => "Join to the game",
				"parameters" => false
			)
		),
		"leave" => array(
			"BASE" => array(
				"description" => "Leave the game",
				"parameters" => false
			)
		),
		"opt" => array(
			"BASE" => array(
				"description" => "Show current options",
				"parameters" => false
			),
			"show-mafia" => array(
				"description" => "Show identity on day punish",
				"parameters" => "0|1"
			),
			"mafia-state" => array(
				"description" => "Set when mafia will win: 0: mafia count = ppl count, 1: mafia count = 0",
				"parameters" => "0|1"
			),
			"dead-talk" => array(
				"description" => "Set if dead can talk (1: dead can talk, 0: dead cannot talk)",
				"parameters" => "0|1"
			),
			"verbose" => array(
				"description" => "Set if verbose mode should be enabled",
				"parameters" => "0|1"
			),
			"night-time" => array(
				"description" => "Minimum allowed night time (at least 100)",
				"parameters" => "<seconds>"
			),
			"day-time" => array(
				"description" => "Minimum allowed day time (at least 100)",
				"parameters" => "<seconds>"
			)
		),
		"name" => array(
			"BASE" => array(
				"description" => "Set the name of the game",
				"parameters" => "<game_name>"
			)
		),
		"save" => array(
			"BASE" => array(
				"description" => "Save current game and optionally set the name of the current game",
				"parameters" => "[<game_name>]"
			)
		),
		"load" => array(
			"BASE" => array(
				"description" => "Load saved game and optionally set the name of the current game",
				"parameters" => "[<game_name>]"
			)
		),
		"start" => array(
			"BASE" => array(
				"description" => "Start game",
				"parameters" => "<mafiacount> [<have_dr> [<have_detective> [<have_invulnerable>]]]"
			)
		),
		"restart" => array(
			"BASE" => array(
				"description" => "ReStart game to register again",
				"parameters" => false
			)
		),
		"whoami" => array(
			"BASE" => array(
				"description" => "Ask who you are in the current game",
				"parameters" => false
			)
		),
		"count" => array(
			"BASE" => array(
				"description" => "Prints the number of players",
				"parameters" => false
			)
		),
		"list" => array(
			"BASE" => array(
				"description" => "List all registered and in game state",
				"parameters" => false
			)
		),
		"vote" => array(
			"BASE" => array(
				"description" => "List current votes",
				"parameters" => false
			)
		),
		"punish" => array(
			"BASE" => array(
				"description" => "Day command to punish sombebody, or remove vote (-)",
				"parameters" => "<nick>|-"
			)
		),
		"timeout" => array(
			"BASE" => array(
				"description" => "Request timeout",
				"parameters" => false
			)
		),
		"kill" => array(
			"BASE" => array(
				"description" => "Mafia command, kill somebody, or nobody (*), or remote vote (-)",
				"parameters" => "<nick>|-|*"
			)
		),
		"heal" => array(
			"BASE" => array(
				"description" => "Heal in night time",
				"parameters" => "<nick>"
			)
		),
		"whois" => array(
			"BASE" => array(
				"description" => "Detector command in night time to ask if someone is mafia",
				"parameters" => "<nick>"
			)
		),
		"wish" => array(
			"BASE" => array(
				"description" => "A wish by the last one dead at night",
				"parameters" => "<wish>"
			)
		),
		"mafia" => array(
			"BASE" => array(
				"description" => "Say you think someone is mafia",
				"parameters" => "<nick> [<reason>]"
			)
		),
		"slap" => array(
			"BASE" => array(
				"description" => "Slap somebody!",
				"parameters" => "<nick> [<reason>]"
			)
		),
		"voice" => array(
			"BASE" => array(
				"description" => "Ask for voice, if you should have it but don't",
				"parameters" => false
			)
		),
		"validate" => array(
			"BASE" => array(
				"description" => "Remove a user which has left channel from the game",
				"parameters" => "<nick>"
			)
		),
		" ) " => array(
			"BASE" => array(
				"description" => "Not a command itself!",
				"parameters" => false
			)
		),
		"kick" => array(
			"BASE" => array(
				"description" => "Kick a user out of channel",
				"parameters" => false
			)
		),
		"drop" => array(
			"BASE" => array(
				"description" => "Remove a nick from game",
				"parameters" => false
			)
		),
		"raw" => array(
			"BASE" => array(
				"description" => "Send raw message ",
				"parameters" => "<rawmessage>"
			)
		),
	);

}
?>
