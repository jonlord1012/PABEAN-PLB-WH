<?php

namespace App\Models;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use DateTime;

class Msub_data_asset extends Model
{

    public static function handleAction($method, $param)
    {
        switch ($method) {
            case 'read_data':
                return self::read_data($param);
            case 'read_item_part':
                return self::read_item_part($param);
            case 'fromsumber_data':
                return self::fromsumber_data($param);
            case 'read_lampiran':
                return self::read_lampiran($param);
            default:
                return ['error' => 'Action not recognized'];
        }
    }

    public static function read_data($param)
    {
        $labelbarcode = isset($param['labelbarcode']) ? $param['labelbarcode'] : "";

        $query = DB::table('tr_sub_asset')
            ->select('*');
        if ($labelbarcode)
            $query->where("assetlabel", $labelbarcode);

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
        $query = DB::table('tr_sub_asset')
            ->select('*')
            ->where("assetnumber", $param["assetnumber"]);

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
    public static function fromsumber_data($param)
    {
        // $query = DB::connection('oracle')->table('GRMASTER')  // Menggunakan koneksi Oracle
        //     ->select('*');
        $query = DB::table('sumber_data_asset')  // Menggunakan koneksi Oracle
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
        $query = DB::table('sub_data_asset')
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
        $vfilename = "sub_data_asset_download_" . $date->format('Y_m_d_H_i_s') . ".xlsx";
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
        $field = [
            'asset_type' => $vdata['asset_type'],
            'sub_asset_no' => $vdata['assetno'],
            'asset_key' => $vdata['assetkey'],
            'asset_sapno' => $vdata['assetsapno'],
            'asset_partname' => $vdata['assetname'],
            'asset_aquisition_date' => $vdata['assetaquisitiondate'],
            'ponumber' => $vdata['ponumber'],
            'podate' => $vdata['podate'],
            'grnumber' => $vdata['grnumber'],
            'grdate' => $vdata['grdate'],
            'prnumber' => $vdata['prnumber'],
            'prdate' => $vdata['prdate'],
            'invnumber' => $vdata['invnumber'],
            'invdate' => $vdata['invdate'],
            'asset_pic_dept' => $vdata['asset_pic_dept'],
            'asset_group' => $vdata['assetgroup'],
            'asset_category' => $vdata['assetcategory'],
            'asset_cost' => $vdata['assetcost'],
            'asset_costcenter' => $vdata['assetcostcenter'],
            'asset_label' => $vdata['assetlabel'],
            'asset_remark' => $vdata['assetremark'],
            'asset_location' => $vdata['assetlocation'],
            'asset_sub_location' => $vdata['assetsublocation'],
            'asset_condition' => $vdata['assetcondition'],
        ];

        if (!empty($vdata['assetid'])) {
            $updatedGroup = DB::table('sub_data_asset')->where('assetid', $vdata['assetid'])->update(array_filter($field));

            if ($updatedGroup) {
                foreach ($vitem as $item) {
                    $existingDetail = DB::table('sub_data_asset_detail')
                        ->where('assetnumber', $vdata['assetid'])
                        ->where('grnumber', $item['grnumber'])
                        ->first();

                    if ($existingDetail) {
                        // Update existing record
                        DB::table('sub_data_asset_detail')
                            ->where('id', $existingDetail->id)
                            ->update([
                                'partname' => $item['partname'],
                                'grqty' => $item['grqty'],
                                'partgroup' => $item['partgroup'],
                                'partcategory' => $item['partcategory'],
                                'part_price' => $item['part_price'],
                                'part_currency' => $item['part_currency'],
                                'grnumber' => $item['grnumber'],
                                'grdate' => $item['grdate'],
                                'ponumber' => $item['ponumber'],
                                'podate' => $item['podate'],
                                'sumber_data' => $item['sumber_data'],
                                'sysupdatedate' => date('Y-m-d h:i:s'),
                                'sysupdateuser' => $param['VUSERLOGIN']
                            ]);
                    } else {
                        // Insert new record if not exists
                        DB::table('sub_data_asset_detail')->insert([
                            'assetnumber' => $vdata['assetid'],
                            'partname' => $item['partname'],
                            'partcode' => $item['partcode'],
                            'grqty' => $item['grqty'],
                            'partgroup' => $item['partgroup'],
                            'partcategory' => $item['partcategory'],
                            'part_price' => $item['part_price'],
                            'part_currency' => $item['part_currency'],
                            'grnumber' => $item['grnumber'],
                            'grdate' => $item['grdate'],
                            'ponumber' => $item['ponumber'],
                            'podate' => $item['podate'],
                            'sumber_data' => $item['sumber_data'],
                            'syscreatedate' => date('Y-m-d h:i:s'),
                            'syscreateuser' => $param['VUSERLOGIN']
                        ]);
                    }
                }

                return json_encode([
                    'success' => true,
                    'message' => 'Update Data Success'
                ]);
            } else {
                return json_encode([
                    'success' => false,
                    'message' => 'Update Data Failed'
                ]);
            }
        } else {
            // Insert new data
            $createdGroup = DB::table('sub_data_asset')->insertGetId(array_filter($field), 'assetid');

            if ($createdGroup) {
                $detailData = [];
                foreach ($vitem as $item) {
                    $detailData[] = [
                        'assetnumber' => $createdGroup,
                        'partname' => $item['partname'],
                        'partcode' => $item['partcode'],
                        'grqty' => $item['grqty'],
                        'partgroup' => $item['partgroup'],
                        'partcategory' => $item['partcategory'],
                        'part_price' => $item['part_price'],
                        'part_currency' => $item['part_currency'],
                        'grnumber' => $item['grnumber'],
                        'grdate' => $item['grdate'],
                        'ponumber' => $item['ponumber'],
                        'podate' => $item['podate'],
                        'sumber_data' => $item['sumber_data'],
                        'syscreatedate' => date('Y-m-d h:i:s'),
                        'syscreateuser' => $param['VUSERLOGIN']
                    ];
                }

                DB::table('sub_data_asset_detail')->insert($detailData);

                return json_encode([
                    'success' => true,
                    'message' => 'Add Data Success'
                ]);
            } else {
                return json_encode([
                    'success' => false,
                    'message' => 'Add Data Failed'
                ]);
            }
        }
    }
    public static function save_datas($param)
    {
        $vdata = json_decode($param['data'], true);
        // $vitem = json_decode($param['vitem'], true);

        $field = [
            'asset_type' => $vdata['asset_type'],
            'sub_asset_no' => $vdata['sub_asset_no'],
            'asset_key' => $vdata['asset_key'],
            'asset_sapno' => $vdata['asset_sapno'],
            'asset_partname' => $vdata['asset_partname'],
            'asset_partcode' => $vdata['sub_asset_no'],
            'asset_acquisition_date' => $vdata['asset_acquisition_date'],
            'ponumber' => $vdata['ponumber'],
            'podate' => $vdata['podate'],
            'grnumber' => $vdata['grnumber'],
            'grdate' => $vdata['grdate'],
            'prnumber' => $vdata['prnumber'],
            'prdate' => $vdata['prdate'],
            'invnumber' => $vdata['invnumber'],
            'invdate' => $vdata['invdate'],
            'asset_pic_dept' => $vdata['asset_pic_dept'],
            'asset_group' => $vdata['asset_group'],
            'asset_category' => $vdata['asset_category'],
            'asset_cost' => $vdata['asset_cost'],
            'asset_costcenter' => $vdata['asset_costcenter'],
            'asset_label' => $vdata['asset_label'],
            'asset_remark' => $vdata['asset_remark'],
            'asset_location' => $vdata['asset_location'],
            'asset_sub_location' => $vdata['asset_sub_location'],
            'asset_condition' => $vdata['asset_condition'],
            'syscreatedate' => date('Y-m-d h:i:s'),
            'syscreateuser' => $param['VUSERLOGIN']
        ];

        if (!empty($vdata['tbid'])) {
            $updatedGroup = DB::table('tr_sub_asset')->where('tbid', $vdata['tbid'])->update(array_filter($field));

            if ($updatedGroup) {
                return json_encode([
                    'success' => true,
                    'message' => 'Update Data Success'
                ]);
            } else {
                return json_encode([
                    'success' => false,
                    'message' => 'Update Data Failed'
                ]);
            }
        } else {
            // Insert new data
            $createdGroup = DB::table('tr_sub_asset')->insertGetId(array_filter($field), 'tbid');

            if ($createdGroup) {
                return json_encode([
                    'success' => true,
                    'message' => 'Add Data Success'
                ]);
            } else {
                return json_encode([
                    'success' => false,
                    'message' => 'Add Data Failed'
                ]);
            }
        }
    }

    public static function delete_data($param)
    {
        $vdata = json_decode($param['data'], true);

        if (!isset($vdata['assetid'])) {
            return response()->json([
                'success' => false,
                'message' => ' ID is required'
            ]);
        }

        $deletedDetails = DB::table('sub_data_asset_detail')->where('assetnumber', $vdata['assetid'])->delete();

        $deleted = DB::table('sub_data_asset')->where('assetid', $vdata['assetid'])->delete();

        if ($deleted) {
            return json_encode([
                'success' => true,
                'message' => 'Delete Data Success'
            ]);
        } else {
            return json_encode([
                'success' => false,
                'message' => 'User not found or could not be deleted'
            ]);
        }
    }
    public static function upload_dokumen($param)
    {
        if (isset($param['file']) && $param['file'] instanceof \Illuminate\Http\UploadedFile) {
            $file = $param['file'];
            $vdata = json_decode($param['params'], true);

            $datePrefix = date('ymdHis');
            $assetkey = $vdata['assetkey'];
            $fileId = "{$assetkey}-{$datePrefix}";
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();

            $nama_lampiran = "{$fileId}-{$originalName}.{$extension}";
            $lokasi_folder = base_path('../document/sub_data_asset/');
            $file->move($lokasi_folder, $nama_lampiran);

            $data_insert = [
                'assetkey' => $assetkey,
                'document_path' => "document/sub_data_asset/{$nama_lampiran}",
            ];

            DB::table('sub_data_asset_lampiran')->insert($data_insert);

            $sql_dokumen_lampiran = DB::table('sub_data_asset_lampiran')
                ->where('assetkey', $assetkey)
                ->get();

            $hasil = [
                'success' => 'true',
                'message' => 'upload dokumen success',
                'lampiran' => $sql_dokumen_lampiran
            ];


        } else {
            $hasil = [
                'success' => 'false',
                'message' => 'upload dokumen gagal',
                'lampiran' => null
            ];
        }
        return json_encode($hasil);

    }
    public static function read_lampiran($param)
    {
        $vdata = json_decode($param['vdata'], true);
        $sql_dokumen_lampiran = DB::table('sub_data_asset_lampiran')
            ->where('assetkey', $vdata)
            ->get();

        $hasil = [
            'success' => 'true',
            'message' => 'upload dokumen success',
            'Rows' => $sql_dokumen_lampiran
        ];
        return json_encode($hasil);

    }
}
