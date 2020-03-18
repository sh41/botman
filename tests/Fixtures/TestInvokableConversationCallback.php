<?php


namespace BotMan\BotMan\Tests\Fixtures;

class TestInvokableConversationCallback
{
    private $foo;

    /**
     * TestInvokableConversationCallback constructor.
     */
    public function __construct($foo = null)
    {
        $this->foo = $foo;
    }

    public function __invoke($answer, $conversation)
    {
        $GLOBALS['answer'] = $answer;
        $GLOBALS['called'] = true;
        $GLOBALS['conversation'] = $conversation;
        if ($this->foo) {
            $GLOBALS[$this->foo] = true;
        }
    }

}
