<?php

namespace App\Models;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use DateTime;

class Masset_data extends Model
{

    public static function handleAction($method, $param)
    {
        switch ($method) {
            case 'read_data':
                return self::read_data($param);
            case 'read_item_part':
                return self::read_item_part($param);
            case 'fromlp':
                return self::fromlp($param);
            default:
                return ['error' => 'Action not recognized'];
        }
    }

    public static function read_data($param)
    {
        $labelbarcode = isset($param['labelbarcode']) ? $param['labelbarcode'] : "";

        $query = DB::table('assetdata')
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
        $vdata = json_decode($param['vdata'], true);
        $query = DB::table('assetdata_detail')
            ->select('*')
            ->where("assetnumber", $vdata["assetno"]);

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

        $field = [
            'assetinfo' => $vdata['assetinfo'],
            'assetno' => $vdata['assetno'],
            'assetsapno' => $vdata['assetsapno'],
            'assetkey' => $vdata['assetkey'],
            'assetname' => $vdata['assetname'],
            'assetpic' => $vdata['assetpic'],
            'assetgroup' => $vdata['assetgroup'],
            'assetcategory' => $vdata['assetcategory'],
            'assetcost' => $vdata['assetcost'],
            'assetcostcenter' => $vdata['assetcostcenter'],
            'assetlocation' => $vdata['assetlocation'],
            'assetsublocation' => $vdata['assetsublocation'],
            'assetcondition' => $vdata['assetcondition'],
            'assetaquisitiondate' => $vdata['assetaquisitiondate'],
            'assetlabel' => $vdata['assetlabel'],
            'assetremark' => $vdata['assetremark'],
            'syscreateuser' => $param['VUSERLOGIN'],
            'syscreatedate' => date('Y-m-d h:i:s')
        ];

        if (!empty($vdata['assetid'])) {
            $updatedGroup = DB::table('assetdata')->where('assetid', $vdata['assetid'])->update(array_filter($field));

            if ($updatedGroup) {
                foreach ($vitem as $item) {
                    $existingDetail = DB::table('assetdata_detail')
                        ->where('assetnumber', $vdata['assetid'])
                        ->where('grnumber', $item['grnumber'])
                        ->first();

                    if ($existingDetail) {
                        // Update existing record
                        DB::table('assetdata_detail')
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
                        DB::table('assetdata_detail')->insert([
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
            $createdGroup = DB::table('assetdata')->insertGetId(array_filter($field), 'assetid');

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

                DB::table('assetdata_detail')->insert($detailData);

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

        $deletedDetails = DB::table('assetdata_detail')->where('assetnumber', $vdata['assetid'])->delete();

        $deleted = DB::table('assetdata')->where('assetid', $vdata['assetid'])->delete();

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

    public static function download_data_report($param)
    {
        ini_set('max_execution_time', 240);
        $query = DB::table('stodata')
            ->select('period', 'assetinfo', 'assetno', 'assetsapno', 'assetkey', 'assetaquisitiondate', 'assetname', 'assetpic', 'assetgroup', 'assetcategory', 'assetlocation', 'assetsublocation', 'assetcondition', 'assetlabel', 'assetremark', 'assetcostcenter', 'assetcost', 'locationsto', 'conditionsto', 'usernik', 'userscan', 'scandate', 'syscreatedate as create date', 'syscreateuser as create user', );

        if (array_key_exists('filter', $param)) {
            $keyval = json_decode($param['filter'], true);
            foreach ($keyval as $key => $val) {
                $query->where($val['property'], 'like', '%' . $val['value'] . '%');
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
        $vfilename = "report_sto_download_" . $date->format('Y_m_d_H_i_s') . ".xlsx";
        $outputFilePath = base_path("z_download/" . $vfilename);

        $writer->openToFile($outputFilePath);

        $firstSheet = $writer->getCurrentSheet();
        $firstSheet->setName('Rerpot STO');
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


}
