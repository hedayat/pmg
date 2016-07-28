<?php

namespace modules\mafia;

defined('MAFIA_TURN') || define('MAFIA_TURN', 1);
//!defined('PRE_DAY_TURN') 	|| define('PRE_DAY_TURN' , 2);
defined('DAY_TURN') || define('DAY_TURN', 2);

defined('UNDEF_PPL') || define('UNDEF_PPL', 0);
defined('NORMAL_PPL') || define('NORMAL_PPL', 1);
defined('MAFIA_PPL') || define('MAFIA_PPL', 2);
defined('DR_PPL') || define('DR_PPL', 3);
defined('DETECTIVE_PPL') || define('DETECTIVE_PPL', 4);
defined('NOHARM_PPL') || define('NOHARM_PPL', 5);
defined('GODFATHER_PPL') || define('GODFATHER_PPL', 6);

use awesomeircbot\server\Server;
use config\Config;

class MafiaGame {

    private static $gameName = "default";
    private static $instanse;

    /**
     * 
     * Night time out
     * @var integer
     */
    static $NIGHT_TIMEOUT = 180;

    /**
     * 
     * Day time out
     * @var integer
     */
    static $DAY_TIMEOUT = 360;

    static $PUNISH_TIMEOUT = 60;

    /**
     * 
     * List of in game nicks
     * @var array
     */
    private $inGameNicks = array();

    /**
     * 
     * List of in game nicks with data 
     * @var array
     */
    private $inGamePart = array();

    /**
     * 
     * Time, MAFIA_TURN and DAY_TURN
     * @var integer
     */
    private $state = 0;

    /**
     * Kill votes in night
     * @var array
     */
    private $killVotes;

    /**
     * Punish vote in day
     * @var array
     */
    private $punishVotes;
    //private $lobbyPass;
    /**
     * Mafia channel password
     * @var string
     */
    private $mafiaPass;

    /**
     * Dr vote to heal
     * @var string
     */
    private $drVote;

    /**
     * Detective suspect
     * @var string
     */
    private $detectiveVote;

    /**
     * night start time (Unix timestamp)
     * @var integer
     */
    private $nightTurnTime = 0;

    /**
     * day start time (Unix timestamp)
     * @var integer
     */
    private $dayTurnTime = 0;

    private $punishStartTime = 0;
    private $punishWho = "";

    /**
     * The one who dead last
     * @var string
     */
    private $lastDead;

    /**
     * Last dead's wish
     * @var string
     */
    private $lastWish;

    /**
     *  Show alive mafia count each day?
     * @var integer
     */
    static $SHOW_MAFIA_COUNT = 0;

    /**
     * Win state , I will remove this soon
     * @var integer
     * @deprecated
     */
    static $WON_STATE_NORMAL = 1;

    /**
     * Dead people can talk or not
     * @var Integer
     */
    static $DEAD_IS_TALKING = 0;

    static $VERBOSE = 1;

    /**
     * 
     * Set mode for channel
     * @param string $channel channel name
     * @param string $mode IRC mode
     */
    private function setMode($channel, $mode) {
        $this->say("ChanServ", "SET $channel MLOCK $mode");
        sleep(1);
    }

    /**
     * 
     * Claim channel ownership
     * @param string $channel
     * @param string $who
     */
    private function setOp($channel, $who) {
        $this->say("ChanServ", "OP  " . $channel . " " . $who);
        sleep(1);
    }

    /**
     * 
     * Say something to channel or people
     * @param string $who channel/nick
     * @param string $message
     */
    private function say($who, $message) {
        //static $lastTime = 0;
        //static $count = 0;
        //Try to avoid flood :D
        //$count++;
        /* 		if ($lastTime > time() - 3 && $count > 5)
          {
          $lastTime = time();
          $count = 0;
          sleep(1);
          }
          elseif ($lastTime < time() - 3)
          {
          $lastTime = 0;
          $count = 0;
          sleep(1);
          } */
        $server = Server::getInstance();
        $server->message($who, $message);
        sleep(1);
    }

    /**
     * 
     * Send /me message
     * @param string $who nick/channel
     * @param string $message
     */
    private function act($who, $message) {
        $server = Server::getInstance();
        $server->act($who, $message);
        sleep(1);
    }

    /**
     *
     * Send notice
     * @param string $who nick/channel
     * @param string $message
     */
    private function notice($who, $message) {
        $server = Server::getInstance();
        $server->notice($who, $message);
        sleep(1);
    }

    /**
     * 
     * Set channel topic
     * @param string $channel
     * @param string $topic
     */
    private function topic($channel, $topic) {
        $server = Server::getInstance();
        $server->topic($channel, $topic, true);
        sleep(1);
    }

    /**
     * 
     * Invite $who to $channel
     * @param string $who
     * @param string $channel
     */
    private function invite($who, $channel) {
        $server = Server::getInstance();
        $server->channelInvite($who, $channel);
    }

    /**
     * 
     * Kick user from channel
     * @param string $who
     * @param string $channel
     * @param string $why
     */
    private function kick($who, $channel, $why) {
        $server = Server::getInstance();
        $server->kick($who, $channel, $why);
    }

    private function join($channel) {
        $server = Server::getInstance();
        $server->join($channel);
        sleep(1);
    }

    /*
     * 
     * Do night, set channel to +m every one to -v
     */

    private function doNight() {
        $server = Server::getInstance();
        //1- Set channel mode to modorated
        $this->setMode(Config::$lobbyRoom, "+m");
        //Set mode for all alive player to +v
        $mode = " -";
        $ppl = '';
        $cnt = 0;
        foreach ($this->inGamePart as $nick => $part) {
            $mode .= "v";
            $ppl .= " $nick";
            $cnt++;
            if ($cnt >= 3) {
                $server->raw("MODE " . Config::$lobbyRoom . $mode . $ppl);
                $mode = " -";
                $ppl = '';
                $cnt = 0;
                sleep(1);
            }
        }

        //Send the command
        if ($cnt > 0)
            $server->raw("MODE " . Config::$lobbyRoom . $mode . $ppl);
    }

    /**
     * 
     * Go to day mode
     */
    private function doDay() {
        $server = Server::getInstance();
        //1- Set channel mode to modorated
        $this->setMode(Config::$lobbyRoom, "+m");
        //Set mode for all alive player to +v
        $mode = " +";
        $ppl = '';
        $cnt = 0;
        foreach ($this->inGamePart as $nick => $part) {
            if ($part['alive'] || self::$DEAD_IS_TALKING) {
                $mode .= "v";
                $ppl .= " $nick";
                $cnt++;
                if ($cnt >= 3) {
                    $server->raw("MODE " . Config::$lobbyRoom . $mode . $ppl);
                    $mode = " +";
                    $ppl = '';
                    $cnt = 0;
                    sleep(1);
                }
            }
        }

        //Send the command
        if ($cnt > 0)
            $server->raw("MODE " . Config::$lobbyRoom . $mode . $ppl);
    }

