<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('endereco', function (Blueprint $table) {
            $table->id();
            $table->string('estado', 2);
            $table->string('municipio');
            $table->string('bairro');
            $table->char('cep', 8);
            $table->string('logradouro');
            $table->string('numero', 10);
            $table->string('complemento')->nullable();
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
        Schema::dropIfExists('endereco');
    }
};
