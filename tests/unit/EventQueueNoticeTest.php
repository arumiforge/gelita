<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * The "connection lost" toast of the event queue (core/events.js).
 *
 * A batch cancelled because the student moved to the next screen fails like a
 * network error. The toast must wait until the failure holds (browser offline,
 * still rate-limited, or a quiet retry fails too), so an online student never
 * sees "Koneksi terputus" for a page change.
 *
 * @internal
 */
final class EventQueueNoticeTest extends CIUnitTestCase
{
    private function source(): string
    {
        return (string) file_get_contents(FCPATH . 'assets/js/core/events.js');
    }

    private function keepForLater(): string
    {
        $this->assertSame(1, preg_match('/\n  keepForLater\(batch, error\) \{\n(.*?)\n  \}\n/s', $this->source(), $match));

        return $match[1];
    }

    public function testFailedBatchIsStoredBeforeAnyNotice(): void
    {
        $body = $this->keepForLater();

        $this->assertLessThan(strpos($body, "toast(t('savedOffline')"), strpos($body, 'this.store(batch);'));
    }

    public function testNoticeWaitsForALastingFailure(): void
    {
        $body = $this->keepForLater();

        $this->assertStringContainsString("navigator.onLine === false || error?.code === 'RATE_LIMITED' || this.failures > 1", $body);
        $this->assertMatchesRegularExpression('/if \(!lasting\) \{.*?setTimeout\(\(\) => \{ this\.retryTimer = null; this\.resend\(\); \}, RETRY_MS\);\s*return;\s*\}/s', $body);
        $this->assertLessThan(strpos($body, "toast(t('savedOffline')"), strpos($body, 'if (!lasting)'));
    }

    public function testAcceptedBatchResetsTheFailureCount(): void
    {
        $this->assertMatchesRegularExpression(
            '/await apiRequest\(this\.path, \{ method: \'POST\', body \}\);\s*this\.failures = 0;/',
            $this->source(),
        );
        $this->assertMatchesRegularExpression('/const RETRY_MS = \d{4,5};/', $this->source());
    }
}
