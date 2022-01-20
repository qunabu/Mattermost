<?php

namespace EscolaLms\Mattermost\Tests;

use EscolaLms\Auth\EscolaLmsAuthServiceProvider;
use EscolaLms\Courses\EscolaLmsCourseServiceProvider;
use EscolaLms\Scorm\EscolaLmsScormServiceProvider;
use GuzzleHttp\Middleware;
use EscolaLms\Mattermost\EscolaLmsMattermostServiceProvider;
use EscolaLms\Settings\EscolaLmsSettingsServiceProvider;
use Gnello\Mattermost\Laravel\MattermostServiceProvider;
use Laravel\Passport\Passport;
use EscolaLms\Lrs\Tests\Models\Client;
use EscolaLms\Auth\Models\User;
use EscolaLms\Core\Tests\TestCase as CoreTestCase;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class TestCase extends CoreTestCase
{
    protected MockHandler $mock;
    protected $history;
    protected $container = [];

    protected function setUp(): void
    {
        parent::setUp();
        Passport::useClientModel(Client::class);
    }

    protected function getPackageProviders($app): array
    {
        return [
            ...parent::getPackageProviders($app),
            EscolaLmsMattermostServiceProvider::class,
            MattermostServiceProvider::class,
            EscolaLmsAuthServiceProvider::class,
            EscolaLmsCourseServiceProvider::class,
            EscolaLmsScormServiceProvider::class,
            EscolaLmsSettingsServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('passport.client_uuids', true);

        $this->mock = new MockHandler([new Response(200, ['Token' => 'Token'], 'Hello, World'),]);
        $this->history = Middleware::history($this->container);
        $handlerStack = HandlerStack::create($this->mock);
        $handlerStack->push($this->history);

        $app['config']->set('mattermost.servers.default.guzzle', ['handler' => $handlerStack]);
    }
}
