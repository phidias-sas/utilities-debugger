<?php 
namespace Phidias\Utilities;

use Phidias\Utilities\Debugger\Message;

class Debugger
{
    private static $enabled             = false;
    private static $initialTimestamp    = false;

    private static $messages            = array();
    private static $blockStack          = array();
    private static $stackDepth          = 0;

    public static function enable()
    {
        self::$initialTimestamp = microtime(true);
        self::$enabled          = true;

        self::add('debugger enabled');
    }

    public static function disable()
    {
        self::$enabled = false;
    }

    public static function isEnabled()
    {
        return self::$enabled;
    }

    public static function getInitialTimestamp()
    {
        return self::$initialTimestamp;
    }

    public static function startBlock($text, $type = null, $callbacks = null)
    {
        if (!self::$enabled) {
            return;
        }

        $message = new Message($text, $type, $callbacks);
        self::$blockStack[self::$stackDepth] = $message;

        if (self::$stackDepth) {
            self::$blockStack[self::$stackDepth-1]->messages[] = $message;
        } else {
            self::$messages[] = $message;
        }

        self::$stackDepth++;
    }

    public static function endBlock()
    {
        if (self::$stackDepth == 0) {
            return;
        }

        self::$blockStack[self::$stackDepth-1]->duration = microtime(true)-self::$blockStack[self::$stackDepth-1]->timestamp;
        self::$stackDepth--;
    }

    public static function collapseAll()
    {
        while (self::$stackDepth > 0) {
            self::endBlock();
        }
    }

    public static function add($text, $type = null, $callbacks = null)
    {
        if (!self::$enabled) {
            return;
        }

        $message = new Message($text, $type, $callbacks);

        if (self::$stackDepth) {
            self::$blockStack[self::$stackDepth-1]->messages[] = $message;
        } else {
            self::$messages[] = $message;
        }
    }

    public static function getMessages()
    {
        return self::$messages;
    }

    public static function getPeakMemory()
    {
        return memory_get_peak_usage();
    }

    public static function getTotalTime()
    {
        return microtime(true) - self::$initialTimestamp;
    }


    public static function flush()
    {
        if (!self::$enabled) {
            return;
        }

        if (! $template = realpath(__DIR__."/../template.php")) {
            return;
        }

        self::collapseAll();
        include $template;
    }

}
