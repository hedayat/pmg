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
		"mhelp"	=>  "modules\mafia\MafiaHelp",
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

	public static $help;
}

Mafia::$help = array(
		"MAFIA COMMANDS: (" => array(
			"BASE" => array(
				"description" => _("Not a command itself!"),
				"parameters" => false
			)
		),
		"register" => array(
			"BASE" => array(
				"description" => _("Join to the game"),
				"parameters" => false
			)
		),
		"leave" => array(
			"BASE" => array(
				"description" => _("Leave the game"),
				"parameters" => false
			)
		),
		"opt" => array(
			"BASE" => array(
				"description" => _("Show current options"),
				"parameters" => false
			),
			"show-mafia" => array(
				"description" => _("Show identity on day punish"),
				"parameters" => _("0|1")
			),
			"mafia-state" => array(
				"description" => _("Set when mafia will win: 0: mafia count = ppl count, 1: mafia count = 0"),
				"parameters" => _("0|1")
			),
			"dead-talk" => array(
				"description" => _("Set if dead can talk (1: dead can talk, 0: dead cannot talk)"),
				"parameters" => _("0|1")
			),
			"verbose" => array(
				"description" => _("Set if verbose mode should be enabled"),
				"parameters" => _("0|1")
			),
			"night-time" => array(
				"description" => _("Minimum allowed night time (at least 100)"),
				"parameters" => _("<seconds>")
			),
			"day-time" => array(
				"description" => _("Minimum allowed day time (at least 100)"),
				"parameters" => _("<seconds>")
			)
		),
		"name" => array(
			"BASE" => array(
				"description" => _("Set the name of the game"),
				"parameters" => _("<game_name>")
			)
		),
		"save" => array(
			"BASE" => array(
				"description" => _("Save current game and optionally set the name of the current game"),
				"parameters" => _("[<game_name>]")
			)
		),
		"load" => array(
			"BASE" => array(
				"description" => _("Load saved game and optionally set the name of the current game"),
				"parameters" => _("[<game_name>]")
			)
		),
		"start" => array(
			"BASE" => array(
				"description" => _("Start game"),
				"parameters" => _("<mafiacount> [<have_dr> [<have_detective> [<have_invulnerable> [<have_godfather>]]]]")
			)
		),
		"restart" => array(
			"BASE" => array(
				"description" => _("ReStart game to register again"),
				"parameters" => false
			)
		),
		"whoami" => array(
			"BASE" => array(
				"description" => _("Ask who you are in the current game"),
				"parameters" => false
			)
		),
		"count" => array(
			"BASE" => array(
				"description" => _("Prints the number of players"),
				"parameters" => false
			)
		),
		"list" => array(
			"BASE" => array(
				"description" => _("List all registered and in game state"),
				"parameters" => false
			)
		),
		"vote" => array(
			"BASE" => array(
				"description" => _("List current votes"),
				"parameters" => false
			)
		),
		"punish" => array(
			"BASE" => array(
				"description" => _("Day command to punish sombebody, or remove vote (-)"),
				"parameters" => _("<nick>|-")
			)
		),
		"timeout" => array(
			"BASE" => array(
				"description" => _("Request timeout"),
				"parameters" => false
			)
		),
		"kill" => array(
			"BASE" => array(
				"description" => _("Mafia command, kill somebody, or nobody (*), or remote vote (-)"),
				"parameters" => _("<nick>|-|*")
			)
		),
		"heal" => array(
			"BASE" => array(
				"description" => _("Heal in night time"),
				"parameters" => _("<nick>")
			)
		),
		"whois" => array(
			"BASE" => array(
				"description" => _("Detector command in night time to ask if someone is mafia"),
				"parameters" => _("<nick>")
			)
		),
		"wish" => array(
			"BASE" => array(
				"description" => _("A wish by the last one punished during a day"),
				"parameters" => _("<wish>")
			)
		),
		"mafia" => array(
			"BASE" => array(
				"description" => _("Say you think someone is mafia"),
				"parameters" => _("<nick> [<reason>]")
			)
		),
		"slap" => array(
			"BASE" => array(
				"description" => _("Slap somebody!"),
				"parameters" => _("<nick> [<reason>]")
			)
		),
		"voice" => array(
			"BASE" => array(
				"description" => _("Ask for voice, if you should have it but don't"),
				"parameters" => false
			)
		),
		"validate" => array(
			"BASE" => array(
				"description" => _("Remove a user which has left channel from the game"),
				"parameters" => _("<nick>")
			)
		),
		" ) " => array(
			"BASE" => array(
				"description" => _("Not a command itself!"),
				"parameters" => false
			)
		),
		"kick" => array(
			"BASE" => array(
				"description" => _("Kick a user out of channel"),
				"parameters" => false
			)
		),
		"drop" => array(
			"BASE" => array(
				"description" => _("Remove a nick from game"),
				"parameters" => false
			)
		),
		"raw" => array(
			"BASE" => array(
				"description" => _("Send raw message "),
				"parameters" => _("<rawmessage>")
			)
		),
	);

?>
