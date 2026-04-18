<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fleet_market_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('brand');
            $table->string('model');
            $table->string('machine_type'); // adt, excavator, loader, etc.
            $table->unsignedSmallInteger('year')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->string('currency', 5)->default('ZAR');
            $table->enum('condition', ['new', 'used', 'refurbished'])->default('used');
            $table->unsignedInteger('hours_on_machine')->nullable();
            $table->text('description')->nullable();
            $table->json('images')->nullable();         // array of storage paths
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 30)->nullable();
            $table->string('location')->nullable();     // e.g. "Limpopo, South Africa"
            $table->enum('status', ['active', 'sold', 'withdrawn'])->default('active');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fleet_market_listings');
    }
};
