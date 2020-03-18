<?php


namespace BotMan\BotMan\Tests;


use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Cache\ArrayCache;
use BotMan\BotMan\Drivers\Tests\FakeDriver;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use BotMan\BotMan\Tests\Fixtures\TestConversation;
use BotMan\BotMan\Tests\Fixtures\TestInvokableConversationCallback;
use Illuminate\Support\Collection;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class BotManConversationCallablesAsServicesTest extends TestCase
{

    /** @var ArrayCache */
    protected $cache;

    public function tearDown()
    {
        m::close();
    }

    public function setUp()
    {
        parent::setUp();
        $this->cache = new ArrayCache();
    }

    /**
     * @param $data
     * @return \BotMan\BotMan\BotMan
     */
    protected function getBot($data)
    {
        $botman = BotManFactory::create([], $this->cache);

        $data = Collection::make($data);
        /** @var FakeDriver $driver */
        $driver = m::mock(FakeDriver::class)->makePartial();

        $driver->isBot = $data->get('is_from_bot', false);
        $driver->messages = [new IncomingMessage($data->get('message'), $data->get('sender'), $data->get('recipient'))];

        $botman->setDriver($driver);

        return $botman;
    }
    /** @test */
    public function it_picks_up_conversations_and_runs_invokable_service_as_callable()
    {
        $GLOBALS['answer'] = '';
        $GLOBALS['called'] = false;
        $GLOBALS['conversation'] = null;
        $botman = $this->getBot([
            'sender' => 'UX12345',
            'recipient' => 'general',
            'message' => 'Hi Julia',
        ]);

        /** @var ContainerInterface|m\Mock $containerMock */
        $containerMock = m::mock(ContainerInterface::class);
        $containerMock->shouldReceive('get')
            ->with(TestInvokableConversationCallback::class)
            ->once()
            ->andReturn(new TestInvokableConversationCallback());

        $botman->setContainer($containerMock);

        $conversation = new TestConversation();

        $botman->hears('Hi Julia', function ($botman) use ($conversation) {
            $botman->storeConversation($conversation, TestInvokableConversationCallback::class);
        });
        $botman->listen();

        /*
         * Now that the first message is saved, fake a reply
         */
        $botman = $this->getBot([
            'sender' => 'UX12345',
            'recipient' => 'general',
            'message' => 'Hello again',
        ]);
        $botman->setContainer($containerMock);

        $botman->listen();

        $this->assertSame($conversation, $GLOBALS['conversation']);
        $this->assertInstanceOf(Answer::class, $GLOBALS['answer']);
        $this->assertFalse($GLOBALS['answer']->isInteractiveMessageReply());
        $this->assertSame('Hello again', $GLOBALS['answer']->getText());
        $this->assertTrue($GLOBALS['called']);
    }


    /** @test */
    public function it_picks_up_conversations_with_multiple_callbacks_and_runs_invokable_service_as_callable()
    {
        $GLOBALS['answer'] = '';
        $GLOBALS['called_foo'] = false;
        $GLOBALS['called_bar'] = false;

        $foo = new TestInvokableConversationCallback('called_foo');
        $bar = new TestInvokableConversationCallback('called_bar');


        $botman = $this->getBot([
            'sender' => 'UX12345',
            'recipient' => 'general',
            'message' => 'Hi Julia',
        ]);

        $botman->hears('Hi Julia', function ($botman) {
            $conversation = new TestConversation();

            $botman->storeConversation($conversation, [
                [
                    'pattern' => 'token_one',
                    'callback' => 'foo',
                ],
                [
                    'pattern' => 'token_two',
                    'callback' => 'bar',
                ],
            ]);
        });
        $botman->listen();

        /*
         * Now that the first message is saved, fake a reply
         */
        $botman = $this->getBot([
            'sender' => 'UX12345',
            'recipient' => 'general',
            'message' => 'token_two',
        ]);
        /** @var ContainerInterface|m\Mock $containerMock */
        $containerMock = m::mock(ContainerInterface::class);
        $containerMock->shouldReceive('get')
            ->once()
            ->with('bar')
            ->andReturn( $bar);

        $botman->setContainer($containerMock);
        $botman->listen();

        $this->assertInstanceOf(Answer::class, $GLOBALS['answer']);
        $this->assertFalse($GLOBALS['answer']->isInteractiveMessageReply());
        $this->assertSame('token_two', $GLOBALS['answer']->getText());
        $this->assertFalse($GLOBALS['called_foo']);
        $this->assertTrue($GLOBALS['called_bar']);

    }

}