    /**
     * 
     * Ask for voice, and bot gave them if authorized
     * @param string $forWho
     */
    function askVoice($forWho) {
        if (!$this->isIn($forWho)) {
            $this->say($forWho, _("You are not in game, sorry"));
            return;
        }

        if ($this->state != DAY_TURN) {
            $this->say($forWho, _("Its not a good time to ask this."));
            return;
        }

        if (!$this->isAlive($forWho) && !self::$DEAD_IS_TALKING) {
            $this->say($forWho, _("You are dead! rest in peace :D (Or I'll kick you!)"));
            return;
        }

        $mode = ' +v ';
        $server = Server::getInstance();
        $server->raw("MODE " . Config::$lobbyRoom . $mode . $forWho);
    }

    /**
     * 
     * Random function
     * @param integer $min
     * @param integer $max
     * @return integer
     */
    static function rand($min, $max) {
        if (function_exists("mt_rand"))
            return mt_rand($min, $max);
        else {
            echo "mt_rand is not available!!";
            return rand($min, $max);
        }
    }

    /**
     * 
     * Its singlton, so its a private method
     */
    private function __construct() {
        //Get ownership of channels
        $this->setOp(Config::$lobbyRoom, Config::$nickname);
        $this->setOp(Config::$mafiaRoom, Config::$nickname);

        //Remove password if any, just in case
        $this->setMode(Config::$lobbyRoom, "-ki");
        $this->setMode(Config::$mafiaRoom, "-ki");
        //Set private and secret flags :)
        $this->setMode(Config::$mafiaRoom, "+ps");
        //Remove modorated flag
        $this->setMode(Config::$lobbyRoom, "-m");

        //Join to channel (in case of drop on flood :D )
        $this->join(Config::$lobbyRoom);
        $this->join(Config::$mafiaRoom);

        //Yet again, set the OP since some time when you join, you need to set it again :(
        $this->setOp(Config::$lobbyRoom, Config::$nickname);
        $this->setOp(Config::$mafiaRoom, Config::$nickname);

        //Change topics
        $this->topic(Config::$mafiaRoom, "Welcome to Persian Mafia Game! but leave this channel soon, its not place for you to stay!! - Channel is logged!");
        $this->topic(Config::$lobbyRoom, "Register for game to play! see " . Config::$manualLink);
    }

    /**
     * 
     * Format message and add color to it
     * @param integer $code IRC Color code, vary in different clients
     * @param string $message
     * @return string
     */
    public static function colorize($code, $message) {
        return chr(3) . $code . $message . chr(3) . "1";
    }

    /**
     * 
     * Set message to bold
     * @param string $message
     * @return string
     */
    public static function bold($message) {
        return chr(2) . $message . chr(2);
    }

    /**
     * 
     * Both bold and color
     * @param integer $code
     * @param string $message
     * @return string
     */
    public static function boco($code, $message) {
        return MafiaGame::bold(MafiaGame::colorize($code, $message));
    }

    /**
     * 
     * Get current game object
     * @param boolean $force force to recreate?
     * @return MafiaGame
     */
    public static function getInstance($force = false) {
        if (!self::$instanse) {
            self::$instanse = new MafiaGame();
            return self::$instanse;
        }

        if ($force) {
            if (count(self::$instanse->inGameNicks)) {
                $server = Server::getInstance();

                $mode = " -";
                $ppl = '';
                $cnt = 0;
                foreach ($game->inGamePart as $nick => $part) {
                    $mode .= "v";
                    $ppl .= " $nick";
                    $cnt++;
                    if ($cnt >= 3) {
                        $server->raw("MODE " . Config::$lobbyRoom . $mode . $ppl);
                        $mode = " -";
                        $ppl = '';
                        $cnt = 0;
                        sleep(1);
                    }
                }

                //Send the command
                if ($cnt > 0)
                    $server->raw("MODE " . Config::$lobbyRoom . $mode . $ppl);
            }
            self::$instanse = new MafiaGame();
        }
        return self::$instanse;
    }

    /**
     * 
     * Add nick to game
     * @param string $nick
     */
    public function addNick($nick) {
        if ($this->state) {
            $this->say($nick, MafiaGame::colorize(2, _("The game is already on, sorry!")));
            return;
        };

        if ($this->isIn($nick)) {
            $this->say($nick, MafiaGame::bold(MafiaGame::colorize(2, _("You are already in game, for exit use command !leave"))));
            return;
        }
        $this->inGameNicks[strtolower($nick)] = $nick;
        $this->inGamePart[strtolower($nick)] = array('mode' => NORMAL_PPL, 'alive' => true);

        $this->say($nick, sprintf(_("Welcome to game %s, wait for start :D you can read the manual at %s"), MafiaGame::colorize(3, $nick), Config::$manualLink));
        $this->act(Config::$lobbyRoom, sprintf(_("%s joined to the game :), total players: %d"), MafiaGame::colorize(3, $nick), $this->getCount()));
    }

    /**
     * 
     * User change nick handler
     * @param string $from old nick
     * @param string $to new nick
     */
    public function changeNick($from, $to) {
        //Fix a bug that case a change nick to current nick drop you with no trace at all :D
        if (strtolower($from) == strtolower($to))
            return;
        if ($this->isIn($from)) {
            $this->inGameNicks[strtolower($to)] = $this->inGameNicks[strtolower($from)];
            $this->inGamePart[strtolower($to)] = $this->inGamePart[strtolower($from)];

            unset($this->inGameNicks[strtolower($from)]);
            unset($this->inGamePart[strtolower($from)]);

            if ($this->state == MAFIA_TURN) {
                if (isset($this->killVotes[strtolower($from)])) {
                    $this->killVotes[strtolower($to)] = $this->killVotes[strtolower($from)];
                    unset($this->killVotes[strtolower($from)]);
                }

                foreach ($this->killVotes as &$votes) {
                    if (strtolower($votes) == strtolower($from)) {
                        $votes = $to;
                    }
                }

                if (strtolower($this->drVote) == strtolower($from))
                    $this->drVote = $to;
            }

            if ($this->state == DAY_TURN) {
                if (isset($this->punishVotes[strtolower($from)])) {
                    $this->punishVotes[strtolower($to)] = $this->punishVotes[strtolower($from)];
                    unset($this->punishVotes[strtolower($from)]);
                }

                foreach ($this->punishVotes as &$votes) {
                    if (strtolower($votes) == strtolower($from)) {
                        $votes = $to;
                    }
                }
            }

            $this->act(Config::$lobbyRoom, MafiaGame::boco(4, $from) . _(" Changed his/her nick to ") . MafiaGame::boco(2, $to));
        }
    }

