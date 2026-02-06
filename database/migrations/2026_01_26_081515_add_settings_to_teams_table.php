<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->string('email')->nullable()->after('name');
            $table->string('timezone')->default('UTC')->after('email');
            $table->string('language', 10)->default('en')->after('timezone');
            $table->string('currency', 10)->default('USD')->after('language');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $columns = Schema::getColumnListing('teams');
        if (in_array('email', $columns)) {
            Schema::table('teams', function (Blueprint $table) {
                $table->dropColumn('email');
            });
        }
        if (in_array('timezone', $columns)) {
            Schema::table('teams', function (Blueprint $table) {
                $table->dropColumn('timezone');
            });
        }
        if (in_array('language', $columns)) {
            Schema::table('teams', function (Blueprint $table) {
                $table->dropColumn('language');
            });
        }
        if (in_array('currency', $columns)) {
            Schema::table('teams', function (Blueprint $table) {
                $table->dropColumn('currency');
            });
        }
    }
};
