<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('userpanel_usertypes_options', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('usertype');
            $table->string('name', 255);
            $table->string('value', 255);

            $table->unique(['usertype', 'name']);

            $table->foreign('usertype')->references('id')->on('userpanel_usertypes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('userpanel_usertypes_options');
    }
};
