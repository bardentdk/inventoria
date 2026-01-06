<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->string('donation_recipient')->nullable()->after('is_donation');
            $table->date('donation_date')->nullable()->after('donation_recipient');
        });
    }

    public function down()
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['donation_recipient', 'donation_date']);
        });
    }
};
