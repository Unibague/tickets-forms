<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePqrsTable extends Migration
{
    public function up()
    {
        Schema::create('pqrs', function (Blueprint $table) {
            $table->id();
            $table->integer('issue_id')->nullable();
            $table->string('nombre');
            $table->string('email');
            $table->string('tipo_usuario');
            $table->string('tipo_solicitud');
            $table->string('asunto');
            $table->text('descripcion');
            $table->string('area_enrutamiento');
            $table->string('categoria')->nullable();
            $table->string('prioridad');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pqrs');
    }
}
