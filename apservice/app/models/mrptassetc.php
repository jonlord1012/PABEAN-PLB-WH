<?php

namespace App\Models;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use DateTime;

class Mrptassetc extends Model
{
    public static function handleAction($method, $param)
    {
        switch ($method) {
            case 'read_data':
                return self::read_data($param);
            default:
                return ['error' => 'Action not recognized'];
        }
    }

    public static function read_data($param)
    {
        $query = DB::table('assetdata')
            ->select(
                'assetdata.assetid',
                'assetdata.assetcondition',
                'derived_table.assetcondition',
                'derived_table.period',
                'assetdata.assetno',
                'assetdata.assetkey',
                'assetdata.assetsapno',
                'assetdata.assetname',
                'assetdata.assetgroup',
                'assetdata.assetcategory',
                'assetdata.assetlocation',
                'assetdata.assetsublocation',
                'assetdata.assetcostcenter',
                'assetdata.assetpic',
                'assetdata.assetremark',
                'assetdata.assetinfo',
                'assetdata.assetaquisitiondate',
                'assetdata.assetlabel',
                'assetdata.assetcost'
            )
            ->join(
                DB::raw("(SELECT A.assetno, A.assetcondition, B.period
                        FROM stodata A
                        LEFT JOIN speriod B
                        ON A.period = B.period
                        WHERE B.period = (
                            SELECT period
                            FROM speriod
                            WHERE status = 'OPEN'
                            ORDER BY tgl_mulai DESC
                            LIMIT 1
                        )
                        AND NOT A.assetcondition LIKE '%BAGUS%'
                        GROUP BY A.assetno, A.assetcondition, B.period) AS derived_table"),
                'assetdata.assetno',
                '=',
                'derived_table.assetno'
            )
            ->whereColumn('assetdata.assetcondition', '<>', 'derived_table.assetcondition');

        // Handle filters
        if (array_key_exists('filter', $param)) {
            $keyval = json_decode($param['filter'], true);
            foreach ($keyval as $key => $val) {
                $query->where($val['property'], 'like', '%' . $val['value'] . '%');
            }
        }

        // Clone the query to calculate total rows count
        $countQuery = clone $query;
        $count = $countQuery->count();

        // Handling sorting if provided in params
        if (array_key_exists('sort', $param)) {
            $keyval = json_decode($param['sort'], true);
            foreach ($keyval as $key => $val) {
                $query->orderBy($val['property'], $val['direction']);
            }
        }

        // Handling limit and offset if provided
        if (array_key_exists('limit', $param)) {
            $query->limit($param['limit'])->offset($param['start']);
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

        $field = array(
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
        );

        if (!empty($vdata['assetid'])) {
            $updatedGroup = DB::table('assetdata')->where('assetid', $vdata['assetid'])->update(array_filter($field));

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
            $createdGroup = DB::table('assetdata')->insert(array_filter($field));

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
