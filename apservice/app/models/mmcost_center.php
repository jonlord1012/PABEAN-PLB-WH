<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use DateTime;

class Mmcost_center extends Model
{
    public static function handleAction($method, $param)
    {
        switch ($method) {
            case 'read_data':
                return self::read_data($param);
            default:
                return ['error' => 'Action not recognized'];
        }
    }

    public static function read_data($param)
    {
        $query = DB::table('cpmatrix')
            ->select('defid', 'defname', 'defcode')
            ->where('defmodule', '=', 'MCOST');

        if (array_key_exists('keywhere', $param)) {
            $keyval = json_decode($param['keywhere'], true);
            foreach ($keyval as $key => $val) {
                $query->where($val['property'], $val['value']);
            }
        }

        if (array_key_exists('filter', $param)) {
            $keyval = json_decode($param['filter'], true);

            foreach ($keyval as $key => $val) {
                $colname = ['sysupdatedate', 'syscreatedate'];
                if (in_array($val['property'], $colname)) {
                    $query->whereRaw("TO_CHAR(" . $val['property'] . ", 'YYYY-MM-DD HH24:MI:SS') LIKE ?", ['%' . $val['value'] . '%']);
                } else {
                    $query->whereRaw("UPPER(" . $val['property'] . ") LIKE ?", ['%' . strtoupper($val['value']) . '%']);
                }
            }
        }

        $count = $query->count();

        if (array_key_exists('limit', $param)) {
            $query->limit($param['limit'])->offset($param['start']);
        }

        if (array_key_exists('sort', $param)) {
            $keyval = json_decode($param['sort'], true);
            foreach ($keyval as $key => $val) {
                $query->orderBy($val['property'], $val['direction']);
            }
        } else {
            $query->orderBy('syscreatedate', 'desc');
        }

        $rows = $query->get();
        return json_encode([
            'TotalRows' => $count,
            'Rows' => $rows
        ]);
    }

    public static function download_data($param)
    {
        $query = DB::table('cpmatrix')
            ->select('defcode as Cost Center Code', 'defname as Cost Center Name')
            ->where('defmodule', '=', 'MCOST');

        if (array_key_exists('filter', $param)) {
            $keyval = json_decode($param['filter'], true);

            foreach ($keyval as $key => $val) {
                $colname = ['sysupdatedate', 'syscreatedate'];
                if (in_array($val['property'], $colname)) {
                    $query->whereRaw("TO_CHAR(" . $val['property'] . ", 'YYYY-MM-DD HH24:MI:SS') LIKE ?", ['%' . $val['value'] . '%']);
                } else {
                    $query->whereRaw("UPPER(" . $val['property'] . ") LIKE ?", ['%' . strtoupper($val['value']) . '%']);
                }
            }
        }

        if (array_key_exists('sort', $param)) {
            $keyval = json_decode($param['sort'], true);
            foreach ($keyval as $key => $val) {
                $query->orderBy($val['property'], $val['direction']);
            }
        }

        $rows = $query->get()->toArray();

        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        if (!empty($rows)) {
            $header = array_keys((array) $rows[0]);

            $columnIndex = 1;
            foreach ($header as $field) {
                $columnLetter = Coordinate::stringFromColumnIndex($columnIndex);
                $worksheet->setCellValue($columnLetter . '1', $field);
                $columnIndex++;
            }

            $rowIndex = 2;
            foreach ($rows as $row) {
                $columnIndex = 1;
                foreach ((array) $row as $cell) {
                    $columnLetter = Coordinate::stringFromColumnIndex($columnIndex);
                    $worksheet->setCellValue($columnLetter . $rowIndex, $cell);
                    $columnIndex++;
                }
                $rowIndex++;
            }
        }

        $date = new DateTime();
        $vfilename = "data_asset_cost_center_download_" . $date->format('Y_m_d_H_i_s') . ".xlsx";
        $outputFilePath = base_path("z_download/" . $vfilename);

        $writer = new Xlsx($spreadsheet);
        $writer->save($outputFilePath);

        $hasil = [
            'success' => "true",
            'remark' => 'File Download',
            'filename' => 'apservice/z_download/' . $vfilename
        ];

        return json_encode($hasil);
    }

    public static function save_data($param)
    {
        $vdata = json_decode($param['vdata'], true);

        $field = array(
            'defmodule' => 'MCOST',
            'defname' => $vdata['defname'],
            'defcode' => $vdata['defcode'],
            'syscreateuser' => $param['VUSERLOGIN'],
            'syscreatedate' => date('Y-m-d H:i:s')
        );

        $existingCostcenter = DB::table('cpmatrix')->where('defmodule', $field['defmodule'])
            ->where('defname', $vdata['defname']);

        if (!empty($vdata['defid'])) {
            $existingCostcenter->where('defid', '!=', $vdata['defid']);
        }

        $existingRecord = $existingCostcenter->first();
        if ($existingCostcenter->exists()) {
            return json_encode([
                'success' => false,
                'message' => "Cost Center name " . $existingRecord->defname . " sudah ada"
            ]);
        }

        if (!empty($vdata['defid'])) {
            $updatedCostcenter = DB::table('cpmatrix')->where('defid', $vdata['defid'])->update(array_filter($field));

            if ($updatedCostcenter) {
                return json_encode([
                    'success' => true,
                    'message' => 'Update Data Success'
                ]);
            } else {
                return json_encode([
                    'success' => false,
                    'message' => 'Update Data Failed'
                ]);
            }
        } else {
            $field['defcode'] = $vdata['defname'];

            $createdCostcenter = DB::table('cpmatrix')->insert(array_filter($field));

            if ($createdCostcenter) {
                return json_encode([
                    'success' => true,
                    'message' => 'Add Data Success'
                ]);
            } else {
                return json_encode([
                    'success' => false,
                    'message' => 'Add Data Failed'
                ]);
            }
        }
    }

    public static function delete_data($param)
    {
        $defid = $param['defid'];
        try {
            $deleted = DB::table('cpmatrix')->where('defid', $defid)->delete();

            if ($deleted) {
                return json_encode([
                    'success' => true,
                    'message' => 'Data berhasil dihapus'
                ]);
            } else {
                return json_encode([
                    'success' => false,
                    'message' => 'Gagal menghapus data'
                ]);
            }
        } catch (\Exception $e) {
            return json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
}
