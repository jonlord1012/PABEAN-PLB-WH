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


class Mmst_rack extends Model
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
      $query = DB::table('mst_rack');

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
      }else{
        $query->orderBy('create_date', 'desc');
        $query->orderBy('update_date', 'desc');
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

    if ($vdata['id'] === 0) {
      return self::proses_data_insert($param);
    } else {
      return self::proses_data_update($param);
    }
  }

  public static function proses_data_insert($param)
  {
    $vdata = json_decode($param['vdata'], true);

    $check = DB::table('mst_rack')
      ->where('rack_no', $vdata['rack_no'])
      ->count();

    if ($check > 0) {
      return json_encode([
        'success' => 'false',
        'message' => 'Data gagal disimpan. Rack Number ' . $vdata['RACK_NO'] . ' sudah ada!',
        'vdata' => null
      ]);
    }

    // Data untuk insert
    $insert_data = [
      'rack_location' => $vdata['rack_location'],
      'rack_no' => $vdata['rack_no'],
      'rack_category' => $vdata['rack_category'],
      'part_no' => $vdata['part_no'],
      'max_qty' => $vdata['max_qty'],
      'safety_qty' => $vdata['safety_qty'],
      'create_user' => $param["VUSERLOGIN"],
      'update_user' => date('Y-m-d H:i:s')
    ];

    $insert_id = DB::table('mst_rack')->insertGetId($insert_data);

    // Ambil ID terakhir
    if ($insert_id) {
      // Ambil data yang baru disimpan
      $getdata = DB::table('mst_rack')
        ->where('id', $insert_id)
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
    // Cek duplikasi nama kategori di record lain
    $check_name = DB::table('mst_rack')
      ->where('rack_no', $vdata['rack_no'])
      ->where('id', '!=', $vdata['id']) // Exclude current record
      ->count();

    if ($check_name > 0) {
      return json_encode([
        'success' => 'false',
        'message' => 'Data gagal disimpan. Rack Number sudah ada!',
        'vdata' => null
      ]);
    }

    // Jika lolos semua validasi, lakukan update
    $update_data = [
      'rack_location' => $vdata['rack_location'],
      'rack_no' => $vdata['rack_no'],
      'rack_category' => $vdata['rack_category'],
      'part_no' => $vdata['part_no'],
      'max_qty' => $vdata['max_qty'],
      'safety_qty' => $vdata['safety_qty'],
      'update_user' => $param["VUSERLOGIN"],
      'update_date' => date('Y-m-d H:i:s')
    ];

    $updated = DB::table('mst_rack')
      ->where('id', $vdata['id'])
      ->update($update_data);

    if ($updated) {
      $getdata = DB::table('mst_rack')
        ->where('id', $vdata['id'])
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
    $deleted = DB::table('mst_rack')
      ->where('id', $vdata['id'])
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

  public static function read_data_item_byrack($param)
  {
    try {
      $vdata = json_decode($param['vdata'], true);
      $query = DB::table('mst_part')
        ->where('part_no', $vdata['part_no']);

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

  public static function download_data($param)
  {
    $query = DB::table('mst_rack')
    ->select(
        'rack_location',
        'rack_no',
        'rack_category',
        'part_no',
        'max_qty',
        'safety_qty',
        'create_user',
        'create_date',
        'update_user',
        'update_date'
    );



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
      $vfilename = "result_rack_location_download_" . $date->format('Y_m_d_H_i_s') . ".xlsx";
      $outputFilePath = base_path("../z_download/" . $vfilename);

      $writer->openToFile($outputFilePath);

      $firstSheet = $writer->getCurrentSheet();
      $firstSheet->setName('Data Rack Location');
      $header = [
        'Rack Location',
        'Rack No',
        'Rack Category',
        'Part No',
        'Max Qty',
        'Safety Qty',
        'Create User',
        'Create Date',
        'Update User',
        'Update Date',
    ];
    
    
      $writer->addRow(WriterEntityFactory::createRowFromArray($header));
      foreach ($rows as $data) {
          $writer->addRow(WriterEntityFactory::createRowFromArray((array) $data));
      }

      $writer->setCurrentSheet($firstSheet);

      $writer->close();

      $hasil = [
          'success' => "true",
          'remark' => 'File Download',
          'filename' => 'z_download/' . $vfilename
      ];

      return json_encode($hasil);
  }
}