    /**
     * 
     * Check if user is in game 
     * @param string $nick
     */
    public function isIn($nick) {
        return isset($this->inGameNicks[strtolower($nick)]);
    }

    /**
     * 
     * Remove nick from game
     * @param string $nick
     */
    public function removeNick($nick) {


        if (!$this->isIn($nick))
            return;
        if ($this->state) {
            if (!$this->inGamePart[strtolower($nick)]['alive']) {
                $this->say($nick, _("You are dead, so its ok to leave. just say goodbye to other people ;D"));
                return;
            }

            $this->inGamePart[strtolower($nick)]['alive'] = false;


            if ($this->state == MAFIA_TURN) {
                if (isset($this->killVotes[strtolower($nick)])) {
                    unset($this->killVotes[strtolower($nick)]);
                }

                foreach ($this->killVotes as &$votes) {
                    if (strtolower($votes) == strtolower($nick)) {
                        $votes = false;
                    }
                }

                if (strtolower($this->drVote) == strtolower($nick))
                    $this->drVote = false;
            }

            if ($this->state == DAY_TURN) {
                if (isset($this->punishVotes[strtolower($nick)])) {
                    unset($this->punishVotes[strtolower($nick)]);
                }

                foreach ($this->punishVotes as &$votes) {
                    if (strtolower($votes) == strtolower($nick)) {
                        $votes = false;
                    }
                }
            }

            $nick = MafiaGame::boco($nick);
            $this->say(Config::$lobbyRoom,
                sprintf(_("User %s is leaving! all votes to him/her are set to not vote!"), $nick));
            $this->say(Config::$mafiaRoom,
                sprintf(_("User %s is leaving! all votes to him/her are set to not vote!"), $nick));
        } else {
            unset($this->inGameNicks[strtolower($nick)]);
            unset($this->inGamePart[strtolower($nick)]);
            $this->say($nick, _("See you soon :)"));
            $this->say(Config::$lobbyRoom,
                sprintf(_("%s left the game :(, total players: %d"), $nick, $this->getCount()));
        }
    }

    /**
     * 
     * Get count of registered players
     * @return integer
     */
    public function getCount() {
        return count($this->inGameNicks);
    }

    /**
     * 
     * Chack if dr is dead?
     * @return boolean
     */
    private function isDrDead() {
        foreach ($this->inGamePart as $nick => $data) {
            if ($this->inGamePart[strtolower($nick)]['mode'] == DR_PPL)
                return!$this->inGamePart[strtolower($nick)]['alive'];
        }
        return true;
    }

    /**
     *
     * Chack if detective is dead?
     * @return boolean
     */
    private function isDetectiveDead() {
        foreach ($this->inGamePart as $nick => $data) {
            if ($this->inGamePart[strtolower($nick)]['mode'] == DETECTIVE_PPL)
                return!$this->inGamePart[strtolower($nick)]['alive'];
        }
        return true;
    }

    /**
     * 
     * Say each player what he/she is and ..
     */
    private function startInfo() {
        $this->topic(Config::$mafiaRoom, "Game in progress, and every thing is logged. Its mafia room!");
        $this->topic(Config::$lobbyRoom, "Game in progress, and every thing is logged. Its city room!");
        $mafia_pl = "";
        foreach ($this->inGamePart as $nick => $data) {
            if ($this->isMafia($data)) {
               $mafia_pl = $mafia_pl . " " . $nick;
            }
        }
        foreach ($this->inGamePart as $nick => $data) {
            if ($this->isMafia($data)) {
                if ($data['mode'] == GODFATHER_PPL)
                    $this->say($nick, MafiaGame::boco(9, _("You are the Godfather!!")));
                else
                    $this->say($nick, MafiaGame::boco(9, _("You are mafia!!")));
                $this->say($nick, MafiaGame::boco(9, _("Mafia crew: "). $mafia_pl));
                if (self::$VERBOSE) {
                    $this->say($nick, sprintf(_("You are mafia :D Please join %s and %s"),
                        Config::$mafiaRoom, Config::$lobbyRoom));
                    $this->say($nick, Config::$mafiaRoom . " Password : " . $this->mafiaPass . " and " . Config::$lobbyRoom); #. _(" Password : ") . $this->lobbyPass);
                }

                $this->invite($nick, Config::$mafiaRoom);
                $this->invite($nick, Config::$lobbyRoom);
                sleep(1);
                $this->say($nick, _("Use this command :") . " /join " . Config::$mafiaRoom . ' ' . $this->mafiaPass);
            } else {
                $this->say($nick, MafiaGame::boco(9, _("You are <NOT> mafia!")));
                if (self::$VERBOSE) {
                    $this->say($nick, sprintf(_("The game begin, go to sleep! (Join %s room please and stay away from %s its dangerous!)", Config::$lobbyRoom, Config::$mafiaRoom)));
                    //$this->say($nick, Config::$lobbyRoom); #. _(" Password : ") . $this->lobbyPass);
                }

                $this->invite($nick, Config::$lobbyRoom);
            }

            if ($data['mode'] == DR_PPL) {
                $this->say($nick, MafiaGame::bold(sprintf(
                    _("You are doctor!, use %s command in PRIVATE to heal one ppl in NIGHT!"),
                    MafiaGame::colorize(2, "!heal"))
                    )
                );
            }

            if ($data['mode'] == DETECTIVE_PPL) {
                $this->say($nick, MafiaGame::bold(sprintf(
                    _("You are detective!, use %s command in PRIVATE to identify one ppl each NIGHT!"),
                    MafiaGame::colorize(2, "!whois"))
                    )
                );
            }

            if ($data['mode'] == NOHARM_PPL) {
                $this->say($nick, MafiaGame::bold(_("You are Invulnerable! you die only with punish command!")));
            }
            sleep(2);
        }

        $this->say(Config::$lobbyRoom, MafiaGame::boco(2, _("The game is on! gg and have fun!")));
    }

    /**
     * 
     * Choose $count from $list
     * @param array $list
     * @param integer $count
     * @return array 
     */
    private function randSelectFrom(&$list, $count) {
        $result = array();
        while ($count) {
            $count--;
            sort($list); //To fix the broken keys
            $rand = self::rand(0, count($list) - 1);
            $result[] = $list[$rand];
            unset($list[$rand]);
        }

        return $result;
    }

