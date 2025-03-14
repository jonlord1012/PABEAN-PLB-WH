<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;


class Mmdocument extends Model
{
    public static function handleAction($method, $param)
    {
        switch ($method) {
            case 'read_data_change_requist':
                return self::read_data_change_requist($param);
            case 'read_data_asset_idle':
                return self::read_data_asset_idle($param);
            default:
                return ['error' => 'Action not recognized'];
        }
    }

    public static function read_data_change_requist($param)
    {
        $query = DB::table('cpmatrix')
            ->select('defcode', 'defname')
            ->where('defmodule', 'MC_REQUIST');

        $rows = $query->get();
        return json_encode([
            'TotalRows' => $rows->count(),
            'Rows' => $rows
        ]);
    }
    public static function read_data_asset_idle($param)
    {
        $query = DB::table('cpmatrix')
            ->select('defcode', 'defname')
            ->where('defmodule', 'MCA_IDLE');

        $rows = $query->get();
        return json_encode([
            'TotalRows' => $rows->count(),
            'Rows' => $rows
        ]);
    }
}
