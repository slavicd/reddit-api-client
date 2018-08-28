<?php


use PHPUnit\Framework\TestCase;

use Entropi\RedditClient\Client;
use Dotenv\Dotenv;

class Test extends TestCase
{
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
    }

    public function testSubmitLink()
    {

    }
}
