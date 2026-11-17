<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * 30 §19.3 finding (e), client half: once the server answers a batch of
 * core/events.js with 401 (the login ended, e.g. after a withdrawal), the
 * page stops sending: no new events, no timer, no quiet retry, no beacon.
 * The server half is EndedSessionCsrfTest.
 *
 * @internal
 */
final class EventQueueEndedSessionTest extends CIUnitTestCase
{
    private function source(): string
    {
        return (string) file_get_contents(FCPATH . 'assets/js/core/events.js');
    }

    private function method(string $signature): string
    {
        $this->assertSame(1, preg_match('/\n  ' . preg_quote($signature, '/') . ' \{\n(.*?)\n  \}\n/s', $this->source(), $match), $signature);

        return $match[1];
    }

    public function testA401EndsTheTransportBeforeAnyRetry(): void
    {
        $body = $this->method('keepForLater(batch, error)');

        $this->assertMatchesRegularExpression('/if \(!recoverable\) \{.*?if \(error\.status === 401\) this\.end\(\);\s*return;\s*\}/s', $body);
        $this->assertLessThan(strpos($body, 'this.retryTimer = setTimeout'), strpos($body, 'this.end();'));
    }

    public function testAnEndedTransportSendsNothing(): void
    {
        $end = $this->method('end()');
        $this->assertStringContainsString('this.ended = true;', $end);
        $this->assertStringContainsString('clearTimeout(this.retryTimer);', $end);

        $this->assertStringStartsWith('    if (this.ended) return;', $this->method('schedule()'));
        $this->assertStringStartsWith('    if (this.ended) return;', $this->method('async resend()'));
        $this->assertMatchesRegularExpression('/this\.timer = null;\s*if \(this\.ended\) return;\s*if \(force\)/', $this->method('async flush(force = false)'));
        $this->assertStringContainsString('while (!this.ended && ', $this->method('async drain()'));
    }

    public function testNoNewEventIsQueuedAfterTheLoginEnded(): void
    {
        $this->assertSame(3, substr_count($this->source(), 'if (!CONFIG.sessionTag || transport.ended) return;'), 'emit, emitAudio, restoreQueued');
    }
}
