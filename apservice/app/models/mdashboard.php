<?php
namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use DateTime;

class Mdashboard extends Model
{
    public static function dtpanel_board($param)
    {
        $period = $param['period'];
        $query = DB::select("
        SELECT
            p.period,
            COUNT(s.asset_no) AS total_assets,
            COUNT(CASE WHEN s.scan_date IS NOT NULL THEN 1 END) AS scanned_assets,
            ROUND(SUM(CASE WHEN s.scan_date IS NOT NULL THEN 1 ELSE 0 END)::numeric / COUNT(*)::numeric * 100, 1) AS percent_scanned,
            COUNT(CASE WHEN s.scan_date IS NULL THEN 1 END) AS asset_notscan,
            ROUND(SUM(CASE WHEN s.scan_date IS NOT NULL THEN 0 ELSE 1 END)::numeric / COUNT(*)::numeric * 100, 1) AS percent_notscan,
            TO_CHAR(MAX(p.tgl_mulai), 'DD MONTH YYYY') AS tgl_mulai,
            TO_CHAR(MAX(p.tgl_selesai), 'DD MONTH YYYY') AS tgl_selesai
        FROM
            speriod p
        LEFT JOIN
            stodata s ON s.period = p.period
        WHERE
            p.period = ?
        GROUP BY
            p.period, p.tgl_mulai, p.tgl_selesai
    ", [$period]);

        return $query;
    }


    public static function dtpanel_assetchange($param)
    {
        $period = $param['period'];
        $query = DB::select("
        SELECT
            c.defname AS kategori,
            COALESCE(SUM(CASE WHEN s.asset_condition = c.defname THEN 1 ELSE 0 END), 0) as qty
        FROM cpmatrix c
        LEFT JOIN (
            SELECT * FROM stodata
        ) s ON s.asset_condition = c.defname
        WHERE c.defmodule = 'MCONDITION'
        AND s.period = ?
        GROUP BY c.defname
    ", [$period]);


        return $query;
    }

    public static function dtpanel_pic($param)
    {
        $period = $param['period'];
        $query = DB::select("
          SELECT
            s.asset_pic_dept as area,
            COUNT(s.asset_no) AS qty,
            SUM(CASE WHEN s.scan_date IS NOT NULL THEN 1 ELSE 0 END) AS asset_scanned
          FROM stodata as s
          JOIN speriod p ON s.period = p.period
          WHERE p.status = 'OPEN'
          AND s.period = ?
          GROUP BY s.asset_pic_dept
      ", [$period]);
        return $query;
    }

    public static function dtpanel_lokasi($param)
    {
        $query = DB::select("
        SELECT
            s.asset_group AS area,
            CONCAT(s.asset_group, ' Scanned') AS area2,
            COUNT(s.asset_no) AS qty,
            SUM(CASE WHEN s.scan_date IS NOT NULL THEN 1 ELSE 0 END) AS asset_scanned
        FROM stodata AS s
        JOIN speriod AS p ON s.period = p.period
        WHERE p.status = 'OPEN'
        GROUP BY s.asset_group
    ");

        return $query;
    }


    public static function dtpanel_data($param)
    {
        $query = DB::select("
               SELECT * FROM assetdata

    ", );
        return $query;
    }
    public static function period_aktif($param)
    {
        $data = DB::select("SELECT * FROM speriod WHERE status = 'OPEN'");

        $data = json_decode(json_encode($data[0]), true);

        return json_encode(
            array(
                'panel_board' => self::dtpanel_board($data),
                'panel_assetchange' => self::dtpanel_assetchange($data),
                'panel_pic' => self::dtpanel_pic($data),
                'panel_lokasi' => self::dtpanel_lokasi($data),
            )
        );
    }

    public static function period_report($param)
    {
        $data = json_decode($param['data'], true);
        return json_encode(
            array(
                'panel_board' => self::dtpanel_board($data),
                'panel_assetchange' => self::dtpanel_assetchange($data),
                'panel_pic' => self::dtpanel_pic($data),
                'panel_lokasi' => self::dtpanel_lokasi($data),
            )
        );
    }

    public static function download_period_report($param)
    {

    }


}
