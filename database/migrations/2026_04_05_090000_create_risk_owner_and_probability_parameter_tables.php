<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risk_owner_parameters', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('probability_scales', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('score')->unique();
            $table->string('description', 255);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $riskOwnerOptions = [
            'BT - BANKING APPLICATIONS',
            'BT - CORE BANKING SYSTEM CONVENTIONAL',
            'BT - CORE BANKING SYSTEM SYARIAH',
            'PM - REGULATORY & FINANCIAL REPORTING',
            'QA - QA & INNOVATION',
            'DI - DIGITAL SOLUTION',
            'OM - BUSINESS & DIGITAL OPERATION',
            'BT - DIGITAL BANKING',
            'BT - SURROUNDING APPLICATION',
            'IF - SWITCHING & MIDDLEWARE',
            'BS - BUSINESS STRATEGY',
            'CR - CUSTOMER RELATIONS',
            'OM - DATA CENTER OPERATION',
            'IF - INFRASTRUCTURE',
            'OM - IT OPERATION MANAGEMENT',
            'RI - RESEARCH & INNOVATION',
            'BS - CEO OFFICE',
            'CS - CYBER SECURITY',
            'BS - BUSINESS SUPPORT',
            'HC - HC, FINANCE, & LEGAL',
            'CO - COMPLIANCE',
        ];

        $now = now();

        foreach ($riskOwnerOptions as $index => $name) {
            DB::table('risk_owner_parameters')->insert([
                'name' => $name,
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $probabilityScales = [
            ['score' => 1, 'description' => '1. Sangat tidak mungkin terjadi'],
            ['score' => 2, 'description' => '2. Kemungkinan kecil terjadi'],
            ['score' => 3, 'description' => '3. Mungkin terjadi sesekali'],
            ['score' => 4, 'description' => '4. Sangat mungkin terjadi'],
            ['score' => 5, 'description' => '5. Hampir pasti terjadi'],
        ];

        foreach ($probabilityScales as $index => $scale) {
            DB::table('probability_scales')->insert([
                'score' => $scale['score'],
                'description' => $scale['description'],
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('probability_scales');
        Schema::dropIfExists('risk_owner_parameters');
    }
};
