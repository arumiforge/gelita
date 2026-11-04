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
    // kelas 5 (Dok 1): − ResearchPhase, ParticipantConsent, ScoringProfile, ChallengeOption; + ItemTry, DeviceLog
    // + AssetPackage (asset package Stage 1)
    // + ParticipantConsentModel (research revision 10, part 4: 30 §15, coded consent per student; not the old guardian consent)
    private const MODEL_COUNT = 38;

    private const ENTITY_COUNT = 11;  // − ChallengeOption

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
        'assetPackages'        => App\Services\AssetPackageService::class,
        // Part 4 (30 §15)
        'consentService'       => App\Services\ConsentService::class,
        'dataRequests'         => App\Services\DataRequestService::class,
        'riskFollowUp'         => App\Services\RiskFollowUpService::class,
        'participantCopy'      => App\Services\ParticipantCopyService::class,
        'studyClosure'         => App\Services\StudyClosureService::class,
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

    public function testGradeFiveModelsReplaceThePhaseConsentAndScoreModels(): void
    {
        foreach (['ItemTryModel' => 'item_tries', 'DeviceLogModel' => 'device_logs'] as $name => $table) {
            $class = 'App\\Models\\' . $name;

            $this->assertSame($table, $this->propertyOf(new $class(), 'table'), $class);
        }

        foreach (['ResearchPhaseModel', 'ScoringProfileModel', 'ChallengeOptionModel'] as $gone) {
            $this->assertFileDoesNotExist(APPPATH . "Models/{$gone}.php");
        }

        $this->assertFileDoesNotExist(APPPATH . 'Entities/ChallengeOption.php');
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
