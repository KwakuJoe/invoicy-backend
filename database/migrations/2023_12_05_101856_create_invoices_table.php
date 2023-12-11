<?php

use App\Enums\InvoiceStatusEnum;
use App\Models\Invoice;
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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->text('invoice_id');
            $table->foreignId('user_id');
            $table->datetime('invoice_date');
            $table->string('client_id');
            $table->string('client_address');
            $table->string('client_name');
            $table->string('client_email');
            $table->string('client_phone');
            $table->string('client_alternate_phone');
            $table->decimal('total_amount');
            $table->decimal('delivery_amount');
            $table->text('additional_information');
            $table->string('status')->default(InvoiceStatusEnum::PROCESSING);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
