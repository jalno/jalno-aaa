<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('userpanel_usertypes', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('title', 255);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('userpanel_usertypes');
    }
};