    /**
     * 
     * Start the game!
     * @param integer $mafia mafia count
     * @param integer $dr has dr or not, 0 for not
     * @param integer $detective has detectiv or not, 0 for not
     * @param integer $noharm has noharm or not, 0 for not
     */
    public function start($mafia, $dr = 0, $detective = 0, $noharm = 0, $godfather = 0) {
        if (!self::$gameName) {
            $this->say(Config::$lobbyRoom, _("You need to set game name with !name command."));
            return;
        }
        if ($mafia == 0) {
            $this->say(Config::$lobbyRoom, _("At least one mafia must exist!"));
            return;
        }
        $normal = $this->getCount() - $mafia;
        if ($this->state <> 0)
            return;

        $cnt = $mafia;
        if ($dr)
            $cnt++;
        if ($detective)
            $cnt++;
        if ($noharm)
            $cnt++;
        if ($godfather)
            $cnt++;
        if ($cnt > $this->getCount()) {
            $this->say(Config::$lobbyRoom, sprintf(_("You need at least %d player but you are %d"), $cnt, $this->getCount()));
            return;
        }

        $haveDr = $dr ? _("One") : _("No");
        $haveDet = $detective ? _("One") : _("No");
        $haveNoHarm = $noharm ? _("One") : _("No");
        $haveGodfather = $godfather ? _("One") : _("No");
        $this->act(Config::$lobbyRoom, sprintf(self::bold(_("Starting game with %d Mafia(s), %s Dr, %s Detective, %s Invulnerable, and %s Godfather.")), $mafia, $haveDr, $haveDet, $haveNoHarm, $haveGodfather));

        $this->setOp(Config::$mafiaRoom, Config::$nickname);
        $this->setOp(Config::$lobbyRoom, Config::$nickname);

        $this->setMode(Config::$lobbyRoom, "-m");
        $this->setMode(Config::$mafiaRoom, "-m");

        //$this->lobbyPass = self::rand(1 , 1000000);
        $this->mafiaPass = self::rand(1, 1000000);

        //First kick all, from mafia channel
        foreach ($this->inGameNicks as $nick => $data) {
            $this->kick($nick, Config::$mafiaRoom, "The game is going to begin!");
        }


        $this->setMode(Config::$mafiaRoom, "+ik " . $this->mafiaPass);
        //$this->setMode(Config::$lobbyRoom , "+k " . $this->lobbyPass);

        $listOfUsers = array_keys($this->inGameNicks);

        if (!self::$VERBOSE)
            $this->say(Config::$lobbyRoom, _('Selecting roles...'));

        if ($dr) {
            $result = $this->randSelectFrom($listOfUsers, 1);
            foreach ($result as $who)
                $this->inGamePart[strtolower($who)] = array('mode' => DR_PPL, 'alive' => true);
            if (self::$VERBOSE)
                $this->say(Config::$lobbyRoom, sprintf(_('DEBUG : Choose %d dr from %d player'), count($result), count($listOfUsers) + 1));
        }


        if ($detective) {
            $result = $this->randSelectFrom($listOfUsers, 1);
            foreach ($result as $who)
                $this->inGamePart[strtolower($who)] = array('mode' => DETECTIVE_PPL, 'alive' => true);
            if (self::$VERBOSE)
                $this->say(Config::$lobbyRoom, sprintf(_('DEBUG : Choose %d detective from %d player'), count($result), count($listOfUsers) + 1));
        }

        if ($noharm) {
            $result = $this->randSelectFrom($listOfUsers, 1);
            foreach ($result as $who)
                $this->inGamePart[strtolower($who)] = array('mode' => NOHARM_PPL, 'alive' => true);
            if (self::$VERBOSE)
                $this->say(Config::$lobbyRoom, sprintf(_('DEBUG : Choose %d inv from %d player'), count($result), count($listOfUsers) + 1));
        }

        if ($godfather) {
            $result = $this->randSelectFrom($listOfUsers, 1);
            foreach ($result as $who)
                $this->inGamePart[strtolower($who)] = array('mode' => GODFATHER_PPL, 'alive' => true);
            if (self::$VERBOSE)
                $this->say(Config::$lobbyRoom, sprintf(_('DEBUG : Choose %d godfather from %d player'), count($result), count($listOfUsers) + 1));
        }

        $result = $this->randSelectFrom($listOfUsers, $mafia);
        foreach ($result as $who)
            $this->inGamePart[strtolower($who)] = array('mode' => MAFIA_PPL, 'alive' => true);
        if (self::$VERBOSE)
                $this->say(Config::$lobbyRoom, sprintf(_('DEBUG : Choose %d mafia from %d player'), count($result), count($listOfUsers) + $mafia));

        $this->state = DAY_TURN;

        $this->startInfo();
        $this->sayStatus(false);
        return true;
    }

    /**
     * 
     * Get people type
     * @param string $nick
     * @return integer
     */
    public function getTypeOf($nick) {
        if (!$this->isIn($nick))
            return false;
        return $this->inGamePart[strtolower($nick)]['mode'];
    }
    public function getGamePart($nick) {
        if (!$this->isIn($nick))
            return false;
        return $this->inGamePart[strtolower($nick)];
    }

    /**
     * 
     * @param string $nick
     * @return bool
     */
    public function isMafia($nick) {
        if (is_string($nick)) {
            if (!$this->isIn($nick))
                return false;
            $data = $this->getGamePart($nick);
            $part = $data['mode'];
        }
        else
            $part = $nick['mode'];
        return $part === MAFIA_PPL || $part === GODFATHER_PPL;
    }

    /**
     * 
     * @param string $nick
     * @return bool
     */
    public function isCitizen($nick) {
        return !$this->isMafia($nick);
    }

    /**
     * 
     * If people is alive or dead?
     * @param string $nick
     * @return boolean
     */
    public function isAlive($nick) {
        if (!$this->isIn($nick))
            return false;
        return $this->inGamePart[strtolower($nick)]['alive'];
    }

    /**
     * 
     * Get current state (Day or night?)
     * @return integer
     */
    public function getState() {
        return $this->state;
    }

    /**
     * 
     * Prepare vote system for kills
     */
    private function prepareKillVote() {
        $this->killVotes = array();
        foreach ($this->inGamePart as $nick => $data) {
            if ($this->isMafia($data) && $data['alive'])
                $this->killVotes[strtolower($nick)] = false;
        }
    }

    /**
     * 
     * Prepare vote system for punish
     */
    private function preparePunishVote() {
        $this->punishVotes = array();
        foreach ($this->inGamePart as $nick => $data) {
            if ($data['alive'])
                $this->punishVotes[strtolower($nick)] = false;
        }
    }

