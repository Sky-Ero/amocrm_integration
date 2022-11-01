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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->nullable()->nullable();
            $table->integer('price')->nullable();

            $table->integer('responsible_user_id')->nullable();
            $table->integer('group_id')->nullable();
            $table->integer('createdBy')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
            $table->integer('account_id')->nullable();
            $table->integer('pipeline_id')->nullable();
            $table->integer('status_id')->nullable();
            $table->integer('source_id')->nullable();
            $table->integer('loss_reason_id')->nullable();
            $table->json('tags')->nullable();
            $table->integer('company_id')->nullable();
            $table->json('catalog_elements')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leads');
    }
};
