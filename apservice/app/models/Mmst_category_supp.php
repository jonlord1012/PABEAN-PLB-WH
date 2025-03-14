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


class Mmst_category_supp extends Model
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

  public static function read_data($param)
  {
    try {
      $query = DB::table('mst_category_supplier');

      // Filter
      if (array_key_exists('filter', $param)) {
        $keyval = json_decode($param['filter'], true);
        foreach ($keyval as $key => $val) {
          $colname = ['syscreatedate', 'sysupdatedate'];
          if (in_array($val['property'], $colname)) {
            // format create date
            $query->whereRaw("DATE_FORMAT(" . $val['property'] . ", '%Y-%m-%d %H:%i:%s') LIKE ?", ['%' . $val['value'] . '%']);
          } else {
            // cek apakah value numeric, tidak pakai upper. jika bukan numeric pakai upper
            if (is_numeric($val['value'])) {
              $query->where($val['property'], 'LIKE', '%' . $val['value'] . '%');
            } else {
              $query->whereRaw("UPPER(" . $val['property'] . ") LIKE ?", ['%' . strtoupper($val['value']) . '%']);
            }
          }
        }
      }

      // Clone query untuk mendapatkan total sebelum limit
      $countQuery = clone $query;
      $count = $countQuery->count();

      // Sort
      if (array_key_exists('sort', $param)) {
        $keyval = json_decode($param['sort'], true);
        foreach ($keyval as $key => $val) {
          $query->orderBy($val['property'], $val['direction']);
        }
      }

      if (array_key_exists('limit', $param) && array_key_exists('start', $param)) {
        $query->limit($param['limit'])->offset($param['start']);
      }

      $rows = $query->get()->toArray();

      // Convert each row to UTF-8 dan convert object ke array
      $rows = array_map(function ($row) {
        $rowArray = (array)$row;
        return array_map(function ($value) {
          return mb_convert_encoding($value, 'UTF-8', 'auto');
        }, $rowArray);
      }, $rows);

      return json_encode([
        'TotalRows' => $count,
        'Rows' => $rows,
        'success' => true
      ]);
    } catch (\Exception $e) {
      return json_encode([
        'TotalRows' => 0,
        'Rows' => [],
        'success' => false,
        'message' => $e->getMessage()
      ]);
    }
  }
  public static function save_data($param)
  {
    $vdata = json_decode($param['vdata'], true);

    if ($vdata['ID'] === 0) {
      return self::proses_data_insert($param);
    } else {
      return self::proses_data_update($param);
    }
  }

  public static function proses_data_insert($param)
  {
    $vdata = json_decode($param['vdata'], true);

    $check = DB::table('mst_category_supplier')
      ->where('CATEGORY_CODE', $vdata['CATEGORY_CODE'])
      ->orWhere('CATEGORY_NAME', $vdata['CATEGORY_NAME'])
      ->count();

    if ($check > 0) {
      return json_encode([
        'success' => 'false',
        'message' => 'Data gagal disimpan. Kode atau Nama Kategori sudah ada!',
        'vdata' => null
      ]);
    }

    // Data untuk insert
    $insert_data = [
      'CATEGORY_CODE' => $vdata['CATEGORY_CODE'],
      'CATEGORY_NAME' => $vdata['CATEGORY_NAME'],
      'SYSCREATEUSER' => $param["VUSERLOGIN"],
      'SYSCREATEDATE' => date('Y-m-d H:i:s')
    ];

    $insert_id = DB::table('mst_category_supplier')->insertGetId($insert_data);

    // Ambil ID terakhir
    if ($insert_id) {
      // Ambil data yang baru disimpan
      $getdata = DB::table('mst_category_supplier')
        ->where('ID', $insert_id)
        ->first();

      return json_encode([
        'success' => 'true',
        'message' => 'Data berhasil disimpan',
        'vdata' => json_encode($getdata)
      ]);
    } else {
      return json_encode([
        'success' => 'false',
        'message' => 'Data gagal disimpan',
        'vdata' => null
      ]);
    }
  }
  public static function proses_data_update($param)
  {
    $vdata = json_decode($param['vdata'], true);

    // Validasi data
    if (empty($vdata['CATEGORY_CODE']) || empty($vdata['CATEGORY_NAME'])) {
      return json_encode([
        'success' => 'false',
        'message' => 'Data gagal disimpan, kategori dan nama kategori harus diisi',
        'vdata' => null
      ]);
    }

    // Cek duplikasi kode kategori di record lain
    $check_code = DB::table('mst_category_supplier')
      ->where('CATEGORY_CODE', $vdata['CATEGORY_CODE'])
      ->where('ID', '!=', $vdata['ID']) // Exclude current record
      ->count();

    if ($check_code > 0) {
      return json_encode([
        'success' => 'false',
        'message' => 'Data gagal disimpan. Kode Kategori sudah digunakan!',
        'vdata' => null
      ]);
    }

    // Cek duplikasi nama kategori di record lain
    $check_name = DB::table('mst_category_supplier')
      ->where('CATEGORY_NAME', $vdata['CATEGORY_NAME'])
      ->where('ID', '!=', $vdata['ID']) // Exclude current record
      ->count();

    if ($check_name > 0) {
      return json_encode([
        'success' => 'false',
        'message' => 'Data gagal disimpan. Nama Kategori sudah digunakan!',
        'vdata' => null
      ]);
    }

    // Jika lolos semua validasi, lakukan update
    $update_data = [
      'CATEGORY_CODE' => $vdata['CATEGORY_CODE'],
      'CATEGORY_NAME' => $vdata['CATEGORY_NAME'],
      'SYSUPDATEUSER' => $param["VUSERLOGIN"],
      'SYSUPDATEDATE' => date('Y-m-d H:i:s')
    ];

    $updated = DB::table('mst_category_supplier')
      ->where('ID', $vdata['ID'])
      ->update($update_data);

    if ($updated) {
      $getdata = DB::table('mst_category_supplier')
        ->where('ID', $vdata['ID'])
        ->first();

      return json_encode([
        'success' => 'true',
        'message' => 'Data berhasil diupdate',
        'vdata' => json_encode($getdata)
      ]);
    } else {
      return json_encode([
        'success' => 'false',
        'message' => 'Data gagal diupdate',
        'vdata' => null
      ]);
    }
  }
  public static function delete_data($param)
  {
    $vdata = json_decode($param['vdata'], true);

    // Mulai transaksi
    DB::beginTransaction();

    // Hapus data berdasarkan ID
    $deleted = DB::table('mst_category_supplier')
      ->where('ID', $vdata['ID'])
      ->delete();

    if ($deleted) {
      // Jika berhasil, commit transaksi
      DB::commit();
      return json_encode([
        'success' => 'true',
        'message' => 'Data berhasil dihapus'
      ]);
    } else {
      // Jika gagal, rollback transaksi
      DB::rollBack();
      return json_encode([
        'success' => 'false',
        'message' => 'Data gagal dihapus'
      ]);
    }
  }
}
