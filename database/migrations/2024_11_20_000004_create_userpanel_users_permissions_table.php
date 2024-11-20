<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('userpanel_users_permissions', function (Blueprint $table) {
            $table->integer('user_id');
            $table->string('permission', 255);

            $table->primary(['user_id', 'permission']);

            $table->foreign('user_id')
                ->references('id')
                ->on('userpanel_users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('userpanel_users_permissions');
    }
};
