<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * The Claude Code hooks of .claude/settings.json (.claude/hooks/): what they
 * let through and what they stop. Exit code 2 is the one Claude Code reads as
 * "blocked" (PreToolUse) or "show this to Claude" (PostToolUse).
 *
 * The language-file branch of after-edit.sh starts PHPUnit itself and is not
 * run from here.
 *
 * @internal
 */
final class ClaudeHooksTest extends CIUnitTestCase
{
    private const FULL_RUN = '4242 php vendor/bin/phpunit --no-coverage';
    private const ONE_FILE = '4242 php vendor/bin/phpunit --no-coverage tests/unit/TryLimitTest.php';

    /** An empty value would not reach the script, which would then read the real process list. */
    private const NOTHING = '1 /sbin/init';

    protected function setUp(): void
    {
        parent::setUp();

        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('The hooks are bash scripts.');
        }
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('bashCommands')]
    public function testSecondFullRunIsRefused(string $command, string $running, int $expected): void
    {
        [$code, $stderr] = $this->runHook('before-bash.sh', ['tool_input' => ['command' => $command]], $running);

        $this->assertSame($expected, $code, $stderr);
    }

    /**
     * @return iterable<string, array{string, string, int}>
     */
    public static function bashCommands(): iterable
    {
        yield 'full run, nothing running' => ['vendor/bin/phpunit --no-coverage', self::NOTHING, 0];
        yield 'full run during a full run' => ['vendor/bin/phpunit --no-coverage', self::FULL_RUN, 2];
        yield 'full run in a pipeline during a full run' => ['vendor/bin/phpunit --no-coverage 2>&1 | tail -5', self::FULL_RUN, 2];
        yield 'composer test during a full run' => ['composer test', self::FULL_RUN, 2];
        yield 'filtered run during a full run' => ['vendor/bin/phpunit --no-coverage --filter TryLimitTest', self::FULL_RUN, 0];
        yield 'one file during a full run' => ['vendor/bin/phpunit --no-coverage tests/unit/TryLimitTest.php', self::FULL_RUN, 0];
        yield 'full run during a one-file run' => ['vendor/bin/phpunit --no-coverage', self::ONE_FILE, 0];
        yield 'other command during a full run' => ['git status', self::FULL_RUN, 0];
        yield 'log file named after phpunit' => ['vendor/bin/phpunit --no-coverage', '4242 tail -f phpunit.log', 0];
    }

    public function testEditedPhpFileIsSyntaxChecked(): void
    {
        $dir = sys_get_temp_dir() . '/gelita-hooks-' . bin2hex(random_bytes(4));
        mkdir($dir);
        file_put_contents($dir . '/Good.php', "<?php\n\nreturn 1;\n");
        file_put_contents($dir . '/Bad.php', "<?php\n\nreturn 1 +;\n");
        file_put_contents($dir . '/notes.md', "<?php return 1 +;\n");

        try {
            $this->assertSame(0, $this->runHook('after-edit.sh', ['tool_input' => ['file_path' => $dir . '/Good.php']])[0]);
            $this->assertSame(0, $this->runHook('after-edit.sh', ['tool_input' => ['file_path' => $dir . '/notes.md']])[0]);
            $this->assertSame(0, $this->runHook('after-edit.sh', ['tool_input' => ['file_path' => $dir . '/Missing.php']])[0]);
            $this->assertSame(0, $this->runHook('after-edit.sh', ['tool_input' => []])[0]);

            [$code, $stderr] = $this->runHook('after-edit.sh', ['tool_input' => ['file_path' => $dir . '/Bad.php']]);
            $this->assertSame(2, $code);
            $this->assertStringContainsString('Bad.php', $stderr);
        } finally {
            array_map('unlink', glob($dir . '/*') ?: []);
            rmdir($dir);
        }
    }

    /**
     * @param array<string, mixed> $input What Claude Code sends the hook on standard input.
     *
     * @return array{int, string} Exit code and stderr.
     */
    private function runHook(string $script, array $input, string $running = self::NOTHING): array
    {
        $process = proc_open(
            ['bash', ROOTPATH . '.claude/hooks/' . $script],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            ROOTPATH,
            ['GELITA_HOOK_PS' => $running] + getenv(),
        );
        $this->assertIsResource($process);

        fwrite($pipes[0], (string) json_encode($input));
        fclose($pipes[0]);
        stream_get_contents($pipes[1]);
        $stderr = (string) stream_get_contents($pipes[2]);

        return [proc_close($process), $stderr];
    }
}
