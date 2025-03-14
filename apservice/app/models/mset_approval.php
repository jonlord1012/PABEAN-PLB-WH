<?php

namespace App\Models;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use DateTime;
use Illuminate\Support\Facades\Date;

class Mset_approval extends Model
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
        $query = DB::table('vw_department AS A')
            ->select([
                'A.defmodule',
                'A.deptcode',
                'A.deptname',
                'B.defid',
            ])
            ->leftJoin('cpmatrix AS B', 'A.deptcode', '=', 'B.defcode');

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

        if (isset($param['limit'])) {
            $query->limit($param['limit'])->offset($param['start']);
        }

        if (isset($param['sort'])) {
            $keyval = json_decode($param['sort'], true);
            foreach ($keyval as $val) {
                $query->orderBy($val['property'], $val['direction']);
            }
        }

        $rows = $query->get()->toArray();
        return json_encode([
            'TotalRows' => $count,
            'Rows' => $rows,
        ]);
    }
    public static function read_module($param)
    {
        $query = DB::table('vw_doc_approval AS A')
            ->select([
                'A.defmodule',
                'A.deptcode',
                'A.deptname',
                'A.defid',
            ]);

        if (array_key_exists('keywhere', $param)) {
            $keyval = json_decode($param['keywhere'], true);
            foreach ($keyval as $key => $val) {
                $query->where($val['property'], $val['value']);
            }
        }

        if (array_key_exists('filter', $param)) {
            $keyval = json_decode($param['filter'], true);
            foreach ($keyval as $val) {
                $query->where($val['property'], 'LIKE', '%' . $val['value'] . '%');
            }
        }

        $count = $query->count();

        if (isset($param['limit'])) {
            $query->limit($param['limit'])->offset($param['start']);
        }

        if (isset($param['sort'])) {
            $keyval = json_decode($param['sort'], true);
            foreach ($keyval as $val) {
                $query->orderBy($val['property'], $val['direction']);
            }
        }

        $rows = $query->get()->toArray();
        return json_encode([
            'TotalRows' => $count,
            'Rows' => $rows,
        ]);
    }

    public static function list_groupdepartment($param)
    {
        $vdata = json_decode($param['vdata'], true);
        $query = DB::table("VW_DEPARTMENT as A")
            ->select(["A.*", "B.userlogin", "B.USERDEPT"])
            ->leftJoin('cpuser_DEPARTMENT as B', 'A.deptcode', '=', 'B.USERDEPT')
            ->where("B.userlogin", $vdata['userlogin']);

        $rows = $query->get();
        return json_encode($rows);
    }

    public static function create($param)
    {
        $vdata = json_decode($param['data'], true);
        $field = [
            'defmodule' => $vdata['defmodule'],
            'defcode' => $vdata['defcode'],
            'defname' => $vdata['defname'],
            'syscreateuser' => $param['VUSERNAME'],
            'syscreatedate' => date('Y-m-d H:i:s'),
        ];
        $isExist = DB::table('cpmatrix')->where("defcode", $vdata['defcode'])->first();
        if ($isExist) {
            DB::table('cpmatrix')
                ->where("defcode", $vdata['defcode'])
                ->update(array_filter($field));
            return json_encode([
                'success' => 'true',
                'message' => 'Kode sudah ada, deskripsi berhasil diubah'
            ]);
        }
        $result = DB::table('cpmatrix')->insert($field);

        return json_encode([
            'success' => $result ? 'true' : 'false',
            'message' => $result ? 'Insert Data Success' : 'Insert Data Failed',
        ]);
    }

    public static function update_data($param)
    {
        $vdata = json_decode($param['data'], true);
        $field = [
            'defmodule' => $vdata['defmodule'],
            'defcode' => $vdata['defcode'],
            'defname' => $vdata['defname'],
            'sysupdateuser' => $param['VUSERNAME'],
            'sysupdatedate' => Date::now(),
        ];

        $result = DB::table('cpmatrix')
            ->where('defid', $vdata['defid'])
            ->update($field);

        return json_encode([
            'success' => $result ? 'true' : 'false',
            'message' => $result ? 'Update Data Success' : 'Update Data Failed',
        ]);
    }

    public static function edit($param)
    {
        $vdata = $param['data'];
        $rows = DB::table('cpmatrix')->where('defid', $vdata['defid'])->first();

        return json_encode([
            'success' => 'true',
            'message' => 'Data Displayed',
            'data' => $rows,
        ]);
    }

    public static function delete_data($param)
    {
        $vdata = json_decode($param['data'], true);
        $result = DB::table('cpmatrix')->where('defid', $vdata['defid'])->delete();
        DB::table('cdept_approval')->where('dept_code', $vdata['defcode'])->delete();

        return json_encode([
            'success' => $result ? "true" : "false",
            'message' => $result ? 'Delete Data Success' : 'Delete Data Failed',
        ]);
    }

    public static function read_dept_approval($param)
    {
        $vdata = json_decode($param['vdata'], true);
        $SQL = "
            SELECT
                A.id,
                A.dept_code,
                A.module_name,
                A.approval,
                CONCAT('COLUMN_',
                    CASE
                        WHEN A.approval LIKE '%DOC_APPROVAL1%' THEN '1'
                        WHEN A.approval LIKE '%DOC_APPROVAL2%' THEN '2'
                        WHEN A.approval LIKE '%DOC_APPROVAL3%' THEN '3'
                        WHEN A.approval LIKE '%DOC_APPROVAL4%' THEN '4'
                        ELSE '0'  -- You can change this to a more meaningful value if needed
                    END
                ) AS APPROVAL,
                A.userlogin,
                B.username 
            FROM cdept_approval AS A
            LEFT JOIN cpuser B ON A.userlogin = B.userlogin
            WHERE A.module_name = '" . $vdata['module_name'] . "' 
            AND A.dept_code = '" . $vdata['deptcode'] . "'
            ORDER BY A.approval
        ";

        $result = DB::select($SQL);
        return json_encode($result);
    }

    public static function list_userapproval($param)
    {
        $SQL = "
        select userlogin,USERNAME,USERGROUP,USERACTIVE FROM cpuser where USERACTIVE='YES'
        ";

        $result = DB::select($SQL);
        return json_encode($result);
    }

    public static function process_datsa($param)
    {
        $SQL_CALLSP = "EXEC sp_proccess_doc
        @Vuserlogin=?,
        @VMODULE=?,
        @VDATA=?
        ";
        $data = [
            $param['Vuserlogin'],
            $param['module'],
            $param['vdata'],
        ];
        $result = DB::select($SQL_CALLSP, $data);
        return json_encode($result);
    }


    public static function process_data($param)
    {

        $data = array(
            0 => $param['vuserlogin'],
            1 => $param['vmodule'],
            2 => $param['vdata'],
        );

        $result = DB::select('SELECT * FROM sp_process_doc_approval(?,?,?)', $data);
        return json_encode($result);
    }

    public static function download_data($param)
    {
        ini_set('max_execution_time', 240);
        $query = DB::table('vw_department')
            ->select('deptcode', 'deptname', );

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
        $vfilename = "download_data_department_" . $date->format('Y_m_d_H_i_s') . ".xlsx";
        $outputFilePath = base_path("z_download/" . $vfilename);

        $writer->openToFile($outputFilePath);

        $firstSheet = $writer->getCurrentSheet();
        $firstSheet->setName('Departments');
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
