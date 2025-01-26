<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvertedTable extends Migration
{
    public function up()
    {
        Schema::create('registry_inverted', function (Blueprint $table) {
            $table->id();
            $table->boolean('inverted')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('registry_inverted');
    }
}
