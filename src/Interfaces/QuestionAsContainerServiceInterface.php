<?php


namespace BotMan\BotMan\Interfaces;


use BotMan\BotMan\Messages\Conversations\Conversation;

interface QuestionAsContainerServiceInterface
{
    public function setConversation(Conversation $conversation): void;
    public function setBot(BotMan $botMan): void;
}