    /**
     * 
     * Show list of users, in game show their dead/alive status too.
     * @param string $user who requested?
     */
    public function listAllUsers($user) {
        $this->act($user, _("Get user list , player count : ") . count($this->inGameNicks));
        $count = 0;
        if ($this->state) {
            foreach ($this->inGamePart as $nick => $data) {
                if ($data['alive']) {
                    $code = 2;
                    $alive = _("ALIVE");
                } else {
                    $code = 3;
                    $alive = _("DEAD");
                }
                $this->say($user, sprintf(_("%s is %s!"), MafiaGame::boco($code, $nick), MafiaGame::bold($alive)));
                $count++;
                if ($count > 5) {
                    sleep(1);
                    $count = 0;
                }
            }
        } else {
            foreach ($this->inGameNicks as $nick => $data) {
                $this->say($user, MafiaGame::boco(2, $nick) . _(" is in the game."));
                $count++;
                if ($count > 5) {
                    sleep(1);
                    $count = 0;
                }
            }
        }
    }

    /**
     * 
     * Get alive count
     * @return integer
     */
    public function getAliveCount() {
        $result = 0;
        foreach ($this->inGamePart as $nick => $data) {
            if ($data['alive'])
                $result++;
        }

        return $result;
    }

    /**
     * 
     * get dead count
     * @return integer
     */
    public function getDeadCount() {
        $result = 0;
        foreach ($this->inGamePart as $nick => $data) {
            if (!$data['alive'])
                $result++;
        }

        return $result;
    }

    /**
     * 
     * Get mafia count
     * @return integer
     */
    public function getMafiaCount() {
        $result = 0;
        foreach ($this->inGamePart as $nick => $data) {
            if ($this->isMafia($data) && $data['alive'])
                $result++;
        }

        return $result;
    }

    /**
     * 
     * Get normal people count
     * @return integer
     */
    public function getPplCount() {
        $result = 0;
        foreach ($this->inGamePart as $nick => $data) {
            if ($this->isCitizen($data) && $data['alive'])
                $result++;
        }

        return $result;
    }

    /**
     * 
     * Report game status on finish, re-create the game
     */
    public static function report() {
        $game = self::getInstance();
        $count = 0;
        foreach ($game->inGamePart as $nick => $data) {
            $type = $game->isMafia($data) ? _('Mafia') : _('Citizen');
            $type .= $game->getTypeOf($nick) === DR_PPL ? _(' AND Doctor') : '';
            $type .= $game->getTypeOf($nick) === DETECTIVE_PPL ? _(' AND Detective') : '';
            $type .= $game->getTypeOf($nick) === NOHARM_PPL ? _(' AND Invulnerable') : '';
            $type .= $game->getTypeOf($nick) === GODFATHER_PPL ? _(' AND GodFather') : '';
            $type = MafiaGame::boco(6, $type);
            $aliveOrDead = $game->isAlive($nick) ? _('Alive') : _('Dead');
            $aliveOrDead = MafiaGame::boco(7, $aliveOrDead);
            $cNick = MafiaGame::boco(2, $nick);
            $game->say(Config::$lobbyRoom,
                sprintf(_("%s was %s and is %s"), $cNick, $type, $aliveOrDead));

            $count++;

            if ($count > 5) {
                sleep(1);
                $count = 0;
            }
        }
        $game->setMode(Config::$lobbyRoom, "-m");
        $game->say(Config::$lobbyRoom, sprintf(_("Please leave %s"), Config::$mafiaRoom));
        $game->say(Config::$mafiaRoom, sprintf(_("Please leave %s"), Config::$mafiaRoom));
        //last:D kick all, from mafia channel
        $game = self::getInstance();
        foreach ($game->inGameNicks as $nick => $data) {
            $game->kick($nick, Config::$mafiaRoom, "The game is done, you need to leave!");
        }

        $rand = self::rand(100000, 999999);
        $game->setMode(Config::$mafiaRoom, "+k " . $rand);

        $server = Server::getInstance();

        $mode = " -";
        $ppl = '';
        $cnt = 0;
        foreach ($game->inGamePart as $nick => $part) {
            $mode .= "v";
            $ppl .= " $nick";
            $cnt++;
            if ($cnt >= 3) {
                $server->raw("MODE " . Config::$lobbyRoom . $mode . $ppl);
                $mode = " -";
                $ppl = '';
                $cnt = 0;
                sleep(1);
            }
        }

        //Send the command
        if ($cnt > 0)
            $server->raw("MODE " . Config::$lobbyRoom . $mode . $ppl);

        self::getInstance(true);
    }

    /**
     * 
     * Say status in start of daay
     * @param boolean $killed is anyone killed 
     */
    public function sayStatus($killed = true) {
        if ($this->checkWinState())
            return;
        switch ($this->state) {
            case MAFIA_TURN :
                $this->prepareKillVote();
                //$this->setMode(Config::$lobbyRoom , "+m");
                $this->doNight();
                $this->drVote = $this->isDrDead();
                $this->detectiveVote = $this->isDetectiveDead();
                $this->say(Config::$mafiaRoom, sprintf(MafiaGame::bold(
                    _("Your turn to kill!! use %s command to vote")), MafiaGame::colorize(2, "!kill")));
                if (self::$VERBOSE) {
                    $this->say(Config::$mafiaRoom, MafiaGame::bold(_("!kill *  : for kill nobody")));
                    $this->say(Config::$mafiaRoom, MafiaGame::bold(_("!kill -  : for remove your vote")));
                    $this->say(Config::$mafiaRoom, MafiaGame::bold(_("!vote  : To see other mafias (and their votes)")));
                    $this->say(Config::$mafiaRoom, MafiaGame::bold(_("!list  : To see list of all players")));
                    $this->say(Config::$lobbyRoom, MafiaGame::bold(_("!timeout  : To end the night after ") . self::$NIGHT_TIMEOUT));
                }
                $this->act(Config::$lobbyRoom, _("Good night ppl ;)"));
                $this->nightTurnTime = time();
                break;
            case DAY_TURN :
                $this->preparePunishVote();
                //$this->setMode(Config::$lobbyRoom , "-m");
                $this->doDay();
                $this->act(Config::$mafiaRoom, _("Your turn to hide!!"));
                if ($killed) {
                    $this->say(Config::$lobbyRoom, MafiaGame::bold(sprintf(
                        _("Hi ppl, there is a dead! lets find the killer and punish him/her. use %s command"),
                        MafiaGame::colorize(2, "!punish"))));
                } else {
                    $this->say(Config::$lobbyRoom, MafiaGame::bold(sprintf(_("Hi ppl, No one dead. peeowh!! either its a doctor's job or mafia trick :D, but who care? use %s command"), MafiaGame::colorize(2, "!punish"))));
                }
                if (self::$VERBOSE) {
                    $this->say(Config::$lobbyRoom, MafiaGame::bold(_("!punish -  : for remove your vote")));
                    $this->say(Config::$lobbyRoom, MafiaGame::bold(_("!vote  : To see other people votes")));
                    $this->say(Config::$lobbyRoom, MafiaGame::bold(_("!list  : To see list of all players")));
                    $this->say(Config::$lobbyRoom, MafiaGame::bold(_("!voice : If you must have voice and you have no voice, (mostly reconnect problems)")));
                    $this->say(Config::$lobbyRoom, MafiaGame::bold(_("!timeout  : To end the day after ") . self::$DAY_TIMEOUT . _(" and 60% of player cast their vote.")));
                }
                $this->dayTurnTime = time();
                break;
        }

        MafiaGame::saveGame(true);
    }

