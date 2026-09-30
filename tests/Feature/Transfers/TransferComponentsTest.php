<?php

namespace Tests\Feature\Transfers;

use App\Enums\TransferStatus;
use App\Models\Transfer;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

/** Shared transfer UI parts: timeline states, badge, route, header. */
class TransferComponentsTest extends TestCase
{
    private function transfer(TransferStatus $status, array $at = []): Transfer
    {
        $t = new Transfer(['status' => $status]);
        $t->requested_at = now()->subDays(3);
        foreach ($at as $column) {
            $t->{$column} = now()->subDay();
        }
        $t->updated_at = now();
        // No relations loaded: "who" resolves to null without queries.
        foreach (['requestedBy', 'reviewedBy', 'packedBy', 'receivedBy', 'transporter'] as $rel) {
            $t->setRelation($rel, null);
        }

        return $t;
    }

    private function states(Transfer $t): array
    {
        return array_column($t->timeline(), 'state', 'key');
    }

    public function test_timeline_states_follow_the_transfer(): void
    {
        $this->assertSame(
            ['requested' => 'done', 'approved' => 'current', 'packed' => 'todo', 'shipped' => 'todo', 'delivered' => 'todo', 'received' => 'todo'],
            $this->states($this->transfer(TransferStatus::PENDING))
        );

        // Approved and packing started: packing is the current step, not done.
        $this->assertSame(
            ['requested' => 'done', 'approved' => 'done', 'packed' => 'current', 'shipped' => 'todo', 'delivered' => 'todo', 'received' => 'todo'],
            $this->states($this->transfer(TransferStatus::APPROVED, ['reviewed_at', 'packed_at']))
        );

        $this->assertSame(
            ['requested' => 'done', 'approved' => 'done', 'packed' => 'done', 'shipped' => 'done', 'delivered' => 'current', 'received' => 'todo'],
            $this->states($this->transfer(TransferStatus::IN_TRANSIT, ['reviewed_at', 'packed_at', 'shipped_at']))
        );

        $received = $this->states($this->transfer(TransferStatus::RECEIVED, ['reviewed_at', 'packed_at', 'shipped_at', 'delivered_at', 'received_at']));
        $this->assertSame(['done'], array_values(array_unique($received)));
    }

    public function test_rejected_and_cancelled_end_in_a_stopped_step(): void
    {
        $this->assertSame(
            ['requested' => 'done', 'rejected' => 'stopped'],
            $this->states($this->transfer(TransferStatus::REJECTED, ['reviewed_at']))
        );

        $this->assertSame(
            ['requested' => 'done', 'approved' => 'done', 'cancelled' => 'stopped'],
            $this->states($this->transfer(TransferStatus::CANCELLED, ['reviewed_at']))
        );
    }

    public function test_steps_not_reached_show_no_time(): void
    {
        $steps = collect($this->transfer(TransferStatus::PENDING)->timeline())->keyBy('key');

        $this->assertNotNull($steps['requested']['at']);
        $this->assertNull($steps['received']['at']);
    }

    public function test_components_render_with_one_shared_style_block(): void
    {
        $t = $this->transfer(TransferStatus::IN_TRANSIT, ['reviewed_at', 'packed_at', 'shipped_at']);

        $html = Blade::render(
            '<x-transfers.header title="TR-1" sub="Details" back="/x" mono><x-slot:actions><a>Go</a></x-slot:actions></x-transfers.header>'
            . '<x-transfers.status :status="$status" />'
            . '<x-transfers.route from="Central Warehouse" to="Remera Shop" />'
            . '<x-transfers.timeline :transfer="$t" />',
            ['status' => TransferStatus::DELIVERED, 't' => $t]
        );

        $this->assertSame(1, substr_count($html, '<style>'), 'styles printed once');
        $this->assertStringContainsString('tf-title tf-title-mono', $html);
        $this->assertStringContainsString('tf-badge tf-tone-pink', $html);
        $this->assertStringContainsString('Remera Shop', $html);
        $this->assertStringContainsString('aria-current="step"', $html);
        $this->assertSame(6, substr_count($html, 'class="tf-step '));
    }

    public function test_every_status_has_a_design_token_tone(): void
    {
        foreach (TransferStatus::cases() as $status) {
            $this->assertMatchesRegularExpression('/^(amber|accent|red|violet|pink|green|text-dim)$/', $status->tone());
            $this->assertSame('var(--' . $status->tone() . ')', $status->cssColor());
        }
    }
}
