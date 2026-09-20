<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')->updateOrInsert(
            ['key' => 'default_locale'],
            [
                'value'       => 'en',
                'type'        => 'string',
                'group'       => 'general',
                'label'       => 'Default language',
                'description' => 'Language used app-wide for any user who has not picked a personal language. Users can still switch their own language regardless of this setting.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]
        );
    }

    public function down(): void
    {
        // Safe to leave — settings rows are additive
    }
};