    /**
     * 
     * Check if game meet the end or not :D
     * @return boolean
     */
    public function checkWinState() {
        if (self::$SHOW_MAFIA_COUNT)
            $this->act(Config::$lobbyRoom, MafiaGame::bold(sprintf(_("There is %d player, %d dead and %d mafia player"), $this->getCount(), $this->getDeadCount(), $this->getMafiaCount())));
        else
            $this->act(Config::$lobbyRoom, MafiaGame::bold(sprintf(_("There is %d player, %d dead"), $this->getCount(), $this->getDeadCount())));

        if (self::$WON_STATE_NORMAL) {
            if ($this->getPplCount() == $this->getMafiaCount()) {
                $this->act(Config::$lobbyRoom, MafiaGame::boco(3, _("Mafia won!")));
                $this->state = 0;
                self::report();
                return true;
            }
        } else {
            if ($this->getPplCount() == 0) {
                $this->act(Config::$lobbyRoom, MafiaGame::boco(3, _("Mafia won!")));
                $this->state = 0;
                self::report();
                return true;
            }
        }

        if ($this->getMafiaCount() == 0) {
            $this->act(Config::$lobbyRoom, MafiaGame::boco(4, _("Ppl won!")));
            $this->state = 0;
            self::report();
            return true;
        }

        return false;
    }

    /**
     * 
     * Mafia kill command
     * @param string $I
     * @param string $you
     */
    public function iSayKillYou($I, $you) {
        $I = strtolower($I);
        $you = strtolower($you);
        if ($this->state != MAFIA_TURN) {
            $this->say($I, _("Wow! you are mad! its day time!"));
            return;
        }

        if ((($this->isIn($you) && $this->isAlive($you)) || $you == "*")
                && $this->isAlive($I)
                && $this->isMafia($I)
                && isset($this->killVotes[$I])) {
            $this->killVotes[$I] = $you;
            $this->say(Config::$mafiaRoom, sprintf(_("%s casted his/her vote for killing %s"), $I, $you));
        } elseif ($you == '-') {
            $this->killVotes[$I] = false;
            $this->say(Config::$mafiaRoom, sprintf(_("%s removed his/her vote. be fast :)"), $I));
        } else {
            $this->say($I, _("Your vote not accepted!"));
        }

        return $this->nightTimeEnd();
    }

    /**
     * 
     * Check for night if ended
     * @return boolean
     */
    private function nightTimeEnd() {
        foreach ($this->killVotes as $vote)
            if ($vote === false) {
                $this->act(Config::$lobbyRoom, _("Waiting for dr/detective/mafias to vote !"));
                return false;
            }
        $result = array_count_values($this->killVotes);
        $max = -1;
        $who = '';
        $hasDuplicate = false;
        foreach ($result as $dead => $wanted) {
            if ($wanted == $max)
                $hasDuplicate = true;
            elseif ($wanted > $max) {
                $who = $dead;
                $max = $wanted;
                $hasDuplicate = false;
            }
        }

        if ($hasDuplicate) {
            $this->say(Config::$mafiaRoom, MafiaGame::colorize(4, _("There is a tie! please some one fix his/her vote!")));
            return false;
        }

        if (!$this->drVote || !$this->detectiveVote) {
            $this->act(Config::$lobbyRoom, _("Waiting for dr/detective/mafias to vote !"));
            return false;
        }

        if ($who != "*" &&
                strtolower($who) != strtolower($this->drVote) &&
                $this->inGamePart[strtolower]['mode'] != NOHARM_PPL) {
            $this->inGamePart[strtolower($who)]['alive'] = false;
            $this->state = DAY_TURN;
            $this->say(Config::$mafiaRoom, sprintf(_("You kill %s"), MafiaGame::boco(2, $who)));
            $this->say(Config::$lobbyRoom, sprintf(_("ALERT!!! They killed %s, lets find the killer!"), MafiaGame::boco(2, $who)));
            $this->say($who, MafiaGame::bold(_("You are dead! please respect others and be quiet. Thanks.")));
            $sayMe = $who;
        } else {
            $this->state = DAY_TURN;
            $this->say(Config::$mafiaRoom, _("Nobody killed :D"));
            $this->say(Config::$lobbyRoom, _("No body kiled last night! WOOOW! but lets hunt some of them!"));
            $sayMe = false;
        }
        if (self::$VERBOSE)
            $this->listAllUsers(Config::$lobbyRoom);
        $this->sayStatus($sayMe);
        return $who;
    }

    public function thisIsMyLastWish($I, $wish) {
        if (strtolower($I) != strtolower($this->lastDead)) {
            $this->say($I, _("You are not the last dead people!"));
            if ($this->isAlive($I))
                $this->say($I, _("You are not dead! you are a cheat!"));

            return;
        }

        if ($this->lastWish) {
            $this->say($I, _("You already said : ") . $this->lastWish);
            return;
        }

        $this->lastWish = $wish;

        $this->say(Config::$lobbyRoom, sprintf(_("This is %s's last wish: "), $I));
        $this->say(Config::$lobbyRoom, $wish);
    }

    /**
     * 
     * Dr vote
     * @param string $I
     * @param string $you
     */
    public function iSayHealYou($I, $you) {
        $I = strtolower($I);
        $you = strtolower($you);
        if ($this->state != MAFIA_TURN) {
            $this->say($I, _("You can not heal, just in night."));
            return;
        }

        if ((($this->isIn($you) && $this->isAlive($you)) || $you == "*")
                && $this->isAlive($I)
                && $this->getTypeOf($I) == DR_PPL) {
            $this->drVote = $you;
            $this->say($I, sprintf(_("You heal %s"), $you));

            if ($I == $you) {
                $this->act($I, _("Thinks you are selfish!"));
            }
        } elseif ($you == '-') {
            $this->drVote = false;
            $this->say($I, _("You heal no body! heal some one!"));
        } else {
            $this->say($I, sprintf(_("You can not heal %s!"), $you));
        }

        $this->nightTimeEnd();
    }

