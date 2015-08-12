<?php
namespace Phidias\Utilities\Debugger;

class Message
{
    public $timestamp;
    public $text;
    public $type;
    public $duration;
    public $memory;
    public $file;
    public $line;

    public $messages;   //sub-messages

    public function __construct($text, $type = null, $callbacks = null)
    {
        $this->timestamp    = microtime(true);
        $this->text         = $text;
        $this->type         = $type;
        $this->duration     = null;
        $this->memory       = memory_get_usage();

        $this->messages = array();

        $trace = debug_backtrace();

        if ($callbacks === null) {
            if (isset($trace[1]) && ($trace[1]['function'] == 'add' || $trace[1]['function'] == 'startBlock') ) {
                $this->file = $trace[1]['file'];
                $this->line = $trace[1]['line'];
            }
        } else {
            foreach ($callbacks as $callback) {
                $targetClass    = $callback[0];
                $targetMethod   = $callback[1];

                foreach ($trace as $invocation) {
                    if (isset($invocation['class']) && $invocation['class'] == $targetClass && $invocation['function'] == $targetMethod) {
                        $this->file = $invocation['file'];
                        $this->line = $invocation['line'];
                    }
                }
            }
        }

    }

}
