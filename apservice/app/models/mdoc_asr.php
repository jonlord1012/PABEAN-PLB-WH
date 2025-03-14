<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use DateTime;
use Carbon\Carbon;

class Mdoc_asr extends Model
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
        $query = DB::table('asr_header as a')
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
        $get_asr_header = DB::table('asr_header')->where('tbid', '=', $vdata['tbid'])->first();
        return json_encode([
            'success' => 'true',
            'message' => 'Data ditampilkan',
            'vdata' => json_encode((array) $get_asr_header)
        ]);
    }
    public static function load_edit_assetno($param)
    {
        $vdata = json_decode($param['vdata'], true);
        $getdata = DB::table('asr_detail')->where('tbid', '=', $vdata['tbid'])->first();
        return json_encode([
            'success' => 'true',
            'message' => 'Data ditampilkan',
            'vdata' => json_encode((array) $getdata)
        ]);
    }
    public static function read_data_item_asset($param)
    {
        $vdata = json_decode($param['vdata'], true);
        $query = DB::table(DB::raw("(
            SELECT
                *,
                COALESCE(SUM(asset_price + asset_book_value), 0) as total_amount
            FROM asr_detail
            WHERE dokumen_no = '" . $vdata['dokumen_no'] . "'
            GROUP BY tbid, dokumen_no, asset_no, asset_name
        ) as temp_table"))
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
        $SQLproses = DB::table('asr_header')->insertGetId([
            'dokumen_no' => DB::raw("(SELECT generate_asr_no('" . $vdata['dept_code'] . "'))"),
            'dokumen_date' => $vdata['dokumen_date'],
            'dept_code' => $vdata['dept_code'],
            'dept_name' => DB::raw("(SELECT deptname FROM vw_department where deptcode='" . $vdata['dept_code'] . "' LIMIT 1)"),
            'customer_name' => $vdata['customer_name'],
            'syscreateuser' => $param['VUSERLOGIN'],
            'dokumen_remark' => $vdata['dokumen_remark']
        ], 'tbid');
        if ($SQLproses) {
            $getdata = DB::table('asr_header')->where('tbid', $SQLproses)->first();
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
        $SQLproses = DB::table('asr_header')
            ->where("tbid", $vdata["tbid"])
            ->update([
                'dokumen_no' => $vdata['dokumen_no'],
                'dokumen_date' => $vdata['dokumen_date'],
                'dept_code' => $vdata['dept_code'],
                'dept_name' => DB::raw("(SELECT deptname FROM vw_department where deptcode='" . $vdata['dept_code'] . "' LIMIT 1)"),
                'customer_name' => $vdata['customer_name'],
                'dokumen_remark' => $vdata['dokumen_remark'],
                'sysupdateuser' => $param['VUSERLOGIN'],
                'sysupdatedate' => DB::raw("(select now())"),
            ]);
        if ($SQLproses > 0) {
            $getdata = DB::table('asr_header')->where('tbid', $SQLproses)->first();
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

            DB::table('asr_header')
                ->where('dokumen_no', $vitem['dokumen_no'])
                ->delete();
            DB::table('asr_detail')
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
        $SQLinsert = DB::table('asr_detail')->insertGetId([
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
            'asset_reason' => $vitem['asset_reason'],
            'asset_price' => $vitem['asset_price'],
            'asset_book_value' => $vitem['asset_book_value'],
            'syscreateuser' => $param['VUSERLOGIN'],
        ], 'tbid');
        if ($SQLinsert) {
            $getdata = DB::table('asr_detail')->where('tbid', $SQLinsert)->first();
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
        $SQLupdate = DB::table('asr_detail')
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
                'asset_reason' => $vitem['asset_reason'],
                'asset_price' => $vitem['asset_price'],
                'asset_book_value' => $vitem['asset_book_value'],
                'sysupdateuser' => $param['VUSERLOGIN'],
                'sysupdatedate' => DB::raw("(select now())")
            ]);

        if ($SQLupdate > 0) {
            $getdata = DB::table('asr_detail')->where('tbid', $vitem["tbid"])->first();
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
            DB::table('asr_detail')
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
        $SQLproses = DB::table('asr_header')
            ->where("tbid", $vdata["tbid"])
            ->update([
                'dokumen_status' => 'POSTING',
                'dokumen_posting_user' => $param['VUSERLOGIN'],
                'dokumen_posting_date' => DB::raw("(select now())"),
            ]);
        if ($SQLproses > 0) {
            $getdata = DB::table('asr_header')->where('tbid', $vdata["tbid"])->first();
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
            //hapus asr HEADER
            DB::statement("
                delete from asr_header where dokumen_no=?
            ", [$vdata['dokumen_no']]);

            //hapus asr DETAIL
            DB::statement("
                delete from asr_detail where dokumen_no=?
            ", [$vdata['dokumen_no']]);

            //hapus asr SUB DETAIL
            DB::statement("
                delete from asr_sub_detail where dokumen_no=?
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
        $vheader = DB::table('asr_header')->where('tbid', '=', $vdata['tbid'])->first();
        $sql = "
            SELECT
                *,
                COALESCE(SUM(SUM(asset_book_value)) OVER(), 0) as total_book_value,
                COALESCE(SUM(SUM(asset_price)) OVER(), 0) as total_price,
                COALESCE(SUM(asset_price + asset_book_value), 0) as total_amount,
                COALESCE(SUM(SUM(asset_price + asset_book_value)) OVER(), 0) as total_all_amount
            FROM asr_detail
            WHERE dokumen_no = ?
            GROUP BY dokumen_no, asset_no, asset_name
        ";

        $vbody = DB::select($sql, [$vheader->dokumen_no]);

        $html = '
        <style>

        </style>

        <div style="float: left; width: 70%">&nbsp;</div>
        <div style="width: 30%; float: left;">
            <table style=" border-collapse: collapse; ">
                <tbody>
                    <tr>
                        <td colspan="4" style="font-style:italic">
                            Penjualan ke affiliate
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
                        <td> Affiliate </td>
                    </tr>
                    <tr>
                        <td colspan="4" style="font-style:italic">
                            Penjualan ke non affiliate
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
                        <td> Non Affiliate </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div style="height:10px;"></div>
        <div style="width: 80%">
        <table style="font-size:14pt; font-weight:bold; border-collapse: collapse; width: 100%;">
            <tbody>
                <tr>
                    <td style="text-align: center; padding: 3.5px;width:30%;">
                        <u style="text-decoration:underline;">ASSET SALES REQUISITION<u>
                        <p style="text-decoration:none;">ASR<p>
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
                        <td style="height: 20px;">FA Form 01-01</td>
                    </tr>
                </tbody>
            </table>
            <table style=" border: none; margin-top:10px;">
                <tbody>
                    <tr>
                        <td style="font-weight:normal; padding: 3.5px; width:25%;">NO</td>
                        <td style="font-weight:normal; padding: 3.5px; width:5%;">:</td>
                        <td style="padding: 2.5px; width:70%; color:blue; font-weight:bold; font-size: 10pt">' . $vheader->dokumen_no . '</td>
                    </tr>
                    <tr>
                        <td style="font-weight:normal; padding: 3.5px; width:25%;">DATE</td>
                        <td style="font-weight:normal; padding: 3.5px; width:5%;">:</td>
                        <td style="padding: 2.5px; width:70%; color:blue; font-weight:bold;font-size: 10pt">' . Carbon::parse($vheader->dokumen_date)->format('d/m/Y') . '</td>
                    </tr>
                    <tr>
                        <td style="font-weight:normal; padding: 3.5px; width:25%;">DEPT/SECTION</td>
                        <td style="font-weight:normal; padding: 3.5px; width:5%;font-size: 10pt">:</td>
                        <td style="padding: 2.5px; width:70%;"> ' . $vheader->dept_name . ' </td>
                    </tr>
                    <tr>
                        <td style="font-weight:normal; padding: 3.5px; width:25%;">CUSTOMER</td>
                        <td style="font-weight:normal; padding: 3.5px; width:5%;font-size: 10pt">:</td>
                        <td style="padding: 2.5px; width:70%;"> SAM </td>
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
                        <td style="padding: 3px; border: 1px solid black; text-align: center;">GM/MGR</td>
                        <td style="padding: 3px; border: 1px solid black; text-align: center;">SSPV/SPV</td>
                    </tr>
                </table>
            </div>

        </div>


        <!--
        ========================================================================================================
        AREA DATA BODY=======================================================================================
        -->

        <div style="border: 1px solid; margin-top: 20px; ">
            <div style="padding: 10px;">
                <table style="border-collapse: collapse; width: 100%;">
                    <colgroup>
                        <col style="width: 3%;">
                        <col style="width: 20%;">
                        <col style="width: 10%;">
                        <col style="width: 10%;">
                        <col style="width: 10%;">
                        <col style="width: 12%;">
                        <col style="width: 12%;">
                        <col style="width: 12%;">
                    </colgroup>
                    <tbody>
                        <tr>
                        <th style="border: 1px solid #000; padding: 8px; font-weight: normal;">No</th>
                        <th style="border: 1px solid #000; padding: 8px; font-weight: normal;">Asset Name</th>
                        <th style="border: 1px solid #000; padding: 8px; font-weight: normal;">Asset Number</th>
                        <th style="border: 1px solid #000; padding: 8px; font-weight: normal;">Asset Number SAP</th>
                        <th style="border: 1px solid #000; padding: 8px; font-weight: normal;">Reason</th>
                        <th style="border: 1px solid #000; padding: 8px; font-weight: normal;">Book Value</th>
                        <th style="border: 1px solid #000; padding: 8px; font-weight: normal;">Sales Price</th>
                        <th style="border: 1px solid #000; padding: 8px; font-weight: normal;">Total Amount (USD)</th>
                        </tr>

                       ';

        $nocount = 1;

        foreach ($vbody as $item) {

            $html .= '
                <tr>
                    <td style="border: 1px solid #000; padding: 4px;">1</td>
                    <td style="border: 1px solid #000; padding: 4px; font-size: 8pt;">' . $item->asset_name . '</td>
                    <td style="border: 1px solid #000; padding: 4px; font-size: 8pt;">' . $item->asset_no . '</td>
                    <td style="border: 1px solid #000; padding: 4px; font-size: 8pt;">' . $item->asset_sap_no . '</td>
                    <td style="border: 1px solid #000; padding: 4px; font-size: 8pt;"> ' . $item->asset_reason . ' </td>
                    <td style="border: 1px solid #000; padding: 4px; font-size: 8pt; width: 10%; text-align: right;">' . $item->asset_book_value . '</td>
                    <td style="border: 1px solid #000; padding: 4px; font-size: 8pt; width: 10%; text-align: right;"> ' . $item->asset_price . '</td>
                    <td style="border: 1px solid #000; padding: 4px; font-size: 8pt; width: 10%; text-align: right;"> ' . $item->total_amount . '  </td>
                </tr>
                ';
            $nocount++;
        }


        $html .= '
                <tr>
                    <td colspan="5" style="border: 1px solid #000; padding: 4px; font-weight: bold; text-align: center;">TOTAL</td>
                    <td style="border: 1px solid #000; padding: 4px; font-weight: normal;font-size: 8pt; text-align: right;"> ' . $item->total_book_value . ' </td>
                    <td style="border: 1px solid #000; padding: 4px; font-weight: normal;font-size: 8pt; text-align: right;"> ' . $item->total_price . '  </td>
                    <td style="border: 1px solid #000; padding: 4px; font-weight: normal;font-size: 8pt; text-align: right;"> ' . $item->total_all_amount . '  </td>
                </tr>
                    </tbody>
                    </table>

                <div style="height:10px;"></div>


                <!--
                ========================================================================================================
                AREA DATA FOOTER=======================================================================================
                -->

                <div style="width: 100%; margin: 20px;">
                    <div style="float:left; width: 77%;">
                        <div style="border:1px solid; width: 200px; text-align: center;">
                            <div>WHITE: VOUCHER</div>
                        </div>
                    </div>
                    <div style="float:left; width: 23%;">
                        <div style="border:1px solid; width: 200px; text-align: center;">
                            <div>BLUE: DEPT</div>
                        </div>
                    </div>
                </div>

                <div style="width: 100%">
                    <div style="float:left; width: 20%;">
                        <table style=" border-collapse: collapse;">
                            <tbody>
                                <tr>
                                    <td colspan="4">
                                    </td>
                                    <td>
                                        <table style="border: 1px solid; width: 18px;">
                                            <tbody>
                                                <tr>
                                                    <td style="height: 15px; text-align:center;"> </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                    <td> TRANSPORT </td>
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
                                    <td> DELIVERY COST </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div style="float:left;width: 20%;">
                        <table style=" border-collapse: collapse;">
                            <tbody>
                                <tr>
                                    <td colspan="4">
                                    </td>
                                    <td>
                                        <table style="border: 1px solid; width: 18px;">
                                            <tbody>
                                                <tr>
                                                    <td style="height: 15px; text-align:center;"> </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                    <td> AIR / SEA </td>
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
                                    <td> PREPAID </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div style="float:left;width: 20%;">
                        <table style=" border-collapse: collapse;">
                            <tbody>
                                <tr>
                                    <td colspan="4">
                                    </td>
                                    <td>
                                        <table style="border: 1px solid; width: 18px;">
                                            <tbody>
                                                <tr>
                                                    <td style="height: 15px; text-align:center;"> </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                    <td> LAND </td>
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
                                    <td> COLLECT </td>
                                </tr>
                            </tbody>
                        </table>
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
        $mpdf->SetWatermarkText("ASR");
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

            $SQLupdate = DB::table('asr_detail')
                ->where('tbid', "=", $vdata['tbid'])
                ->update([
                    'asset_image' => $nama_lampiran
                ]);

            if ($SQLupdate > 0) {
                $getdata = DB::table('asr_detail')->where('tbid', $vdata["tbid"])->first();
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
