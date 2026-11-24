<?php

namespace Tests\Feature\Security;

use App\Http\Middleware\ThrottleScannerGuessing;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class ScannerThrottleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('scanner-miss|127.0.0.1');
    }

    public function test_guessing_codes_is_cut_off_after_too_many_misses(): void
    {
        for ($i = 0; $i < ThrottleScannerGuessing::MAX_MISSES; $i++) {
            $this->postJson('/api/scanner/connect', ['session_code' => 'ZZZZZ' . ($i % 10)])->assertStatus(404);
        }

        $this->postJson('/api/scanner/connect', ['session_code' => 'ZZZZZ9'])->assertStatus(429);
        $this->postJson('/api/scanner/scan', ['session_code' => 'ZZZZZ9', 'barcode' => '1'])->assertStatus(429);

        RateLimiter::clear('scanner-miss|127.0.0.1');
    }

    public function test_badly_formed_requests_do_not_count_as_guesses(): void
    {
        for ($i = 0; $i < ThrottleScannerGuessing::MAX_MISSES + 3; $i++) {
            $this->postJson('/api/scanner/connect', ['session_code' => 'x'])->assertStatus(400);
        }

        $this->postJson('/api/scanner/connect', ['session_code' => 'ZZZZZ1'])->assertStatus(404);
    }
}
