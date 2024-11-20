<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('userpanel_usertypes_priorities', function (Blueprint $table) {
            $table->integer('parent');
            $table->integer('child');

            $table->primary(['parent', 'child']);

            $table->foreign('parent')->references('id')->on('userpanel_usertypes')->onDelete('cascade');
            $table->foreign('child')->references('id')->on('userpanel_usertypes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('userpanel_usertypes_priorities');
    }
};
