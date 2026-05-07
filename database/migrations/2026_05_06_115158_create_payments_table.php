<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->index();
            $table->foreignId('provider_id')->constrained('payment_providers');
            $table->foreignId('currency_id')->constrained('currencies');
            $table->uuid('transaction_id')->unique();
            $table->string('status')->default('pending');
            $table->decimal('amount', 12);
            $table->string('redirect_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
