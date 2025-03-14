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

class Mdoc_sto extends Model
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
            default:
                return ['error' => 'Action not recognized'];
        }
    }
    public static function read_data($param)
    {

        $query = DB::table('baab_header')
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
        } else {
            $query->orderBy('syscreatedate', 'desc');
        }

        $rows = $query->get();
        return json_encode([
            'TotalRows' => $count,
            'Rows' => $rows
        ]);
    }
    public static function read_item_part($param)
    {
        $query = DB::table('baab_detail')
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
    public static function save_data($param)
    {
        $vdata = json_decode($param['data'], true);
        $vitem = json_decode($param['vitem'], true);
        $department = $vdata['dept_name'];
        $separate_department = explode(' - ', $department);
        $dept_code = trim($separate_department[0]);
        $currentYear = date('Y');
        $currentMonth = date('m');
        $maxNumber = DB::table('baab_header')
            ->where('dokumen_no', 'like', '%BAAB/' . $dept_code . '/' . $currentMonth . '/' . $currentYear)
            ->selectRaw("
            COALESCE(
                MAX(CAST(REGEXP_REPLACE(
                    SPLIT_PART(dokumen_no, 'BAAB', 1), '[^0-9]', '', 'g'
                ) AS INTEGER)), 0
            ) as max_number
        ")
            ->value('max_number');
        if (is_null($maxNumber)) {
            return json_encode(['success' => false, 'message' => 'Failed to generate max number']);
        }


        $nextNumber = str_pad(($maxNumber ? $maxNumber + 1 : 1), 3, '0', STR_PAD_LEFT);
        $dokumen_no = $nextNumber . "/" . "BAAB/" . $dept_code . "/" . $currentMonth . "/" . $currentYear;

        // Validasi dokumen_no
        if (empty($dokumen_no)) {
            return json_encode(['success' => false, 'message' => 'Failed to generate dokumen_no']);
        }
        // $dept_name = trim($separate_department[1]);
        DB::beginTransaction();

        try {
            $field = [
                'dokumen_no' => $vdata['dokumen_no'] != '' ? $vdata['dokumen_no'] : $dokumen_no,
                'dokumen_date' => $vdata['dokumen_date'],
                'dept_name' => $vdata['dept_name'],
                'pic_deptname' => $vdata['pic_deptname'],
                'register_asset_date' => $vdata['register_asset_date'],
                'label_asset_date' => $vdata['label_asset_date'],
                'dokumen_remark' => $vdata['dokumen_remark'],
                'syscreateuser' => $param['VUSERLOGIN'],
                'syscreatedate' => date('Y-m-d H:i:s'),
            ];

            if (!empty($vdata['tbid'])) {
                $updatedGroup = DB::table('baab_header')->where('tbid', $vdata['tbid'])->update(array_filter($field));

                if (!$updatedGroup) {
                    DB::rollBack();
                    return json_encode(['success' => false, 'message' => 'Update Header Failed']);
                }
            } else {


                $createdGroupId = DB::table('baab_header')->insertGetId(array_filter($field), 'tbid');

                if (!$createdGroupId) {
                    DB::rollBack();
                    return json_encode(['success' => false, 'message' => 'Insert Header Failed']);
                }

                $vdata['tbid'] = $createdGroupId; // Assign ID header ke detail
            }

            // Proses Detail
            foreach ($vitem as $item) {
                // Cek jika detail sudah ada berdasarkan tbid atau dokumen_no + asset_partcode
                $existingDetail = DB::table('baab_detail')
                    ->where('dokumen_no', $vdata['dokumen_no'])
                    ->where('asset_partcode', $item['asset_partcode'])
                    ->first();

                if ($existingDetail) {
                    // Update Detail
                    $updatedDetail = DB::table('baab_detail')
                        ->where('dokumen_no', $vdata['dokumen_no'])
                        ->where('asset_partcode', $item['asset_partcode'])
                        ->update([
                            'asset_partname' => $item['asset_partname'],
                            'asset_category' => $item['asset_category'],
                            'sysupdateuser' => $param['VUSERLOGIN'],
                            'sysupdatedate' => date('Y-m-d H:i:s'),
                        ]);

                    if (!$updatedDetail) {
                        DB::rollBack();
                        return json_encode(['success' => false, 'message' => 'Update Detail Failed']);
                    }
                } else {
                    // Jika tidak ditemukan, maka buat data baru
                    $lastAsset = DB::table('baab_detail')
                        ->selectRaw("MAX(CAST(REGEXP_REPLACE(asset_no, '[^0-9]', '', 'g') AS INTEGER)) as max_asset")
                        ->value('max_asset');

                    $newAssetNo = $lastAsset
                        ? 'ASSET-' . ($lastAsset + 1)
                        : 'ASSET-1';

                    // Insert Detail
                    $insertedDetail = DB::table('baab_detail')->insert([
                        'dokumen_no' => $dokumen_no,
                        'asset_no' => $newAssetNo,
                        'asset_partname' => $item['asset_partname'],
                        'asset_partcode' => $item['asset_partcode'],
                        'asset_category' => $item['asset_category'],
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

    public static function save_data_item($param)
    {
        $vdata = json_decode($param['data'], true);
        $vitem = json_decode($param['vitem'], true);
        $dokumen_no = json_decode($param['dokumen_no'], true);

        // $dept_name = trim($separate_department[1]);
        DB::beginTransaction();
        $lastAsset = DB::table('baab_detail')
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
                $updatedGroup = DB::table('baab_detail')->where('tbid', $vdata['tbid'])->update(array_filter($field));

                if (!$updatedGroup) {
                    DB::rollBack();
                    return json_encode(['success' => false, 'message' => 'Update Detail Failed']);
                }
            } else {
                $createdGroupId = DB::table('baab_detail')->insertGetId(array_filter($field), 'tbid');

                if (!$createdGroupId) {
                    DB::rollBack();
                    return json_encode(['success' => false, 'message' => 'Insert Detail Failed']);
                }

                $vdata['tbid'] = $createdGroupId;
            }

            // Proses Sub Asset
            foreach ($vitem as $item) {
                // Cek jika kombinasi dokumen_no dan asset_partcode sudah ada
                $existingDetail = DB::table('baab_sub_detail')
                    ->where('dokumen_no', $dokumen_no)
                    ->where('asset_partcode', $item['partcode'])
                    ->first();

                if ($existingDetail) {
                    // Update Detail
                    $updatedDetail = DB::table('baab_sub_detail')
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

                    $lastAsset = DB::table('baab_sub_detail')->max('sub_asset_no');
                    $newSubAssetNo = $lastAsset
                        ? 'SUB-ASSET-' . ((int) str_replace('SUB-ASSET-', '', $lastAsset) + 1)
                        : 'SUB-ASSET-1';
                    // Insert Detail
                    $insertedDetail = DB::table('baab_sub_detail')->insert([
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
                delete from baab_header where dokumen_no=?
            ", [$vdata['dokumen_no']]);

            //hapus BAAB DETAIL
            DB::statement("
                delete from baab_detail where dokumen_no=?
            ", [$vdata['dokumen_no']]);

            //hapus BAAB SUB DETAIL
            DB::statement("
                delete from baab_sub_detail where dokumen_no=?
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
        // $vrequest = [
        //     'VUSERLOGIN' => $param['VUSERLOGIN'],
        //     'module' => 'show_data',
        //     'vdata' => $param['vdata'],
        //     'vitem' => '{}'
        // ];
        // $vrespon = self::process_data($vrequest);
        // $hasil = json_decode($vrespon, true)[0];

        // $vheader = json_decode($hasil['vheader'], true);
        // $header = $vheader[0];
        // $detail = json_decode($hasil['vdetail'], true);

        // checkpoint
        $html = '
<div style="height:10px;"></div>
<div style="width: 80%">
    <table style="font-size:14pt; font-weight:bold; border-collapse: collapse; width: 100%;">
        <tbody>
            <tr>
                <td style="text-align: center; padding: 3.5px;width:30%;">
                    <u style="text-decoration:underline;">BERITA ACARA STO FIXED ASSET<u>
                            <div style="margin-bottom: 5px; font-weight: normal;">Tahun : <span style="color: blue">YYYY</span></div>
                </td>
            </tr>
        </tbody>
    </table>
</div>


<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-top: 10px; gap: 20px;">
    <div style="float: left; width: 50%">
        <table style="border: 1px solid; float: left;">
            <tbody>
                <tr>
                    <td style="height: 20px;">FA Form 02-01</td>
                </tr>
            </tbody>
        </table>
        <table style=" border: none; margin-top:10px;">
            <tbody>
                <tr>
                    <td style="font-weight:normal; padding: 3.5px; width:25%;">DATE</td>
                    <td style="font-weight:normal; padding: 3.5px; width:5%;">:</td>
                    <td style="padding: 2.5px; width:70%; color:blue; font-weight:bold;"></td>
                </tr>
                <tr>
                    <td style="font-weight:normal; padding: 3.5px; width:25%;">DEPT</td>
                    <td style="font-weight:normal; padding: 3.5px; width:5%;">:</td>
                    <td style="padding: 2.5px; width:70%; color:blue; font-weight:bold;"></td>
                </tr>
            </tbody>
        </table>
    </div>
    <div style="float: right; width: 46%">
        <table style="width: 100%; border-collapse: collapse; float: left;">
            <tr>
                <td style="padding: 3px; border: 1px solid black; text-align: center; width: 20%;">Approved</td>
                <td style="padding: 3px; border: 1px solid black; text-align: center; width: 20%;">Verified</td>
                <td style="padding: 3px; border: 1px solid black; text-align: center; width: 20%;">Checked</td>
                <td style="padding: 3px; border: 1px solid black; text-align: center; width: 20%;">Pendamping</td>
                <td style="padding: 3px; border: 1px solid black; text-align: center; width: 20%;">Penghitung</td>
            </tr>
            <tr>
                <td style="padding: 10px; border: 1px solid black; text-align: center; height: 50px;"></td>
                <td style="padding: 10px; border: 1px solid black; text-align: center; height: 50px;"></td>
                <td style="padding: 10px; border: 1px solid black; text-align: center; height: 50px;"></td>
                <td style="padding: 10px; border: 1px solid black; text-align: center; height: 50px;"></td>
                <td style="padding: 10px; border: 1px solid black; text-align: center; height: 50px;"></td>
            </tr>
            <tr>
                <td style="padding: 3px; border: 1px solid black; text-align: center;">DFM/GM</td>
                <td style="padding: 3px; border: 1px solid black; text-align: center;">MGR</td>
                <td style="padding: 3px; border: 1px solid black; text-align: center;">SSPV/SPV</td>
                <td style="padding: 3px; border: 1px solid black; text-align: center;">FIN</td>
                <td style="padding: 3px; border: 1px solid black; text-align: center;">PIC</td>
            </tr>
        </table>
    </div>

</div>
<div style="border: 1px solid black; margin-top: 15px">
    <!-- Statement -->
    <div style="margin: 15px 0; line-height: 1.3; padding-top: 8px; padding-left: 15px">
        Dengan ini menyatakan bahwa pada tanggal <span style="color: blue; font-weight: bold">DD/MM/YYYY</span>, telah
        selesai dilaksanakan proses pengecekan/penghitungan aset tetap<br />
        dengan summary sebagai berikut:
    </div>
    <!-- Data Table -->
    <div style="width: 100%; overflow: hidden; padding: 0 15px 15px 15px">
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px">
            <thead>
                <tr>
                    <th rowspan="2" style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 3%;">No</th>
                    <th rowspan="2" style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 30%;">Kategori Asset
                    </th>
                    <th colspan="3" style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 27%;">Jumlah</th>
                    <th colspan="4" style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 40%;">Kondisi Aktual
                    </th>
                </tr>
                <tr>
                    <th style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 9%;">Daftar</th>
                    <th style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 9%;">Aktual</th>
                    <th style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 9%;">Selisih</th>
                    <th style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 10%;">Bagus Aktif</th>
                    <th style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 10%;">Idle Permanent</th>
                    <th style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 10%;">Idle Temporary</th>
                    <th style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 10%;">Rusak</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td style="border: 1px solid #000; padding: 4px; text-align: right">1</td>
                    <td style="border: 1px solid #000; padding: 4px;">Electronic, communications Equipment</td>
                    <td style="border: 1px solid #000; padding: 4px; text-align: right">2</td>
                    <td style="border: 1px solid #000; padding: 4px; text-align: right">0</td>
                    <td style="border: 1px solid #000; padding: 4px; text-align: right">3</td>
                    <td style="border: 1px solid #000; padding: 4px; text-align: right">4</td>
                    <td style="border: 1px solid #000; padding: 4px; text-align: right">5</td>
                    <td style="border: 1px solid #000; padding: 4px; text-align: right">3</td>
                    <td style="border: 1px solid #000; padding: 4px; text-align: right">2</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Checkbox -->
<div style="margin-top: 15px">
    <table style="font-size: 8pt; border-collapse: collapse; width: 18%">
        <tbody>
            <tr>
                <td colspan="4"></td>
                <td>
                    <table style="border: 1px solid; width: 12px">
                        <tbody>
                            <tr>
                                <td style="height: 10px"></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td>Form STO terlampir</td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Directors Comment Box -->
<div style="width: 100%; border: 1px solid #000; margin-top: 15px; padding: 10px; height: 80px; position: relative">
    <span style="font-style: italic; position: absolute; top: 10px; left: 10px">Directors comment :</span>
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
        $mpdf->SetWatermarkText("BA STO-02-01");
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
}
