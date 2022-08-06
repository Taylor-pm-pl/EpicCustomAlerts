<?php

declare(strict_types=1);

namespace davidglitch04\EpicCustomAlerts;

use davidglitch04\EpicCustomAlerts\command\EpicCustomAlerts;
use davidglitch04\EpicCustomAlerts\listener\EventListener;
use davidglitch04\EpicCustomAlerts\updater\GetUpdateInfo;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByBlockEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class Loader extends PluginBase{

    private static $instance = null;

    public array $config;

    public function onLoad(): void
    {
        self::$instance = $this;
    }
    
    public function onEnable(): void
    {
        $this->saveDefaultConfig();
        $this->config = (array)$this->getConfig()->getAll();
        DefaultPermissions::registerPermission(new Permission("epiccustomalerts.command.allow", "Allow to use epiccustomalerts control"));
        $this->getServer()->getCommandMap()->register("epiccustomalerts", new EpicCustomAlerts($this));
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        if(VersionInfo::IS_DEVELOPMENT_BUILD){ /* @phpstan-ignore-line (If condition is always false.) */
			$this->getLogger()->warning("You are using the development builds. Development builds might have unexpected bugs, crash, break your plugins, corrupt all your data and more. Unless you're a developer and know what you're doing, please AVOID using development builds in production!");
		}
        $this->checkUpdater();
    }

    protected function checkUpdater() : void {
        $this->getServer()->getAsyncPool()->submitTask(new GetUpdateInfo($this, "https://raw.githubusercontent.com/David-pm-pl/EpicCustomAlerts/stable/poggit_news.json"));
    }

    public static function getInstance(): Loader{
        return self::$instance;
    }

    public function getFileHack(): string{
        return $this->getFile();
    }

    public function isCustom(string $type) : bool {
        return $this->config[$type]["custom"];
    }

    public function isHidden(string $type) : bool {
        return $this->config[$type]["hide"];
    }

    public function getMessage(
        string $type, 
        array $replaces
        ) : string 
        {
            $msg = $this->config[$type]["message"];
            foreach($replaces as $key => $value){
                $msg = str_replace("{" . $key . "}", (string)$value, $msg);
            }
            return TextFormat::colorize($msg);
    }

    public function isDeathHidden(EntityDamageEvent $cause = null){
        if(!$cause){
            return $this->config["Death"]["hide"];
        }
        switch($cause->getCause()){
            case EntityDamageEvent::CAUSE_CONTACT:
                return $this->config["Death"]["death-contact-message"]["hide"];
            case EntityDamageEvent::CAUSE_ENTITY_ATTACK:
                return $this->config["Death"]["kill-message"]["hide"];
            case EntityDamageEvent::CAUSE_PROJECTILE:
                return $this->config["Death"]["death-projectile-message"]["hide"];
            case EntityDamageEvent::CAUSE_SUFFOCATION:
                return $this->config["Death"]["death-suffocation-message"]["hide"];
            case EntityDamageEvent::CAUSE_FALL:
                return $this->config["Death"]["death-fall-message"]["hide"];
            case EntityDamageEvent::CAUSE_FIRE:
                return $this->config["Death"]["death-fire-message"]["hide"];
            case EntityDamageEvent::CAUSE_FIRE_TICK:
                return $this->config["Death"]["death-on-fire-message"]["hide"];
            case EntityDamageEvent::CAUSE_LAVA:
                return $this->config["Death"]["death-lava-message"]["hide"];
            case EntityDamageEvent::CAUSE_DROWNING:
                return $this->config["Death"]["death-drowning-message"]["hide"];
            case EntityDamageEvent::CAUSE_ENTITY_EXPLOSION:
            case EntityDamageEvent::CAUSE_BLOCK_EXPLOSION:
                return $this->config["Death"]["death-explosion-message"]["hide"];
            case EntityDamageEvent::CAUSE_VOID:
                return $this->config["Death"]["death-void-message"]["hide"];
            case EntityDamageEvent::CAUSE_SUICIDE:
                return $this->config["Death"]["death-suicide-message"]["hide"];
            case EntityDamageEvent::CAUSE_MAGIC:
                return $this->config["Death"]["death-magic-message"]["hide"];
            default:
                return $this->config["Death"]["hide"];
        }
    }

    public function isDeathCustom(EntityDamageEvent $cause = null){
        if(!$cause){
            return $this->config["Death"]["custom"];
        }
        switch($cause->getCause()){
            case EntityDamageEvent::CAUSE_CONTACT:
                return $this->config["Death"]["death-contact-message"]["custom"];
            case EntityDamageEvent::CAUSE_ENTITY_ATTACK:
                return $this->config["Death"]["kill-message"]["custom"];
            case EntityDamageEvent::CAUSE_PROJECTILE:
                return $this->config["Death"]["death-projectile-message"]["custom"];
            case EntityDamageEvent::CAUSE_SUFFOCATION:
                return $this->config["Death"]["death-suffocation-message"]["custom"];
            case EntityDamageEvent::CAUSE_FALL:
                return $this->config["Death"]["death-fall-message"]["custom"];
            case EntityDamageEvent::CAUSE_FIRE:
                return $this->config["Death"]["death-fire-message"]["custom"];
            case EntityDamageEvent::CAUSE_FIRE_TICK:
                return $this->config["Death"]["death-on-fire-message"]["custom"];
            case EntityDamageEvent::CAUSE_LAVA:
                return $this->config["Death"]["death-lava-message"]["custom"];
            case EntityDamageEvent::CAUSE_DROWNING:
                return $this->config["Death"]["death-drowning-message"]["custom"];
            case EntityDamageEvent::CAUSE_ENTITY_EXPLOSION:
            case EntityDamageEvent::CAUSE_BLOCK_EXPLOSION:
                return $this->config["Death"]["death-explosion-message"]["custom"];
            case EntityDamageEvent::CAUSE_VOID:
                return $this->config["Death"]["death-void-message"]["custom"];
            case EntityDamageEvent::CAUSE_SUICIDE:
                return $this->config["Death"]["death-suicide-message"]["custom"];
            case EntityDamageEvent::CAUSE_MAGIC:
                return $this->config["Death"]["death-magic-message"]["custom"];
            default:
                return $this->config["Death"]["custom"];
        }
    }

    public function getDeathMessage(Player $player, EntityDamageEvent $cause = null){
        $replaces = array(
            "PLAYER" => $player->getName(),
            "MAXPLAYERS" => $this->getServer()->getMaxPlayers(),
            "TOTALPLAYERS" => count($this->getServer()->getOnlinePlayers()),
            "TIME" => date($this->config["date-format"]));
        if(!$cause){
            $message = $this->config["Death"]["message"];
        }else{
            switch($cause->getCause()){
                case EntityDamageEvent::CAUSE_CONTACT:
                    $message = $this->config["Death"]["death-contact-message"]["message"];
                    if($cause instanceof EntityDamageByBlockEvent){
                        $replaces["BLOCK"] = $cause->getDamager()->getName();
                        break;
                    }
                    $replaces["BLOCK"] = "Unknown";
                    break;
                case EntityDamageEvent::CAUSE_ENTITY_ATTACK:
                    $message = $this->config["Death"]["kill-message"]["message"];
                    $killer = $cause->getDamager();
                    if($killer instanceof Player){
                        $replaces["KILLER"] = $killer->getName();
                        $itemhand = $killer->getInventory()->getItemInHand();
                        $replaces["ITEM"] = ($itemhand->getId() !== 0) ? $itemhand->getName() : "hand";
                        break;
                    }
                    if($killer instanceof Entity){
                        $replaces["KILLER"] = $killer->getNameTag();
                        $replaces["ITEM"] = "Unknown";
                        break;
                    }
                    $replaces["KILLER"] = "Unknown";
                    $replaces["ITEM"] = "Unknown";
                    break;
                case EntityDamageEvent::CAUSE_PROJECTILE:
                    $message = $this->config["Death"]["death-projectile-message"]["message"];
                    $killer = $cause->getDamager();
                    if($killer instanceof Player){
                        $replaces["KILLER"] = $killer->getName();
                        $replaces["BOW"] = $killer->getInventory()->getItemInHand()->getName();
                        break;
                    }
                    $replaces["KILLER"] = "Unknown";
                    $replaces["BOW"] = "Unknown";
                    break;
                case EntityDamageEvent::CAUSE_SUFFOCATION:
                    $message = $this->config["Death"]["death-suffocation-message"]["message"];
                    break;
                case EntityDamageEvent::CAUSE_FALL:
                    $message = $this->config["Death"]["death-fall-message"]["message"];
                    break;
                case EntityDamageEvent::CAUSE_FIRE:
                    $message = $this->config["Death"]["death-fire-message"]["message"];
                    break;
                case EntityDamageEvent::CAUSE_FIRE_TICK:
                    $message = $this->config["Death"]["death-on-fire-message"]["message"];
                    break;
                case EntityDamageEvent::CAUSE_LAVA:
                    $message = $this->config["Death"]["death-lava-message"]["message"];
                    break;
                case EntityDamageEvent::CAUSE_DROWNING:
                    $message = $this->config["Death"]["death-drowning-message"]["message"];
                    break;
                case EntityDamageEvent::CAUSE_ENTITY_EXPLOSION:
                case EntityDamageEvent::CAUSE_BLOCK_EXPLOSION:
                    $message = $this->config["Death"]["death-explosion-message"]["message"];
                    break;
                case EntityDamageEvent::CAUSE_VOID:
                    $message = $this->config["Death"]["death-void-message"]["message"];
                    break;
                case EntityDamageEvent::CAUSE_SUICIDE:
                    $message = $this->config["Death"]["death-suicide-message"]["message"];
                    break;
                case EntityDamageEvent::CAUSE_MAGIC:
                    $message = $this->config["Death"]["death-magic-message"]["message"];
                    break;
                default:
                    $message = $this->config["Death"]["message"];
                    break;
            }
        }
        foreach($replaces as $key => $value){
            $message = str_replace("{" . $key . "}", (string) $value, $message);
        }
        return TextFormat::colorize($message);
    }
}
