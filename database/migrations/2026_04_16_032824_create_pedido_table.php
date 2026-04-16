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
        Schema::create('pedido', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_tipo_pagamento')->nullable()->constrained('tipo_pagamento');
            $table->foreignId('id_vendedor')->constrained('usuario');
            $table->foreignId('id_pedido_status')->constrained('pedido_status');
            $table->decimal('valor_total', 10, 2);
            $table->decimal('valor_pago', 10, 2)->nullable();
            $table->decimal('valor_a_pagar', 10, 2)->default(0);
            $table->decimal('troco', 10, 2)->default(0);
            $table->text('observacao')->nullable();
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
        Schema::dropIfExists('pedido');
    }
};