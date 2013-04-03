<?php

namespace ZendDbMigrations\Library;

/**
 * Класс для вывода информации от миграций
 */
class OutputWriter
{
    private $closure;

    public function __construct(\Closure $closure = null)
    {
        if ($closure === null) {
            $closure = function($message) {};
        }
        $this->closure = $closure;
    }

    /**
     * Запись вывода в Closure функцию
     *
     * @param string $message  The message to write.
     */
    public function write($message)
    {
        $closure = $this->closure;
        $closure($message);
    }
}