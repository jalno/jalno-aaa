<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('userpanel_countries', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('code', 3)->unique();
            $table->string('name', 255);
            $table->string('dialing_code', 3);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('userpanel_countries');
    }
};
