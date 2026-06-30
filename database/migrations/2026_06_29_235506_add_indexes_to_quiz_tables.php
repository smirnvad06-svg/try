<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToQuizTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!$this->hasIndex('questions', 'questions_quiz_id_index')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->index('quiz_id');
            });
    }   if (!$this->hasIndex('responses', 'responses_question_id_index')) {
            Schema::table('responses', function (Blueprint $table) {
                $table->index('question_id');
            });
        };
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropIndex(['quiz_id']);
        });

        Schema::table('responses', function (Blueprint $table) {
            $table->dropIndex(['question_id']);
        });
    }

    private function hasIndex(string $table, string $index): bool
    {
        $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = '{$index}'");
        return count($indexes) > 0;
    }
}
