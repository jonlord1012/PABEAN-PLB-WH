<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use DateTime;

class Mdoc_fir extends Model
{
    public static function handleAction($method, $param)
    {
        switch ($method) {
            case 'read_data':
                return self::read_data($param);
            case 'read_item_part':
                return self::read_item_part($param);
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
        $query = DB::table('fir_header as a')
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
        $get_fir_header = DB::table('fir_header')->where('tbid', '=', $vdata['tbid'])->first();
        return json_encode([
            'success' => 'true',
            'message' => 'Data ditampilkan',
            'vdata' => json_encode((array) $get_fir_header)
        ]);
    }
    public static function load_edit_assetno($param)
    {
        $vdata = json_decode($param['vdata'], true);
        $getdata = DB::table('fir_detail as a')
            ->select([
                'a.tbid',
                'a.dokumen_no',
                'a.asset_no',
                'b.asset_control_no',
                'b.asset_sap_no',
                'b.asset_key',
                'b.asset_name',
                'b.asset_spesification',
                'b.asset_item_group',
                'b.asset_item_category',
                'b.asset_item_subcategory',
                'b.asset_merk',
                'a.asset_bc_type',
                'b.asset_serial_no',
                'b.asset_group',
                'b.asset_category',
                'b.asset_condition',
                'b.asset_location',
                'b.asset_sublocation',
                'b.asset_costcenter',
                'a.asset_reason',
                'a.asset_bookvalue',
                'b.asset_image',
                'a.asset_condition_old',
                'a.asset_location_old',
                'a.asset_sublocation_old',
                'a.asset_costcenter_old',
                'a.asset_condition_new',
                'a.asset_location_new',
                'a.asset_sublocation_new',
                'a.asset_costcenter_new',
            ])
            ->where('a.tbid', '=', $vdata['tbid'])
            ->leftJoin('tr_asset as b', 'b.asset_no', '=', 'a.asset_no')
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
        $query = DB::table('fir_detail')
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

        $rows = $query->get();
        return json_encode([
            'TotalRows' => $count,
            'Rows' => $rows
        ]);
    }
    public static function read_item_part($param)
    {
        $query = DB::table('bapa_detail as A')
            ->select(
                'A.*',
                'A.asset_name AS assetname',
                'A.sub_asset_name AS new_assetname'
            )
            ->where("A.dokumen_no", $param["dokumen_no"]);

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

    public static function process_data($param)
    {
        $data = array(
            0 => $param['vdata'],
            1 => $param['vitem'],
            2 => $param['VUSERLOGIN'],
            3 => $param['vmodule']
        );

        $result = DB::select('SELECT * FROM public.sp_process_doc_fir(?, ?, ?,?)', $data);
        return json_encode($result);
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
        $SQLproses = DB::table('fir_header')->insertGetId([
            'dokumen_no' => DB::raw("(SELECT generate_fir_no('" . $vdata['dept_code'] . "'))"),
            'dokumen_date' => $vdata['dokumen_date'],
            'dept_code' => $vdata['dept_code'],
            'dept_name' => DB::raw("(SELECT deptname FROM vw_department where deptcode='" . $vdata['dept_code'] . "' LIMIT 1)"),
            'syscreateuser' => $param['VUSERLOGIN'],
            'dokumen_remark' => $vdata['dokumen_remark']
        ], 'tbid');
        if ($SQLproses) {
            $getdata = DB::table('fir_header')->where('tbid', $SQLproses)->first();
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
        $SQLproses = DB::table('fir_header')
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
            $getdata = DB::table('fir_header')->where('tbid', $SQLproses)->first();
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

            DB::table('fir_header')
                ->where('dokumen_no', $vitem['dokumen_no'])
                ->delete();
            DB::table('fir_detail')
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
        $SQLinsert = DB::table('fir_detail')->insertGetId([
            'dokumen_no' => $vheader['dokumen_no'],
            'asset_no' => $vitem['asset_no'],
            'asset_name' => $vitem['asset_name'],
            'asset_control_no' => $vitem['asset_control_no'],
            'asset_sap_no' => $vitem['asset_sap_no'],
            'asset_key' => $vitem['asset_key'],
            'asset_condition_old' => $vitem['asset_condition_old'],
            'asset_condition_new' => $vitem['asset_condition_new'],
            'asset_location_old' => $vitem['asset_location_old'],
            'asset_location_new' => $vitem['asset_location_new'],
            'asset_sublocation_old' => $vitem['asset_sublocation_old'],
            'asset_sublocation_new' => $vitem['asset_sublocation_new'],
            'asset_costcenter_old' => $vitem['asset_costcenter_old'],
            'asset_costcenter_new' => $vitem['asset_costcenter_new'],
            'asset_reason' => $vitem['asset_reason'],
            'asset_bookvalue' => $vitem['asset_bookvalue'],
            'asset_bc_type' => $vitem['asset_bc_type'],
            'sysupdateuser' => $param['VUSERLOGIN'],
            'sysupdatedate' => DB::raw("(select now())")
        ], 'tbid');
        if ($SQLinsert) {
            $getdata = DB::table('fir_detail')->where('tbid', $SQLinsert)->first();
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
        $SQLupdate = DB::table('fir_detail')
            ->where('tbid', "=", $vitem["tbid"])
            ->update([
                'dokumen_no' => $vheader['dokumen_no'],
                'asset_no' => $vitem['asset_no'],
                'asset_control_no' => $vitem['asset_control_no'],
                'asset_sap_no' => $vitem['asset_sap_no'],
                'asset_key' => $vitem['asset_key'],
                'asset_condition_old' => $vitem['asset_condition_old'],
                'asset_condition_new' => $vitem['asset_condition_new'],
                'asset_location_old' => $vitem['asset_location_old'],
                'asset_location_new' => $vitem['asset_location_new'],
                'asset_sublocation_old' => $vitem['asset_sublocation_old'],
                'asset_sublocation_new' => $vitem['asset_sublocation_new'],
                'asset_costcenter_old' => $vitem['asset_costcenter_old'],
                'asset_costcenter_new' => $vitem['asset_costcenter_new'],
                'asset_reason' => $vitem['asset_reason'],
                'asset_bookvalue' => $vitem['asset_bookvalue'],
                'asset_bc_type' => $vitem['asset_bc_type'],
                'sysupdateuser' => $param['VUSERLOGIN'],
                'sysupdatedate' => DB::raw("(select now())")
            ]);

        if ($SQLupdate > 0) {
            $getdata = DB::table('fir_detail')->where('tbid', $vitem["tbid"])->first();
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
            DB::table('fir_detail')
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

    public static function dokumen_posting($param)
    {
        $vdata = json_decode($param['vdata'], true);
        $SQLproses = DB::table('fir_header')
            ->where("tbid", $vdata["tbid"])
            ->update([
                'dokumen_status' => 'POSTING',
                'dokumen_posting_user' => $param['VUSERLOGIN'],
                'dokumen_posting_date' => DB::raw("(select now())"),
            ]);
        if ($SQLproses > 0) {
            $getdata = DB::table('fir_header')->where('tbid', $vdata["tbid"])->first();
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

    public static function view_pdf($param)
    {
        $vdata = json_decode($param['vdata'], true);
        $vheader = DB::table('fir_header')->where('tbid', '=', $vdata['tbid'])->first();
        $vbody = DB::table('fir_detail')->where('dokumen_no', '=', value: $vheader->dokumen_no)->get();

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
                        <u style="text-decoration:underline;">FIXED ASSET IDLE REQUISITION<u>
                         <p style="text-decoration:none;">FIR<p>
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
                        <td style="height: 20px;">FA Form 06 - 02</td>
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
                        <td style="padding: 2.5px; width:60%; font-weight:bold;"> ' . $vheader->dept_name . ' </td>
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

        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; margin-top: 10px;">
            <table style=" border-collapse: collapse; width: 100%;">
                <tbody>
                    <tr>
                        <td colspan="6" style="font-weight:bold;">
                            Category Asset Idle :
                        </td>
                    </tr>
                    <tr>
                        <td colspan="6" style="font-size: 8pt;">
                            (Mohon untuk dichecklist)
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                        </td>
                        <td>
                            <table style="border: 1px solid; width: 18px;">
                                <tbody>
                                    <tr>
                                        <td style="height: 15px; text-align:center;">&#10004;</td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                        <td> Pemilik Asset mengetahui asset tersebut tidak akan digunakan lagi (Permanen) </td>
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
                        <td> Asset sudah tidak ada future value (Permanen) </td>
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
                        <td> Asset hanya berpindah sementara dalam jangka waktu 1 bulan atau lebih (Temporer) </td>
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
                        <td> Pemilik Asset tidak betul - betul mengetahui bahwa asset tersebut tidak akan digunakan lagi (Temporer) </td>
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
                        <td> Asset Idle menjadi Asset Aktif kembali </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!--
        ========================================================================================================
        AREA DATA BODY=======================================================================================
        -->

        <table style="border-collapse: collapse; width: 100%; margin-top:20px;">
            <colgroup>
                <col style="width: 3%;">
                <col style="width: 20%;">
                <col style="width: 10%;">
                <col style="width: 10%;">
                <col style="width: 10%;">
                <col style="width: 10%;">
                <col style="width: 12%;">
                <col style="width: 12%;">
                <col style="width: 12%;">
            </colgroup>
            <tbody>
                <tr>
                    <th rowspan="2" style="border: 1px solid #000; padding: 8px; font-weight: normal; width: 4%;">No</th>
                    <th colspan="3" style="border: 1px solid #000; padding: 8px; font-weight: normal; width: 36%;">Fixed Asset</th>
                    <th colspan="4" style="border: 1px solid #000; padding: 8px; font-weight: normal; width: 30%;">Location</th>
                    <th colspan="2" style="border: 1px solid #000; padding: 8px; font-weight: normal; width: 15%;">Kondisi Asset</th>
                    <th rowspan="2" style="border: 1px solid #000; padding: 8px; font-weight: normal; width: 10%;">Reason</th>
                    <th rowspan="2" style="border: 1px solid #000; padding: 8px; font-weight: normal; width: 5%;">Book Value</th>

                </tr>
                <tr>
                <th style="border: 1px solid #000; padding: 8px; font-weight: normal;">Asset Number</th>
                <th style="border: 1px solid #000; padding: 8px; font-weight: normal;">Asset Number Sap</th>
                <th style="border: 1px solid #000; padding: 8px; font-weight: normal;">Asset Name</th>
                <th style="border: 1px solid #000; padding: 8px; font-weight: normal;">Cost Center Old</th>
                <th style="border: 1px solid #000; padding: 8px; font-weight: normal;">Old</th>
                <th style="border: 1px solid #000; padding: 8px; font-weight: normal;">Cost CenterNew</th>
                <th style="border: 1px solid #000; padding: 8px; font-weight: normal;">New</th>
                <th style="border: 1px solid #000; padding: 8px; font-weight: normal;">Old</th>
                <th style="border: 1px solid #000; padding: 8px; font-weight: normal;">New</th>
                </tr>
                    ';

        $nocount = 1;
        $totalBookValue = 0;
        foreach ($vbody as $item) {
            $html .= '
                <tr>
                <td style="border: 1px solid #000; padding: 4px;">' . $nocount . '</td>
                <td style="border: 1px solid #000; padding: 4px; font-size: 8pt;">' . $item->asset_no . '</td>
                <td style="border: 1px solid #000; padding: 4px; font-size: 8pt;">' . $item->asset_sap_no . '</td>
                <td style="border: 1px solid #000; padding: 4px; font-size: 8pt;">' . $item->asset_name . '</td>
                <td style="border: 1px solid #000; padding: 4px; font-size: 8pt;">' . $item->asset_costcenter_old . '</td>
                <td style="border: 1px solid #000; padding: 4px; font-size: 8pt;">' . $item->asset_location_old . '</td>
                <td style="border: 1px solid #000; padding: 4px; font-size: 8pt;">' . $item->asset_costcenter_new . '</td>
                <td style="border: 1px solid #000; padding: 4px; font-size: 8pt;">' . $item->asset_location_new . '</td>
                <td style="border: 1px solid #000; padding: 4px; font-size: 8pt;">' . $item->asset_condition_old . '</td>
                <td style="border: 1px solid #000; padding: 4px; font-size: 8pt;">' . $item->asset_condition_new . '</td>
                <td style="border: 1px solid #000; padding: 4px; font-size: 8pt; width: 10%;">' . $item->asset_reason . '</td>
                <td style="border: 1px solid #000; padding: 4px; font-size: 8pt; width: 5%; text-align: right;">' . number_format($item->asset_bookvalue, 2, '.', ',') . '</td>
                </tr>


                 ';
            $totalBookValue += $item->asset_bookvalue;
            $nocount++;
        }
        $html .= '
         <tr>
                <td colspan="11" style="border: 1px solid #000; padding: 4px; font-weight: bold; text-align: center;">TOTAL</td>
                <td style="border: 1px solid #000; padding: 4px; font-weight: normal;font-size: 8pt; text-align: right;">' . number_format($totalBookValue, 2, '.', ',') . ' </td>
                </tr>
                    </tbody>
                    </table>

                <div style="height:10px;"></div>

      <div style="height:30px;"></div>

        <div style="width: 100%; margin-left:30px;">
          <div style="float: left; width: 80%;">,</div>
          <span style="float: left; width: 20%; font-style: italic; font-weight:bold;">Original to finance & acc. Dept</span>
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
        $mpdf->SetWatermarkText("FIR");
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
}
