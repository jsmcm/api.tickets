<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('threads', function (Blueprint $table) {
            $table->id();
            $table->foreignId("ticket_id");
            $table->dateTime("date")->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->enum("type", ["from-client", "internal-note", "to-client", "to-client-ml"]);
            $table->longText("message")->fulltext();
            $table->timestamps();
        });

        Schema::table('threads', function (Blueprint $table) {
            $table->softDeletes();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('threads');
    }
};
