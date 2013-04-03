<?php

namespace ZfSimpleMigrations\Library;

class OutputWriter
{
    private $closure;

    public function __construct(\Closure $closure = null)
    {
        if ($closure === null) {
            $closure = function ($message) {
            };
        }
        $this->closure = $closure;
    }

    /**
     * @param string $message message to write
     */
    public function write($message)
    {
        call_user_func($this->closure, $message);
    }

    /**
     * @param $line
     */
    public function writeLine($line)
    {
        $this->write($line . "\n");
    }
}