<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone', 20)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            
            // Location binding (nullable for owner)
            $table->unsignedBigInteger('location_id')->nullable();
            
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
        
        // Add enum columns using raw SQL (this is the CORRECT way)
        DB::statement("ALTER TABLE users ADD COLUMN role user_role DEFAULT 'shop_manager'");
        DB::statement("ALTER TABLE users ADD COLUMN location_type location_type");
        
        // Create composite index
        DB::statement('CREATE INDEX users_location_type_location_id_idx ON users(location_type, location_id)');
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};