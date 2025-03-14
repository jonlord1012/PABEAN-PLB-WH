<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;

class Mcp_usergroup extends Model
{
    public static function read($param)
    {
        switch ($param["method"]) {
            case "read_data":
                return self::read_data($param);
            case "create_group":
                return self::create_group($param);
            case "delete_group":
                return self::delete_group($param);
            case "update_groupaccess":
                return self::update_groupaccess($param);
            default:
                return false;
        }
    }
    public static function read_data($param)
    {
        $query = DB::table("VW_GROUPUSER as A")
            ->select("A.*");

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
        @VDATA=?
        ";
        $data = [
            $param['VUSERLOGIN'] ?? "",
            $param['module'] ?? "",
            $param['vdata'] ?? '{}'
        ];
        $result = DB::select($SQL_CALLSP, $data);
        return json_encode($result);
    }
    public static function download_file($param)
    {

        $varlike = '';
        $arr_field = [
            'DEFID' => 'DEFID',
            'DEFMODULE' => 'DEFMODULE',
            'GROUPNAME' => 'GROUPNAME'
        ];

        if (array_key_exists('filter', $param)) {
            $keyval = json_decode($param['filter'], true);
            foreach ($keyval as $key => $val) {
                //$this->db->like(array_search($val['property'],$arr_field),$val['value']);
                $varlike = $varlike . " AND " . array_search($val['property'], $arr_field) . " like '%" . $val['value'] . "%'";
            }
        }

        $SQL = "
        select * from VW_GROUPUSER where
        DEFID<>''
        " . $varlike . "
        ";

        $dateTime = new \DateTime();
        $vfilename = 'download_user_group_' . $dateTime->format('Y_m_d_H_i_s') . '.xlsx';
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

    public static function create_group($param)
    {
        $SQL = "
            declare @VGROUP varchar(20) = '" . $param['vdata'] . "';
            SET @VGROUP = 'GROUP_' + REPLACE(@VGROUP, ' ', '_');
            IF NOT EXISTS (select top 1 * FROM VW_GROUPUSER WHERE GROUPNAME=@VGROUP)
            BEGIN
                insert into VW_GROUPUSER(DEFMODULE,GROUPNAME)
                VALUES('GROUP_USERLOGIN',@VGROUP);
            END
        ";
        $result = DB::statement($SQL);
        return json_encode($result);
    }
    public static function delete_group($param)
    {
        $SQL = "
            declare @VGROUP varchar(20) = '" . $param['vdata'] . "';
            SET @VGROUP = REPLACE(@VGROUP,' ','_');
            IF EXISTS (select top 1 * FROM VW_GROUPUSER WHERE GROUPNAME=@VGROUP)
            BEGIN
                delete VW_GROUPUSER WHERE GROUPNAME=@VGROUP;
                delete from a_menuaccess where RGROUP=@VGROUP;
            END
        ";
        $result = DB::statement($SQL);
        return json_encode($result);
    }

    public static function list_menuaccess($param)
    {

        $vdata = json_decode($param['vdata'], true);
        $vgroup_user = $vdata['GROUPNAME'] ?? '';
        $SQL = "
        WITH TBL_MENU as (
            SELECT A.MCODE,A.MPARRENT,A.MMODULE,A.MNAME,A.MCONTROL,A.MCHILDREN,A.MEXPAND,A.MLEAF,A.MSHORT,A.MALLOWCLICK,A.MQTIP,
            CASE
                WHEN B.DEFSHORT is not NULL THEN B.DEFSHORT
                ELSE 1000
            END as MODULE_SHORT,
            CASE
                WHEN B.DEFNAME is not NULL THEN UPPER(B.DEFNAME)
                ELSE ''
            END as MODULE_NAME
            FROM a_menu as A
            LEFT JOIN a_matrix as B on A.MMODULE = B.DEFCODE
            ),
            TBL_AKSES as (
            SELECT DISTINCT A.RGROUP,A.RMENUID,B.MPARRENT FROM a_menuaccess A
            LEFT JOIN a_menu as B on A.RMENUID = B.MCODE
            WHERE A.RGROUP='" . $vgroup_user . "' AND B.MCODE is not NULL
            )
            SELECT A.*,
            CASE
                WHEN B.RMENUID is NOT NULL THEN 'TRUE'
                ELSE 'FALSE'
            END as CHECKED,
            '" . $vgroup_user . "' as GROUP_USER
            FROM TBL_MENU A
            LEFT JOIN TBL_AKSES B on A.MCODE = B.RMENUID


        ";
        $result = DB::select($SQL);
        return json_encode($result);
    }

    public static function update_groupaccess($param)
    {
        $SQL = "
            DECLARE @VJSONDATA nvarchar(max)='" . $param['vdata'] . "';
            DECLARE @VGROUP_USER varchar(30);
            SELECT TOP 1 @VGROUP_USER=GROUP_USER FROM OPENJSON(@VJSONDATA) WITH
            (
            GROUP_USER varchar(max),
            MCODE varchar(max)
            );


            WITH TBL_JSON as (
            SELECT GROUP_USER,MCODE FROM OPENJSON(@VJSONDATA) WITH
            (
            GROUP_USER varchar(max),
            MCODE varchar(max)
            )
            ),
            TBL_JOIN as (
            SELECT A.GROUP_USER,B.MCODE,B.MMODULE
            FROM TBL_JSON A
            LEFT JOIN a_menu B on A.MCODE = B.MCODE
            WHERE B.MMODULE is not NULL
            ),
            TARGET as (
            SELECT * FROM a_menuaccess WHERE RGROUP=@VGROUP_USER
            )
            MERGE INTO TARGET
            USING TBL_JOIN  SOURCE
            ON TARGET.RGROUP = SOURCE.GROUP_USER AND TARGET.RMODULE = SOURCE.MMODULE AND TARGET.RMENUID = SOURCE.MCODE
            WHEN NOT MATCHED BY TARGET THEN
            INSERT (
            RGROUP,RMODULE,RMENUID
            ) VALUES (
            SOURCE.GROUP_USER,SOURCE.MMODULE,SOURCE.MCODE

            )
            WHEN NOT MATCHED BY SOURCE THEN
            DELETE
            ;

            --===========================
            -- VALIDASI ULANG
            --===========================
            DECLARE @NTBL_ACC as table(
                RGROUP varchar(25),
                RMENUID varchar(20),
                RMODULE varchar(50)
            );
            WITH TBL_ACC as (
            SELECT A.RGROUP,A.RMENUID,B.MPARRENT as MPARRENT1 FROM a_menuaccess A
            LEFT JOIN a_menu B on A.RMENUID = B.MCODE
            WHERE RGROUP=@VGROUP_USER
            ),
            TBL_ACC2 as (
            SELECT A.RGROUP,A.RMENUID,A.MPARRENT1,B.MPARRENT as MPARRENT2 FROM TBL_ACC A
            LEFT JOIN a_menu B on A.MPARRENT1 = B.MCODE
            WHERE RGROUP=@VGROUP_USER
            ),
            TBL_ACC3 as (
            SELECT A.RGROUP,A.RMENUID,A.MPARRENT1,A.MPARRENT2,B.MPARRENT as MPARRENT3 FROM TBL_ACC2 A
            LEFT JOIN a_menu B on A.MPARRENT2 = B.MCODE
            WHERE RGROUP=@VGROUP_USER
            ),
            TBL_ACC4 as (
            SELECT A.RGROUP,A.RMENUID,A.MPARRENT1,A.MPARRENT2,A.MPARRENT3,B.MPARRENT as MPARRENT4 FROM TBL_ACC3 A
            LEFT JOIN a_menu B on A.MPARRENT3 = B.MCODE
            WHERE RGROUP=@VGROUP_USER
            )
            INSERT INTO @NTBL_ACC (RGROUP,RMENUID)
            SELECT RGROUP,RMENUID FROM TBL_ACC4 UNION ALL
            SELECT RGROUP,MPARRENT1 FROM TBL_ACC4 UNION ALL
            SELECT RGROUP,MPARRENT2 FROM TBL_ACC4 UNION ALL
            SELECT RGROUP,MPARRENT3 FROM TBL_ACC4 UNION ALL
            SELECT RGROUP,MPARRENT4 FROM TBL_ACC4 ;

            DELETE FROM @NTBL_ACC WHERE RMENUID is NULL or RMENUID='0';

            UPDATE T SET
            T.RMODULE = S.MMODULE
            FROM @NTBL_ACC T
            INNER JOIN a_menu S on T.RMENUID = S.MCODE;

            WITH TARGET as (
                SELECT * FROM a_menuaccess WHERE RGROUP=@VGROUP_USER
            )
            MERGE INTO TARGET
            USING (
                SELECT DISTINCT RGROUP,RMENUID,RMODULE FROM @NTBL_ACC
                )   SOURCE
            ON TARGET.RGROUP = SOURCE.RGROUP AND TARGET.RMENUID = SOURCE.RMENUID
            WHEN NOT MATCHED BY TARGET THEN
            INSERT (
            RGROUP,RMODULE,RMENUID
            ) VALUES (
            SOURCE.RGROUP,SOURCE.RMODULE,SOURCE.RMENUID
            )
            ;



        ";
        DB::statement($SQL);
        $hasil = array(
            'success' => 'true',
            'message' => 'Data berhasil di update'
        );

        return json_encode($hasil);
    }
}
