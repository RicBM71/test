<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHalbalinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('halbalins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('operacion', 1)->nullable();
            $table->string('username_his', 30)->nullable();
            $table->timestamp('created_his');
            $table->unsignedInteger('albalin_id')->index();
            $table->unsignedInteger('albaran_id')->index();
            $table->unsignedInteger('empresa_id');
            $table->unsignedInteger('producto_id');
            $table->decimal('unidades', 12, 2)->default(0);
            $table->decimal('importe_unidad', 12, 4)->default(0);
            $table->decimal('precio_coste', 10, 2)->default(0);
            $table->decimal('importe_venta', 10, 2)->default(0);
            $table->unsignedInteger('iva_id');
            $table->decimal('iva', 6, 2)->default(0);
            $table->decimal('descuento', 5, 2)->default(0);
            $table->string('notas')->nullable();
            $table->string('username', 30)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('halbalins');
    }
}
