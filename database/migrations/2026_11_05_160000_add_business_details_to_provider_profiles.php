<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->string('trade_name', 160)->nullable();
            $table->string('provider_type', 80)->nullable();
            $table->unsignedSmallInteger('years_of_experience')->nullable();
            $table->string('commercial_registration', 80)->nullable();
            $table->string('legal_representative', 160)->nullable();
            $table->string('legal_representative_id', 30)->nullable();
            $table->string('company_seal')->nullable();
            $table->string('commercial_registration_document')->nullable();
            $table->string('legal_representative_id_document')->nullable();
            $table->string('signed_annex_a')->nullable();
            $table->string('municipal_license')->nullable();
            $table->timestamp('contract_accepted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'trade_name',
                'provider_type',
                'years_of_experience',
                'commercial_registration',
                'legal_representative',
                'legal_representative_id',
                'company_seal',
                'commercial_registration_document',
                'legal_representative_id_document',
                'signed_annex_a',
                'municipal_license',
                'contract_accepted_at',
            ]);
        });
    }
};