<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;

class Mcp_userlogin extends Model
{
    public static function read($param)
    {
        switch ($param["method"]) {
            case "read_data":
                return self::read_data($param);
            case "read_groupuser":
                return self::read_groupuser($param);
            default:
                return false;
        }
    }
    public static function read_data($param)
    {
        $query = DB::table("a_user as A")
            ->select([
                "A.*",
                DB::raw("ISNULL(B.DEPTCODE, '-') as DEPTCODE"),
                DB::raw("ISNULL(B.DEPTNAME, '-') as DEPTNAME")
            ])
            ->leftJoin('VW_DEPARTMENT as B', function ($join) use ($param) {
                $join->on('A.USERDEPT', '=', 'B.DEPTCODE');
            });

        if (array_key_exists('filter', $param)) {
            $keyval = json_decode($param['filter'], true);
            foreach ($keyval as $key => $val) {
                $query->whereRaw("{$val['property']} LIKE ?", ["%" . strtoupper($val['value']) . "%"]);
            }
        }
        $tempdb = clone $query;
        $count = $tempdb->count();

        if (array_key_exists('limit', $param)) {
            $query->limit($param['limit'])->offset($param['start']);
        }

        if (array_key_exists('sort', $param)) {
            $keyval = json_decode($param['sort'], true);
            foreach ($keyval as $key => $val) {
                $query->orderBy($val['property'], $val['direction']);
            }
        } else {
            $query->orderBy('A.SYSCREATEDATE', 'desc');
            $query->orderBy('A.SYSUPDATEDATE', 'desc');
        }

        $rows = $query->get();
        $data = [
            'TotalRows' => $count,
            'Rows' => $rows
        ];
        return json_encode($data);
    }
    public static function read_groupuser($param)
    {
        $query = DB::table("VW_GROUPUSER")
            ->select([
                "*",
            ]);


        if (array_key_exists('filter', $param)) {
            $keyval = json_decode($param['filter'], true);
            foreach ($keyval as $key => $val) {
                $query->whereRaw("{$val['property']} LIKE ?", ["%" . strtoupper($val['value']) . "%"]);
            }
        }
        $tempdb = clone $query;
        $count = $tempdb->count();

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
        $data = [
            'TotalRows' => $count,
            'Rows' => $rows
        ];
        return json_encode($data);
    }
    public static function process_data($param)
    {
        $SQL_CALLSP = "EXEC SP_PROCESS_CP_USERLOGIN
        @VUSERLOGIN=?,
        @VMODULE=?,
        @VDATA=?,
        @VDEPT=?
        ";
        $data = [
            $param['VUSERLOGIN'] ?? "",
            $param['module'] ?? "",
            $param['vdata'] ?? '{}',
            $param['vdept'] ?? '{}'
        ];
        $result = DB::select($SQL_CALLSP, $data);
        return json_encode($result);
    }
    public static function download_file($param)
    {

        $varlike = '';
        $arr_field = [
            'USERGROUP' => 'USERGROUP',
            'USERLOGIN' => 'USERLOGIN',
            'USERNAME' => 'USERNAME',
            'DEPTCODE' => 'DEPTCODE',
            'DEPTNAME' => 'DEPTNAME',
            'USEREMAIL' => 'USEREMAIL',
            'USERACTIVE' => 'USERACTIVE'
        ];

        if (array_key_exists('filter', $param)) {
            $keyval = json_decode($param['filter'], true);
            foreach ($keyval as $key => $val) {
                //$this->db->like(array_search($val['property'],$arr_field),$val['value']);
                $varlike = $varlike . " AND " . array_search($val['property'], $arr_field) . " like '%" . $val['value'] . "%'";
            }
        }

        $SQL = "
        SELECT
        A.USERGROUP,A.USERLOGIN,A.USERNAME,A.USEREMAIL,A.USERACTIVE,
        B.DEPTCODE,B.DEPTNAME
        FROM a_user A
        LEFT JOIN VW_DEPARTMENT B ON A.USERDEPT = B.DEPTCODE
        WHERE DEFID<>''
        " . $varlike . "
        ";
        $dateTime = new \DateTime();
        $vfilename = 'download_user_login_' . $dateTime->format('Y_m_d_H_i_s') . '.xlsx';
        $directoryPath = base_path('z_download');

        if (!is_dir($directoryPath)) {
            mkdir($directoryPath, 0775, true);
        }

        $filepath = $directoryPath . '/' . $vfilename;

        $rows = DB::select($SQL);
        if (!$rows) {
            $hasil = [
                'success' => "false",
                'message' => 'Data to download empty',
            ];

            return json_encode($hasil);
        }

        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToFile($filepath);

        $header = array_keys((array)$rows[0] ?? []);
        $headerRow = WriterEntityFactory::createRowFromArray($header);
        $writer->addRow($headerRow);

        foreach ($rows as $row) {
            $dataRow = WriterEntityFactory::createRowFromArray((array)$row);
            $writer->addRow($dataRow);
        }

        $writer->close();

        $hasil = [
            'success' => "true",
            'remark' => 'File Download',
            'filename' => 'apservice/z_download/' . $vfilename
        ];

        return json_encode($hasil);
    }
}
