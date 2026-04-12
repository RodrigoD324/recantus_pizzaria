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
        Schema::create('contato', function (Blueprint $table) {
            $table->id();
            $table->char('ddd_telefone', 2)->nullable();
            $table->string('telefone', 9)->nullable();
            $table->char('ddd_celular', 2);
            $table->string('celular', 9);
            $table->string('email')->unique();
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
        Schema::dropIfExists('contato');
    }
};
