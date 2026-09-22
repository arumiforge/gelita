<?php

use CodeIgniter\Model;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Seluruh komponen tahap 3 harus dapat di-autoload dan dibuat instansinya.
 * Menangkap salah ketik nama kelas, tabrakan properti trait, dan service
 * yang lupa didaftarkan di Config\Services.
 *
 * @internal
 */
final class DataLayerWiringTest extends CIUnitTestCase
{
    private const MODEL_COUNT = 29;

    private const ENTITY_COUNT = 11;

    /** Nama service → kelas yang diharapkan */
    private const SERVICES = [
        'contentRepository'    => App\Services\ContentRepository::class,
        'gameContext'          => App\Services\GameContext::class,
        'sessionService'       => App\Services\SessionService::class,
        'challengeService'     => App\Services\ChallengeService::class,
        'scoringService'       => App\Services\ScoringService::class,
        'eventService'         => App\Services\EventService::class,
        'analyticsService'     => App\Services\AnalyticsService::class,
        'contentImportService' => App\Services\ContentImportService::class,
    ];

    public function testEveryModelLoadsAndDeclaresItsTable(): void
    {
        $files = glob(APPPATH . 'Models/*.php');

        $this->assertCount(self::MODEL_COUNT, $files);

        foreach ($files as $file) {
            $class = 'App\\Models\\' . basename($file, '.php');
            $model = new $class();

            $this->assertInstanceOf(Model::class, $model, $class);
            $this->assertNotSame('', $this->propertyOf($model, 'table'), "{$class} tanpa tabel");
        }
    }

    public function testEveryEntityLoads(): void
    {
        $files = glob(APPPATH . 'Entities/*.php');

        $this->assertCount(self::ENTITY_COUNT, $files);

        foreach ($files as $file) {
            $class = 'App\\Entities\\' . basename($file, '.php');

            $this->assertInstanceOf(CodeIgniter\Entity\Entity::class, new $class(), $class);
        }
    }

    public function testEveryServiceIsRegistered(): void
    {
        foreach (self::SERVICES as $name => $class) {
            $this->assertInstanceOf($class, service($name), "service('{$name}')");
        }
    }

    public function testGelitaValidationRulesetIsActive(): void
    {
        $rules = config('Validation')->ruleSets;

        $this->assertContains(App\Validation\GelitaRules::class, $rules);
    }

    private function propertyOf(object $object, string $name): string
    {
        $property = (new ReflectionClass($object))->getProperty($name);

        return (string) $property->getValue($object);
    }
}
