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
        Schema::create('mcp_requests', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable();
            $table->text('request_text');
            $table->string('request_type')->default('search');
            $table->json('search_parameters')->nullable();
            $table->longText('response_text')->nullable();
            $table->json('found_files')->nullable();
            $table->integer('files_count')->default(0);
            $table->string('status')->default('pending');
            $table->decimal('processing_time', 8, 3)->nullable();
            $table->text('error_message')->nullable();
            $table->string('user_ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('session_id');
            $table->index('request_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mcp_requests');
    }
};
