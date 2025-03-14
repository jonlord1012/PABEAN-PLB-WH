<?php

namespace App\Models;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use DateTime;
use Throwable;

class Mdoc_fwr extends Model
{
    public static function handleAction($method, $param)
    {
        switch ($method) {
            case 'read_data':
                return self::read_data($param);
            case 'read_item_part':
                return self::read_item_part($param);
            case 'read_sumber_data':
                return self::read_sumber_data($param);
            case 'read_select_oldasset':
                return self::read_select_oldasset($param);
            default:
                return ['error' => 'Action not recognized'];
        }
    }
    public static function read_data($param)
    {
        $query = DB::table('fwr_header as a')
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
        $get_fwr_header = DB::table('fwr_header')->where('tbid', '=', $vdata['tbid'])->first();
        return json_encode([
            'success' => 'true',
            'message' => 'Data ditampilkan',
            'vdata' => json_encode((array) $get_fwr_header)
        ]);
    }
    public static function load_edit_assetno($param)
    {
        $vdata = json_decode($param['vdata'], true);
        $getdata = DB::table('fwr_detail')->where('tbid', '=', $vdata['tbid'])->first();
        return json_encode([
            'success' => 'true',
            'message' => 'Data ditampilkan',
            'vdata' => json_encode((array) $getdata)
        ]);
    }
    public static function read_data_item_asset($param)
    {
        $vdata = json_decode($param['vdata'], true);
        $query = DB::table('fwr_detail')
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
        $query = DB::table('fwr_detail')
            ->select('*')
            ->where('dokumen_no', "=", $param['dokumen_no']);

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
        $SQLproses = DB::table('fwr_header')->insertGetId([
            'dokumen_no' => DB::raw("(SELECT generate_fwr_no('" . $vdata['dept_code'] . "'))"),
            'dokumen_date' => $vdata['dokumen_date'],
            'dept_code' => $vdata['dept_code'],
            'dept_name' => DB::raw("(SELECT deptname FROM vw_department where deptcode='" . $vdata['dept_code'] . "' LIMIT 1)"),
            'syscreateuser' => $param['VUSERLOGIN'],
            'dokumen_remark' => $vdata['dokumen_remark']
        ], 'tbid');
        if ($SQLproses) {
            $getdata = DB::table('fwr_header')->where('tbid', $SQLproses)->first();
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
        $SQLproses = DB::table('fwr_header')
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
            $getdata = DB::table('fwr_header')->where('tbid', $SQLproses)->first();
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

            DB::table('fwr_header')
                ->where('dokumen_no', $vitem['dokumen_no'])
                ->delete();
            DB::table('fwr_detail')
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
        try {
            $SQLinsert = DB::table('fwr_detail')->insertGetId([
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
                'asset_book_value' => $vitem['asset_book_value'],
                'syscreateuser' => $param['VUSERLOGIN'],
            ], 'tbid');
        } catch (Throwable $e) {
            return json_encode([
                'success' => 'false',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ]);
        }

        if ($SQLinsert) {
            $getdata = DB::table('fwr_detail')->where('tbid', $SQLinsert)->first();
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
        $SQLupdate = DB::table('fwr_detail')
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
                'asset_book_value' => $vitem['asset_book_value'],
                'sysupdateuser' => $param['VUSERLOGIN'],
                'sysupdatedate' => DB::raw("(select now())")
            ]);

        if ($SQLupdate > 0) {
            $getdata = DB::table('fwr_detail')->where('tbid', $vitem["tbid"])->first();
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
            DB::table('fwr_detail')
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
        $SQLproses = DB::table('fwr_header')
            ->where("tbid", $vdata["tbid"])
            ->update([
                'dokumen_status' => 'POSTING',
                'dokumen_posting_user' => $param['VUSERLOGIN'],
                'dokumen_posting_date' => DB::raw("(select now())"),
            ]);
        if ($SQLproses > 0) {
            $getdata = DB::table('fwr_header')->where('tbid', $vdata["tbid"])->first();
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


    public static function save_data_item($param)
    {
        $vdata = json_decode($param['data'], true);
        $vitem = json_decode($param['vitem'], true);
        $dokumen_no = json_decode($param['dokumen_no'], true);

        // $dept_name = trim($separate_department[1]);
        DB::beginTransaction();
        $lastAsset = DB::table('fwr_detail')
            ->selectRaw("MAX(CAST(REGEXP_REPLACE(asset_no, '[^0-9]', '', 'g') AS INTEGER)) as max_asset")
            ->value('max_asset');

        $newAssetNo = $lastAsset
            ? 'ASSET-' . ($lastAsset + 1)
            : 'ASSET-1';
        try {
            $field = [
                'dokumen_no' => $dokumen_no,
                'asset_no' => $newAssetNo,
                'asset_name' => $vdata['asset_name'],
                'asset_partname' => $vdata['asset_name'],
                'asset_partcode' => $vdata['asset_partcode'],
                'asset_group' => $vdata['asset_group'],
                'asset_category' => $vdata['asset_category'],
                'asset_location' => $vdata['asset_location'],
                'asset_sub_location' => $vdata['asset_sub_location'] ?? '',
                'asset_condition' => $vdata['asset_condition'] ?? '',
                'asset_label' => $vdata['asset_label'] ?? '',
                'asset_costcenter' => $vdata['asset_costcenter'] ?? '',
                'qty' => $vdata['asset_cost'] ?? 0,
                'pr_no' => $vdata['pr_no'],
                'pr_date' => $vdata['pr_date'],
                'gr_no' => $vdata['gr_no'],
                'gr_date' => $vdata['gr_date'],
                'po_no' => $vdata['po_no'],
                'po_date' => $vdata['po_date'],
                'invoice_no' => $vdata['invoice_no'],
                'invoice_date' => $vdata['invoice_date'],
                'syscreateuser' => $param['VUSERLOGIN'],
                'syscreatedate' => date('Y-m-d H:i:s'),
            ];

            if (!empty($vdata['tbid'])) {
                $updatedGroup = DB::table('fwr_detail')->where('tbid', $vdata['tbid'])->update(array_filter($field));

                if (!$updatedGroup) {
                    DB::rollBack();
                    return json_encode(['success' => false, 'message' => 'Update Detail Failed']);
                }
            } else {
                $createdGroupId = DB::table('fwr_detail')->insertGetId(array_filter($field), 'tbid');

                if (!$createdGroupId) {
                    DB::rollBack();
                    return json_encode(['success' => false, 'message' => 'Insert Detail Failed']);
                }

                $vdata['tbid'] = $createdGroupId;
            }

            // Proses Sub Asset
            foreach ($vitem as $item) {
                // Cek jika kombinasi dokumen_no dan asset_partcode sudah ada
                $existingDetail = DB::table('fwr_sub_detail')
                    ->where('dokumen_no', $dokumen_no)
                    ->where('asset_partcode', $item['partcode'])
                    ->first();

                if ($existingDetail) {
                    // Update Detail
                    $updatedDetail = DB::table('fwr_sub_detail')
                        ->where('dokumen_no', $dokumen_no)
                        ->where('asset_partcode', $item['partcode'])
                        ->update([
                            'asset_partname' => $item['partname'],
                            'asset_category' => $item['partcategory'],
                            'sysupdateuser' => $param['VUSERLOGIN'],
                            'sysupdatedate' => date('Y-m-d H:i:s'),
                        ]);

                    if (!$updatedDetail) {
                        DB::rollBack();
                        return json_encode(['success' => false, 'message' => 'Update Detail Failed']);
                    }
                } else {
                    // Generate asset_no baru

                    $lastAsset = DB::table('fwr_sub_detail')->max('sub_asset_no');
                    $newSubAssetNo = $lastAsset
                        ? 'SUB-ASSET-' . ((int) str_replace('SUB-ASSET-', '', $lastAsset) + 1)
                        : 'SUB-ASSET-1';
                    // Insert Detail
                    $insertedDetail = DB::table('fwr_sub_detail')->insert([
                        'dokumen_no' => $dokumen_no,
                        'asset_no' => $newAssetNo,
                        'sub_asset_no' => $newSubAssetNo,
                        'asset_name' => $item['partname'],
                        'asset_partname' => $item['partname'],
                        'asset_partcode' => $item['partcode'],
                        'asset_group' => $vdata['asset_group'],
                        'asset_category' => $vdata['asset_category'],
                        'asset_location' => $vdata['asset_location'],
                        'asset_sub_location' => $vdata['asset_sub_location'] ?? '',
                        'asset_condition' => $vdata['asset_condition'] ?? '',
                        'asset_label' => $vdata['asset_label'] ?? '',
                        'asset_costcenter' => $vdata['asset_costcenter'] ?? '',
                        'qty' => $vdata['asset_cost'] ?? 0,
                        'pr_no' => $vdata['pr_no'],
                        'pr_date' => $vdata['pr_date'],
                        'gr_no' => $vdata['gr_no'],
                        'gr_date' => $vdata['gr_date'],
                        'po_no' => $vdata['po_no'],
                        'po_date' => $vdata['po_date'],
                        'invoice_no' => $vdata['invoice_no'],
                        'invoice_date' => $vdata['invoice_date'],
                        'asset_status' => 'AKTIF',
                        'asset_type' => 'ASSET',
                        'syscreateuser' => $param['VUSERLOGIN'],
                        'syscreatedate' => date('Y-m-d H:i:s'),
                    ]);

                    if (!$insertedDetail) {
                        DB::rollBack();
                        return json_encode(['success' => false, 'message' => 'Insert Detail Failed']);
                    }
                }
            }

            // Commit transaksi
            DB::commit();

            return json_encode([
                'success' => true,
                'message' => !empty($vdata['tbid']) ? 'Update Data Success' : 'Add Data Success',
            ]);
        } catch (Throwable $e) {
            // Rollback transaksi jika ada error
            DB::rollBack();
            return json_encode([
                'success' => false,
                'message' => 'An unexpected error occurred: ' . $e->getMessage(),
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

    public static function delete_data($param)
    {
        $vdata = json_decode($param['vdata'], true);
        DB::beginTransaction();

        try {
            //hapus BAAB HEADER
            DB::statement("
                delete from fwr_header where dokumen_no=?
            ", [$vdata['dokumen_no']]);

            //hapus BAAB DETAIL
            DB::statement("
                delete from fwr_detail where dokumen_no=?
            ", [$vdata['dokumen_no']]);

            //hapus BAAB SUB DETAIL
            DB::statement("
                delete from fwr_sub_detail where dokumen_no=?
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
        $vheader = DB::table('fwr_header')->where('tbid', '=', $vdata['tbid'])->first();
        $vbody = DB::table('fwr_detail')->where('dokumen_no', '=', value: $vheader->dokumen_no)->get();

        $html = '
                <div style="float: left; width: 70%">&nbsp;</div>
               <div style="width: 40%; float: right;">
            </div>
                <div style="width: 80%">
                <table style="font-size:14pt; font-weight:bold; border-collapse: collapse; width: 100%;">
                    <tbody>
                        <tr>
                            <td style="text-align: center; padding: 3.5px;width:30%;">
                                <u style="text-decoration:underline;">FIXED ASSET WRITE OFF REQUISITION<u>
                                <p style="text-decoration:none;">FWR<p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                </div>


                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-top: 10px; gap: 20px;">
                    <div style="float: left; width: 50%">
                    <table  style="border: 1px solid; float: left;">
                        <tbody>
                            <tr>
                                <td style="height: 20px;">FA Form 07-01</td>
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
                                <td style="font-weight:normal; padding: 3.5px; width:25%;">DEPT/SECTION</td>
                                <td style="font-weight:normal; padding: 3.5px; width:5%;">:</td>
                                <td style="padding: 2.5px; width:70%; color:blue; font-weight:bold;">' . $vheader->dept_name . ' </td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                    <div style="float: right; width: 46%">
                    <table style="width: 100%; border-collapse: collapse; float: left;">
                        <tr>
                            <td style="padding: 3px; border: 1px solid black; text-align: center; width: 20%;">Approved 1</td>
                            <td style="padding: 3px; border: 1px solid black; text-align: center; width: 20%;">Approved 2</td>
                            <td style="padding: 3px; border: 1px solid black; text-align: center; width: 20%;">Verified</td>
                            <td style="padding: 3px; border: 1px solid black; text-align: center; width: 20%;">Checked</td>
                            <td style="padding: 3px; border: 1px solid black; text-align: center; width: 20%;">Prepared</td>
                        </tr>
                        <tr>
                            <td style="padding: 10px; border: 1px solid black; text-align: center; height: 50px;"></td>
                            <td style="padding: 10px; border: 1px solid black; text-align: center; height: 50px;"></td>
                            <td style="padding: 10px; border: 1px solid black; text-align: center; height: 50px;"></td>
                            <td style="padding: 10px; border: 1px solid black; text-align: center; height: 50px;"></td>
                            <td style="padding: 10px; border: 1px solid black; text-align: center; height: 50px;"></td>
                        </tr>
                        <tr>
                            <td style="padding: 3px; border: 1px solid black; text-align: center;">DIR</td>
                            <td style="padding: 3px; border: 1px solid black; text-align: center;">FM</td>
                            <td style="padding: 3px; border: 1px solid black; text-align: center;">FIN</td>
                            <td style="padding: 3px; border: 1px solid black; text-align: center;">GM/DFM-MGR</td>
                            <td style="padding: 3px; border: 1px solid black; text-align: center;">SSPV-SPV</td>
                        </tr>
                    </table>
                </div>


                </div>

                <!-- Data Table -->
                <div style="margin-top: 10px; ">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <th style="border: 1px solid black; padding: 8px; text-align: center; font-weight: normal; width: 4%;">No</th>
                        <th style="border: 1px solid black; padding: 8px; text-align: center; font-weight: normal; width: 25%;" >Asset Name</th>
                        <th style="border: 1px solid black; padding: 8px; text-align: center; font-weight: normal; width: 10%;">Asset Number</th>
                        <th style="border: 1px solid black; padding: 8px; text-align: center; font-weight: normal; width: 12%;">Asset Number SAP</th>
                        <th style="border: 1px solid black; padding: 8px; text-align: center; font-weight: normal; width: 12%;">Kondisi Asset</th>
                        <th style="border: 1px solid black; padding: 8px; text-align: center; font-weight: normal; width: 4%;">Qty</th>
                        <th style="border: 1px solid black; padding: 8px; text-align: center; font-weight: normal; width: 20%;">Reason</th>
                        <th style="border: 1px solid black; padding: 8px; text-align: center; font-weight: normal; width: 6%;">Book Value</th>
                    </tr>  ';
        $nocount = 1;
        $totalBookValue = 0;
        foreach ($vbody as $item) {
            $html .= '
                <tr>
                    <td style="border: 1px solid black; padding: 8px; font-size: 8pt;">' . $nocount . '</td>
                    <td style="border: 1px solid black; padding: 8px; font-size: 8pt;">' . $item->asset_name . '</td>
                    <td style="border: 1px solid black; padding: 8px; font-size: 8pt;">' . $item->asset_no . '</td>
                    <td style="border: 1px solid black; padding: 8px; font-size: 8pt;">' . $item->asset_sap_no . '</td>
                    <td style="border: 1px solid black; padding: 8px; font-size: 8pt;">' . $item->asset_condition . '</td>
                    <td style="border: 1px solid black; padding: 8px; font-size: 8pt; text-align: right;">1.00</td>
                    <td style="border: 1px solid black; padding: 8px; font-size: 8pt;">' . $item->asset_reason . '</td>
                    <td style="border: 1px solid black; padding: 8px; font-size: 8pt; text-align: right;">' . number_format($item->asset_book_value, 2, '.', ',') . '</td>
                <tr>
                 ';
            $totalBookValue += $item->asset_book_value;
            $nocount++;
        }
        $html .= '

                </tr>
                    <tr>
                        <td colspan="7" style="border: 1px solid black; padding: 8px; text-align: center; font-weight: bold;">TOTAL</td>
                        <td style="border: 1px solid black; padding: 8px; text-align: right;">' . number_format($totalBookValue, 2, '.', ',') . '</td>
                    </tr>
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
        $mpdf->SetWatermarkText("FWR 07-01");
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

            $SQLupdate = DB::table('fwr_detail')
                ->where('tbid', "=", $vdata['tbid'])
                ->update([
                    'asset_image' => $nama_lampiran
                ]);

            if ($SQLupdate > 0) {
                $getdata = DB::table('fwr_detail')->where('tbid', $vdata["tbid"])->first();
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
