<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use App\Custom\userJWT;
use OpenSpout\Writer\XLSX\Writer as XLSXWriter;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use DateTime;
use Tymon\JWTAuth\Facades\JWTAuth;


class Mglobal extends Model
{

    public static function handleAction($method, $params)
    {
        switch ($method) {
            case 'read_data':
                return self::read_data($params);
            default:
                return ['error' => 'Action not recognized'];
        }
    }

    public static function read_part_group($param)
    {
        $sql = DB::table('vw_mst_part_group')
            ->get();
        return $sql;
    }
    public static function read_part_category($param)
    {
        $sql = DB::table('vw_mst_part_category')
            ->get();
        return $sql;
    }
    public static function read_part_type($param)
    {
        $sql = DB::table('vw_mst_part_type')
            ->get();
        return $sql;
    }
    public static function read_part_uom($param)
    {
        $sql = DB::table('vw_mst_part_uom')
            ->get();
        return $sql;
    }
    public static function read_part_base_part($param)
    {
        $sql = DB::table('vw_mst_part_base')
            ->get();
        return $sql;
    }
    public static function read_part_rackcategory($param)
    {
        $sql = DB::table('vw_mst_rack_category')
            ->get();
        return $sql;
    }
}
