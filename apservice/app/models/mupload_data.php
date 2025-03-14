<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use PhpOffice\PhpSpreadsheet\IOFactory;
use DateTime;


class Mupload_data extends Model
{
    public static function handleAction($method, $param)
    {
        switch ($method) {
            case 'upload':
                return self::upload($param);
            case 'upload_sto_data':
                return self::upload_sto_data($param);
            default:
                return ['error' => 'Action not recognized'];
        }
    }

    public static function uploadGroup($param)
    {
        $imported = json_decode($param['vdata'], true);

        foreach ($imported as $row) {

            $locData = [
                'defmodule' => 'MGROUP',
                'defcode' => $row['groupcode'],
                'defname' => $row['groupname'],
                'syscreateuser' => $param["VUSERLOGIN"],
                'syscreatedate' => date('Y-m-d H:i:s')
            ];


            DB::table('cpmatrix')->updateOrInsert(
                ['defname' => $row['groupname'], 'defmodule' => 'MGROUP'],
                array_filter($locData)
            );
        }

        return json_encode(['success' => true, 'message' => 'Import Master Group Berhasil!']);
    }


    public static function uploadCategory($param)
    {
        $imported = json_decode($param['vdata'], true);

        foreach ($imported as $row) {

            $locData = [
                'defmodule' => 'MCATEGORY',
                'defcode' => $row['categorycode'],
                'defname' => $row['categoryname'],
                'syscreateuser' => $param["VUSERLOGIN"],
                'syscreatedate' => date('Y-m-d H:i:s')
            ];


            DB::table('cpmatrix')->updateOrInsert(
                ['defname' => $row['categoryname'], 'defmodule' => 'MCATEGORY'],
                array_filter($locData)
            );
        }

        return json_encode(['success' => true, 'message' => 'Import Master Kategori Berhasil!']);
    }
    public static function uploadLocation($param)
    {
        $imported = json_decode($param['vdata'], true);

        foreach ($imported as $row) {

            $locData = [
                'defmodule' => 'MLOCATION',
                'defcode' => $row['locationcode'],
                'defname' => $row['locationname'],
                'syscreateuser' => $param["VUSERLOGIN"],
                'syscreatedate' => date('Y-m-d H:i:s')
            ];


            DB::table('cpmatrix')->updateOrInsert(
                ['defname' => $row['locationname'], 'defmodule' => 'MLOCATION'],
                array_filter($locData)
            );
        }

        return json_encode(['success' => true, 'message' => 'Import Master Lokasi Berhasil!']);
    }

    public static function uploadSubLocation($param)
    {
        $imported = json_decode($param['vdata'], true);

        foreach ($imported as $row) {

            $locData = [
                'defmodule' => 'MSUBLOCATION',
                'defcode' => $row['sublocationcode'],
                'defname' => $row['sublocationname'],
                'syscreateuser' => $param["VUSERLOGIN"],
                'syscreatedate' => date('Y-m-d H:i:s')
            ];


            DB::table('cpmatrix')->updateOrInsert(
                ['defname' => $row['sublocationname'], 'defmodule' => 'MSUBLOCATION'],
                array_filter($locData)
            );
        }

        return json_encode(['success' => true, 'message' => 'Import Master Sub Lokasi Berhasil!']);
    }

