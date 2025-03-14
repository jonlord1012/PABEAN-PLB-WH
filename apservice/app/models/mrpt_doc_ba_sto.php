<?php

namespace App\Models;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use DateTime;
use Throwable;

class Mrpt_doc_ba_sto extends Model
{

    public static function handleAction(array $param)
    {
        switch ($param['method']) {
            case 'read_data':
                return self::read_data($param);
            default:
                return json_encode([
                    'success' => 'false',
                    'message' => 'Method ' . $param['method'] . ' tidak ada'
                ]);
        }
    }
    public static function read_data($param)
    {
        $query = DB::table('ba_sto_header')
            ->select('*')
            ->where('dokumen_status', '=', 'POSTING')
            ->whereNotNull([
                'approval8_user',
                'approval8_date'
            ]);

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
                    // format create date
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
        }

        $rows = $query->get();
        return json_encode([
            'TotalRows' => $count,
            'Rows' => $rows
        ]);
    }

    public static function load_edit_dokumen($param)
    {
        $vdata = json_decode($param['vdata'], true);
        $getdata = DB::table('ba_sto_header')->where('dokumen_no', '=', $vdata['dokumen_no'])->first();
        return json_encode([
            'success' => 'true',
            'message' => 'Data ditampilkan',
            'vdata' => json_encode((array) $getdata)
        ]);
    }
    public static function load_edit_assetno($param)
    {
        $vdata = json_decode($param['vdata'], true);
        $getdata = DB::table('ba_sto_detail')->where('tbid', '=', $vdata['tbid'])->first();
        return json_encode([
            'success' => 'true',
            'message' => 'Data ditampilkan',
            'vdata' => json_encode((array) $getdata)
        ]);
    }

    public static function read_data_item_detail($param)
    {
        $vdata = json_decode($param['vdata'], true);
        $getdata = DB::table('ba_sto_detail')->where(['dokumen_no' => $vdata['dokumen_no'], 'period' => $vdata['period']])->get();
        return json_encode([
            'TotalRows' => $getdata->count(),
            'Rows' => $getdata
        ]);
    }
}
