<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pipelines', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->nullable();
            $table->boolean('is_main')->nullable();
            $table->integer('sort')->nullable();
            $table->integer('responsible_user_id')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('closest_task_at')->nullable();
            $table->integer('ltv')->nullable();
            $table->boolean('is_deleted')->nullable();
            $table->integer('purchases_count')->nullable();
            $table->integer('average_check')->nullable();
            $table->integer('account_id')->nullable();
            $table->json('catalog_elements')->nullable();
            $table->json('companies')->nullable();
            $table->json('tags')->nullable();
            $table->json('segments')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pipelines');
    }
};
