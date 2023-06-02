<?php

declare(strict_types=1);

namespace davidglitch04\EpicCustomAlerts;

use davidglitch04\EpicCustomAlerts\command\EpicCustomAlerts;
use davidglitch04\EpicCustomAlerts\listener\EventListener;
use davidglitch04\EpicCustomAlerts\updater\CheckUpdateTask;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByBlockEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\VanillaItems;
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
        $this->config = $this->getConfig()->getAll();
        DefaultPermissions::registerPermission(new Permission("epiccustomalerts.command.allow", "Allow to use epiccustomalerts control"));
        $this->getServer()->getCommandMap()->register("epiccustomalerts", new EpicCustomAlerts($this));
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        if(VersionInfo::IS_DEVELOPMENT_BUILD){
			$this->getLogger()->warning("You are using the development builds. Development builds might have unexpected bugs, crash, break your plugins, corrupt all your data and more. Unless you're a developer and know what you're doing, please AVOID using development builds in production!");
		}
        $this->checkUpdater();
    }

    protected function checkUpdater() : void {
        $this->getServer()->getAsyncPool()->submitTask(new CheckUpdateTask($this->getDescription()->getName(), $this->getDescription()->getVersion()));
    }

    public static function getInstance(): Loader{
        return self::$instance;
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
        return match ($cause->getCause()) {
            EntityDamageEvent::CAUSE_CONTACT => $this->config["Death"]["death-contact-message"]["hide"],
            EntityDamageEvent::CAUSE_ENTITY_ATTACK => $this->config["Death"]["kill-message"]["hide"],
            EntityDamageEvent::CAUSE_PROJECTILE => $this->config["Death"]["death-projectile-message"]["hide"],
            EntityDamageEvent::CAUSE_SUFFOCATION => $this->config["Death"]["death-suffocation-message"]["hide"],
            EntityDamageEvent::CAUSE_FALL => $this->config["Death"]["death-fall-message"]["hide"],
            EntityDamageEvent::CAUSE_FIRE => $this->config["Death"]["death-fire-message"]["hide"],
            EntityDamageEvent::CAUSE_FIRE_TICK => $this->config["Death"]["death-on-fire-message"]["hide"],
            EntityDamageEvent::CAUSE_LAVA => $this->config["Death"]["death-lava-message"]["hide"],
            EntityDamageEvent::CAUSE_DROWNING => $this->config["Death"]["death-drowning-message"]["hide"],
            EntityDamageEvent::CAUSE_ENTITY_EXPLOSION, EntityDamageEvent::CAUSE_BLOCK_EXPLOSION => $this->config["Death"]["death-explosion-message"]["hide"],
            EntityDamageEvent::CAUSE_VOID => $this->config["Death"]["death-void-message"]["hide"],
            EntityDamageEvent::CAUSE_SUICIDE => $this->config["Death"]["death-suicide-message"]["hide"],
            EntityDamageEvent::CAUSE_MAGIC => $this->config["Death"]["death-magic-message"]["hide"],
            default => $this->config["Death"]["hide"],
        };
    }

    public function isDeathCustom(EntityDamageEvent $cause = null){
        if(!$cause){
            return $this->config["Death"]["custom"];
        }
        return match ($cause->getCause()) {
            EntityDamageEvent::CAUSE_CONTACT => $this->config["Death"]["death-contact-message"]["custom"],
            EntityDamageEvent::CAUSE_ENTITY_ATTACK => $this->config["Death"]["kill-message"]["custom"],
            EntityDamageEvent::CAUSE_PROJECTILE => $this->config["Death"]["death-projectile-message"]["custom"],
            EntityDamageEvent::CAUSE_SUFFOCATION => $this->config["Death"]["death-suffocation-message"]["custom"],
            EntityDamageEvent::CAUSE_FALL => $this->config["Death"]["death-fall-message"]["custom"],
            EntityDamageEvent::CAUSE_FIRE => $this->config["Death"]["death-fire-message"]["custom"],
            EntityDamageEvent::CAUSE_FIRE_TICK => $this->config["Death"]["death-on-fire-message"]["custom"],
            EntityDamageEvent::CAUSE_LAVA => $this->config["Death"]["death-lava-message"]["custom"],
            EntityDamageEvent::CAUSE_DROWNING => $this->config["Death"]["death-drowning-message"]["custom"],
            EntityDamageEvent::CAUSE_ENTITY_EXPLOSION, EntityDamageEvent::CAUSE_BLOCK_EXPLOSION => $this->config["Death"]["death-explosion-message"]["custom"],
            EntityDamageEvent::CAUSE_VOID => $this->config["Death"]["death-void-message"]["custom"],
            EntityDamageEvent::CAUSE_SUICIDE => $this->config["Death"]["death-suicide-message"]["custom"],
            EntityDamageEvent::CAUSE_MAGIC => $this->config["Death"]["death-magic-message"]["custom"],
            default => $this->config["Death"]["custom"],
        };
    }

    public function getDeathMessage(Player $player, EntityDamageEvent $cause = null): string
    {
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
                        $itemInHand = $killer->getInventory()->getItemInHand();
                        $replaces["ITEM"] = (!$itemInHand->equals(VanillaItems::AIR())) ? $itemInHand->getName() : "hand";
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
