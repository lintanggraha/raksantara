<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImpactScaleSeeder extends Seeder
{
    public function run()
    {
        DB::table('impact_scales')->insert([

            // ===== REVENUE =====
            ['impact_category_id'=>1,'description'=>'REVENUE : <= 200 JT','score'=>1],
            ['impact_category_id'=>1,'description'=>'REVENUE : 200 JT s.d <= 2 M','score'=>2],
            ['impact_category_id'=>1,'description'=>'REVENUE : 2 M s.d <= 4 M','score'=>3],
            ['impact_category_id'=>1,'description'=>'REVENUE : 4 M s.d <= 10 M','score'=>4],
            ['impact_category_id'=>1,'description'=>'REVENUE : > 10 M','score'=>5],

            // ===== EBITDA =====
            ['impact_category_id'=>2,'description'=>'EBITDA : N/A','score'=>1],
            ['impact_category_id'=>2,'description'=>'EBITDA : < 100 JT','score'=>2],
            ['impact_category_id'=>2,'description'=>'EBITDA : 100 JT s.d 1 M','score'=>3],
            ['impact_category_id'=>2,'description'=>'EBITDA : 1 M s.d <= 4 M','score'=>4],
            ['impact_category_id'=>2,'description'=>'EBITDA : > 4 M','score'=>5],

            // ===== FINANSIAL =====
            ['impact_category_id'=>3,'description'=>'Finansial : < Rp 10 Juta','score'=>1],
            ['impact_category_id'=>3,'description'=>'Finansial : Rp 10 Juta - Rp 100 Juta','score'=>2],
            ['impact_category_id'=>3,'description'=>'Finansial : Rp 100 Juta - Rp 1 Miliar','score'=>3],
            ['impact_category_id'=>3,'description'=>'Finansial : Rp 1 Miliar - Rp 10 Miliar','score'=>4],
            ['impact_category_id'=>3,'description'=>'Finansial : > Rp 10 Miliar','score'=>5],

            // ===== FINANCIAL SIBER =====
            ['impact_category_id'=>4,'description'=>'Financial Siber : Rp 0 - <6 M','score'=>1],
            ['impact_category_id'=>4,'description'=>'Financial Siber : Rp 6 M - <12 M','score'=>2],
            ['impact_category_id'=>4,'description'=>'Financial Siber : Rp 12 M - <18 M','score'=>3],
            ['impact_category_id'=>4,'description'=>'Financial Siber : Rp 18 M - <24 M','score'=>4],
            ['impact_category_id'=>4,'description'=>'Financial Siber : Rp 24 M - <30 M','score'=>5],

            // ===== TUNTUTAN HUKUM =====
            ['impact_category_id'=>5,'description'=>'Tuntutan Hukum : N/A','score'=>1],
            ['impact_category_id'=>5,'description'=>'Perselisihan dengan pelanggan','score'=>2],
            ['impact_category_id'=>5,'description'=>'Tuntutan hukum / denda pelanggan','score'=>3],
            ['impact_category_id'=>5,'description'=>'Peringatan regulator / penyelidikan pidana','score'=>4],
            ['impact_category_id'=>5,'description'=>'Pencabutan izin usaha / penyidikan pidana','score'=>5],

            // ===== PUBLIKASI NEGATIF =====
            ['impact_category_id'=>6,'description'=>'Media Lokal','score'=>1],
            ['impact_category_id'=>6,'description'=>'Media Provinsi','score'=>2],
            ['impact_category_id'=>6,'description'=>'Media Nasional <= 5 pemberitaan','score'=>3],
            ['impact_category_id'=>6,'description'=>'Media Nasional > 5 hari','score'=>4],
            ['impact_category_id'=>6,'description'=>'Media Internasional','score'=>5],

            // ===== SLA =====
            ['impact_category_id'=>7,'description'=>'SLA > 95%','score'=>1],
            ['impact_category_id'=>7,'description'=>'SLA 94-95%','score'=>2],
            ['impact_category_id'=>7,'description'=>'SLA 92-94%','score'=>3],
            ['impact_category_id'=>7,'description'=>'SLA 90-92%','score'=>4],
            ['impact_category_id'=>7,'description'=>'SLA < 90%','score'=>5],

            // ===== PROYEK =====
            ['impact_category_id'=>8,'description'=>'Proyek > 0.83','score'=>1],
            ['impact_category_id'=>8,'description'=>'Proyek 0.78-0.83','score'=>2],
            ['impact_category_id'=>8,'description'=>'Proyek 0.73-0.78','score'=>3],
            ['impact_category_id'=>8,'description'=>'Proyek 0.68-0.73','score'=>4],
            ['impact_category_id'=>8,'description'=>'Proyek < 0.68','score'=>5],

            // ===== KESELAMATAN =====
            ['impact_category_id'=>9,'description'=>'Tidak ada kerugian','score'=>1],
            ['impact_category_id'=>9,'description'=>'Kerugian kecil','score'=>2],
            ['impact_category_id'=>9,'description'=>'Cedera ringan','score'=>3],
            ['impact_category_id'=>9,'description'=>'Cedera sedang','score'=>4],
            ['impact_category_id'=>9,'description'=>'Cacat / kematian','score'=>5],

            // ===== KEAMANAN INFORMASI =====
            ['impact_category_id'=>10,'description'=>'N/A','score'=>1],
            ['impact_category_id'=>10,'description'=>'Temuan observasi','score'=>2],
            ['impact_category_id'=>10,'description'=>'Kesalahan manusia','score'=>3],
            ['impact_category_id'=>10,'description'=>'Temuan signifikan <180 hari','score'=>4],
            ['impact_category_id'=>10,'description'=>'Temuan signifikan <30 hari','score'=>5],

            // ===== REPUTASI =====
            ['impact_category_id'=>11,'description'=>'Tidak signifikan','score'=>1],
            ['impact_category_id'=>11,'description'=>'Minor','score'=>2],
            ['impact_category_id'=>11,'description'=>'Moderat','score'=>3],
            ['impact_category_id'=>11,'description'=>'Signifikan','score'=>4],
            ['impact_category_id'=>11,'description'=>'Sangat parah','score'=>5],

            // ===== OPERASIONAL =====
            ['impact_category_id'=>12,'description'=>'Tidak ada gangguan','score'=>1],
            ['impact_category_id'=>12,'description'=>'Gangguan kecil','score'=>2],
            ['impact_category_id'=>12,'description'=>'Gangguan sedang','score'=>3],
            ['impact_category_id'=>12,'description'=>'Gangguan besar','score'=>4],
            ['impact_category_id'=>12,'description'=>'Operasional berhenti total','score'=>5],

            // ===== KEPATUHAN =====
            ['impact_category_id'=>13,'description'=>'Tidak ada pelanggaran','score'=>1],
            ['impact_category_id'=>13,'description'=>'Pelanggaran minor','score'=>2],
            ['impact_category_id'=>13,'description'=>'Pelanggaran sedang','score'=>3],
            ['impact_category_id'=>13,'description'=>'Pelanggaran serius','score'=>4],
            ['impact_category_id'=>13,'description'=>'Pelanggaran berat','score'=>5],

        ]);
    }
}
