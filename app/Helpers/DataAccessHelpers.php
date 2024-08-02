<?php

namespace App\Helpers;

use Carbon\Carbon;
use CurlHandle;
use Illuminate\Support\Facades\DB;

class DataAccessHelpers
{

    public static function convertArrayToNumber($value)
    {

        try {


            $value = str_replace(',', '', $value);

            // Mengubah string menjadi float/angka
            $value = floatval($value);



            return $value;
        } catch (\Exception $e) {
            dd($e);
        }
    }

    public static function convertToNumber($value)
    {

        try {


            $value = str_replace(',', '', $value);

            // Mengubah string menjadi float/angka
            $value = floatval($value);


            return $value;
        } catch (\Exception $e) {
            dd($e);
        }
    }

    public static function generateTransactionNumber($clientCode)
    {
        $currentYear = Carbon::now()->translatedFormat('Y');
        $totalMeeting = DB::table('trx_meeting_schedule')->select('*')->whereYear('created_at', $currentYear)->count();
        $number = '0000';
        if ($totalMeeting == 0) {
            $number = '0001';
        } else {
            // $number = substr($number, 0, $totalMeeting).$totalMeeting;
            $number = sprintf("%04d", (int)$totalMeeting + 1);
        }

        $code = $clientCode;
        $transNumber = "TRX/MT-SCHD/$code/$currentYear/$number";

        return $transNumber;
    }

    public static function formatValueMoney($number)
    {
        $val = number_format($number, 2, ".", ",");
        $setVal = 'Rp. ' . $val;

        return $setVal;
    }

    public static function is_base64($s)
    {
        return (bool) preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $s);
    }

    public static function getMac()
    {
        $cmd = "arp -a " . $_SERVER["REMOTE_ADDR"];
        $status = 0;
        $return = [];
        exec($cmd, $return, $status);
        if (isset($return[3])) return strtoupper(str_replace("-", ":", substr($return[3], 24, 17)));
        return false;
    }
}
