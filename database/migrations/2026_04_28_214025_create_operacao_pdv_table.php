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
        Schema::create('operacao_pdv', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario')->constrained('usuario');
            $table->foreignId('id_operacao_pdv_tipo')->constrained('operacao_pdv_tipo');
            $table->decimal('valor', 10, 2);
            $table->text('observacao')->nullable();
            $table->foreignId('id_cancelamento')->nullable()->constrained('cancelamento');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operacao_pdv');
    }
};
