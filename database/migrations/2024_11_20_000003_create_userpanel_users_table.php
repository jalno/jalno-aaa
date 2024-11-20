<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('userpanel_users', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name', 100);
            $table->string('lastname', 100);
            $table->string('email', 100)->unique();
            $table->string('cellphone', 20)->unique();
            $table->string('password', 255);
            $table->integer('type');
            $table->string('phone', 20)->nullable();
            $table->string('city', 100)->nullable();
            $table->integer('country')->nullable();
            $table->unsignedInteger('zip')->nullable();
            $table->string('address', 255)->nullable();
            $table->string('web', 255)->nullable();
            $table->unsignedInteger('lastonline');
            $table->string('remember_token', 32)->nullable()->unique();
            $table->integer('credit');
            $table->string('avatar', 255)->nullable();
            $table->unsignedInteger('registered_at');
            $table->boolean('has_custom_permissions')->default(0);
            $table->tinyInteger('status');

            $table->foreign('type')->references('id')->on('userpanel_usertypes')->onDelete('cascade');
            $table->foreign('country')->references('id')->on('userpanel_countries')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('userpanel_users');
    }
};
