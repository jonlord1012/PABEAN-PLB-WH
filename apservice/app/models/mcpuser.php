<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;


class Mcpuser extends Model
{

  public static function read(array $param)
  {
    switch ($param['method']) {
      case 'read_data':
        return self::read_data($param);
      case 'create':
        return self::create($param);
      case 'update_data':
        return self::update_data($param);
      case 'delete_data':
        return self::delete_data($param);
      case 'list_group_user':
        return self::list_group_user($param);
      case 'list_group_department':
        return self::list_group_department($param);
      case 'list_menu_access':
        return self::list_menu_access($param);
      case 'list_my_menu_access':
        return self::list_my_menu_access($param);
      case 'update_groupaccess':
        return self::update_groupaccess($param);
      default:
        return json_encode([
          'success' => 'false',
          'message' => 'Method ' . $param['method'] . ' tidak ada'
        ]);
    }
  }

  public static function read_data($param)
  {
    $query = DB::table('cpuser')
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

  public static function create($param)
  {
    $vdata = json_decode($param['vheader'], true);
    $data = array(
      0 => $vdata["userpassword"],
    );
    $encryptPw = DB::select('SELECT * FROM tdk_md5(?)', $data)[0];
    $field = array(
      'userlogin' => $vdata['userlogin'] ?? null,
      'username' => $vdata['username'] ?? null,
      'userpassword' => $encryptPw->tdk_md5 ?? null,
      'useremail' => $vdata['useremail'] ?? null,
      'useractive' => $vdata['useractive'] ?? null,
      'usergroup' => $vdata['usergroup'] ?? null,
    );

    $createdUser = DB::table('cpuser')->insert(array_filter($field));

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


  public static function update_data($param)
  {
    $vdata = json_decode($param['vheader'], true);

    if (!isset($vdata['userid'])) {
      return response()->json([
        'success' => false,
        'message' => 'User ID is required'
      ]);
    }

    $field = [
      'userlogin' => $vdata['userlogin'] ?? null,
      'username' => $vdata['username'] ?? null,
      'userpassword' => $vdata['userpassword'] ?? null,
      'useremail' => $vdata['useremail'] ?? null,
      'useractive' => $vdata['useractive'] ?? null,
      'usergroup' => $vdata['usergroup'] ?? null,
    ];

    $user = DB::table('cpuser')->where('userid', $vdata['userid'])->update(array_filter($field));

    if ($user) {
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
  }

  public static function delete_data($param)
  {
    $vdata = json_decode($param['vheader'], true);

    if (!isset($vdata['userid'])) {
      return response()->json([
        'success' => false,
        'message' => 'User ID is required'
      ]);
    }

    $deleted = DB::table('cpuser')->where('userid', $vdata['userid'])->delete();

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


  public static function list_group_user($param)
  {
    $query = DB::table('a_matrix')
      ->select('*')
      ->where('defmodule', 'USER_GROUP');

    // $query2 = DB::table('wv_a_menuaccess')
    // ->select('*')
    // ->where('usergroup', $param['usergroup']);

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

  public static function list_group_department($param)
  {
    $decodeVdata = json_decode($param['vdata'], true);
    $query = DB::table('a_matrix')
      ->select('*')
      ->where(['defmodule' => 'MDEPARTMENT', 'defcode' => $decodeVdata['userdept']]);


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

  public static function list_menu_access($param)
  {
    $query = DB::table('a_menuaccess')
      ->select('a_menuaccess.*', 'a_menu.*')
      ->join('a_menu', 'a_menu.mcode', '=', 'a_menuaccess.rmenuid');


    $count = $query->count();

    $rows = $query->get();
    return json_encode([
      'TotalRows' => $count,
      'Rows' => $rows
    ]);
  }

  public static function list_my_menu_access($param)
  {
    $decodeVdata = json_decode($param['vdata'], true);

    $query = DB::table('a_menuaccess')
      ->where('rgroup', $decodeVdata['defcode'])
      ->select('a_menuaccess.*', 'a_menu.*')
      ->join('a_menu', 'a_menu.mcode', '=', 'a_menuaccess.rmenuid');

    $count = $query->count();

    $rows = $query->get();
    return json_encode([
      'TotalRows' => $count,
      'Rows' => $rows
    ]);
  }

  public static function update_groupaccess($param)
  {
    $paneldata = $param['panelsvdata'];

    foreach ($paneldata as $pdata) {
      if ($pdata['checked'] == true) {
        try {
          $field = array(
            'rgroup' => $param['chosen'] ?? null,
            'rmodule' => $pdata['rmodule'] ?? null,
            'rmenuid' => $pdata['mcode'] ?? null,
          );

          DB::table('a_menuaccess')->insert(array_filter($field));
        } catch (\Exception $e) {
        }
      } else if ($pdata['checked'] == false) {
        try {
          DB::table('a_menuaccess')
            ->where([
              'rgroup' => $param['chosen'],
              'rmodule' => $pdata['rmodule'],
              'rmenuid' => $pdata['mcode']
            ])->delete();
        } catch (\Exception $e) {
        }
      }
    }
  }

  //   public static function update_groupaccess($param)
  //   {
  //     $paneldata = $param['panelsvdata'];
  //     dd($paneldata);
  //     $decodeVdata = json_decode($param['vdata'], true);
  //     $existData = [];
  //     foreach ($decodeVdata as $data) {
  //         $results = DB::table('a_menuaccess')
  //             ->where([
  //                 'rgroup' => $param['chosen'],
  //                 'rmenuid' => $data['mcode']
  //             ])
  //             ->get();
  //         $existData = array_merge($existData, $results->toArray());
  //     }

  //     $modifiedExistData = [];
  //     foreach ($decodeVdata as $item) {
  //         $shouldKeep = true;

  //         foreach ($existData as $filter) {
  //             if ($item['rmodule'] === $filter->rmodule && $item['mcode'] === $filter->rmenuid) {
  //                 $shouldKeep = false;
  //                 break;
  //             }
  //         }

  //         if ($shouldKeep) {
  //             $modifiedExistData[] = $item;
  //         }
  //     }

  //     foreach ($modifiedExistData as $mED) {
  //         $field = array(
  //             'rgroup' => $param['chosen'] ?? null,
  //             'rmodule' => $mED['rmodule'] ?? null,
  //             'rmenuid' => $mED['mcode'] ?? null,
  //           );

  //       $createdAccess = DB::table('a_menuaccess')->insert(array_filter($field));
  //     }


  //       if ($createdAccess) {
  //         return json_encode([
  //           'success' => true,
  //           'message' => 'Update Group Success'
  //         ]);
  //       } else {
  //         return json_encode([
  //           'success' => false,
  //           'message' => 'Update Group Failed'
  //         ]);
  //       }
  //   }
}
