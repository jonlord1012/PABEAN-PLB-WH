<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;


class Msperiod extends Model
{
    public static function handleAction($method, $param)
    {
        switch ($method) {
            case 'read_data':
                return self::read_data($param);
            case 'save_data':
                return self::save_data($param);
            //   case 'create':
            //     return self::create($param);
            //   case 'update_data':
            //         return self::update_data($param);
            case 'delete_data':
                return self::delete_data($param);
            case 'load_month':
                return self::load_month($param);
            default:
                return ['error' => 'Action not recognized'];
        }
    }
    public static function read_data($param)
    {
        $query = DB::table('speriod')
            ->select("*");

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

    public static function save_data($param)
    {
        $vdata = json_decode($param['VDATA'], true);

        $field = array(
            'period' => date('Ymd'),
            'bulan' => $vdata['bulan'],
            'tahun' => date('Y'),
            'status' => $vdata['status'],
            'syscreateuser' => $param['VUSERLOGIN'],
            'syscreatedate' => date('Y-m-d h:i:s')
        );

        if (!empty($vdata['id'])) {
            $updatedUser = DB::table('speriod')->where('id', $vdata['id'])->update(array_filter($field));

            if ($updatedUser) {
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
            $createdUser = DB::table('speriod')->insert(array_filter($field));

            if ($createdUser) {
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
        $vdata = json_decode($param['VDATA'], true);

        if (!isset($vdata['id'])) {
            return response()->json([
                'success' => false,
                'message' => ' ID is required'
            ]);
        }

        $deleted = DB::table('speriod')->where('id', $vdata['id'])->delete();

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

    public static function load_month($param)
    {
        $query = DB::table('cpmatrix')
            ->select('defid', 'defcode', 'defname')
            ->where('defmodule', '=', 'MONTH');

        if (array_key_exists('keywhere', $param)) {
            $keyval = json_decode($param['keywhere'], true);
            foreach ($keyval as $key => $val) {
                $query->where($val['property'], $val['value']);
            }
        }

        if (array_key_exists('filter', $param)) {
            $keyval = json_decode($param['filter'], true);
            foreach ($keyval as $key => $val) {
                $query->whereRaw("{$val['property']} LIKE ?", ["%" . strtoupper($val['value']) . "%"]);
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
}
