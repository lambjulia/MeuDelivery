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
        Schema::table('companies', function (Blueprint $table) {
            $table->string('slug')->unique()->nullable()->after('trade_name');
            $table->boolean('pickup_enabled')->default(true)->after('default_delivery_fee');
            $table->text('banner_path')->nullable()->after('logo_path');
            $table->text('about')->nullable()->after('banner_path');
            $table->integer('min_order_amount')->default(0)->after('default_delivery_fee');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['slug', 'pickup_enabled', 'banner_path', 'about', 'min_order_amount']);
        });
    }
};
