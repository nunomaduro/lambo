<?php

namespace Tests\Feature;

use App\Actions\RunLaravelInstaller;
use App\Shell\Shell;
use Exception;
use Illuminate\Support\Facades\Config;
use Tests\Feature\Fakes\FakeProcess;
use Tests\TestCase;

class RunLaravelInstallerTest extends TestCase
{
    private $shell;

    public function setUp(): void
    {
        parent::setUp();
        $this->shell = $this->mock(Shell::class);
    }

    /** @test */
    function it_runs_the_laravel_installer()
    {
        collect([
            [
                'command' => 'laravel new my-project --quiet',
                'lambo.store.auth' => false,
                'lambo.store.dev' => false,
                'lambo.store.with_output' => false,
            ],
            [
                'command' => 'laravel new my-project',
                'lambo.store.auth' => false,
                'lambo.store.dev' => false,
                'lambo.store.with_output' => true,
            ],
            [
                'command' => 'laravel new my-project --dev --quiet',
                'lambo.store.auth' => false,
                'lambo.store.dev' => true,
                'lambo.store.with_output' => false,
            ],
            [
                'command' => 'laravel new my-project --dev',
                'lambo.store.auth' => false,
                'lambo.store.dev' => true,
                'lambo.store.with_output' => true,
            ],
            [
                'command' => 'laravel new my-project --auth --quiet',
                'lambo.store.auth' => true,
                'lambo.store.dev' => false,
                'lambo.store.with_output' => false,
            ],
            [
                'command' => 'laravel new my-project --auth',
                'lambo.store.auth' => true,
                'lambo.store.dev' => false,
                'lambo.store.with_output' => true,
            ],
            [
                'command' => 'laravel new my-project --auth --dev --quiet',
                'lambo.store.auth' => true,
                'lambo.store.dev' => true,
                'lambo.store.with_output' => false,
            ],

            [
                'command' => 'laravel new my-project --auth --dev',
                'lambo.store.auth' => true,
                'lambo.store.dev' => true,
                'lambo.store.with_output' => true,
            ],
        ])->each(function ($options) {
            Config::set('lambo.store.project_name', 'my-project');
            Config::set('lambo.store.auth', $options['lambo.store.auth']);
            Config::set('lambo.store.dev', $options['lambo.store.dev']);
            Config::set('lambo.store.with_output', $options['lambo.store.with_output']);

            $this->runLaravelInstaller($options['command']);
        });
    }

    /** @test */
    function it_throws_an_exception_if_the_laravel_installer_fails()
    {
        Config::set('lambo.store.project_name', 'my-project');
        Config::set('lambo.store.auth', false);
        Config::set('lambo.store.dev', false);
        Config::set('lambo.store.with_output', false);

        $this->shell->shouldReceive('execInRoot')
            ->andReturn(FakeProcess::fail('failed command'));

        $this->expectException(Exception::class);

        app(RunLaravelInstaller::class)();
    }

    function runLaravelInstaller(string $expectedCommand)
    {
        $this->shell->shouldReceive('execInRoot')
            ->with($expectedCommand)
            ->once()
            ->andReturn(FakeProcess::success());

        app(RunLaravelInstaller::class)();
    }
}
