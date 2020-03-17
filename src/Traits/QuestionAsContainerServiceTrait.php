<?php


namespace BotMan\BotMan\Traits;


use BotMan\BotMan\BotMan;
use BotMan\BotMan\Messages\Conversations\Conversation;

trait QuestionAsContainerServiceTrait
{
    /** @var Conversation */
    protected $conversation;

    /**
     * @var BotMan
     */
    private BotMan $bot;

    /**
     * @param Conversation $conversation
     */
    public function setConversation(Conversation $conversation): void {
        $this->conversation = $conversation;
    }

    /**
     * @param BotMan $botMan
     */
    public function setBot(BotMan $botMan)
    {
        $this->bot = $botMan;
    }
}
