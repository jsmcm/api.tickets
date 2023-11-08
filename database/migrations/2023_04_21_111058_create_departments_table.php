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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id");
            $table->string("department")->nullable(false);
            $table->text("logo_url");
            $table->text("signature");
            $table->string("mail_host");
            $table->integer("pop_port")->default(110);
            $table->integer("smtp_port")->default(587);
            $table->string("mail_username");
            $table->string("mail_password");
            $table->string("email_address");
            $table->timestamps();
        });

        Schema::table('departments', function (Blueprint $table) {
            // Add a unique constraint on 'department_name' and 'company_id' columns
            $table->unique(['department', 'user_id']);
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->softDeletes();
        });
        
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('departments', function (Blueprint $table) {
            // Drop the unique constraint if needed
            $table->dropUnique(['department', 'user_id']);
        });

        Schema::dropIfExists('departments');
    }
};
