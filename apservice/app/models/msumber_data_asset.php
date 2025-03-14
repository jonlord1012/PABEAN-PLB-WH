<?php

namespace App\Models;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use DateTime;

class Msumber_data_asset extends Model
{

    public static function handleAction($method, $param)
    {
        switch ($method) {
            case 'read_data':
                return self::read_data($param);
            case 'fromlp':
                return self::fromlp($param);
            default:
                return ['error' => 'Action not recognized'];
        }
    }
    public static function read_data($param)
    {
        $query = DB::table('sumber_data_asset')
            ->select('*');


        if (array_key_exists('keywhere', $param)) {
            $keyval = json_decode($param['keywhere'], true);
            foreach ($keyval as $key => $val) {
                $query->where($val['property'], $val['value']);
            }
        }

        if (array_key_exists('filter', $param)) {
            $keyval = json_decode($param['filter'], true);
            foreach ($keyval as $key => $val) {
                $colname = ['syscreatedate', 'sysupdatedate'];
                if (in_array($val['property'], $colname)) {
                    $query->whereRaw("FORMAT(" . $val['property'] . ", 'yyyy-MM-dd HH:mm:ss') LIKE ?", ['%' . $val['value'] . '%']);
                } else {
                    // cek apakah value numeric, tidak pakai upper. jika bukan numeric pakai uppper
                    if (is_numeric($val['value'])) {
                        $query->where($val['property'], 'LIKE', '%' . $val['value'] . '%');
                    } else {
                        $query->whereRaw("UPPER(" . $val['property'] . ") LIKE ?", ['%' . strtoupper($val['value']) . '%']);
                    }
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
    public static function fromlp($param)
    {
        $query = DB::connection('oracle')->table('VW_PART_ASSET')  // Menggunakan koneksi Oracle
            ->select('*');

        if (array_key_exists('keywhere', $param)) {
            $keyval = json_decode($param['keywhere'], true);
            foreach ($keyval as $key => $val) {
                $query->where($val['property'], $val['value']);
            }
        }

        if (array_key_exists('filter', $param)) {
            $keyval = json_decode($param['filter'], true);
            foreach ($keyval as $key => $val) {
                $query->whereRaw("{$val['property']} LIKE ?", ["%" . $val['value'] . "%"]);
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
        }

        $rows = $query->get();

        return json_encode([
            'TotalRows' => $count,
            'Rows' => $rows
        ]);

    }

    public static function save_data($param)
    {
        $vdata = json_decode($param['vdata'], true);
        $responses = [];
        foreach ($vdata as $data) {
            $field = array(
                'partname' => $data['partname'],
                'partcode' => $data['partcode'],
                'grqty' => $data['grqty'],
                'partgroup' => $data['partgroup'],
                'partcategory' => $data['partcategory'],
                'part_price' => $data['part_price_usd'],
                'part_currency' => $data['part_currency_usd'],
                'grnumber' => $data['grnumber'],
                'grdate' => $data['grdate'],
                'ponumber' => $data['ponumber'],
                'podate' => $data['podate'],
                'invnumber' => $data['invnumber'],
                'invdate' => $data['invdate'],
                'sumber_data' => $data['sumber_data'],
                'syscreatedate' => date('Y-m-d H:i:s'),
                'syscreateuser' => $param['VUSERLOGIN']
            );

            $existingRecord = DB::table('sumber_data_asset')
                ->where('grnumber', $data['grnumber'])
                ->first();

            if ($existingRecord) {
                $updateSumberData = DB::table('sumber_data_asset')
                    ->where('grnumber', $data['grnumber'])
                    ->update(array_filter($field));

                if ($updateSumberData) {
                    $responses[] = [
                        'success' => true,
                        'message' => 'Update Data Success'
                    ];
                } else {
                    $responses[] = [
                        'success' => false,
                        'message' => 'Update Data Failed'
                    ];
                }
            } else {

                $createSumberData = DB::table('sumber_data_asset')->insert(array_filter($field));

                if ($createSumberData) {
                    $responses[] = [
                        'success' => true,
                        'message' => 'Add Data Success'
                    ];
                } else {
                    $responses[] = [
                        'success' => false,
                        'message' => 'Add Data Failed'
                    ];
                }
            }
        }

        return json_encode($responses);
    }

    public static function download_data($param)
    {
        $query = DB::table('sumber_data_asset')
            ->select('*');


        if (array_key_exists('keywhere', $param)) {
            $keyval = json_decode($param['keywhere'], true);
            foreach ($keyval as $key => $val) {
                $query->where($val['property'], $val['value']);
            }
        }

        if (array_key_exists('filter', $param)) {
            $keyval = json_decode($param['filter'], true);
            foreach ($keyval as $key => $val) {
                $colname = ['syscreatedate', 'sysupdatedate'];
                if (in_array($val['property'], $colname)) {
                    $query->whereRaw("FORMAT(" . $val['property'] . ", 'yyyy-MM-dd HH:mm:ss') LIKE ?", ['%' . $val['value'] . '%']);
                } else {
                    // cek apakah value numeric, tidak pakai upper. jika bukan numeric pakai uppper
                    if (is_numeric($val['value'])) {
                        $query->where($val['property'], 'LIKE', '%' . $val['value'] . '%');
                    } else {
                        $query->whereRaw("UPPER(" . $val['property'] . ") LIKE ?", ['%' . strtoupper($val['value']) . '%']);
                    }
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
        $vfilename = "sumber_data_asset_download_" . $date->format('Y_m_d_H_i_s') . ".xlsx";
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

}
