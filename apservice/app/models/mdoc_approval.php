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

class Mdoc_approval extends Model
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

        $query = DB::table('vw_dokumen_approval')
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
        // sama seperti js, merubah spasi jadi "_"
        $doktype = preg_replace('/\s+/', '_', strtolower($vdata['dokumen_type']));
        $getdata = DB::table($doktype . '_header')->where('dokumen_no', '=', $vdata['dokumen_no'])->first();
        $getdata->dokumen_type = $doktype;
        return json_encode([
            'success' => 'true',
            'message' => 'Data ditampilkan',
            'vdata' => json_encode((array) $getdata)
        ]);
    }
    public static function load_edit_assetno($param)
    {
        $vdata = json_decode($param['vdata'], true);
        // sama seperti js, merubah spasi jadi "_"
        $doktype = preg_replace('/\s+/', '_', strtolower($vdata['dokumen_type']));
        $getdata = DB::table($doktype . '_detail')->where('tbid', '=', $vdata['tbid'])->first();
        $getdata->dokumen_type = $doktype;
        return json_encode([
            'success' => 'true',
            'message' => 'Data ditampilkan',
            'vdata' => json_encode((array) $getdata)
        ]);
    }
    public static function dokumen_approve($param)
    {
        $vdata = json_decode($param['vdata'], true);
        // sama seperti js, merubah spasi jadi "_"
        $doktype = preg_replace('/\s+/', '_', strtolower($vdata['dokumen_type']));
        $getdata = DB::table($doktype . '_header')->where('dokumen_no', '=', $vdata['dokumen_no'])->first();
        if ($getdata) {
            $data = [
                'approval6_user' => $param['VUSERLOGIN'],
                'approval6_date' => new DateTime()
            ];
            DB::table($doktype . '_header')->where('dokumen_no', '=', $vdata['dokumen_no'])->update($data);
            return json_encode([
                'success' => 'true',
                'message' => 'Data berhasil diapprove',
            ]);
        } else {
            return json_encode([
                'success' => 'false',
                'message' => 'Data tidak ditemukan',
            ]);
        }
    }
    public static function dokumen_revise_baab($param)
    {
        $vdata = json_decode($param['vdata'], true);
        $getdata = DB::table('baab_header')->where('dokumen_no', '=', $vdata['dokumen_no'])->first();
        if ($getdata) {
            $data = [
                'dokumen_status' => 'REVISE',
                'dokumen_revise_user' => $param['VUSERLOGIN'],
                'dokumen_revise_date' => new DateTime(),
                'approval1_user' => null,
                'approval1_date' => null,
                'approval2_user' => null,
                'approval2_date' => null,
                'approval3_user' => null,
                'approval3_date' => null,
                'approval4_user' => null,
                'approval4_date' => null,
                'approval5_user' => null,
                'approval5_date' => null,
                'approval6_user' => null,
                'approval6_date' => null,
                'approval7_user' => null,
                'approval7_date' => null,
                'approval8_user' => null,
                'approval8_date' => null,
            ];
            DB::table('baab_header')->where('dokumen_no', '=', $vdata['dokumen_no'])->update($data);
            return json_encode([
                'success' => 'true',
                'message' => 'Data berhasil direvisi',
            ]);
        } else {
            return json_encode([
                'success' => 'false',
                'message' => 'Data tidak ditemukan',
            ]);
        }
    }
}