    /**
     * 
     * Vote for punish
     * @param string $I
     * @param string $you
     */
    public function iSayPunishYou($I, $you) {
        $I = strtolower($I);
        $you = strtolower($you);
        if ($this->state != DAY_TURN) {
            $this->say($I, _("Wow! you are mad! its night!"));
            return;
        }

        if ($this->isIn($you) && $this->isAlive($you) && $this->isAlive($I)
                && isset($this->punishVotes[$I])) {
            $this->punishVotes[$I] = $you;
            $this->say(Config::$lobbyRoom, sprintf(_("%s casted his/her vote for punishing %s"), MafiaGame::boco(2, $I), MafiaGame::boco(2, $you)));
        } elseif ($you == '-') {
            $this->punishVotes[$I] = false;
            $this->say(Config::$lobbyRoom, sprintf(_("%s removed his/her vote!"), MafiaGame::boco(2, $I)));
        } else {
            $this->say($I, _("Your vote is not accepted!"));
        }

        $nokill = false;
        foreach ($this->punishVotes as $vote)
            if ($vote === false) {
                $nokill = true;
                break;
            }

        //$this->nightTimeEnd();
        $result = array_count_values($this->punishVotes);
        $max = -1;
        $who = '';

        $hasDuplicate = false;
        foreach ($result as $dead => $wanted) {
            if ($dead === false) continue;
            if (!$nokill)
                $this->say(Config::$lobbyRoom,
                    sprintf(_("%s has %d vote(s)"), MafiaGame::boco(2, $dead), $wanted));
            if ($wanted == $max)
                $hasDuplicate = true;
            elseif ($wanted > $max) {
                $who = $dead;
                $max = $wanted;
                $hasDuplicate = false;
            }
        }
        if ($this->punishStartTime > 0 && ($this->punishWho != $who || $hasDuplicate))
        {
            $this->say(Config::$lobbyRoom, MafiaGame::bold(_("Punishing canceled!")));
            $this->punishStartTime = 0;
        }

        if ($nokill) {
            if ($this->punishStartTime > 0)
                $this->say(Config::$lobbyRoom, sprintf(_("You will punish %s in %d seconds if his/her votes don't decrease..."), MafiaGame::boco(2, $who), self::$PUNISH_TIMEOUT + $this->punishStartTime - time()));
            return;
        }

        if ($hasDuplicate) {
            $this->say(Config::$lobbyRoom, MafiaGame::bold(_("There is a tie! please some one fix his/her vote!")));
            return;
        }

        if ($this->punishStartTime == 0 || $this->punishWho != $who)
        {
            $this->punishStartTime = time();
            $this->punishWho = $who;
            $this->say(Config::$lobbyRoom, sprintf(_("You will punish %s in %d seconds if his/her votes don't decrease..."), MafiaGame::boco(2, $who), self::$PUNISH_TIMEOUT));
            return;
        }
        else if (time() - $this->punishStartTime < self::$PUNISH_TIMEOUT)
        {
            $this->say(Config::$lobbyRoom, sprintf(_("You will punish %s in %d seconds if his/her votes don't decrease..."), MafiaGame::boco(2, $who), self::$PUNISH_TIMEOUT + $this->punishStartTime - time()));
            return;
        }
        $this->inGamePart[strtolower($who)]['alive'] = false;
        $this->state = MAFIA_TURN;
        $this->act(Config::$mafiaRoom, _("Your turn to kill!"));
        $this->say(Config::$lobbyRoom, _("You punish ") . MafiaGame::boco(2, $who));
        $this->say($who, sprintf(MafiaGame::bold(
            _("You are dead! please respect others and be quiet. Thanks. You can use %s command ONLY ONCE to make your last wish.")),
            MafiaGame::colorize(3, '!wish')));
        $this->punishStartTime = 0;
        $this->lastDead = $who;
        $this->lastWish = false;

        $this->sayStatus();
        return $who;
    }

    /**
     * 
     * Whois command for detective
     * @param string $I
     * @param string $you
     */
    public function iSayWhoAreYou($I, $you) {
        $I = strtolower($I);
        $you = strtolower($you);

        if ($this->state != MAFIA_TURN) {
            $this->say($I, _("Stay hidden! they kill you if they find you!"));
            return;
        }

        if ($this->detectiveVote && $this->detectiveVote != '*') {
            $this->say($I, _("Do not over-do :) you already know too much!"));
            return;
        }

        if ((($this->isIn($you) || $you == "*")
                && $this->isAlive($I)
                && $this->getTypeOf($I) == DETECTIVE_PPL)) {
            $this->detectiveVote = $you;
            if ($you != '*') {
                $result = $this->getTypeOf($you) == MAFIA_PPL ? MafiaGame::boco(8, _("Mafia")) :
                        MafiaGame::boco(8, _("Citizen"));
                $this->say($I, sprintf(_("%s is %s"), $you, $result));
            }
        } else {
            $this->say($I, sprintf(_("You can not know %s!"), $you));
        }

        $this->nightTimeEnd();
    }

    /**
     * 
     * Say vote status to me
     * @param string $me
     */
    public function whosVote($me) {
        $count = 0;
        if (!$this->isIn($me)) {
            $this->act($me, _("You are not in game!"));
            return;
        }

        if ($this->state == DAY_TURN) {
            foreach ($this->punishVotes as $who => $vote) {
                $this->say($me, MafiaGame::boco(2, $who) . " => " . MafiaGame::boco(2, $vote));
                $count++;

                if ($count > 5) {
                    sleep(1);
                    $count = 0;
                }
            }
        } else {
            if ($this->isMafia($me)) {
                foreach ($this->killVotes as $who => $vote) {
                    $this->say($me, MafiaGame::boco(2, $who) . " => " . MafiaGame::boco(2, $vote));
                    $count++;

                    if ($count > 5) {
                        sleep(1);
                        $count = 0;
                    }
                }
            } else {
                $this->act($me, _("Kiding me, right? They kill me if I tell you!"));
            }
        }
    }

