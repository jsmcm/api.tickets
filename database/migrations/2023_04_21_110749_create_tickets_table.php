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

        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId("department_id");
            $table->foreignId("user_id");
            $table->string("subject")->fulltext();
            $table->enum("status", ["open", "overdue", "answered", "closed"])->default("open");
            $table->dateTime("date_opened")->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string("ip");
            $table->string("folder_hash")->default("");
            $table->string("intent")->nullable();
            $table->enum("priority", ["high", "normal", "low"])->default("normal"); 
            $table->timestamps();
        });


        Schema::table('tickets', function (Blueprint $table) {
            $table->softDeletes();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
