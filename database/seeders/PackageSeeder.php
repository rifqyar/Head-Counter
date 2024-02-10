<?php

namespace Database\Seeders;

use App\Models\Module\MasterData\Package;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dataPackage = array([
            'kd_pck' => 'PCK-01',
            'name' => 'Half day Meeting Package',
            'price' => '345.000',
            'details' => '<ul><li>Meeting room usage up to 4(four) hours</li><li>1x lunch or 1x dinner</li><li>1(one) time&nbsp;coffee break during the meeting</li><li>General benefits</li></ul>',
            'count_qr' => 1
        ],[
            'kd_pck' => 'PCK-02',
            'name' => 'Full day Meeting Package',
            'price' => '400.000',
            'details' => '<ul><li>Meeting room usage up to 8(eight) hours</li><li>1x lunch or 1x dinner</li><li>2(two) time&nbsp;coffee break during the meeting</li><li>General benefits</li></ul>',
            'count_qr' => 1
        ],[
            'kd_pck' => 'PCK-03',
            'name' => 'Full board Meeting Package',
            'price' => '345.000',
            'details' => '<ul><li>Meeting room usage up to 12(twelve) hours</li><li>1x lunch or 1x dinner</li><li>2(two) time&nbsp;coffee break during the meeting</li><li>General benefits</li></ul>',
            'count_qr' => 1
        ],[
            'kd_pck' => 'PCK-04',
            'name' => 'Residential Twin Share Package',
            'price' => '950.000',
            'details' => '<ul><li>Meeting room usage up to 12(twelve) hours</li><li>Accomodation Standard Room</li><li>1x breakfast, 1x lunch and 1x dinner</li><li>2(two) times coffee break during the meeting</li><li>General benefits, as mentioned below</li></ul>',
            'count_qr' => 3
        ],[
            'kd_pck' => 'PCK-05',
            'name' => 'Residential Single Share Package',
            'price' => '345.000',
            'details' => '<ul><li>Meeting room usage up to 4(four) hours</li><li>1x lunch or 1x dinner</li><li>1(one) time&nbsp;coffee break during the meeting</li><li>General benefits</li></ul>',
            'count_qr' => 1
        ]);

        foreach ($dataPackage as $val) {
            Package::create($val);
        }
    }
}
