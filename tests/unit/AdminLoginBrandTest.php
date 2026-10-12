<?php

use App\Controllers\Admin\AuthController;
use App\Services\ContentRepository;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * The staff login card opens with the centred GELITA logo (owner, 12 October
 * 2026): the large logo, else the emblem with the word GELITA, else the "G" mark.
 *
 * @internal
 */
final class AdminLoginBrandTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper(['url', 'gelita', 'content', 'ui']);
        session()->remove('staff_id');
    }

    protected function tearDown(): void
    {
        Services::resetSingle('contentRepository');
        Services::resetSingle('renderer');
        parent::tearDown();
    }

    public function testLargeLogoWhenUploaded(): void
    {
        $page = $this->render(['ui.logo-hero' => 'assets/ui/logo-hero.webp', 'ui.logo' => 'assets/ui/logo-gelita.webp']);

        $this->assertMatchesRegularExpression('/<div class="auth-brand">\s*<img class="auth-logo" src="[^"]*logo-hero\.webp" alt="GELITA"/', $page);
        $this->assertStringNotContainsString('auth-emblem', $page);
        $this->assertStringNotContainsString('sidebar-mark', $page);
    }

    public function testEmblemWhenOnlyTheEmblemIsUploaded(): void
    {
        $page = $this->render(['ui.logo' => 'assets/ui/logo-gelita.webp']);

        $this->assertMatchesRegularExpression('/<img class="auth-emblem" src="[^"]*logo-gelita\.webp" alt=""/', $page);
        $this->assertStringContainsString('<span class="auth-wordmark">GELITA</span>', $page);
    }

    public function testLetterMarkWithoutLogos(): void
    {
        $page = $this->render([]);

        $this->assertStringContainsString('<span class="sidebar-mark" aria-hidden="true">G</span>', $page);
        $this->assertStringContainsString('<span class="auth-wordmark">GELITA</span>', $page);
        $this->assertStringNotContainsString('<img', $page);
    }

    /** @param array<string, string> $media asset_key => storage path */
    private function render(array $media): string
    {
        Services::injectMock('contentRepository', new class ($media) extends ContentRepository {
            public function __construct(private array $media)
            {
            }

            public function mediaKeyMap(): array
            {
                return $this->media;
            }
        });
        Services::resetSingle('renderer');

        $controller = new AuthController();
        $controller->initController(service('request'), service('response'), service('logger'));

        return (string) $controller->loginForm();
    }
}
