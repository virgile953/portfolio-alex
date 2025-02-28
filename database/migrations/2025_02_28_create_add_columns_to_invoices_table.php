<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'total_landed_cost')) {
                $table->decimal('total_landed_cost', 10, 2)->default(0);
            }

            if (!Schema::hasColumn('invoices', 'invoice_total')) {
                $table->decimal('invoice_total', 10, 2)->default(0);
            }

            if (!Schema::hasColumn('invoices', 'margin_rate')) {
                $table->decimal('margin_rate', 5, 2)->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['total_landed_cost', 'invoice_total', 'margin_rate']);
        });
    }
}
