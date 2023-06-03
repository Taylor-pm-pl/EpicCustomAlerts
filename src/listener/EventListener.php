<?php

declare(strict_types=1);

namespace davidglitch04\EpicCustomAlerts\listener;

use davidglitch04\EpicCustomAlerts\Loader;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\player\Player;
use pocketmine\Server;

class EventListener implements Listener{

    protected Loader $eca;

    protected $config;

    public function __construct(Loader $eca)
    {
        $this->eca = $eca;
        $this->config = $this->eca->getConfig()->getAll();
    }

    public function onPlayerJoin(PlayerJoinEvent $event) : void {
        $player = $event->getPlayer();
        $replaces = [
            "PLAYER" => $player->getName(),
            "MAXPLAYERS" => Server::getInstance()->getMaxPlayers(),
            "TOTALPLAYERS" => count(Server::getInstance()->getOnlinePlayers()),
            "TIME" => date($this->config["date-format"])
        ];
        if (!$player->hasPlayedBefore() && $this->eca->isCustom("FirstJoin")){
            $event->setJoinMessage($this->eca->getMessage("FirstJoin", $replaces));
        } elseif ($this->eca->isHidden("Join")){
            $event->setJoinMessage("");
        } elseif ($this->eca->isCustom("Join")){
            $event->setJoinMessage($this->eca->getMessage("Join", $replaces));
        } else{
            $event->setJoinMessage($event->getJoinMessage());
        }
    }

    public function onPlayerQuit(PlayerQuitEvent $event) : void {
        $player = $event->getPlayer();
        $replaces = [
            "PLAYER" => $player->getName(),
            "MAXPLAYERS" => Server::getInstance()->getMaxPlayers(),
            "TOTALPLAYERS" => count(Server::getInstance()->getOnlinePlayers()),
            "TIME" => date($this->config["date-format"])
        ];
        if ($this->eca->isHidden("Quit")){
            $event->setQuitMessage("");
        } elseif ($this->eca->isCustom("Quit")){
            $event->setQuitMessage($this->eca->getMessage("Quit", $replaces));
        } else{
            $event->setQuitMessage($event->getQuitMessage());
        }
    }

    public function onPlayerPreLogin(PlayerPreLoginEvent $event) : void {
        $playerInfo = $event->getPlayerInfo();
        $kickFlags = $event->getKickFlags()[0] ?? null;
        $replaces = [
            "PLAYER" => $playerInfo->getUsername(),
            "MAXPLAYERS" => Server::getInstance()->getMaxPlayers(),
            "TOTALPLAYERS" => count(Server::getInstance()->getOnlinePlayers()),
            "TIME" => date($this->config["date-format"])
        ];
        if ($kickFlags === PlayerPreLoginEvent::KICK_FLAG_SERVER_WHITELISTED) {
            if ($this->eca->isCustom("WhitelistedServer")){
                $event->setKickFlag(
                    PlayerPreLoginEvent::KICK_FLAG_SERVER_WHITELISTED,
                    $this->eca->getMessage("WhitelistedServer", $replaces)
                );
            }
        }
        if ($kickFlags === PlayerPreLoginEvent::KICK_FLAG_SERVER_FULL) {
            if ($this->eca->isCustom("FullServer")){
                $event->setKickFlag(
                    PlayerPreLoginEvent::KICK_FLAG_SERVER_FULL,
                    $this->eca->getMessage("FullServer", $replaces)
                );
            }
        }
    }

    public function onChangeWorld(EntityTeleportEvent $event) : void {
        $player = $event->getEntity();
        if ($player instanceof Player){
            $from = $event->getFrom();
            $to = $event->getTo();
            $replaces = [
                "FROM" => $from->getWorld()->getDisplayName(),
    	        "TO" => $to->getWorld()->getDisplayName(),
    	        "PLAYER" => $player->getName(),
    	        "MAXPLAYERS" => Server::getInstance()->getMaxPlayers(),
    	        "TOTALPLAYERS" => count(Server::getInstance()->getOnlinePlayers()),
    	        "TIME" => date($this->config["date-format"])
            ];
            if ($this->eca->isCustom("WorldChange")){
                $msg = $this->eca->getMessage("WorldChange", $replaces);
                Server::getInstance()->broadcastMessage($msg);
            }
        }
    }

    public function onPlayerDeath(PlayerDeathEvent $event): void
    {
        $player = $event->getPlayer();
        $cause = $player->getLastDamageCause();
        if ($this->eca->isDeathHidden($cause)){
            $event->setDeathMessage("");
        } elseif ($this->eca->isDeathCustom($cause)){
            $event->setDeathMessage($this->eca->getDeathMessage($player, $cause));
        } else{
            $event->setDeathMessage($event->getDeathMessage());
        }
    }

    public function onReceivePacket(DataPacketReceiveEvent $event){
    	$origin = $event->getOrigin();
    	$packet = $event->getPacket();
        $replaces = [
            "MAXPLAYERS" => Server::getInstance()->getMaxPlayers(),
            "TOTALPLAYERS" => count(Server::getInstance()->getOnlinePlayers()),
            "TIME" => date($this->config["date-format"])
        ];
    	if($packet instanceof LoginPacket){
    	    if($packet->protocol < ProtocolInfo::CURRENT_PROTOCOL){
    	        if($this->eca->isCustom("OutdatedClient")){
                    $origin->disconnect($this->eca->getMessage("OutdatedClient", $replaces));
                    $event->cancel();
    	        }
    	    } elseif($packet->protocol > ProtocolInfo::CURRENT_PROTOCOL){
    	        if($this->eca->isCustom("OutdatedServer")){
                    $origin->disconnect($this->eca->getMessage("OutdatedServer", $replaces));
                    $event->cancel();
    	        }
    	    }
    	}
    }
}