    /**
     * 
     * Check time out :D
     */
    public function checkNightTimeout() {
        if ($this->state == MAFIA_TURN) {
            $remain = time() - $this->nightTurnTime;
            if (time() - $this->nightTurnTime > self::$NIGHT_TIMEOUT) {
                $this->act(Config::$mafiaRoom, MafiaGame::bold(_("Sorry, time out :D")));
                $this->act(Config::$lobbyRoom, MafiaGame::bold(_("Day time!")));

                foreach ($this->killVotes as &$vote) {
                    if (!$vote)
                        $vote = '*';
                }

                if (!$this->drVote)
                    $this->drVote = '*';

                $this->detectiveVote = true;

                if (!$this->nightTimeEnd()) {
                    $result = array_count_values($this->killVotes);
                    $max = -1;
                    $who = '';
                    foreach ($result as $dead => $wanted) {
                        if ($wanted > $max) {
                            $who = $dead;
                            $max = $wanted;
                        }
                    }
                    $this->act(Config::$mafiaRoom, sprintf(_("Kill forced to %s"), $who));
                    if ($who != "*" && strtolower($who) != strtolower($this->drVote)) {
                        $this->inGamePart[strtolower($who)]['alive'] = false;
                        $this->state = DAY_TURN;
                        $this->say(Config::$mafiaRoom, sprintf(_("You kill %s"), MafiaGame::boco(2, $who)));
                        $this->say(Config::$lobbyRoom, sprintf(_("ALERT!!! They killed %s, lets find the killer!"), MafiaGame::boco(2, $who)));
                        $this->say($who, MafiaGame::bold(_("You are dead! please respect others and be quiet. Thanks.")));
                        $sayMe = true;
                    } else {
                        $this->state = DAY_TURN;
                        $this->say(Config::$mafiaRoom, _("Nobody killed :D"));
                        $this->say(Config::$lobbyRoom, _("No body kiled last night! WOOOW! but lets hunt some of them!"));
                        $sayMe = false;
                    }
                    if (self::$VERBOSE)
                        $this->listAllUsers(Config::$lobbyRoom);
                    $this->sayStatus($sayMe);
                }
            } else {
                $this->say(Config::$lobbyRoom, sprintf(_("%d secound remain from night!"), self::$NIGHT_TIMEOUT - $remain));
            }
        } elseif ($this->state == DAY_TURN) {
            $remain = time() - $this->dayTurnTime;
            $count = 0;
            foreach ($this->punishVotes as $vote) {
                if ($vote)
                    $count++;
            }
            $players = $this->getAliveCount();
            $percent = $count * 100 / $players;
            if ($percent > 60 && $remain > self::$DAY_TIMEOUT) {
                $this->say(Config::$lobbyRoom, _('More than 60% of players cast their votes and day time out is ended. '));
                $this->say(Config::$lobbyRoom, $this->boco(2, _('DOOM!')) . _(' has come to this world :D'));
                $this->say(Config::$lobbyRoom, _('If there is a tie, cast your vote in private.'));


                foreach ($this->punishVotes as $who => $vote) {
                    if (!$vote) {
                        $this->iSayPunishYou($who, $who);
                    }
                }
                //$this->doNight();
            } else {
                $this->say(Config::$lobbyRoom, sprintf(_("%d secound remain from day, %d player of %d cast their vote!"), self::$DAY_TIMEOUT - $remain, $count, $players));
            }
        }
    }

    /**
     * Say the role to players
     * @param $I string
     */
    public function whoAmI($I) {
        if ($this->state != MAFIA_TURN && $this->state != DAY_TURN) {
            return;
        }

        if (!$this->isIn($I)) {
            $this->say($I, _('You are nobody :D'));
            return;
        }

        $data = $this->inGamePart[strtolower($I)];

        if ($this->isMafia($data)) {
             $mafia_pl = "";
             foreach ($this->inGamePart as $nick => $tmpdata)
                 if ($this->isMafia($tmpdata))
                    $mafia_pl = $mafia_pl . " " . $nick;
            if ($data['mode'] == GODFATHER_PPL)
                $this->say($I, MafiaGame::boco(9, _("You are the Godfather!!")));
            else
                $this->say($I, MafiaGame::boco(9, _("You are mafia!!")));
            $this->say($I, MafiaGame::boco(9, _("Mafia crew: "). $mafia_pl));
            if (self::$VERBOSE) {
                $this->say($I, sprintf(_("You are mafia :D Please join %s and %s"), Config::$mafiaRoom, Config::$lobbyRoom));
                $this->say($I, Config::$mafiaRoom . " Password : " . $this->mafiaPass . " and " . Config::$lobbyRoom); #. _(" Password : ") . $this->lobbyPass);
            }
            $this->say($I, _("Use this command :") . " /join " . Config::$mafiaRoom . ' ' . $this->mafiaPass);
        } else {
            $this->say($I, MafiaGame::boco(9, _("You are <NOT> mafia!")));
            if (self::$VERBOSE) {
                $this->say($I, sprintf(_("The game begin, go to sleep! (Join %s room please and stay away from %s its dangerous!)"), Config::$lobbyRoom, Config::$mafiaRoom));
                //$this->say($I, Config::$lobbyRoom); #. _(" Password : ") . $this->lobbyPass);
            }
        }

        if ($data['mode'] == DR_PPL) {
            $this->say($I, MafiaGame::bold(sprintf(
                _("You are doctor!, use %s command in PRIVATE to heal one ppl in NIGHT!"),
                MafiaGame::colorize(2, "!heal"))));
        }

        if ($data['mode'] == DETECTIVE_PPL) {
            $this->say($I, MafiaGame::bold(sprintf(_("You are detective!, use %s command in PRIVATE to identify one ppl each NIGHT!"), MafiaGame::colorize(2, "!whois"))));
        }

        if ($data['mode'] == NOHARM_PPL) {
            $this->say($I, MafiaGame::bold(_("You are Invulnerable! you die only with punish command!")));
        }
    }

    public static function setGameName($name) {
        self::$gameName = $name;
    }

    public static function saveGame($auto = false) {
        try {
            mkdir(dirname(__FILE__) . '/saves');
            $savePath = dirname(__FILE__) . '/saves/' . md5(self::$gameName);
            $data = serialize(self::$instanse);
            file_put_contents($savePath, $data);
            if ($auto && self::$VERBOSE)
                Server::getInstance()->message(Config::$lobbyRoom, _("Save game : ") . self::$gameName);
        } catch (Exception $e) {
            Server::getInstance()->message(Config::$lobbyRoom, _("Save game failed ") . $e->getMessage());
        }
    }

    public static function loadGame() {
        try {
            $savePath = dirname(__FILE__) . '/saves/' . md5(self::$gameName);
            //$data = serialize(MafiaGame::getInstance());
            //file_put_contents($savePath, $data);		
            if (file_exists($savePath)) {
                $data = file_get_contents($savePath);
                self::$instanse = unserialize($data);
                Server::getInstance()->message(Config::$lobbyRoom, _("Load game : ") . self::$gameName);
            } else {
                Server::getInstance()->message(Config::$lobbyRoom, _("Load game : ") . self::$gameName);
            }
        } catch (Exception $e) {
            Server::getInstance()->message(Config::$lobbyRoom, _("Load game failed ") . $e->getMessage());
        }
    }

}
