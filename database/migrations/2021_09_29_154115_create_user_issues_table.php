<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserIssuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_issues_form', function (Blueprint $table) {
            $table->id();
            $table->string('code_user');
            $table->string('issue_id')->nullable();
            $table->string('form_id');
            $table->text('questions');
            $table->text('user_responses');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_issues');
    }
}