    public static function uploadDept($param)
    {
        $imported = json_decode($param['vdata'], true);

        foreach ($imported as $row) {

            $locData = [
                'defmodule' => 'MDEPARTMENT',
                'defcode' => $row['deptcode'],
                'defname' => $row['deptname'],
                'syscreateuser' => $param["VUSERLOGIN"],
                'syscreatedate' => date('Y-m-d H:i:s')
            ];


            DB::table('cpmatrix')->updateOrInsert(
                ['defname' => $row['deptname'], 'defmodule' => 'MDEPARTMENT'],
                array_filter($locData)
            );
        }

        return json_encode(['success' => true, 'message' => 'Import Master Sub Lokasi Berhasil!']);
    }
    public static function uploadCostCenter($param)
    {
        $imported = json_decode($param['vdata'], true);

        foreach ($imported as $row) {

            $locData = [
                'defmodule' => 'MCOST',
                'defcode' => $row['costcentercode'],
                'defname' => $row['costcentername'],
                'syscreateuser' => $param["VUSERLOGIN"],
                'syscreatedate' => date('Y-m-d H:i:s')
            ];


            DB::table('cpmatrix')->updateOrInsert(
                ['defname' => $row['costcentername'], 'defmodule' => 'MCOST'],
                array_filter($locData)
            );
        }

        return json_encode(['success' => true, 'message' => 'Import Master Sub Lokasi Berhasil!']);
    }
    public static function uploadCondition($param)
    {
        $imported = json_decode($param['vdata'], true);

        foreach ($imported as $row) {

            $locData = [
                'defmodule' => 'MCONDITION',
                'defcode' => $row['conditioncode'],
                'defname' => $row['conditionname'],
                'syscreateuser' => $param["VUSERLOGIN"],
                'syscreatedate' => date('Y-m-d H:i:s')
            ];


            DB::table('cpmatrix')->updateOrInsert(
                ['defname' => $row['conditionname'], 'defmodule' => 'MCONDITION'],
                array_filter($locData)
            );
        }

        return json_encode(['success' => true, 'message' => 'Import Master Condition Berhasil!']);
    }
    public static function uploadGroupItem($param)
    {
        $imported = json_decode($param['vdata'], true);

        foreach ($imported as $row) {

            $locData = [
                'defmodule' => 'MGROUP_ITEM',
                'defcode' => $row['groupitem_code'],
                'defname' => $row['groupitem_name'],
                'syscreateuser' => $param["VUSERLOGIN"],
                'syscreatedate' => date('Y-m-d H:i:s')
            ];

            DB::table('cpmatrix')->updateOrInsert(
                ['defname' => $row['groupitem_name'], 'defmodule' => 'MGROUP_ITEM'],
                array_filter($locData)
            );
        }

        return json_encode(['success' => true, 'message' => 'Import Master Group Item Berhasil!']);
    }
    public static function uploadCategoryItem($param)
    {
        $imported = json_decode($param['vdata'], true);

        foreach ($imported as $row) {

            $locData = [
                'defmodule' => 'MKATEGORI_ITEM',
                'defcode' => $row['categoryitem_code'],
                'defname' => $row['categoryitem_name'],
                'syscreateuser' => $param["VUSERLOGIN"],
                'syscreatedate' => date('Y-m-d H:i:s')
            ];


            DB::table('cpmatrix')->updateOrInsert(
                ['defname' => $row['categoryitem_name'], 'defmodule' => 'MKATEGORI_ITEM'],
                array_filter($locData)
            );
        }

        return json_encode(['success' => true, 'message' => 'Import Master Category Item Berhasil!']);
    }
    public static function uploadSubCategoryItem($param)
    {
        $imported = json_decode($param['vdata'], true);

        foreach ($imported as $row) {

            $locData = [
                'defmodule' => 'MSUBKATEGORI_ITEM',
                'defcode' => $row['subcategoryitem_code'],
                'defname' => $row['subcategoryitem_name'],
                'syscreateuser' => $param["VUSERLOGIN"],
                'syscreatedate' => date('Y-m-d H:i:s')
            ];


            DB::table('cpmatrix')->updateOrInsert(
                ['defname' => $row['subcategoryitem_name'], 'defmodule' => 'MSUBKATEGORI_ITEM'],
                array_filter($locData)
            );
        }

        return json_encode(['success' => true, 'message' => 'Import Master Sub Category Item Berhasil!']);
    }
    public static function uploadDataPart($param)
    {
        $imported = json_decode($param['vdata'], true);

        foreach ($imported as $row) {

            $partData = [
                'sumber_data' => $row['sumber_data'],
                'partcode' => $row['partcode'],
                'partname' => $row['partname'],
                'partgroup' => $row['partgroup'],
                'partcategory' => $row['partcategory'],
                'part_currency' => $row['part_currency'],
                'grnumber' => $row['grnumber'],
                'grdate' => $row['grdate'],
                'ponumber' => $row['ponumber'],
                'podate' => $row['podate'],
                'prnumber' => $row['prnumber'],
                'prdate' => $row['prdate'],
                'invnumber' => $row['invnumber'],
                'invdate' => $row['invdate'],
                'syscreateuser' => $param["VUSERLOGIN"],
                'syscreatedate' => date('Y-m-d H:i:s')
            ];

            $exists = DB::table('sumber_data_asset')
                ->where('partcode', $row['partcode'])
                ->where('sumber_data', $row['sumber_data'])
                ->exists();

            if ($exists) {
                // Lakukan Update
                DB::table('sumber_data_asset')
                    ->where('partcode', $row['partcode'])
                    ->where('sumber_data', $row['sumber_data'])
                    ->update(array_merge(
                        array_filter($partData), // Data lainnya
                        [
                            'sysupdateuser' => $param["VUSERLOGIN"],
                            'sysupdatedate' => date('Y-m-d H:i:s')
                        ]
                    ));
            } else {
                // Lakukan Insert
                DB::table('sumber_data_asset')->insert($partData);
            }
        }

        return json_encode(['success' => true, 'message' => 'Data import successful!']);
    }
}
