<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::create('canned_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId("department_id");
            $table->text("message")->nullable(false);
            $table->string("slug");
            $table->string("title");
            $table->boolean("use_ml");
            $table->timestamps();
        });


        Schema::table('canned_replies', function (Blueprint $table) {
            $table->softDeletes();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('canned_replies');
    }
};
