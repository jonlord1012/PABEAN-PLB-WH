<?php

namespace App\Models;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Carbon\Carbon;
use DateTime;

class Mdoc_flr extends Model
{
    public static function handleAction(array $param)
    {
        switch ($param['method']) {
            case 'read_data':
                return self::read_data($param);
            case 'read_item_part':
                return self::read_item_part($param);
            case 'read_sumber_data':
                return self::read_sumber_data($param);
            default:
                return json_encode([
                    'success' => 'false',
                    'message' => 'Method ' . $param['method'] . ' tidak ada'
                ]);
        }
    }
    public static function read_data($param)
    {
        $query = DB::table('flr_header as a')
            ->select('a.*')
            ->join('cpuser_department as b', 'a.dept_code', '=', 'b.userdept')
            ->where('b.userlogin', '=', $param['VUSERLOGIN']);

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
        } else {
            $query->orderBy('syscreatedate', 'desc');
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
        $getdata = DB::table('flr_header')->where('tbid', '=', $vdata['tbid'])->first();
        return json_encode([
            'success' => 'true',
            'message' => 'Data ditampilkan',
            'vdata' => json_encode((array) $getdata)
        ]);
    }
    public static function load_edit_assetno($param)
    {
        $vdata = json_decode($param['vdata'], true);
        $getdata = DB::table('flr_detail')->where('tbid', '=', $vdata['tbid'])->first();
        return json_encode([
            'success' => 'true',
            'message' => 'Data ditampilkan',
            'vdata' => json_encode((array) $getdata)
        ]);
    }
    public static function read_data_item_asset($param)
    {
        $vdata = json_decode($param['vdata'], true);
        $query = DB::table('flr_detail')
            ->select('*')
            ->where('dokumen_no', "=", $vdata['dokumen_no']);

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
        } else {
            $query->orderBy('asset_no', "ASC");
        }
        $rows = $query->get();
        return json_encode([
            'TotalRows' => $count,
            'Rows' => $rows
        ]);
    }
    public static function fromlp($param)
    {
        $query = DB::connection('oracle')->table('GRMASTER')  // Menggunakan koneksi Oracle
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
        }

        $rows = $query->get();

        return json_encode([
            'TotalRows' => $count,
            'Rows' => $rows
        ]);

    }
    public static function download_data($param)
    {
        ini_set('max_execution_time', 240);
        $query = DB::table('assetdata')
            ->select('assetinfo', 'assetno', 'assetsapno', 'assetkey', 'assetaquisitiondate', 'assetname', 'assetpic', 'assetgroup', 'assetcategory', 'assetlocation', 'assetsublocation', 'assetcondition', 'assetlabel', 'assetremark', 'assetcostcenter', 'assetcost');

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

        $writer = WriterEntityFactory::createXLSXWriter();

        $date = new DateTime();
        $vfilename = "asset_data_download_" . $date->format('Y_m_d_H_i_s') . ".xlsx";
        $outputFilePath = base_path("z_download/" . $vfilename);

        $writer->openToFile($outputFilePath);

        $firstSheet = $writer->getCurrentSheet();
        $firstSheet->setName('Asset Data');
        $writer->addRow(WriterEntityFactory::createRowFromArray(array_keys((array) $rows[0])));
        foreach ($rows as $data) {
            $cleanData = array_map(function ($item) {
                return trim(str_replace(array("\r", "\n"), '', $item));
            }, (array) $data);
            $writer->addRow(WriterEntityFactory::createRowFromArray((array) $cleanData));
        }

        $writer->setCurrentSheet($firstSheet);

        $writer->close();

        $hasil = [
            'success' => "true",
            'remark' => 'File Download',
            'filename' => 'apservice/z_download/' . $vfilename
        ];

        return json_encode($hasil);
    }
    public static function dokumen_save($param)
    {
        $vdata = json_decode($param['vdata'], true);
        if ($vdata['tbid'] === 0) {
            return self::proses_dokumen_insert($param);
        } else {
            return self::proses_dokumen_update($param);
        }
    }
    public static function proses_dokumen_insert($param)
    {
        $vdata = json_decode($param['vdata'], true);
        if ($vdata['dept_code'] === '') {
            return json_encode([
                'success' => 'false',
                'message' => 'Data gagal disimpan, pilih department lebih dulu',
                'vdata' => null
            ]);
        }
        $SQLproses = DB::table('flr_header')->insertGetId([
            'dokumen_no' => DB::raw("(SELECT generate_flr_no('" . $vdata['dept_code'] . "'))"),
            'dokumen_date' => $vdata['dokumen_date'],
            'dept_code' => $vdata['dept_code'],
            'dept_name' => DB::raw("(SELECT deptname FROM vw_department where deptcode='" . $vdata['dept_code'] . "' LIMIT 1)"),
            'syscreateuser' => $param['VUSERLOGIN'],
            'dokumen_remark' => $vdata['dokumen_remark']
        ], 'tbid');
        if ($SQLproses) {
            $getdata = DB::table('flr_header')->where('tbid', $SQLproses)->first();
            return json_encode([
                'success' => 'true',
                'message' => 'Data berhasil disimpan',
                'vdata' => json_encode((array) $getdata)
            ]);
        } else {
            return json_encode([
                'success' => 'false',
                'message' => 'Data gagal disimpan',
                'vdata' => null
            ]);
        }

    }
    public static function proses_dokumen_update($param)
    {
        $vdata = json_decode($param['vdata'], true);
        if ($vdata['dept_code'] === '') {
            return json_encode([
                'success' => 'false',
                'message' => 'Data gagal disimpan, pilih department lebih dulu',
                'vdata' => null
            ]);
        }
        $SQLproses = DB::table('flr_header')
            ->where("tbid", $vdata["tbid"])
            ->update([
                'dokumen_no' => $vdata['dokumen_no'],
                'dokumen_date' => $vdata['dokumen_date'],
                'dept_code' => $vdata['dept_code'],
                'dept_name' => DB::raw("(SELECT deptname FROM vw_department where deptcode='" . $vdata['dept_code'] . "' LIMIT 1)"),
                'dokumen_remark' => $vdata['dokumen_remark'],
                'sysupdateuser' => $param['VUSERLOGIN'],
                'sysupdatedate' => DB::raw("(select now())"),
            ]);
        if ($SQLproses > 0) {
            $getdata = DB::table('flr_header')->where('tbid', $SQLproses)->first();
            return json_encode([
                'success' => 'true',
                'message' => 'Data berhasil disimpan',
                'vdata' => json_encode((array) $getdata)
            ]);
        } else {
            return json_encode([
                'success' => 'false',
                'message' => 'Data gagal disimpan',
                'vdata' => null
            ]);
        }
    }
    public static function dokumen_posting($param)
    {
        $vdata = json_decode($param['vdata'], true);
        $SQLcheck_sub = "
        SELECT A.asset_name
        FROM flr_detail AS A
        WHERE A.dokumen_no = ?
        AND NOT EXISTS (
            SELECT 1
            FROM flr_detailsub AS B
            WHERE B.dokumen_no = A.dokumen_no
            AND B.asset_no = A.asset_no
        )
        LIMIT 1
    ";

        $hasNoSubAssets = DB::select($SQLcheck_sub, [$vdata['dokumen_no']]);

        if (!empty($hasNoSubAssets)) {
            return json_encode([
                'success' => 'false',
                'message' => 'Asset: "' . $hasNoSubAssets[0]->asset_name . '" belum memiliki sub asset',
                'vdata' => null
            ]);
        }

        $SQLproses = DB::table('flr_header')
            ->where("tbid", $vdata["tbid"])
            ->update([
                'dokumen_status' => 'POSTING',
                'dokumen_posting_user' => $param['VUSERLOGIN'],
                'dokumen_posting_date' => DB::raw("(select now())"),
            ]);
        if ($SQLproses > 0) {
            $getdata = DB::table('flr_header')->where('tbid', $vdata["tbid"])->first();
            return json_encode([
                'success' => 'true',
                'message' => 'Data berhasil disimpan',
                'vdata' => json_encode((array) $getdata)
            ]);
        } else {
            return json_encode([
                'success' => 'false',
                'message' => 'Data gagal disimpan',
                'vdata' => null
            ]);
        }
    }
    public static function dokumen_delete($param)
    {
        $vitem = json_decode($param['vdata'], true);
        try {

            DB::table('flr_header')
                ->where('dokumen_no', $vitem['dokumen_no'])
                ->delete();
            DB::table('flr_detailsub')
                ->where('dokumen_no', $vitem['dokumen_no'])
                ->delete();
            DB::table('flr_detail')
                ->where('dokumen_no', $vitem['dokumen_no'])
                ->delete();

            return json_encode([
                'success' => 'true',
                'message' => 'Dokumen berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'success' => 'false',
                'message' => $e->getMessage()
            ]);
        }
    }
    public static function save_item_asset($param)
    {
        $vitem = json_decode($param['vitem'], true);

        // check item  data baru atau data lama
        if ($vitem['tbid'] === 0) {
            return self::item_asset_insert($param);
        } else {
            return self::item_asset_update($param);
        }

    }
    public static function item_asset_insert($param)
    {

        $vheader = json_decode($param['vheader'], true);
        $vitem = json_decode($param['vitem'], true);
        $SQLinsert = DB::table('flr_detail')->insertGetId([
            'dokumen_no' => $vheader['dokumen_no'],
            'asset_no' => DB::raw("(SELECT '9' || LPAD((COALESCE(MAX(tbid), 0) + 1)::TEXT, 9, '0') AS hasil FROM flr_detail)"),
            'asset_control_no' => $vitem['asset_control_no'],
            'asset_sap_no' => $vitem['asset_sap_no'],
            'asset_key' => $vitem['asset_key'],
            'asset_name' => $vitem['asset_name'],
            'asset_spesification' => $vitem['asset_spesification'],
            'asset_group' => $vitem['asset_group'],
            'asset_category' => $vitem['asset_category'],
            'asset_condition' => $vitem['asset_condition'],
            'asset_location' => $vitem['asset_location'],
            'asset_sublocation' => $vitem['asset_sublocation'],
            'asset_costcenter' => $vitem['asset_costcenter'],
            'asset_item_group' => $vitem['asset_item_group'],
            'asset_item_category' => $vitem['asset_item_category'],
            'asset_item_subcategory' => $vitem['asset_item_subcategory'],
            'asset_merk' => $vitem['asset_merk'],
            'asset_bc_type' => $vitem['asset_bc_type'],
            'asset_serial_no' => $vitem['asset_serial_no'],
            'asset_amount' => $vitem['asset_amount'],
            'asset_kontrak_no' => $vitem['asset_kontrak_no'],
            'asset_tglsewa_start' => $vitem['asset_tglsewa_start'],
            'asset_tglsewa_end' => $vitem['asset_tglsewa_end'],
            'asset_pv_hak_guna' => isset($vitem['asset_pv_hak_guna']) ? $vitem['asset_pv_hak_guna'] : 0,
            'syscreateuser' => $param['VUSERLOGIN'],
        ], 'tbid');
        if ($SQLinsert) {
            $getdata = DB::table('flr_detail')->where('tbid', $SQLinsert)->first();
            return json_encode([
                'success' => 'true',
                'message' => 'Data berhasil disimpan',
                'vdata' => json_encode((array) $getdata)
            ]);
        } else {
            return json_encode([
                'success' => 'false',
                'message' => 'Data gagal disimpan',
                'vdata' => null
            ]);
        }
    }
    public static function item_asset_update($param)
    {

        $vheader = json_decode($param['vheader'], true);
        $vitem = json_decode($param['vitem'], true);
        $SQLupdate = DB::table('flr_detail')
            ->where('tbid', "=", $vitem["tbid"])
            ->update([
                'dokumen_no' => $vheader['dokumen_no'],
                'asset_no' => $vitem['asset_no'],
                'asset_control_no' => $vitem['asset_control_no'],
                'asset_sap_no' => $vitem['asset_sap_no'],
                'asset_key' => $vitem['asset_key'],
                'asset_name' => $vitem['asset_name'],
                'asset_spesification' => $vitem['asset_spesification'],
                'asset_group' => $vitem['asset_group'],
                'asset_category' => $vitem['asset_category'],
                'asset_condition' => $vitem['asset_condition'],
                'asset_location' => $vitem['asset_location'],
                'asset_sublocation' => $vitem['asset_sublocation'],
                'asset_costcenter' => $vitem['asset_costcenter'],
                'asset_item_group' => $vitem['asset_item_group'],
                'asset_item_category' => $vitem['asset_item_category'],
                'asset_item_subcategory' => $vitem['asset_item_subcategory'],
                'asset_merk' => $vitem['asset_merk'],
                'asset_bc_type' => $vitem['asset_bc_type'],
                'asset_serial_no' => $vitem['asset_serial_no'],
                'asset_amount' => $vitem['asset_amount'],
                'asset_kontrak_no' => $vitem['asset_kontrak_no'],
                'asset_tglsewa_start' => $vitem['asset_tglsewa_start'],
                'asset_tglsewa_end' => $vitem['asset_tglsewa_end'],
                'asset_pv_hak_guna' => isset($vitem['asset_pv_hak_guna']) ? $vitem['asset_pv_hak_guna'] : 0,
                'sysupdateuser' => $param['VUSERLOGIN'],
                'sysupdatedate' => DB::raw("(select now())")
            ]);

        if ($SQLupdate > 0) {
            $getdata = DB::table('flr_detail')->where('tbid', $vitem["tbid"])->first();
            return json_encode([
                'success' => 'true',
                'message' => 'Data berhasil diupdate',
                'vdata' => json_encode((array) $getdata)
            ]);
        } else {
            return json_encode([
                'success' => 'false',
                'message' => 'Data gagal disimpan',
                'vdata' => null
            ]);
        }
    }
    public static function item_asset_delete($param)
    {
        $vitem = json_decode($param['vdata'], true);
        try {

            DB::table('flr_detailsub')
                ->where('dokumen_no', $vitem['dokumen_no'])
                ->where('asset_no', $vitem['asset_no'])
                ->delete();
            DB::table('flr_detail')
                ->where('dokumen_no', $vitem['dokumen_no'])
                ->where('asset_no', $vitem['asset_no'])
                ->delete();

            return json_encode([
                'success' => 'true',
                'message' => 'Data berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'success' => 'false',
                'message' => $e->getMessage()
            ]);
        }
    }
    public static function subitem_asset_insert($param)
    {

        $vheader = json_decode($param['vheader'], true);
        $vitem = json_decode($param['vitem'], true);


        try {
            $data_insert = [];
            foreach ($vitem as $item) {
                $data_insert[] = [
                    'dokumen_no' => $vheader['dokumen_no'],
                    'asset_no' => $vheader['asset_no'],
                    'sumber_data' => $item['sumber_data'],
                    'partcode' => $item['partcode'],
                    'partname' => $item['partname'],
                    'qty' => 1,
                    'currency' => $item['part_currency'],
                    'amount' => $item['part_price'],
                    'po_no' => $item['ponumber'],
                    'po_date' => $item['podate'],
                    'gr_no' => $item['grnumber'],
                    'gr_date' => $item['grdate'],
                    'invoice_no' => $item['invnumber'],
                    'invoice_date' => $item['invdate'],
                    'pr_no' => $item['prnumber'],
                    'pr_date' => $item['prdate']
                ];
            }

            DB::table('flr_detailsub')->insert($data_insert);

            return json_encode([
                'success' => 'true',
                'message' => 'Data berhasil disimpan'
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'success' => 'false',
                'message' => $e->getMessage()
            ]);
        }

    }
    public static function subitem_asset_delete($param)
    {

        $vitem = json_decode($param['vdata'], true);
        try {

            DB::table('flr_detailsub')->where('tbid', $vitem['tbid'])->delete();

            return json_encode([
                'success' => 'true',
                'message' => 'Data berhasil disimpan'
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'success' => 'false',
                'message' => $e->getMessage()
            ]);
        }

    }
    public static function read_sumber_data($param)
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
    public static function read_data_sub_asset($param)
    {
        $vdata = json_decode($param['vdata'], true);
        $query = DB::table('flr_detailsub')
            ->select('*')
            ->where("dokumen_no", $vdata['dokumen_no'])
            ->where("asset_no", $vdata['asset_no']);


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

    public static function delete_data($param)
    {
        $vdata = json_decode($param['vdata'], true);
        DB::beginTransaction();

        try {
            //hapus flr HEADER
            DB::statement("
                delete from flr_header where dokumen_no=?
            ", [$vdata['dokumen_no']]);

            //hapus flr DETAIL
            DB::statement("
                delete from flr_detail where dokumen_no=?
            ", [$vdata['dokumen_no']]);

            //hapus flr SUB DETAIL
            DB::statement("
                delete from flr_sub_detail where dokumen_no=?
            ", [$vdata['dokumen_no']]);

            DB::commit();
            return json_encode([
                'success' => 'true',
                'message' => 'Data berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return json_encode([
                'success' => 'false',
                'message' => 'Dokumen gagal dihapus: ' . $e->getMessage()
            ]);
        }
    }
    public static function view_pdf($param)
    {
        $vdata = json_decode($param['vdata'], true);
        $vheader = DB::table('flr_header')->where('tbid', '=', $vdata['tbid'])->first();
        $SQL_vbody = "
            SELECT
                A.*,
                TRIM(TRAILING '.' FROM TRIM(TRAILING '0' FROM TO_CHAR(A.asset_amount, 'FM999999999.9999'))) AS asset_amount,
                TRIM(TRAILING '.' FROM TRIM(TRAILING '0' FROM TO_CHAR(A.asset_pv_hak_guna, 'FM999999999.9999'))) AS asset_pv_hak_guna
            FROM flr_detail AS A
            WHERE A.dokumen_no = ?
            GROUP BY A.dokumen_no, A.asset_no, A.asset_name, A.asset_amount, A.asset_pv_hak_guna
        ";

        $vbody = DB::select($SQL_vbody, [$vheader->dokumen_no]);

        $html = '
        <style>
        </style>

        <div style="float: left; width: 70%">&nbsp;</div>
        <div style="height:10px;"></div>
        <div style="width: 80%">
        <table style="font-size:14pt; font-weight:bold; border-collapse: collapse; width: 100%;">
            <tbody>
                <tr>
                    <td style="text-align: center; padding: 3.5px;width:30%;">
                        <u style="text-decoration:underline;">FORM LEASE REQUISITION<u>
                         <p style="text-decoration:none;">FLR<p>
                    </td>
                </tr>
            </tbody>
        </table>
        </div>


        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-top: 10px; gap: 20px;">
            <div style="float: left; width: 60%">
             <table  style="border: 1px solid; float: left;">
                <tbody>
                    <tr>
                        <td style="height: 20px;">FA Form 08-01</td>
                    </tr>
                </tbody>
            </table>
            <table style=" border: none; margin-top:10px;">
                <tbody>
                    <tr>
                        <td style="font-weight:normal; padding: 3.5px; width:35%;">NO</td>
                        <td style="font-weight:normal; padding: 3.5px; width:5%;">:</td>
                        <td style="padding: 2.5px; width:60%; color:blue; font-weight:bold;">' . $vheader->dokumen_no . '</td>
                    </tr>
                    <tr>
                        <td style="font-weight:normal; padding: 3.5px; width:35%;">DATE</td>
                        <td style="font-weight:normal; padding: 3.5px; width:5%;">:</td>
                        <td style="padding: 2.5px; width:60%; color:blue; font-weight:bold;">' . Carbon::parse($vheader->dokumen_date)->format('d/m/Y') . '</td>
                    </tr>
                    <tr>
                        <td style="font-weight:normal; padding: 3.5px; width:35%;">DEPT</td>
                        <td style="font-weight:normal; padding: 3.5px; width:5%;">:</td>
                        <td style="padding: 2.5px; width:60%; font-weight:bold;">' . $vheader->dept_name . '</td>
                    </tr>
                </tbody>
            </table>
            </div>
            <div style="float: left; width: 40%">
                <table style="width: 100%; border-collapse: collapse; float: left;">
                    <tr>
                        <td style="padding: 3px; border: 1px solid black; text-align: center;">Approved</td>
                        <td style="padding: 3px; border: 1px solid black; text-align: center;">Verified</td>
                        <td style="padding: 3px; border: 1px solid black; text-align: center;">Checked</td>
                        <td style="padding: 3px; border: 1px solid black; text-align: center;">Prepared</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border: 1px solid black; text-align: center; height: 50px; width: 25%;"></td>
                        <td style="padding: 10px; border: 1px solid black; text-align: center; height: 50px; width: 25%;"></td>
                        <td style="padding: 10px; border: 1px solid black; text-align: center; height: 50px; width: 25%;"></td>
                        <td style="padding: 10px; border: 1px solid black; text-align: center; height: 50px; width: 25%;"></td>
                    </tr>
                    <tr>
                        <td style="padding: 3px; border: 1px solid black; text-align: center;">DIR/FM</td>
                        <td style="padding: 3px; border: 1px solid black; text-align: center;">FIN</td>
                        <td style="padding: 3px; border: 1px solid black; text-align: center;">DFM/GM-MGR</td>
                        <td style="padding: 3px; border: 1px solid black; text-align: center;">SSPV-SPV</td>
                    </tr>
                </table>
            </div>

        </div>

        <!--
        ========================================================================================================
        AREA DATA BODY=======================================================================================
        -->
        <div style="border: 1px solid; margin-top: 20px; ">
            <p style="font-style: italic; color:blue; margin:1;">Diisi oleh PIC Aset tetap/Aset Owner</p>
            <div style="padding: 10px;">
                <p style="margin:0; margin-left:30px; ">Dengan ini menyatakan bahwa pada tanggal <span style="color:blue;">DD/MM/YYYY</span>, telah diterima dan dilakukan pemeriksaan atas :</p>
                <table style="border-collapse: collapse; width: 100%;">
                    <tbody>
                        <tr>
                        <th style="border: 1px solid #000; padding: 4px; font-weight: normal;font-size: 9pt;">No</th>
                        <th style="border: 1px solid #000; padding: 4px; font-weight: normal;font-size: 9pt;">Kategori Asset</th>
                        <th style="border: 1px solid #000; padding: 4px; font-weight: normal;font-size: 9pt;">Nama Asset Sewa Hak Guna</th>
                        <th style="border: 1px solid #000; padding: 4px; font-weight: normal;font-size: 9pt;">No. Kontrak</th>
                        <th style="border: 1px solid #000; padding: 4px; font-weight: normal;font-size: 9pt;">Nilai Asset</th>
                        <th style="border: 1px solid #000; padding: 4px; font-weight: normal;font-size: 9pt;">Periode Sewa</th>
                        <th style="border: 1px solid #000; padding: 4px; font-weight: normal;font-size: 9pt;">Cost Center</th>
                        <th style="border: 1px solid #000; padding: 4px; font-weight: normal;font-size: 9pt;">Lokasi</th>
                        <th style="border: 1px solid #000; padding: 4px; font-weight: normal;font-size: 9pt;">Present Value Sewa Hak Guna</th>
                        <th style="border: 1px solid #000; padding: 4px; font-weight: normal;font-size: 9pt;">Asset No</th>
                        <th style="border: 1px solid #000; padding: 4px; font-weight: normal;font-size: 9pt;">Masa Pakai (Tahun)</th>
                        </tr>

                        ';

        $nocount = 1;

        foreach ($vbody as $item) {

            $html .= '
                    <tr>
                        <td style="border: 1px solid #000; padding: 4px; font-size: 8pt; width: 4%;">' . $nocount . '</td>
                        <td style="border: 1px solid #000; padding: 4px; font-size: 8pt; width: 12%;">' . $item->asset_category . '</td>
                        <td style="border: 1px solid #000; padding: 4px; font-size: 8pt; width: 20%;">' . $item->asset_name . '</td>
                        <td style="border: 1px solid #000; padding: 4px; font-size: 8pt; width: 12%;">' . $item->asset_kontrak_no . '</td>
                        <td style="border: 1px solid #000; padding: 4px; font-size: 8pt; width: 7%; text-align: right;">' . $item->asset_amount . '</td>
                        <td style="border: 1px solid #000; padding: 4px; font-size: 8pt; width: 15%;"> ' . $item->asset_tglsewa_start . ' - ' . $item->asset_tglsewa_end . '  </td>
                        <td style="border: 1px solid #000; padding: 4px; font-size: 8pt; width: 8%;"> ' . $item->asset_costcenter . '</td>
                        <td style="border: 1px solid #000; padding: 4px; font-size: 8pt; width: 6%;"> ' . $item->asset_location . '</td>
                        <td style="border: 1px solid #000; padding: 4px; font-size: 8pt; width: 10%; text-align: right;">' . $item->asset_pv_hak_guna . '</td>
                        <td style="border: 1px solid #000; padding: 4px; font-size: 8pt; width: 10%;">  ' . $item->asset_sap_no . ' </td>
                        <td style="border: 1px solid #000; padding: 4px; font-size: 8pt; width: 6%; text-align: right;">  ' . $item->asset_masa_pakai . '  </td>
                    </tr>
                    ';
            $nocount++;
        }

        $html .= '
                    </tbody>
                </table>

                <div style="height:20px;"></div>


                <!--
                ========================================================================================================
                AREA DATA FOOTER=======================================================================================
                -->

                <div style="width: 100%">
                    <div style="float:left; width: 40%;">
                        <table style=" border-collapse: collapse;">
                            <tbody>
                                <tr>
                                    <td colspan="4">
                                        Sesuai Dengan Identifikasi :
                                    </td>
                                    <td>
                                        <table style="border: 1px solid; width: 18px;">
                                            <tbody>
                                                <tr>
                                                    <td style="height: 15px; text-align:center;"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                    <td> Asset Sewa Hak Guna Baru </td>
                                </tr>
                                <tr>
                                    <td colspan="4">
                                    </td>
                                    <td>
                                        <table style="border: 1px solid; width: 18px;">
                                            <tbody>
                                                <tr>
                                                    <td style="height: 15px; text-align:center;"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                    <td> Asset Sewa Hak Guna Modifikasi </td>
                                </tr>
                                <tr>
                                    <td colspan="4">
                                    </td>
                                    <td>
                                        <table style="border: 1px solid; width: 18px;">
                                            <tbody>
                                                <tr>
                                                    <td style="height: 15px; text-align:center;"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                    <td> Asset Sewa Hak Guna Disposal </td>
                                </tr>
                                <tr>
                                    <td colspan="4">
                                    </td>
                                    <td>
                                        <table style="border: 1px solid; width: 18px;">
                                            <tbody>
                                                <tr>
                                                    <td style="height: 15px; text-align:center;"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                    <td>Lebih dari Setahun </td>
                                </tr>
                                <tr>
                                    <td colspan="4">
                                    </td>
                                    <td>
                                        <table style="border: 1px solid; width: 18px;">
                                            <tbody>
                                                <tr>
                                                    <td style="height: 15px; text-align:center;"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                    <td> Lebih dari USD 5000 </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div style="float:left;width: 40%;">
                        <table style=" border-collapse: collapse;">
                            <tbody>
                                <tr>
                                    <td colspan="4">
                                        Lampiran :
                                    </td>
                                    <td>
                                        <table style="border: 1px solid; width: 18px;">
                                            <tbody>
                                                <tr>
                                                    <td style="height: 15px; text-align:center;"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                    <td> Kontrak Sewa </td>
                                </tr>
                                <tr>
                                    <td colspan="4">
                                    </td>
                                    <td>
                                        <table style="border: 1px solid; width: 18px;">
                                            <tbody>
                                                <tr>
                                                    <td style="height: 15px; text-align:center;"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                    <td> PR </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

         <!--
        ========================================================================================================
        AREA DATA FOOTER=======================================================================================
        -->

        <div style="border: 1px solid; margin-top: 20px;">
            <p style="font-style: italic; color:blue; margin:1px;">Diisi oleh finance & accounting</p>
            <div style="padding: 10px;">
                <p style="margin:0; margin-left:30px;">Dengan ini menyatakan bahwa pada tanggal <span style="color:blue;">DD/MM/YYYY</span>, telah dilakukan pengecekan kontrak sewa.</p>
                <div style="width: 100%; margin-left:30px;">
                    <div style="float: left; width: 30%;">Dengan Asset No.</div>
                    <span style="float: left; width: 20%; margin-left: 70px; padding: 5px; background-color:yellow;">214297812</span>
                    <div style="clear: both;"></div>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 20px;margin-top: 20px;">
                    <table style=" border-collapse: collapse; width: 30%;">
                        <tbody>
                            <tr>
                                <td colspan="4">
                                    Lampiran :
                                </td>
                                <td>
                                    <table style="border: 1px solid; width: 18px;">
                                        <tbody>
                                            <tr>
                                                <td style="height: 15px; text-align:center;"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                                <td> Nama & register asset key </td>
                            </tr>
                            <tr>
                                <td colspan="4">
                                </td>
                                <td>
                                    <table style="border: 1px solid; width: 18px;">
                                        <tbody>
                                            <tr>
                                                <td style="height: 15px; text-align:center;"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                                <td> Pembuatan Label </td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div style="height:30px;"></div>

        <div style="width: 100%; margin-left:30px;">
            <div style="float: left; width: 70%;">,</div>
            <span style="float: left; width: 30%; font-style: italic; font-weight:bold;">Original to finance & acc. Dept</span>
            <div style="clear: both;"></div>
        </div>
        <div style="width: 100%; margin-left:30px;">
            <div style="float: left; width: 70%;">,</div>
            <span style="float: left; width: 30%; font-style: italic; font-weight:bold;">Copy to dept PIC Aset Tetap/Aset Owner</span>
            <div style="clear: both;"></div>
        </div>
        ';


        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'c',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 5,
            'margin_bottom' => 10,
            'default_font_size' => 10,
            'default_font' => 'helvetica'
        ]);


        $mpdf->AddPage('L');
        // Menambahkan teks watermark
        $mpdf->SetWatermarkText("FLR");
        $mpdf->showWatermarkText = true;
        $mpdf->watermark_font = 'DejaVuSansCondensed';
        $mpdf->watermarkTextAlpha = 0.23;

        // Mengatur mode tampilan PDF ke 'fullpage'
        $mpdf->SetDisplayMode('fullpage');

        $mpdf->WriteHTML($html);




        // Output PDF ke variabel base64
        ob_start();
        $mpdf->Output();
        $pdfData = ob_get_contents();
        ob_end_clean();

        // Konversi data PDF ke base64
        $base64Data = base64_encode($pdfData);

        $hasil = array(
            'pdf' => $base64Data
        );
        return json_encode($hasil);
    }
    public static function uploadfile($param)
    {
        if (isset($param['file']) && $param['file'] instanceof \Illuminate\Http\UploadedFile) {
            $file = $param['file'];
            $vdata = json_decode($param['params'], true)['vdata'];

            $fileSize = $file->getSize();
            $maxSize = 2 * 1024 * 1024;

            $datePrefix = date('ymdHis');
            $asset_no = $vdata['asset_no'];

            $groupId = "{$asset_no}-{$datePrefix}";
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();

            $nama_lampiran = "{$groupId}-{$originalName}.{$extension}";
            $lokasi_folder = base_path('../document/images/');
            $full_path = $lokasi_folder . $nama_lampiran;

            // hapus file jika sudah ada
            // kode bikin lambat, dikomen dulu
            // $existingRecord = DB::table('flr_detail')->where('tbid', '=', $vdata['tbid'])->first();
            // if ($existingRecord && !empty($existingRecord->asset_image)) {
            //     $existingFilePath = $lokasi_folder . $existingRecord->asset_image;
            //     if (file_exists($existingFilePath)) {
            //         unlink($existingFilePath);
            //     }
            // }

            if ($fileSize <= $maxSize) {
                // jika file size kurang dari 2 mb, skip
                $file->move($lokasi_folder, $nama_lampiran);
            } else {
                // jike file size lebih dari 2 mb, compress
                try {
                    switch ($extension) {
                        case 'jpeg':
                        case 'jpg':
                            $source = imagecreatefromjpeg($file->getRealPath());
                            break;
                        case 'png':
                            $source = imagecreatefrompng($file->getRealPath());
                            break;
                        case 'gif':
                            $source = imagecreatefromgif($file->getRealPath());
                            break;
                        default:
                            return json_encode([
                                'success' => 'false',
                                'message' => 'format file tidak support',
                            ]);
                    }

                    $originalWidth = imagesx($source);
                    $originalHeight = imagesy($source);

                    $scale = sqrt($maxSize / $fileSize);
                    $newWidth = round($originalWidth * $scale);
                    $newHeight = round($originalHeight * $scale);

                    $compressed = imagecreatetruecolor($newWidth, $newHeight);

                    if ($extension == 'png') {
                        imagealphablending($compressed, false);
                        imagesavealpha($compressed, true);
                        $transparent = imagecolorallocatealpha($compressed, 255, 255, 255, 127);
                        imagefilledrectangle($compressed, 0, 0, $newWidth, $newHeight, $transparent);
                    }

                    imagecopyresampled(
                        $compressed,
                        $source,
                        0,
                        0,
                        0,
                        0,
                        $newWidth,
                        $newHeight,
                        $originalWidth,
                        $originalHeight
                    );

                    $qualities = [75, 50, 25];
                    foreach ($qualities as $quality) {
                        imagejpeg($compressed, $full_path, $quality);

                        $compressedSize = filesize($full_path);
                        if ($compressedSize <= $maxSize) {
                            break;
                        }
                    }

                    imagedestroy($source);
                    imagedestroy($compressed);

                } catch (\Exception $e) {
                    return json_encode([
                        'success' => 'false',
                        'message' => 'Gagal mengkompresi gambar: ' . $e->getMessage(),
                        'vdata' => null
                    ]);
                }
            }

            $SQLupdate = DB::table('flr_detail')
                ->where('tbid', "=", $vdata['tbid'])
                ->update([
                    'asset_image' => $nama_lampiran
                ]);

            if ($SQLupdate > 0) {
                $getdata = DB::table('flr_detail')->where('tbid', $vdata["tbid"])->first();
                return json_encode([
                    'success' => 'true',
                    'message' => 'Image berhasil diupdate',
                    'vdata' => json_encode((array) $getdata)
                ]);
            } else {
                return json_encode([
                    'success' => 'false',
                    'message' => 'Image gagal disimpan',
                    'vdata' => null
                ]);
            }


        } else {
            $hasil = [
                'success' => 'false',
                'message' => 'upload dokumen gagal',
                'lampiran' => null
            ];
        }
        return json_encode($hasil);

    }

}
