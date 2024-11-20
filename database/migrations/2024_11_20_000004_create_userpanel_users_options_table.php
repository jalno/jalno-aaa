<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('userpanel_users_options', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('user');
            $table->string('name', 255);
            $table->string('value', 255);

            $table->unique(['user', 'name']);

            $table->foreign('user')->references('id')->on('userpanel_users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('userpanel_users_options');
    }
};
