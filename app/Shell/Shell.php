<?php

namespace App\Shell;

use Illuminate\Config\Repository;
use Symfony\Component\Process\Process;

class Shell
{
    protected $rootPath;
    protected $projectPath;

    public function __construct(Repository $config)
    {
        $this->rootPath = $config->get('lambo.store.root_path');
        $this->projectPath = $config->get('lambo.store.project_path');
    }

    public function execInRoot($command)
    {
        return $this->exec("cd {$this->rootPath} && $command", $command);
    }

    public function execInProject($command)
    {
        return $this->exec("cd {$this->projectPath} && $command", $command);
    }

    public function getOutputFormatter()
    {
        return app('console')->option('no-ansi')
            ? new PlainOutputFormatter
            : new ColorOutputFormatter;
    }

    public function buildProcess($command): Process
    {
        $process = app()->make(Process::class, [
            'command' => $command,
        ]);
        $process->setTimeout(null);
        return $process;
    }

    protected function exec($command, $description)
    {
        $showConsoleOutput = config('lambo.store.with_output');
        $out = app(\Symfony\Component\Console\Output\ConsoleOutput::class);

        $outputFormatter = $this->getOutputFormatter();
        $out->writeln($outputFormatter->start($description));

        $process = $this->buildProcess($command);
        $process->run(function ($type, $buffer) use ($out, $outputFormatter, $showConsoleOutput) {
            if (empty($buffer) || $buffer === PHP_EOL) {
                return;
            }

            if (Process::ERR === $type || $showConsoleOutput) {
                $out->writeln(
                    $outputFormatter->progress(
                        $buffer,
                        Process::ERR === $type
                    )
                );
            }
        });

        return $process;
    }
}
