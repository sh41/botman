<?php

namespace BotMan\BotMan\Interfaces\Middleware;

use BotMan\BotMan\BotMan;

interface PreSending
{
    /**
     * Handle an outgoing message payload before/after it
     * hits the message service.
     *
     * @param mixed $payload
     * @param callable $next
     * @param BotMan $bot
     *
     * @return mixed
     */
    public function PreSending($payload, $next, BotMan $bot);
}
