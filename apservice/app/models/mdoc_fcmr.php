<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Carbon\Carbon;
use DateTime;

class Mdoc_fcmr extends Model
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
        $query = DB::table('fcmr_header as a')
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
        $get_fcmr_header = DB::table('fcmr_header')->where('tbid', '=', $vdata['tbid'])->first();
        return json_encode([
            'success' => 'true',
            'message' => 'Data ditampilkan',
            'vdata' => json_encode((array) $get_fcmr_header)
        ]);
    }
    public static function load_edit_assetno($param)
    {
        $vdata = json_decode($param['vdata'], true);
        $getdata = DB::table('fcmr_detail as a')
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
                'a.asset_remark',
                'a.asset_reason',
                'b.asset_image',
                'a.asset_name_old',
                'a.dept_name_old',
                'a.dept_code_old',
                'a.asset_location_old',
                'a.asset_sublocation_old',
                'a.asset_costcenter_old',
                'a.asset_name_new',
                'a.dept_code_new',
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
        $query = DB::table('fcmr_detail')
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
        $SQLproses = DB::table('fcmr_header')->insertGetId([
            'dokumen_no' => DB::raw("(SELECT generate_fcmr_no('" . $vdata['dept_code'] . "'))"),
            'dokumen_date' => $vdata['dokumen_date'],
            'dept_code' => $vdata['dept_code'],
            'dept_name' => DB::raw("(SELECT deptname FROM vw_department where deptcode='" . $vdata['dept_code'] . "' LIMIT 1)"),
            'syscreateuser' => $param['VUSERLOGIN'],
            'dokumen_remark' => $vdata['dokumen_remark']
        ], 'tbid');
        if ($SQLproses) {
            $getdata = DB::table('fcmr_header')->where('tbid', $SQLproses)->first();
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
        $SQLproses = DB::table('fcmr_header')
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
            $getdata = DB::table('fcmr_header')->where('tbid', $SQLproses)->first();
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

            DB::table('fcmr_header')
                ->where('dokumen_no', $vitem['dokumen_no'])
                ->delete();
            DB::table('fcmr_detail')
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
        $SQLinsert = DB::table('fcmr_detail')->insertGetId([
            'dokumen_no' => $vheader['dokumen_no'],
            'asset_no' => $vitem['asset_no'],
            'asset_control_no' => $vitem['asset_control_no'],
            'asset_sap_no' => $vitem['asset_sap_no'],
            'asset_key' => $vitem['asset_key'],
            'asset_name_old' => $vitem['asset_name_old'],
            'asset_name_new' => $vitem['asset_name_new'],
            'dept_code_new' => $vitem['dept_code_new'],
            'dept_name_new' => DB::raw("(SELECT deptname FROM vw_department where deptcode='" . $vitem['dept_code_new'] . "' LIMIT 1)"),
            'dept_name_old' => $vitem['dept_name_old'],
            'asset_location_old' => $vitem['asset_location_old'],
            'asset_location_new' => $vitem['asset_location_new'],
            'asset_sublocation_old' => $vitem['asset_sublocation_old'],
            'asset_sublocation_new' => $vitem['asset_sublocation_new'],
            'asset_costcenter_old' => $vitem['asset_costcenter_old'],
            'asset_costcenter_new' => $vitem['asset_costcenter_new'],
            'asset_remark' => $vitem['asset_remark'],
            'asset_bc_type' => $vitem['asset_bc_type'],
            'asset_reason' => $vitem['asset_reason'],
            'syscreateuser' => $param['VUSERLOGIN']
        ], 'tbid');
        if ($SQLinsert) {
            $getdata = DB::table('fcmr_detail')->where('tbid', $SQLinsert)->first();
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
        $SQLupdate = DB::table('fcmr_detail')
            ->where('tbid', "=", $vitem["tbid"])
            ->update([
                'dokumen_no' => $vheader['dokumen_no'],
                'asset_no' => $vitem['asset_no'],
                'asset_control_no' => $vitem['asset_control_no'],
                'asset_sap_no' => $vitem['asset_sap_no'],
                'asset_key' => $vitem['asset_key'],
                'asset_name_old' => $vitem['asset_name_old'],
                'asset_name_new' => $vitem['asset_name_new'],
                'dept_code_new' => $vitem['dept_code_new'],
                'dept_name_new' => DB::raw("(SELECT deptname FROM vw_department where deptcode='" . $vitem['dept_code_new'] . "' LIMIT 1)"),
                'dept_name_old' => $vitem['dept_name_old'],
                'asset_location_old' => $vitem['asset_location_old'],
                'asset_location_new' => $vitem['asset_location_new'],
                'asset_sublocation_old' => $vitem['asset_sublocation_old'],
                'asset_sublocation_new' => $vitem['asset_sublocation_new'],
                'asset_costcenter_old' => $vitem['asset_costcenter_old'],
                'asset_costcenter_new' => $vitem['asset_costcenter_new'],
                'asset_remark' => $vitem['asset_remark'],
                'asset_reason' => $vitem['asset_reason'],
                'asset_bc_type' => $vitem['asset_bc_type'],
                'sysupdateuser' => $param['VUSERLOGIN'],
                'sysupdatedate' => DB::raw("(select now())")
            ]);

        if ($SQLupdate > 0) {
            $getdata = DB::table('fcmr_detail')->where('tbid', $vitem["tbid"])->first();
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
            $deletedRows = DB::table('fcmr_detail')
                ->where('dokumen_no', $vitem['dokumen_no'])
                ->where('asset_no', $vitem['asset_no'])
                ->delete();

            if ($deletedRows > 0) {
                return json_encode([
                    'success' => 'true',
                    'message' => 'Data berhasil dihapus'
                ]);
            } else {
                return json_encode([
                    'success' => 'false',
                    'message' => 'Data tidak ditemukan atau sudah dihapus sebelumnya'
                ]);
            }
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
        $SQLproses = DB::table('fcmr_header')
            ->where("tbid", $vdata["tbid"])
            ->update([
                'dokumen_status' => 'POSTING',
                'dokumen_posting_user' => $param['VUSERLOGIN'],
                'dokumen_posting_date' => DB::raw("(select now())"),
            ]);
        if ($SQLproses > 0) {
            $getdata = DB::table('fcmr_header')->where('tbid', $vdata["tbid"])->first();
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
    public static function delete_data($param)
    {
        $vdata = json_decode($param['vdata'], true);
        DB::beginTransaction();

        try {
            //hapus fcmr HEADER
            DB::statement("
                delete from fcmr_header where dokumen_no=?
            ", [$vdata['dokumen_no']]);

            //hapus fcmr DETAIL
            DB::statement("
                delete from fcmr_detail where dokumen_no=?
            ", [$vdata['dokumen_no']]);

            //hapus fcmr SUB DETAIL
            DB::statement("
                delete from fcmr_sub_detail where dokumen_no=?
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
        $vheader = DB::table('fcmr_header')->where('tbid', '=', $vdata['tbid'])->first();
        $vbody = DB::table('fcmr_detail')
            ->where('dokumen_no', '=', $vheader->dokumen_no)->get();

        $html = '
                <div style="float: left; width: 70%">&nbsp;</div>
               <div style="width: 40%; float: right;">
            </div>
                <div style="width: 80%">
                <table style="font-size:14pt; font-weight:bold; border-collapse: collapse; width: 100%;">
                    <tbody>
                        <tr>
                            <td style="text-align: center; padding: 3.5px;width:30%;">
                                <u style="text-decoration:underline;">FIXED ASSET CHANGING - MOVEMENT  REQUISITION<u>
                                <p style="text-decoration:none;">FCMR<p>
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
                                <td style="height: 20px;">FA Form 04-02</td>
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
                            <tr>p
                                <td style="font-weight:normal; padding: 3.5px; width:25%;">DEPT</td>
                                <td style="font-weight:normal; padding: 3.5px; width:5%;">:</td>
                                <td style="padding: 2.5px; width:70%; color:black; font-weight:bold;">' . $vheader->dept_name . '</td>
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
                                <td style="padding: 3px; border: 1px solid black; text-align: center;">DFM/GM</td>
                                <td style="padding: 3px; border: 1px solid black; text-align: center;">FIN</td>
                                <td style="padding: 3px; border: 1px solid black; text-align: center;">MGR</td>
                                <td style="padding: 3px; border: 1px solid black; text-align: center;">SSPV-SPV</td>
                            </tr>
                        </table>
                    </div>

                </div>

                <!-- Checkbox -->
                <div style="margin-top: 15px">
                    <table style="font-size: 10pt; border-collapse: collapse;">
                        <tr>
                            <td colspan="4" style="font-weight: bold">Category Changing Requistion:</td>
                        </tr>
                        <tr>
                            <td colspan="4" style="font-weight: normal; font-size: 8pt">(Mohon untuk di checklis)</td>
                        </tr>
                        <tr>
                            <td>
                                <table style="border: 1px solid; width: 15px; margin-right: 5px; display: inline-block;">
                                    <tr><td style="height: 10px;"></td></tr>
                                </table>
                            </td>
                            <td style="padding-left: 5px;">Jika dimodifikasi merubah bentuk atau nama</td>
                        </tr>
                        <tr>
                            <td>
                                <table style="border: 1px solid; width: 15px; margin-right: 5px; display: inline-block;">
                                    <tr><td style="height: 10px;"></td></tr>
                                </table>
                            </td>
                            <td style="padding-left: 5px;">Jika perubahan PIC Asset</td>
                        </tr>
                        <tr>
                            <td>
                                <table style="border: 1px solid; width: 15px; margin-right: 5px; display: inline-block;">
                                    <tr><td style="height: 10px;"></td></tr>
                                </table>
                            </td>
                            <td style="padding-left: 5px;">Jika perpindahan ke lokasi lain di dalam pabrik / keluar pabrik</td>
                        </tr>
                    </table>
                </div>


                <!-- Data Table -->
                <div style="margin-top: 10px; ">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th rowspan="2" style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 3%;   font-size: 11pt;">No</th>
                            <th colspan="5" style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 40%; font-size: 11pt;  ">Original Fixed Assets</th>
                            <th colspan="5" style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 40%; font-size: 11pt;">New Fixed Assets</th>
                            <th rowspan="2" style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 7.5%; font-size: 11pt; ">Remarks</th>
                            <th rowspan="2" style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 9.5%;  font-size: 11pt;">Reason</th>
                        </tr>
                        <tr>
                            <th style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 18%; font-size: 11pt;">Asset Number</th>
                            <th style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 9%; font-size: 11pt;">Asset Name</th>
                            <th style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 8%; font-size: 11pt;">Dept/PIC</th>
                            <th style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 7%; font-size: 11pt;">Cost Center Old</th>
                            <th style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 7%; font-size: 11pt;">Location Old</th>
                            <th style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 8%; font-size: 11pt;">Asset Number</th>
                            <th style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 9%;">Asset Name</th>
                            <th style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 8%; font-size: 11pt;">Dept/PIC</th>
                            <th style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 7%; font-size: 11pt;">Cost Center New</th>
                            <th style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 7%; font-size: 11pt;">Location New</th>
                        </tr>
                        </thead>

                    <tbody>
                        ';

        $nocount = 1;

        foreach ($vbody as $item) {

            $html .= '
                    <tr>
                        <td style="border: 1px solid #000; padding: 4px; font-size: 8pt;">' . $nocount . '</td>
                        <td style="border: 1px solid #000; padding: 4px; font-size: 8pt;">' . $item->asset_no . '</td>
                        <td style="border: 1px solid #000; padding: 4px; font-size: 8pt;">' . $item->asset_name_old . '</td>
                        <td style="border: 1px solid #000; padding: 4px; font-size: 8pt;">' . $item->dept_name_old . '</td>
                        <td style="border: 1px solid #000; padding: 4px; font-size: 8pt;">' . $item->asset_costcenter_old . '</td>
                        <td style="border: 1px solid #000; padding: 4px; font-size: 8pt;">' . $item->asset_location_old . '</td>
                        <td style="border: 1px solid #000; padding: 4px; font-size: 8pt;">' . $item->asset_no . '</td>
                        <td style="border: 1px solid #000; padding: 4px; font-size: 8pt;">' . $item->asset_name_new . '</td>
                        <td style="border: 1px solid #000; padding: 4px; font-size: 8pt;">' . $item->dept_name_new . '</td>
                        <td style="border: 1px solid #000; padding: 4px; font-size: 8pt;">' . $item->asset_costcenter_new . '</td>
                        <td style="border: 1px solid #000; padding: 4px; font-size: 8pt;">' . $item->asset_location_new . '</td>
                        <td style="border: 1px solid #000; padding: 4px; font-size: 8pt;">' . $item->asset_remark . '</td>
                        <td style="border: 1px solid #000; padding: 4px; font-size: 8pt;">' . $item->asset_reason . '</td>
                    </tr>
                                ';
            $nocount++;
        }



        $html .= '
                    </tbody>
                </table>
                </div>
            </div>
            </div>
        </div>
        </div>
        <!-- Original Notice -->
        <div style="text-align: right; font-style: italic; margin-top: 10px">Original to finance & acc. dept</div>
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
        $mpdf->SetWatermarkText("FCMR");
        $mpdf->showWatermarkText = true;
        $mpdf->watermark_font = 'DejaVuSansCondensed';
        $mpdf->watermarkTextAlpha = 0.23;
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

            $SQLupdate = DB::table('fcmr_detail')
                ->where('tbid', "=", $vdata['tbid'])
                ->update([
                    'asset_image' => $nama_lampiran
                ]);

            if ($SQLupdate > 0) {
                $getdata = DB::table('fcmr_detail')->where('tbid', $vdata["tbid"])->first();
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
    public static function read_remark($param)
    {
        $query = DB::table('cpmatrix')
            ->select('defid', 'defname', 'defcode')
            ->where('defmodule', '=', 'MREMARK');

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
            $query->orderBy('syscreatedate', 'asc');
        }

        $rows = $query->get();
        return json_encode([
            'TotalRows' => $count,
            'Rows' => $rows
        ]);
    }
}
