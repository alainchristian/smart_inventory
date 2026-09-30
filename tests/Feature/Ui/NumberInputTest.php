<?php

namespace Tests\Feature\Ui;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Blade;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

/**
 * Numeric fields use <x-number-input> / <x-money-input>: filled from the
 * right, "12,500" while typing; the bound property still receives "12500".
 */
class NumberInputTest extends TestCase
{
    use DatabaseTransactions;

    /** Pages no route reaches — left untouched by convention. */
    private const ORPHANED = [
        'sales/point-of-sale.blade.php',
        'shop/sales/point-of-sale.blade.php',
        'shop/day-close/open-session.blade.php',
        'shop/day-close/session-manager.blade.php',
        'shop/returns/process-return.backup.blade.php',
    ];

    private function inputOptions(string $html): array
    {
        $this->assertMatchesRegularExpression("/numberInput\\(JSON\\.parse\\('(.*?)'\\)\\)/", $html);
        preg_match("/numberInput\\(JSON\\.parse\\('(.*?)'\\)\\)/", $html, $m);

        return json_decode(json_decode('"' . $m[1] . '"'), true);
    }

    public function test_money_input_renders_a_numeric_text_input(): void
    {
        $html = Blade::render(
            '<x-money-input wire:model="amount" type="number" min="1" step="100" class="xx-input" placeholder="0" :disabled="false" />'
        );

        $this->assertStringContainsString('type="text"', $html);
        $this->assertStringContainsString('inputmode="numeric"', $html);
        $this->assertStringContainsString('x-modelable="numValue"', $html);
        $this->assertStringContainsString('class="num-input money-input xx-input"', $html);
        $this->assertStringContainsString('wire:model="amount"', $html);
        $this->assertStringNotContainsString('type="number"', $html);
        $this->assertStringNotContainsString('min=', $html);
        $this->assertStringNotContainsString('step=', $html);
        $this->assertStringNotContainsString('disabled', $html);
        $this->assertSame(['decimals' => 0, 'signed' => false], $this->inputOptions($html));
    }

    public function test_debounce_is_handled_by_the_input_itself(): void
    {
        // x-modelable bypasses x-model's debounce, so the model is bound
        // without .live/.debounce and the input commits once typing pauses.
        $html = Blade::render('<x-number-input wire:model.live.debounce.400ms="cashAmount" />');
        $this->assertStringContainsString('wire:model="cashAmount"', $html);
        $this->assertSame(400, $this->inputOptions($html)['live']);

        $html = Blade::render('<x-number-input wire:model.live.debounce="qty" />');
        $this->assertSame(150, $this->inputOptions($html)['live']);

        // Plain .live keeps Livewire's own handling.
        $html = Blade::render('<x-number-input wire:model.live="qty" />');
        $this->assertStringContainsString('wire:model.live="qty"', $html);
        $this->assertArrayNotHasKey('live', $this->inputOptions($html));
    }

    public function test_max_decimals_signed_and_align(): void
    {
        $html = Blade::render(
            '<x-number-input wire:model="x" max="{{ $max }}" decimals="2" signed align="center" />',
            ['max' => 12]
        );

        $this->assertSame(['decimals' => 2, 'signed' => true, 'max' => 12], $this->inputOptions($html));
        $this->assertStringContainsString('inputmode="decimal"', $html);
        $this->assertStringContainsString('class="num-input num-center"', $html);
        $this->assertStringNotContainsString('max=', $html);
    }

    public function test_alpine_bindings_and_disabled_pass_through(): void
    {
        $html = Blade::render(
            '<x-money-input x-model="credit" x-on:blur="save()" x-bind:disabled="{{ $locked ? \'true\' : \'false\' }} && !ready" :disabled="true" />',
            ['locked' => true]
        );

        $this->assertStringContainsString('x-model="credit"', $html);
        $this->assertStringContainsString('x-on:blur="save()"', $html);
        $this->assertStringContainsString('x-bind:disabled="true && !ready"', $html);
        $this->assertStringContainsString('disabled="disabled"', $html);
    }

    public function test_no_live_view_uses_a_plain_number_input(): void
    {
        $offenders = [];

        foreach ((new Finder)->files()->in(resource_path('views'))->name('*.blade.php') as $file) {
            $relative = str_replace('\\', '/', $file->getRelativePathname());
            if (str_starts_with($relative, 'components/number-input')) {
                continue;
            }
            foreach (self::ORPHANED as $orphan) {
                if ($relative === 'livewire/' . $orphan) {
                    continue 2;
                }
            }

            // Quoted attribute values may contain ">" (arrow functions), so skip over them.
            preg_match_all('/<input(?=[\s>\/])(?:[^>"]|"[^"]*")*>/s', $file->getContents(), $tags);
            foreach ($tags[0] as $tag) {
                if (str_contains($tag, 'type="number"')) {
                    $offenders[] = $relative . ': ' . preg_replace('/\s+/', ' ', substr($tag, 0, 100));
                }
            }
        }

        $this->assertSame([], $offenders, "Use <x-number-input> / <x-money-input>:\n" . implode("\n", $offenders));
    }

    public function test_owner_settings_page_renders_the_inputs(): void
    {
        $owner = User::forceCreate([
            'name' => 'Owner', 'email' => 'o' . uniqid() . '@example.test', 'password' => 'x',
            'role' => 'owner', 'is_active' => true, 'must_change_password' => false,
        ]);

        $html = $this->actingAs($owner)->get(route('owner.settings'))->assertOk()->getContent();

        foreach (['maxCreditPerCustomer', 'returnApprovalThreshold', 'overdueCreditDays', 'priceOverrideThreshold', 'maxReturnDays'] as $field) {
            $this->assertMatchesRegularExpression('/<input[^>]*x-data="numberInput[^>]*wire:model="' . $field . '"/', $html, $field);
        }
    }
}
