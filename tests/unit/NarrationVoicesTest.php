<?php

use App\Libraries\SyntheticNarrationInstaller;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * The narration's ElevenLabs voices are kept in the repository so the work
 * can move to another ElevenLabs account (owner, 15 November 2026):
 * ai-studio/elevenlabs-voice/voices/{speaker}/voice.json and preview.mp3.
 * The voice table of voices.md must name, for every speaker and language,
 * the voice ID of the account in use (the last row of `accounts`), and the
 * voice_profile built from it must fit its 200 characters.
 *
 * @internal
 */
final class NarrationVoicesTest extends CIUnitTestCase
{
    private const DIR = ROOTPATH . 'ai-studio/elevenlabs-voice/';

    /** @return array<string, array<string, mixed>> folder → voice.json */
    private static function voiceFiles(): array
    {
        $files = [];

        foreach (glob(self::DIR . 'voices/*/voice.json') ?: [] as $path) {
            $files[basename(dirname($path))] = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        }

        ksort($files);

        return $files;
    }

    public function testEverySpeakerHasAVoiceInBothLanguagesAndItsProfileFits(): void
    {
        $sheet = SyntheticNarrationInstaller::parseVoices((string) file_get_contents(self::DIR . 'voices.md'));

        $this->assertSame('v4 (eleven_v4)', $sheet['model']);
        $this->assertSame(['narator', 'jaka', 'mbah kedu'], array_keys($sheet['voices']));

        foreach ($sheet['voices'] as $speaker => $voice) {
            foreach (['id', 'en'] as $locale) {
                $this->assertMatchesRegularExpression('/\([A-Za-z0-9]{20}\)/', $voice[$locale], $speaker . ' ' . $locale . ' names a voice ID');

                $parts = ['ElevenLabs ' . $sheet['model'], $voice[$locale], $voice['settings']];
                $this->assertLessThanOrEqual(200, mb_strlen(implode(' · ', $parts)), $speaker . ' ' . $locale . ': voice_profile would be cut off');
            }
        }
    }

    public function testTheSavedVoicesMatchTheSheet(): void
    {
        $files = self::voiceFiles();
        $this->assertSame(['jaka-en', 'jaka-id', 'mbah-kedu-en', 'mbah-kedu-id', 'narator'], array_keys($files));

        $sheet = SyntheticNarrationInstaller::parseVoices((string) file_get_contents(self::DIR . 'voices.md'))['voices'];
        $named = [];

        foreach ($files as $folder => $voice) {
            $this->assertNotSame('', $voice['description'], $folder);
            $this->assertFileExists(self::DIR . 'voices/' . $folder . '/' . $voice['reference_audio']);
            $this->assertNotSame([], $voice['accounts'], $folder);

            foreach ($voice['accounts'] as $account) {
                $this->assertMatchesRegularExpression('/^akun-\d+$/', $account['account'], $folder . ': accounts are named akun-N, never by e-mail');
            }

            foreach ($voice['clone_samples'] as $sample) {
                $this->assertFileExists(self::DIR . $sample, $folder);
            }

            $current = end($voice['accounts'])['voice_id'];

            foreach ($voice['languages'] as $locale) {
                $cell = $sheet[mb_strtolower($voice['speaker'])][$locale] ?? '';
                $this->assertStringContainsString('(' . $current . ')', $cell, $folder . ' ' . $locale . ': voices.md names the voice of the account in use');
                $named[mb_strtolower($voice['speaker']) . '/' . $locale] = true;
            }
        }

        $this->assertCount(6, $named, 'three speakers × two languages');
    }
}
