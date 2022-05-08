<?php

declare(strict_types=1);

namespace davidglitch04\EpicCustomAlerts\listener;

use davidglitch04\EpicCustomAlerts\Loader;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;
use pocketmine\Server;

class EventListener implements Listener{

    protected Loader $eca;

    protected $config;

    public function __construct(Loader $eca)
    {
        $this->eca = $eca;
        $this->config = (array)$this->eca->getConfig()->getAll();
    }

    public function onPlayerJoin(PlayerJoinEvent $event) : void {
        $player = $event->getPlayer();
        $replaces = [
            "PLAYER" => $player->getName(),
            "MAXPLAYERS" => Server::getInstance()->getMaxPlayers(),
            "TOTALPLAYERS" => count(Server::getInstance()->getOnlinePlayers()),
            "TIME" => date($this->config["date-format"])
        ];
        if ($player->hasPlayedBefore() && $this->eca->isCustom("FirstJoin")){
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

    public function onPlayerLogin(PlayerLoginEvent $event) : void {
        $player = $event->getPlayer();
        $replaces = [
            "PLAYER" => $player->getName(),
            "MAXPLAYERS" => Server::getInstance()->getMaxPlayers(),
            "TOTALPLAYERS" => count(Server::getInstance()->getOnlinePlayers()),
            "TIME" => date($this->config["date-format"])
        ];
        if (count(Server::getInstance()->getOnlinePlayers()) - 1 < Server::getInstance()->getMaxPlayers()){
            if (!Server::getInstance()->isWhitelisted($player->getName())){
                if ($this->eca->isCustom("WhitelistedServer")){
                    $player->kick($this->eca->getMessage("WhitelistedServer", $replaces), $this->eca->getMessage("WhitelistedServer", $replaces));
                    $event->cancel();
                    return;
                }
            }
        } else{
            if ($this->eca->isCustom("FullServer")){
                $player->kick($this->eca->getMessage("FullServer", $replaces), $this->eca->getMessage("FullServer", $replaces));
                $event->cancel();
                return;
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

    public function onPlayerDeath(PlayerDeathEvent $event){
        $player = $event->getPlayer();
        if ($player instanceof Player){
            $cause = $player->getLastDamageCause();
            if ($this->eca->isDeathHidden($cause)){
                $event->setDeathMessage("");
            } elseif ($this->eca->isDeathCustom($cause)){
                $event->setDeathMessage($this->eca->getDeathMessage($player, $cause));
            } else{
                $event->setDeathMessage($event->getDeathMessage());
            }
        }
    }
}