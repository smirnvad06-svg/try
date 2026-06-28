<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Таблица заявок на кредит для личного кабинета.
 *
 * Подключение БД (для команды):
 *   php artisan migrate
 *
 * После миграции приложение автоматически начнёт сохранять заявки в БД
 * (см. AppServiceProvider — привязка CreditApplicationRepositoryInterface).
 */
class CreateCreditApplicationsTable extends Migration
{
    public function up()
    {
        Schema::create('credit_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('purpose')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->string('currency', 10)->nullable();
            $table->unsignedSmallInteger('term')->nullable();
            $table->decimal('rate', 8, 4)->nullable();
            $table->decimal('penalty', 8, 4)->nullable();
            $table->string('category', 50)->nullable();
            $table->decimal('total_cost', 15, 2)->nullable();
            $table->decimal('down_payment', 15, 2)->nullable();

            $table->string('collateral_item')->nullable();
            $table->string('storage_location')->nullable();
            $table->string('collateral_owner')->nullable();

            $table->json('guarantors')->nullable();

            $table->decimal('psk_percent', 8, 2)->nullable();
            $table->decimal('total_paid', 15, 2)->nullable();
            $table->decimal('overpayment', 15, 2)->nullable();
            $table->decimal('monthly_payment', 15, 2)->nullable();

            $table->string('status', 20)->default('draft');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('credit_applications');
    }
}
