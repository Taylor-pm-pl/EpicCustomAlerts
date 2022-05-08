<?php

namespace davidglitch04\EpicCustomAlerts;

use davidglitch04\EpicCustomAlerts\command\EpicCustomAlerts;
use davidglitch04\EpicCustomAlerts\listener\EventListener;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class Loader extends PluginBase{

    private static $instance = null;

    public function onLoad(): void
    {
        self::$instance = $this;
    }
    
    public function onEnable(): void
    {
        $this->saveDefaultConfig();
        DefaultPermissions::registerPermission(new Permission("epiccustomalerts.command.allow", "Allow to use epiccustomalerts control"));
        $this->getServer()->getCommandMap()->register("epiccustomalerts", new EpicCustomAlerts($this));
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
    }

    public static function getInstance(): Loader{
        return self::$instance;
    }

    public function isCustom(string $type) : bool {
        return $this->getConfig()[$type]["enable"];
    }

    public function isHidden(string $type) : bool {
        return $this->getConfig()[$type]["hide"];
    }

    public function getMessage(
        string $type, 
        array $replaces
        ) : string 
        {
            $msg = $this->getConfig()[$type]["message"];
            foreach($replaces as $key => $value){
                $msg = str_replace("{" . $key . "}", $value, $msg);
            }
            return TextFormat::colorize($msg);
    }
}