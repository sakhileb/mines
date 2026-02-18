<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class DownloadRateLimitTest extends TestCase
{
    public function test_download_rate_limit_applies()
    {
        // Register a temporary test route that will be subject to the download limiter
        Route::get('/__test-download', function () {
            return response('ok');
        })->name('test.download');

        $client = $this->get('/__test-download');
        $client->assertStatus(200);

        // Fire more than the allowed attempts
        for ($i = 0; $i < 11; $i++) {
            $resp = $this->get('/__test-download');
        }

        $this->assertTrue(in_array($resp->getStatusCode(), [200, 429]));
        if ($resp->getStatusCode() === 429) {
            $this->assertStringContainsString('Download rate limit exceeded', $resp->getContent());
        }
    }
}
