<?php


use PHPUnit\Framework\TestCase;

use Entropi\RedditClient\Client;
use Dotenv\Dotenv;

class Test extends TestCase
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @expectedException ArgumentCountError
     */
    public function testInstantiationFail1()
    {
        new Client();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInstantiationFail2()
    {
        new Client([]);
    }

    public function testInstantiationSuccess()
    {
        $dotenv = new Dotenv(__DIR__ . '/..');
        $dotenv->load();

        $client = new Client([
            'username'  => getenv('APP_USERNAME'),
            'password'  => getenv('APP_PASSWORD'),
            'app_id'    => getenv('APP_ID'),
            'app_secret'=> getenv('APP_SECRET'),
        ]);

        $this->assertInstanceOf(Client::class, $client);

        $this->assertInstanceOf(stdClass::class, $client->obtainAccessToken());

        return $client;
    }

    /**
     * @depends testInstantiationSuccess
     */
    public function testTextSubmit(Client $client)
    {
        $res = $client->submit(
            'u_' . getenv('APP_USERNAME'),
            'Hello there, this is a test text post!',
            'self',
            null,
            'Hello world'
        );

        $this->assertTrue(
            is_string($res)
            && substr($res, 0, 4) === 'http'
        );
    }

    /**
     * @depends testInstantiationSuccess
     */
    public function testLinkSubmit(Client $client)
    {
        $res = $client->submit(
            'u_' . getenv('APP_USERNAME'),
            'Hello there, this is a test link post!',
            'link',
            'https://www.reddit.com/dev/api/'
        );

        $this->assertTrue(
            is_string($res)
            && substr($res, 0, 4) === 'http'
        );
    }
}
