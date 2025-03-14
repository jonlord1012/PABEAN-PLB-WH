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

class Mfin_verify extends Model
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
        $query = DB::table('vw_dokumen_verify')
            ->select('*');
        // ->where('dokumen_status', '=', 'POSTING')
        // ->whereNotNull([
        // 'approval1_user',
        // 'approval1_date',
        // 'approval2_user',
        // 'approval2_date',
        // 'approval6_user',
        // 'approval6_date',
        // ])
        // ->whereNull([
        //     'approval8_user',
        //     'approval8_date'
        // ]);


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
        $getdata = DB::table($doktype . '_detail as A')
            ->select('A.*', 'B.asset_cost as asset_amount')
            ->leftJoin("tr_asset_finance as B", "B.asset_no", "=", "A.asset_no")
            ->where('A.tbid', '=', $vdata['tbid'])
            ->first();
        $getdata->dokumen_type = $doktype;
        return json_encode([
            'success' => 'true',
            'message' => 'Data ditampilkan',
            'vdata' => json_encode((array) $getdata)
        ]);
    }

    public static function save_item_asset($param)
    {
        $vheader = json_decode($param['vheader'], true);
        $doktype = preg_replace('/\s+/', '_', strtolower($vheader['dokumen_type']));
        $vitem = json_decode($param['vitem'], true);
        $SQLupdate = DB::table($doktype . '_detail')
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
                'asset_amount' => $vitem['asset_amount'],
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
            $getdata = DB::table($doktype . '_detail as A')
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
    public static function save_item_asset_bapa($param)
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
                'actual_cost_verify' => $vitem['actual_cost_verify'],
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
    public static function dokumen_verify($param)
    {
        $vdata = json_decode($param['vdata'], true);
        $doktype = strtolower($vdata['dokumen_type']);
        $getdata = DB::table($doktype . '_header')
            ->select('dept_code', 'dept_name')
            ->where('dokumen_no', $vdata['dokumen_no'])
            ->first();

        if (!$getdata) {
            return json_encode([
                'success' => 'false',
                'message' => 'Data tidak ditemukan'
            ]);
        }

        DB::beginTransaction();
        try {
            $currentDateTime = new DateTime();
            $userLogin = $param['VUSERLOGIN'];

            $processResult = null;
            switch ($doktype) {
                case 'baab':
                    $processResult = self::dokumen_verify_process_baab($param, $getdata);
                    break;
                case 'bapa':
                    $processResult = self::dokumen_verify_process_bapa($param, $getdata);
                    break;
                case 'asr':
                    $processResult = self::dokumen_verify_process_asr($param, $getdata);
                    break;
                case 'fcmr':
                    $processResult = self::dokumen_verify_process_fcmr($param, $getdata);
                    break;
                case 'fir':
                    $processResult = self::dokumen_verify_process_fir($param, $getdata);
                    break;
                case 'flr':
                    $processResult = self::dokumen_verify_process_flr($param, $getdata);
                    break;
                default:
                    break;
            }

            if ($processResult !== null) {
                $result = json_decode($processResult, true);
                if (isset($result['success']) && $result['success'] === 'false') {
                    DB::rollBack();
                    return $processResult;
                }
            }


            DB::table($doktype . '_header')
                ->where('dokumen_no', $vdata['dokumen_no'])
                ->update([
                    'approval8_user' => $userLogin,
                    'approval8_date' => $currentDateTime
                ]);


            DB::commit();
            return json_encode([
                'success' => 'true',
                'message' => 'Data berhasil diverifikasi'
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            return json_encode([
                'success' => 'false',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }
    public static function dokumen_verify_process_baab($param, $getdata)
    {
        $vdata = json_decode($param['vdata'], true);
        $currentDateTime = new DateTime();
        $userLogin = $param['VUSERLOGIN'];

        $baab_asset_data = DB::table('baab_detail')
            ->where('dokumen_no', $vdata['dokumen_no'])
            ->get();

        $asset_inserts = [];
        $finance_inserts = [];
        $sub_asset_inserts = [];

        $validation_errors = [];
        foreach ($baab_asset_data as $item) {
            if (!$item->asset_sap_no) {
                $validation_errors[] = 'Asset "' . $item->asset_name . '" Belum memiliki Nomor SAP';
                continue;
            }
            if (!$item->asset_key) {
                $validation_errors[] = 'Asset "' . $item->asset_name . '" Belum memiliki Asset Key';
                continue;
            }
            if (!$item->asset_category || $item->asset_category === "0") {
                $validation_errors[] = 'Asset "' . $item->asset_name . '" Belum memiliki Category';
                continue;
            }
            if (!$item->asset_group) {
                $validation_errors[] = 'Asset "' . $item->asset_name . '" Belum memiliki Group';
                continue;
            }
            if (!$item->asset_amount || $item->asset_amount == 0) {
                $validation_errors[] = 'Asset "' . $item->asset_name . '" Belum memiliki Cost';
                continue;
            }

            $asset_data = (array) $item;
            $asset_data['asset_status'] = 'AKTIF';
            $asset_data['asset_pic_dept'] = $getdata->dept_code;
            $asset_data['asset_pic_deptname'] = $getdata->dept_name;
            $asset_data['asset_acquisition_date'] = $currentDateTime->format('Y-m-d H:i:s');

            unset(
                $asset_data['asset_bc_type'],
                $asset_data['asset_bc_noaju'],
                $asset_data['asset_bc_tglaju'],
                $asset_data['asset_bc_nodaftar'],
                $asset_data['asset_bc_tgldaftar'],
                $asset_data['asset_bc_nofabrikasi'],
                $asset_data['asset_bc_tglfabrikasi'],
                $asset_data['asset_costcenter_name']
            );

            $asset_inserts[] = $asset_data;

            $finance_inserts[] = [
                'asset_no' => $item->asset_no,
                'asset_acquisition_date' => $currentDateTime->format('Y-m-d H:i:s'),
                'asset_cost' => $item->asset_amount,
                'syscreateuser' => $userLogin
            ];
        }

        if (!empty($validation_errors)) {
            return json_encode([
                'success' => 'false',
                'message' => implode(', ', $validation_errors)
            ]);
        }

        if (!empty($asset_inserts)) {
            $asset_nos = array_column($asset_inserts, 'asset_no');
            $sub_assets = DB::table('baab_detailsub')
                ->where('dokumen_no', $vdata['dokumen_no'])
                ->whereIn('asset_no', $asset_nos)
                ->get();

            foreach ($sub_assets as $sub_item) {
                $sub_asset_inserts[] = (array) $sub_item;
            }
        }

        DB::beginTransaction();
        try {
            if (!empty($asset_inserts)) {
                DB::table('tr_asset')->insert($asset_inserts);
            }
            if (!empty($finance_inserts)) {
                DB::table('tr_asset_finance')->insert($finance_inserts);
            }
            if (!empty($sub_asset_inserts)) {
                DB::table('tr_asset_sub')->insert($sub_asset_inserts);
            }

            DB::commit();

            return null;
        } catch (\Exception $e) {
            DB::rollBack();
            return json_encode([
                'success' => 'false',
                'message' => 'Failed to insert data: ' . $e->getMessage()
            ]);
        }
    }
    public static function dokumen_verify_process_bapa($param, $getdata)
    {
        return DB::transaction(function () use ($param, $getdata) {
            $vdata = json_decode($param['vdata'], true);

            $bapa_detail = DB::table('bapa_detail')
                ->where('dokumen_no', $vdata['dokumen_no'])
                ->first();

            $sub_details = DB::table('bapa_detailsub AS A')
                ->join('tr_asset AS B', 'B.asset_no', '=', 'A.asset_no')
                ->where('A.dokumen_no', $vdata['dokumen_no'])
                ->select('A.*')
                ->get();

            if ($sub_details->isNotEmpty()) {
                $insertData = [];
                foreach ($sub_details as $detail) {
                    $insertData[] = (array) $detail;
                }

                if ($bapa_detail && $bapa_detail->actual_cost_verify !== null) {
                    DB::table('tr_asset')
                        ->where('asset_no', $bapa_detail->asset_no)
                        ->update(['asset_amount' => $bapa_detail->actual_cost_verify]);

                    DB::table('tr_asset_finance')
                        ->where('asset_no', $bapa_detail->asset_no)
                        ->update(['asset_cost' => $bapa_detail->actual_cost_verify]);
                } else {
                    $assetTotals = DB::table('bapa_detailsub')
                        ->where('dokumen_no', $vdata['dokumen_no'])
                        ->groupBy('asset_no')
                        ->select('asset_no', DB::raw('SUM(amount) as total_amount'))
                        ->get();

                    foreach ($assetTotals as $total) {
                        DB::table('tr_asset')
                            ->where('asset_no', $total->asset_no)
                            ->update([
                                'asset_amount' => DB::raw("asset_amount + {$total->total_amount}")
                            ]);

                        DB::table('tr_asset_finance')
                            ->where('asset_no', $total->asset_no)
                            ->update([
                                'asset_cost' => DB::raw("asset_cost + {$total->total_amount}")
                            ]);
                    }
                }

                DB::table('tr_asset_sub')->insert($insertData);
            }

            return null;
        });
    }
    public static function dokumen_verify_process_asr($param, $getdata)
    {
        $vdata = json_decode($param['vdata'], true);
        $asset_detail = DB::table('asr_detail')
            ->where('dokumen_no', $vdata['dokumen_no'])
            ->pluck('asset_no');

        if ($asset_detail->isNotEmpty()) {
            DB::table('tr_asset')
                ->whereIn('asset_no', $asset_detail)
                ->update(['asset_status' => 'TIDAK AKTIF']);
        }

        return null;
    }
    public static function dokumen_verify_process_fcmr($param, $getdata)
    {
        $vdata = json_decode($param['vdata'], true);
        DB::beginTransaction();
        try {
            $asset_details = DB::table('fcmr_detail')
                ->where('dokumen_no', $vdata['dokumen_no'])
                ->get();

            if ($asset_details->isNotEmpty()) {
                foreach ($asset_details as $asset_detail) {
                    $data = [
                        'asset_name' => $asset_detail->asset_name_new,
                        'asset_pic_dept' => $asset_detail->dept_code_new,
                        'asset_pic_deptname' => $asset_detail->dept_name_new,
                        'asset_location' => $asset_detail->asset_location_new,
                        'asset_sublocation' => $asset_detail->asset_sublocation_new,
                    ];

                    DB::table('tr_asset')
                        ->where('asset_no', $asset_detail->asset_no)
                        ->update($data);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return json_encode(['success' => 'false', 'message' => $e->getMessage()]);
        }
    }
    public static function dokumen_verify_process_fir($param, $getdata)
    {
        $vdata = json_decode($param['vdata'], true);
        DB::beginTransaction();
        try {
            $asset_details = DB::table('fir_detail')
                ->where('dokumen_no', $vdata['dokumen_no'])
                ->get();

            if ($asset_details->isNotEmpty()) {
                foreach ($asset_details as $asset_detail) {
                    $data = [
                        'asset_costcenter' => $asset_detail->asset_costcenter_new,
                        'asset_location' => $asset_detail->asset_location_new,
                        'asset_sublocation' => $asset_detail->asset_sublocation_new,
                    ];

                    DB::table('tr_asset')
                        ->where('asset_no', $asset_detail->asset_no)
                        ->update($data);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return json_encode(['success' => 'false', 'message' => $e->getMessage()]);
        }
    }
    public static function dokumen_verify_process_flr($param, $getdata)
    {
        $vdata = json_decode($param['vdata'], true);
        $currentDateTime = new DateTime();
        $userLogin = $param['VUSERLOGIN'];

        $flr_asset_data = DB::table('flr_detail')
            ->where('dokumen_no', $vdata['dokumen_no'])
            ->get();

        $asset_inserts = [];
        $finance_inserts = [];
        $sub_asset_inserts = [];

        $validation_errors = [];
        foreach ($flr_asset_data as $item) {
            if (!$item->asset_sap_no) {
                $validation_errors[] = 'Asset "' . $item->asset_name . '" Belum memiliki Nomor SAP';
                continue;
            }
            if (!$item->asset_key) {
                $validation_errors[] = 'Asset "' . $item->asset_name . '" Belum memiliki Asset Key';
                continue;
            }
            if (!$item->asset_category || $item->asset_category === "0") {
                $validation_errors[] = 'Asset "' . $item->asset_name . '" Belum memiliki Category';
                continue;
            }
            if (!$item->asset_group) {
                $validation_errors[] = 'Asset "' . $item->asset_name . '" Belum memiliki Group';
                continue;
            }
            if (!$item->asset_amount || $item->asset_amount == 0) {
                $validation_errors[] = 'Asset "' . $item->asset_name . '" Belum memiliki Cost';
                continue;
            }

            $asset_data = (array) $item;
            $asset_data['asset_status'] = 'AKTIF';
            $asset_data['asset_pic_dept'] = $getdata->dept_code;
            $asset_data['asset_pic_deptname'] = $getdata->dept_name;
            $asset_data['asset_acquisition_date'] = $currentDateTime->format('Y-m-d H:i:s');

            unset(
                $asset_data['asset_bc_type'],
                $asset_data['asset_bc_noaju'],
                $asset_data['asset_bc_tglaju'],
                $asset_data['asset_bc_nodaftar'],
                $asset_data['asset_bc_tgldaftar'],
                $asset_data['asset_bc_nofabrikasi'],
                $asset_data['asset_bc_tglfabrikasi'],
                $asset_data['asset_costcenter_name'],
                $asset_data['asset_kontrak_no'],
                $asset_data['asset_tglsewa_start'],
                $asset_data['asset_tglsewa_end'],
                $asset_data['asset_pv_hak_guna'],
            );

            $asset_inserts[] = $asset_data;

            $finance_inserts[] = [
                'asset_no' => $item->asset_no,
                'asset_acquisition_date' => $currentDateTime->format('Y-m-d H:i:s'),
                'asset_cost' => $item->asset_amount,
                'syscreateuser' => $userLogin
            ];
        }

        if (!empty($validation_errors)) {
            return json_encode([
                'success' => 'false',
                'message' => implode(', ', $validation_errors)
            ]);
        }

        if (!empty($asset_inserts)) {
            $asset_nos = array_column($asset_inserts, 'asset_no');
            $sub_assets = DB::table('flr_detailsub')
                ->where('dokumen_no', $vdata['dokumen_no'])
                ->whereIn('asset_no', $asset_nos)
                ->get();

            foreach ($sub_assets as $sub_item) {
                $sub_asset_inserts[] = (array) $sub_item;
            }
        }

        DB::beginTransaction();
        try {
            if (!empty($asset_inserts)) {
                DB::table('tr_asset')->insert($asset_inserts);
            }
            if (!empty($finance_inserts)) {
                DB::table('tr_asset_finance')->insert($finance_inserts);
            }
            if (!empty($sub_asset_inserts)) {
                DB::table('tr_asset_sub')->insert($sub_asset_inserts);
            }

            DB::commit();

            return null;
        } catch (\Exception $e) {
            DB::rollBack();
            return json_encode([
                'success' => 'false',
                'message' => 'Failed to insert data: ' . $e->getMessage()
            ]);
        }
    }
    public static function dokumen_verify_process_fwr($param, $getdata)
    {
        $vdata = json_decode($param['vdata'], true);
        DB::beginTransaction();
        try {
            $asset_detail = DB::table('fwr_detail')
                ->where('dokumen_no', $vdata['dokumen_no'])
                ->pluck('asset_no');

            if ($asset_detail->isNotEmpty()) {
                DB::table('tr_asset')
                    ->whereIn('asset_no', $asset_detail)
                    ->update(['asset_status' => 'TIDAK AKTIF']);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return json_encode(['success' => 'false', 'message' => $e->getMessage()]);
        }
    }
    public static function dokumen_revise($param)
    {
        $vdata = json_decode($param['vdata'], true);
        $doktype = strtolower($vdata['dokumen_type']);
        $getdata = DB::table($doktype . '_header')->where('dokumen_no', '=', $vdata['dokumen_no'])->first();
        if ($getdata) {
            $data = [
                'dokumen_status' => 'REVISE',
                // 'dokumen_revise_user' => $param['VUSERLOGIN'],
                // 'dokumen_revise_date' => new DateTime(),
                'dokumen_posting_user' => null,
                'dokumen_posting_date' => null,
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
            DB::table($doktype . '_header')->where('dokumen_no', '=', $vdata['dokumen_no'])->update($data);
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
