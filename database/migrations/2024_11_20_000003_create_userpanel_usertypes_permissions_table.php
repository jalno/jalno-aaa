<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('userpanel_usertypes_permissions', function (Blueprint $table) {
            $table->integer('type');
            $table->string('name', 255);

            $table->primary(['type', 'name']);

            $table->foreign('type')->references('id')->on('userpanel_usertypes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('userpanel_usertypes_permissions');
    }
};
