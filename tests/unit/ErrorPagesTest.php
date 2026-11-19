<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * The GELITA error card (owner, 19 November 2026): 404, 500, 400 and every other client
 * error share app/Views/errors/html/_gelita.php, in friendly Indonesian, with a picture slot
 * per kind and never CodeIgniter's English pages or an exception message.
 *
 * @internal
 */
final class ErrorPagesTest extends CIUnitTestCase
{
    private function render(string $view, array $vars = []): string
    {
        $file = APPPATH . 'Views/errors/html/' . $view;

        return (static function () use ($file, $vars): string {
            extract($vars, EXTR_SKIP);
            ob_start();
            include $file;

            return (string) ob_get_clean();
        })();
    }

    public function testNotFoundIsTheGelitaCard(): void
    {
        $page = $this->render('error_404.php');

        $this->assertStringContainsString('<title>404 · GELITA</title>', $page);
        $this->assertStringContainsString(esc(lang('Game.notFoundTitle')), $page);
        $this->assertStringContainsString('href="' . esc(base_url()) . '"', $page);
        // The picture shows only once ui/error-404.webp is installed (PL-10)
        $this->assertSame(is_file(FCPATH . 'assets/ui/error-404.webp'), str_contains($page, '<body class="has-art"'));
        // Owner, 19 November 2026: the text stands on the picture, no bordered box
        $this->assertStringNotContainsString('class="card', $page);
    }

    public function testOtherClientErrorsAreFriendlyIndonesian(): void
    {
        foreach (['error_400.php' => 400, 'production.php' => 403] as $view => $status) {
            $page = $this->render($view, ['statusCode' => $status]);

            $this->assertStringContainsString('<title>' . $status . ' · GELITA</title>', $page, $view);
            $this->assertStringContainsString(esc(lang('Game.requestErrorTitle')), $page, $view);
            $this->assertStringNotContainsString('Whoops', $page, $view);
            $this->assertStringNotContainsString('Bad Request', $page, $view);
        }
    }

    public function testServerErrorShowsOnlyTheRequestId(): void
    {
        $page = $this->render('error_500.php');

        $this->assertStringContainsString(esc(lang('Game.serverErrorTitle')), $page);
        $this->assertStringContainsString('request_id: ', $page);
    }
}
