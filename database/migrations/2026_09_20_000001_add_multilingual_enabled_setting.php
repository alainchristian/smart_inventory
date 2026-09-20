<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')->updateOrInsert(
            ['key' => 'multilingual_enabled'],
            [
                'value'       => 'false',
                'type'        => 'boolean',
                'group'       => 'general',
                'label'       => 'Enable multiple languages',
                'description' => 'When off, the app is shown to everyone in the default language only and the language switcher is hidden. When on, users can pick their own language (English / Kinyarwanda) independently of the default.',
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
