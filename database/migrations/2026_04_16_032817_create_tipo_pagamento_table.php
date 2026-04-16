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
        Schema::create('tipo_pagamento', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100);
            $table->string('referencia', 50)->nullable();
            $table->boolean('permite_troco')->default(false);
            $table->foreignId('id_cancelamento')->nullable()->constrained('cancelamento');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_pagamento');
    }
};