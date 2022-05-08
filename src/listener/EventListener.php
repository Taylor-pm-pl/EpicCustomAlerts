<?php

namespace davidglitch04\EpicCustomAlerts\listener;

use davidglitch04\EpicCustomAlerts\Loader;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Server;

class EventListener implements Listener{

    protected Loader $eca;

    public function __construct(Loader $eca)
    {
        $this->eca = $eca;
    }

    public function onPlayerJoin(PlayerJoinEvent $event) : void {
        $player = $event->getPlayer();
        $replaces = [
            "PLAYER" => $player->getName(),
            "MAXPLAYERS" => Server::getInstance()->getMaxPlayers(),
            "TOTALPLAYERS" => count(Server::getInstance()->getOnlinePlayers()),
            "TIME" => date($this->cfg["date-format"])
        ];
        if ($player->hasPlayedBefore() && $this->eca->isCustom("FirstJoin")){
            $event->setJoinMessage($this->eca->getMessage("FirtsJoin", $replaces));
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
            "TIME" => date($this->cfg["date-format"])
        ];
        if ($this->eca->isHidden("Quit")){
            $event->setQuitMessage("");
        } elseif ($this->eca->isCustom("Quit")){
            $event->setQuitMessage($this->eca->getMessage("Quit", $replaces));
        } else{
            $event->setQuitMessage($event->getQuitMessage());
        }
    }
}