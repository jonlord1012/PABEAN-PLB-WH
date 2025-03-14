<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use DateTime;
use Carbon\Carbon;

class Mdoc_bapa extends Model
{
    public static function handleAction(array $param)
    {
        switch ($param['method']) {
            case 'read_data':
                return self::read_data($param);
            case 'read_data_item_asset':
                return self::read_data_item_asset($param);
            case 'load_edit':
                return self::load_edit($param);
            case 'read_select_oldasset':
                return self::read_select_oldasset($param);
            case 'read_select_subasset':
                return self::read_select_subasset($param);
            default:
                return ['error' => 'Action not recognized'];
        }
    }
    public static function read_data($param)
    {
        $query = DB::table('bapa_header as a')
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
    public static function load_edit_dokumen($param)
    {
        $vdata = json_decode($param['vdata'], true);
        $get_bapa_header = DB::table('bapa_header')->where('tbid', '=', $vdata['tbid'])->first();
        return json_encode([
            'success' => 'true',
            'message' => 'Data ditampilkan',
            'vdata' => json_encode((array) $get_bapa_header)
        ]);
    }
    public static function load_edit_assetno($param)
    {
        $vdata = json_decode($param['vdata'], true);
        $getdata = DB::table('bapa_detail as A')
            ->select('A.*', 'B.asset_cost as asset_amount')
            ->leftJoin("tr_asset_finance as B", "B.asset_no", "=", "A.asset_no")
            ->where('A.tbid', '=', $vdata['tbid'])
            ->first();
        return json_encode([
            'success' => 'true',
            'message' => 'Data ditampilkan',
            'vdata' => json_encode((array) $getdata)
        ]);
    }
    public static function read_data_item_asset($param)
    {
        $vdata = json_decode($param['vdata'], true);
        $query = DB::table('bapa_detail')
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
        }

        $rows = $query->get()
            ->map(function ($item) {
                // melakukan mapping untuk setiap item,
                // menghitung semua amount
                $sub_cost = DB::table("bapa_detailsub")
                    ->where('dokumen_no', '=', $item->dokumen_no)
                    ->where('asset_no', '=', $item->asset_no)
                    ->get()
                    ->sum('amount');
                $asset_cost = DB::table("tr_asset_finance")
                    ->where('asset_no', '=', $item->asset_no)
                    ->first()->asset_cost;
                $item->asset_cost = intval($asset_cost ?? 0) + intval($sub_cost ?? 0);
                return $item;
            });

        return json_encode([
            'TotalRows' => $count,
            'Rows' => $rows
        ]);
    }
    public static function read_select_oldasset($param)
    {
        $query = DB::table('tr_asset as a')
            ->select('*')
            ->join('cpuser_department as b', 'a.asset_pic_dept', '=', 'b.userdept')
            ->where('b.userlogin', '=', $param['VUSERLOGIN'])
            ->where('a.asset_status', '=', 'AKTIF');

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
    public static function read_select_subasset($param)
    {
        $query = DB::table('tr_sub_asset')
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
        } else {
            $query->orderBy('syscreatedate', 'desc');
        }

        $rows = $query->get();
        return json_encode([
            'TotalRows' => $count,
            'Rows' => $rows
        ]);
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
        $SQLproses = DB::table('bapa_header')->insertGetId([
            'dokumen_no' => DB::raw("(SELECT generate_bapa_no('" . $vdata['dept_code'] . "'))"),
            'dokumen_date' => $vdata['dokumen_date'],
            'dept_code' => $vdata['dept_code'],
            'dept_name' => DB::raw("(SELECT deptname FROM vw_department where deptcode='" . $vdata['dept_code'] . "' LIMIT 1)"),
            'syscreateuser' => $param['VUSERLOGIN'],
            'dokumen_remark' => $vdata['dokumen_remark']
        ], 'tbid');
        if ($SQLproses) {
            $getdata = DB::table('bapa_header')->where('tbid', $SQLproses)->first();
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
        $SQLproses = DB::table('bapa_header')
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
            $getdata = DB::table('bapa_header')->where('tbid', $SQLproses)->first();
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
    public static function save_item_asset($param)
    {
        $vitem = json_decode($param['vitem'], true);

        // check item  data baru atau data lama
        if ($vitem['tbid'] === 0 || $vitem['tbid'] === null) {
            return self::item_asset_insert($param);
        } else {
            return self::item_asset_update($param);
        }

    }
    public static function item_asset_insert($param)
    {

        $vheader = json_decode($param['vheader'], true);
        $vitem = json_decode($param['vitem'], true);
        $SQLinsert = DB::table('bapa_detail')->insertGetId([
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
            'syscreateuser' => $param['VUSERLOGIN'],
        ], 'tbid');
        if ($SQLinsert) {
            $getdata = DB::table('bapa_detail as A')
                ->select('A.*', 'B.asset_cost as asset_amount')
                ->leftJoin("tr_asset_finance as B", "B.asset_no", "=", "A.asset_no")
                ->where('A.tbid', '=', $SQLinsert)
                ->first();
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
        $SQLupdate = DB::table('bapa_detail')
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
                'sysupdateuser' => $param['VUSERLOGIN'],
                'sysupdatedate' => DB::raw("(select now())")
            ]);

        if ($SQLupdate > 0) {
            $getdata = DB::table('bapa_detail as A')
                ->select('A.*', 'B.asset_cost as asset_amount')
                ->leftJoin("tr_asset_finance as B", "B.asset_no", "=", "A.asset_no")
                ->where('A.tbid', '=', $SQLupdate)
                ->first();
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

            DB::table('bapa_detailsub')
                ->where('dokumen_no', $vitem['dokumen_no'])
                ->where('asset_no', $vitem['asset_no'])
                ->delete();
            DB::table('bapa_detail')
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

            DB::table('bapa_detailsub')->insert($data_insert);

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

            DB::table('bapa_detailsub')->where('tbid', $vitem['tbid'])->delete();

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
    public static function read_data_sub_asset($param)
    {
        $vdata = json_decode($param['vdata'], true);
        $query = DB::table('bapa_detailsub')
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
    public static function dokumen_posting($param)
    {
        $vdata = json_decode($param['vdata'], true);
        $SQLcheck_sub = "
        SELECT A.asset_name
        FROM bapa_detail AS A
        WHERE A.dokumen_no = ?
        AND NOT EXISTS (
            SELECT 1
            FROM bapa_detailsub AS B
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
        $SQLproses = DB::table('bapa_header')
            ->where("tbid", $vdata["tbid"])
            ->update([
                'dokumen_status' => 'POSTING',
                'dokumen_posting_user' => $param['VUSERLOGIN'],
                'dokumen_posting_date' => DB::raw("(select now())"),
            ]);
        if ($SQLproses > 0) {
            $getdata = DB::table('bapa_header')->where('tbid', $vdata["tbid"])->first();
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

            DB::table('bapa_header')
                ->where('dokumen_no', $vitem['dokumen_no'])
                ->delete();
            DB::table('bapa_detailsub')
                ->where('dokumen_no', $vitem['dokumen_no'])
                ->delete();
            DB::table('bapa_detail')
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
    public static function delete_data($param)
    {
        $vdata = json_decode($param['vdata'], true);
        DB::beginTransaction();

        try {
            //hapus bapa HEADER
            DB::statement("
                delete from bapa_header where dokumen_no=?
            ", [$vdata['dokumen_no']]);

            //hapus bapa DETAIL
            DB::statement("
                delete from bapa_detail where dokumen_no=?
            ", [$vdata['dokumen_no']]);

            //hapus bapa SUB DETAIL
            DB::statement("
                delete from bapa_sub_detail where dokumen_no=?
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
        $vheader = DB::table('bapa_header')->where('tbid', '=', $vdata['tbid'])->first();
        $SQL_vbody = "
        SELECT
            A.*,
            string_agg(B.partname, ', ') as partname,
            string_agg(
                TRIM(TRAILING '.' FROM TO_CHAR(B.amount, 'FM999999999.')),
                ', '
            ) AS sub_amount,
            TRIM(TRAILING '.' FROM TO_CHAR(coalesce(sum(B.amount), 0) + coalesce(C.asset_cost, 0), 'FM999999999.')) AS total_amount,
            TRIM(TRAILING '.' FROM TO_CHAR(C.asset_cost, 'FM999999999.')) AS asset_cost
        FROM bapa_detail AS A
        LEFT JOIN bapa_detailsub AS B ON B.asset_no = A.asset_no
        LEFT JOIN tr_asset_finance AS C ON C.asset_no = A.asset_no
        WHERE A.dokumen_no = ?
        GROUP BY A.dokumen_no, A.asset_no, A.asset_name, C.asset_cost
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
                            <u style="text-decoration:underline;">BERITA ACARA PENAMBAHAN ASSET<u>
                            <p style="text-decoration:none;">BAPA<p>
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
                            <td style="height: 20px;">FA Form 09-01</td>
                        </tr>
                    </tbody>
                </table>
                <table style=" border: none; margin-top:10px;">
                    <tbody>
                        <tr>
                            <td style="font-weight:normal; padding: 3.5px; width:25%;">NO</td>
                            <td style="font-weight:normal; padding: 3.5px; width:5%;">:</td>
                            <td style="padding: 2.5px; width:70%; color:blue; font-weight:bold;">' . $vheader->dokumen_no . '</td>
                        </tr>
                        <tr>
                            <td style="font-weight:normal; padding: 3.5px; width:25%;">DATE</td>
                            <td style="font-weight:normal; padding: 3.5px; width:5%;">:</td>
                            <td style="padding: 2.5px; width:70%; color:blue; font-weight:bold;">' . Carbon::parse($vheader->dokumen_date)->format('d/m/Y') . '</td>
                        </tr>
                        <tr>
                            <td style="font-weight:normal; padding: 3.5px; width:25%;">DEPT.</td>
                            <td style="font-weight:normal; padding: 3.5px; width:5%;">:</td>
                            <td style="padding: 2.5px; width:70%; color:blue; font-weight:bold;">' . $vheader->dept_name . '</td>
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


            <!-- Data Table -->
            <div style="border: 1px solid black; margin-top: 15px">
            <p style="color: blue; margin: 5; padding: 0">Diisi oleh User</p>
            <!-- Statement -->
            <div style="margin: 15px 0; line-height: 1.3; padding-top: 8px; padding-left: 15px">Dengan ini menyatakan bahwa pada tanggal <span style="color: blue; font-weight: bold">' . Carbon::parse($vheader->dokumen_date)->format('d/m/Y') . '</span>, telah dilakukan perubahan atas:</div>
            <div style="margin-top: 5px;">
                <div style="width: 100%; overflow: hidden; padding: 0 15px 15px 15px">
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px">
                <thead>
                <tr>
                    <th rowspan="2" style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 3%;">NO</th>
                    <th colspan="6" style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 45%;">ORIGINAL FIXED ASSETS</th>
                    <th colspan="7" style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 50%;">NEW FIXED ASSETS</th>
                </tr>
                <tr>
                    <th style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 15%;">ASSET NAME</th>
                    <th style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 9%;">ASSET NUMBER</th>
                    <th style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 7%;">COST CENTER</th>
                    <th style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 10%;">LOCATION</th>
                    <th style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 7%;">DEPT</th>
                    <th style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 6%;">COST</th>
                    <th style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 15%;">ASSET NAME</th>
                    <th style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 10%;">ASSET NUMBER</th>
                    <th style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 7%;">COST CENTER</th>
                    <th style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 10%;">LOCATION</th>
                    <th style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 7%;">DEPT</th>
                    <th style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 8%;">COST ADDITION</th>
                    <th style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 6%;">TOTAL COST</th>
                </tr>
                </thead>

                <tbody>
                ';

        $nocount = 1;

        foreach ($vbody as $item) {

            $html .= '
                <tr>
                    <td style="border: 1px solid #000; padding: 5px" >' . $nocount . '</td>
                    <td style="border: 1px solid #000; padding: 5px; font-size: 8pt;">' . $item->asset_name . '</td>
                    <td style="border: 1px solid #000; padding: 5px; font-size: 8pt;">' . $item->asset_no . '</td>
                    <td style="border: 1px solid #000; padding: 5px; font-size: 8pt;">' . $item->asset_costcenter . '</td>
                    <td style="border: 1px solid #000; padding: 5px; font-size: 8pt;">' . $item->asset_location . '</td>
                    <td style="border: 1px solid #000; padding: 5px; font-size: 8pt;">' . $vheader->dept_name . '</td>
                    <td style="border: 1px solid #000; padding: 5px; font-size: 8pt; text-align: right;">' . $item->asset_cost . '</td>
                    <td style="border: 1px solid #000; padding: 5px; font-size: 8pt;">' . $item->asset_name . '</td>
                    <td style="border: 1px solid #000; padding: 5px; font-size: 8pt;">140000002464-01/IT</td>
                    <td style="border: 1px solid #000; padding: 5px; font-size: 8pt;">' . $item->asset_costcenter . '</td>
                    <td style="border: 1px solid #000; padding: 5px; font-size: 8pt;">' . $item->asset_location . '</td>
                    <td style="border: 1px solid #000; padding: 5px; font-size: 8pt;">' . $vheader->dept_name . '</td>
                    <td style="border: 1px solid #000; padding: 5px; font-size: 8pt; text-align: right;">' . $item->sub_amount . '</td>
                    <td style="border: 1px solid #000; padding: 5px; font-size: 8pt; text-align: right;">' . $item->total_amount . '</td>
                </tr>
                ';
            $nocount++;
        }

        $html .= '
                </tbody>
            </table>

            <!-- Checkbox -->
            <div style="margin-top: 15px">
                <table style="font-size: 10pt; border-collapse: collapse; width: 22%">
                <tbody>
                    <tr>
                    <td colspan="4">Lampiran :</td>
                    <td>
                        <table style="border: 1px solid; width: 15px">
                        <tbody>
                            <tr>
                            <td style="height: 10px"></td>
                            </tr>
                        </tbody>
                        </table>
                    </td>
                    <td>Gambar/photo asset</td>
                    </tr>
                    <tr>
                    <td colspan="4"></td>
                    <td>
                        <table style="border: 1px solid; width: 15px">
                        <tbody>
                            <tr>
                            <td style="height: 10px"></td>
                            </tr>
                        </tbody>
                        </table>
                    </td>
                    <td>Invoice</td>
                    </tr>
                    <tr>
                    <td colspan="4"></td>
                    <td>
                        <table style="border: 1px solid; width: 15px">
                        <tbody>
                            <tr>
                            <td style="height: 10px"></td>
                            </tr>
                        </tbody>
                        </table>
                    </td>
                    <td>PR & PO</td>
                    </tr>
                    <tr>
                    <td colspan="4"></td>
                    <td>
                        <table style="border: 1px solid; width: 15px">
                        <tbody>
                            <tr>
                            <td style="height: 10px"></td>
                            </tr>
                        </tbody>
                        </table>
                    </td>
                    <td>Cek Fisik</td>
                    </tr>
                </tbody>
                </table>
            </div>
            </div>
            </div>
            </div>
        </div>

        <!-- Directors Comment Box -->
        <div style="width: 100%; border: 1px solid #000; margin-top: 15px; height: 80px; position: relative">
            <p style="color: blue; margin: 5; padding: 0">Diisi oleh finance & accounting</p>

            <div style="width: 100%; padding: 0 15px 15px 15px">
            <p>Dengan ini menyatakan bahwa pada tanggal <span style="color: blue; font-weight: bold">' . Carbon::parse($vheader->dokumen_date)->format('d/m/Y') . '</span>, telah dilakukan penambahan nilai atas asset tersebut.</p>
            <table style="font-size: 10pt; border-collapse: collapse; width: 30%">
                <tbody>
                <tr>
                    <td colspan="10">Lampiran :</td>
                    <td>
                    <table style="border: 1px solid; width: 15px">
                        <tbody>
                        <tr>
                            <td style="height: 10px"></td>
                        </tr>
                        </tbody>
                    </table>
                    </td>
                    <td>Nama & register number asset</td>
                </tr>
                </tbody>
            </table>
            </div>
        </div>
        </div>
    </div>
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
        $mpdf->SetWatermarkText("BAPA");
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

            $datePrefix = date('ymdHis');
            $asset_no = $vdata['asset_no'];

            $groupId = "{$asset_no}-{$datePrefix}";
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();

            $nama_lampiran = "{$groupId}-{$originalName}.{$extension}";
            $lokasi_folder = base_path('../document/images/');
            $file->move($lokasi_folder, $nama_lampiran);

            $SQLupdate = DB::table('bapa_detail')
                ->where('tbid', "=", $vdata['tbid'])
                ->update([
                    'asset_image' => $nama_lampiran
                ]);

            if ($SQLupdate > 0) {
                $getdata = DB::table('bapa_detail')->where('tbid', $vdata["tbid"])->first();
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
