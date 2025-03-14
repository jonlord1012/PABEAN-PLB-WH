<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use DateTime;
use Carbon\Carbon;


class Mwh_receiving_itempart extends Model
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
            $query = DB::table('wh_inv_in_header as a')
    ->select(
        'a.receipt_no',
        'a.receipt_date',
        'a.receipt_user',
        'a.approve_user',
        'a.approve_date',
        'b.invoice_no',
        'b.invoice_date',
        'b.part_no',
        'b.mapp_partno as mapping_part',
        'b.group_item as group_part',
        'b.part_name',
        'b.receipt_qty',
        'b.sumber_data',
        'b.bc_type',
        'b.seri_barang',
        'b.nomor_aju',
        'b.tanggal_aju',
        'b.nomor_daftar',
        'b.tanggal_daftar',
        'b.supplier_kode_internal',
        'b.supplier_name',
        'b.jenis_input as jenis_input',
        'a.create_user',
        'a.update_date',
    )
    ->join('wh_inv_in_detail as b', function($join) {
        $join->on('a.receipt_no', '=', 'b.receipt_no');
    });
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
            } else {
                $query->orderBy('update_date', 'desc');
            }

            if (array_key_exists('limit', $param) && array_key_exists('start', $param)) {
                $query->limit($param['limit'])->offset($param['start']);
            }

            $rows = $query->get()->toArray();

            // Convert each row to UTF-8 dan convert object ke array
            $rows = array_map(function ($row) {
                $rowArray = (array) $row;
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
    public static function read_list_dokumen($param)
    {
        try {
            $query = DB::connection('pabean')
                ->table('tr_bc_detail as A')
                ->select([
                    'A.invoice_no',
                    'A.invoice_date',
                    'B.nomor_aju',
                    'B.tanggal_aju',
                    'B.nomor_daftar',
                    'B.tanggal_daftar',
                    'B.kode_dokumen_pabean',
                    'A.mode_source',
                    'B.supplier_kode_internal',
                    'C.nama'
                ])
                ->distinct()
                ->join('tr_bc_header as B', 'A.EDS_NOMOR_AJU', '=', 'B.NOMOR_AJU')
                ->join('referensi_pemasok as C', 'B.supplier_kode_internal', '=', 'C.KODE_INTERNAL')
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from(DB::raw('(
                            SELECT 
                                D.EDS_NOMOR_AJU,
                                D.invoice_no,
                                D.kode_barang,
                                D.seri_barang,
                                SUM(D.jumlah_satuan) as total_qty,
                                COALESCE(SUM(E.receipt_qty), 0) as received_qty
                            FROM tr_bc_detail D
                            LEFT JOIN ' . env('DB_DATABASE_SQLSRV') . '.dbo.wh_inv_in_detail E ON 
                                E.nomor_aju = D.EDS_NOMOR_AJU AND
                                E.invoice_no = D.invoice_no AND
                                E.mapp_partno = D.kode_barang AND
                                E.seri_barang = D.seri_barang
                            GROUP BY 
                                D.EDS_NOMOR_AJU,
                                D.invoice_no,
                                D.kode_barang,
                                D.seri_barang
                            HAVING 
                                SUM(D.jumlah_satuan) > COALESCE(SUM(E.receipt_qty), 0)
                        ) as remaining_items'))
                        ->whereRaw('remaining_items.invoice_no = A.invoice_no')
                        ->whereRaw('remaining_items.EDS_NOMOR_AJU = A.EDS_NOMOR_AJU');
                });
    
            // Filter
            if (array_key_exists('filter', $param)) {
                $keyval = json_decode($param['filter'], true);
                foreach ($keyval as $key => $val) {
                    $colname = ['syscreatedate', 'sysupdatedate'];
                    if (in_array($val['property'], $colname)) {
                        $query->whereRaw("DATE_FORMAT(" . $val['property'] . ", '%Y-%m-%d %H:%i:%s') LIKE ?", ['%' . $val['value'] . '%']);
                    } else {
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
                $rowArray = (array) $row;
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
        try {
            DB::beginTransaction();

            $vdata = json_decode($param['vdata'], true);
            $vdetail = json_decode($param['vdetail'], true);
            $username = $param['VUSERLOGIN'];

            $receiptNoBaru = DB::select('SELECT dbo.GET_NOMOR_WH_IN_MATERIAL() as receipt_no;
            ')[0]->receipt_no;
            $check = DB::table('wh_inv_in_header')
                ->where('receipt_no', $receiptNoBaru)
                ->exists();

            if ($check) {
                return json_encode([
                    'success' => 'false',
                    'message' => 'No receipt sudah digunakan, silahkan ulangi proses save',
                    'vdata' => null
                ]);
            }

            $receiptDate = Carbon::parse($vdata['receipt_date']);
            $tanggalAju = Carbon::parse($vdata['tanggal_aju']);

            if ($receiptDate < $tanggalAju) {
                return json_encode([
                    'success' => 'false',
                    'message' => 'Tanggal Receipt Date tidak boleh lebih kecil dari Tanggal Aju',
                    'vdata' => null
                ]);
            }

            $header_data = [
                'receipt_no' => $receiptNoBaru,
                'receipt_date' => $receiptDate,
                'receipt_user' =>  $username,
                'nomor_aju' =>  $vdata['nomor_aju'],
                'CREATE_USER' => $username,
                'CREATE_DATE' => new DateTime(),
                'UPDATE_USER' => $username,
                'UPDATE_DATE' => new DateTime()
            ];

            DB::table('wh_inv_in_header')->insert($header_data);
            foreach ($vdetail as $detail) {
                $dokumenPart = DB::connection('pabean')
                ->table('VW_WH_DOKUMEN_PART_MATERIAL')
                ->where('NOMOR_AJU', $vdata['nomor_aju'])
                ->where('SERI_BARANG', $detail['seri_barang'])
                ->first();

                $detail_data = [
                    'RECEIPT_NO' => $receiptNoBaru,
                    'RECEIPT_DATE' => $receiptDate,
                    'INVOICE_NO' => $detail['invoice_no'],
                    'INVOICE_DATE' => $detail['invoice_date'] ? Carbon::parse($detail['invoice_date']) : null,
                    'PART_NO' => $detail['part_no'],
                    'MAPP_PARTNO' => $detail['mapp_partno'],
                    'PART_NAME' => $detail['part_name'],
                    'SERI_BARANG' => $detail['seri_barang'],
                    'INVOICE_QTY' => $detail['invoice_qty'],
                    'RECEIPT_QTY' => $detail['input_qty'],
                    'CTN_QTY' => $detail['seri_barang'],
                    'packing_qty' => $detail['seri_barang'],
                    'GROUP_ITEM' => $detail['group_item'],
                    'SUMBER_DATA' => $dokumenPart->MODE_SOURCE ?? null,
                    'BC_TYPE' => $dokumenPart->BC_TYPE ?? null,
                    'tanggal_aju' => $tanggalAju,
                    'NOMOR_AJU' => $vdata['nomor_aju'],
                    'NOMOR_DAFTAR' => $vdata['nomor_daftar'],
                    'TANGGAL_DAFTAR' => $vdata['tanggal_daftar'],
                    'supplier_kode_internal' => $vdata['supplier_kode_internal'],
                    'supplier_name' => $vdata['supplier_name'],
                    'MENU_INPUT' => 'MANUAL',
                    'JENIS_INPUT' => 'MANUAL',
                    'CREATE_USER' => $username,
                    'CREATE_DATE' => new DateTime(),
                    'UPDATE_USER' => $username,
                    'UPDATE_DATE' => new DateTime()
                ];

                if ($dokumenPart) {
                    $detail_data = array_merge($detail_data, [
                        // 'HSCODE' => $dokumenPart->POS_TARIF,
                        // 'CIF' => $dokumenPart->CIF,
                        // 'CIF_RUPIAH' => $dokumenPart->CIF_RUPIAH,
                        // 'FOB' => $dokumenPart->FOB,
                        // 'FREIGHT' => $dokumenPart->FREIGHT,
                        // 'KODE_VALUTA' => $dokumenPart->KODE_VALUTA,
                        // 'NDPBM' => $dokumenPart->NDPBM,
                        // 'HARGA_SATUAN' => $dokumenPart->HARGA_SATUAN,
                        // 'HARGA_INVOICE' => $dokumenPart->HARGA_INVOICE,
                        // 'HARGA_PENYERAHAN' => $dokumenPart->HARGA_PENYERAHAN
                    ]);
                }

                DB::table('wh_inv_in_detail')->insert($detail_data);
            }

            DB::commit();

            $getdata = DB::table('wh_inv_in_header')
                ->where('RECEIPT_NO', $receiptNoBaru)
                ->first();

            return json_encode([
                'success' => 'true',
                'message' => 'Data berhasil disimpan',
                'vdata' => json_encode($getdata)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return json_encode([
                'success' => 'false',
                'message' => 'Data gagal disimpan: ' . $e->getMessage(),
                'vdata' => null
            ]);
        }
    }
    public static function proses_data_update($param)
    {
        try {
            DB::beginTransaction();
    
            $vdata = json_decode($param['vdata'], true);
            $vdetail = json_decode($param['vdetail'], true);
            $username = $param['VUSERLOGIN'];
    
            $receiptNo = $vdata['receipt_no'];
            $receiptDate = Carbon::parse($vdata['receipt_date']);
            $tanggalAju = Carbon::parse($vdata['tanggal_aju']);
    
            // if ($receiptDate < $tanggalAju) {
            //     return json_encode([
            //         'success' => 'false',
            //         'message' => 'Tanggal Receipt Date tidak boleh lebih kecil dari Tanggal Ajus',
            //         'vdata' => null
            //     ]);
            // }
    
            // Update header data
            $header_data = [
                'receipt_date' => $receiptDate,
                'receipt_user' => $username,
                'nomor_aju' => $vdata['nomor_aju'],
                'UPDATE_USER' => $username,
                'UPDATE_DATE' => new DateTime()
            ];
    
            DB::table('wh_inv_in_header')
                ->where('receipt_no', $receiptNo)
                ->update($header_data);
    
            // Delete existing detail data for the receipt_no
            DB::table('wh_inv_in_detail')
                ->where('RECEIPT_NO', $receiptNo)
                ->delete();
    
            // Insert updated detail data
            foreach ($vdetail as $detail) {
                $dokumenPart = DB::connection('pabean')
                    ->table('VW_WH_DOKUMEN_PART_MATERIAL')
                    ->where('NOMOR_AJU', $vdata['nomor_aju'])
                    ->where('SERI_BARANG', $detail['seri_barang'])
                    ->first();
    
                $detail_data = [
                    'RECEIPT_NO' => $receiptNo,
                    'RECEIPT_DATE' => $receiptDate,
                    'INVOICE_NO' => $detail['invoice_no'],
                    'INVOICE_DATE' => $detail['invoice_date'] ? Carbon::parse($detail['invoice_date']) : null,
                    'PART_NO' => $detail['part_no'],
                    'MAPP_PARTNO' => $detail['mapp_partno'],
                    'PART_NAME' => $detail['part_name'],
                    'SERI_BARANG' => $detail['seri_barang'],
                    'INVOICE_QTY' => $detail['invoice_qty'],
                    'RECEIPT_QTY' => $detail['input_qty'],
                    'CTN_QTY' => $detail['seri_barang'],
                    'packing_qty' => $detail['seri_barang'],
                    'GROUP_ITEM' => $detail['group_item'],
                    'SUMBER_DATA' => $dokumenPart->MODE_SOURCE ?? null,
                    'BC_TYPE' => $dokumenPart->BC_TYPE ?? null,
                    'tanggal_aju' => $tanggalAju,
                    'NOMOR_AJU' => $vdata['nomor_aju'],
                    'NOMOR_DAFTAR' => $vdata['nomor_daftar'],
                    'TANGGAL_DAFTAR' => $vdata['tanggal_daftar'],
                    'supplier_kode_internal' => $vdata['supplier_kode_internal'],
                    'supplier_name' => $vdata['supplier_name'],
                    'MENU_INPUT' => 'MANUAL',
                    'JENIS_INPUT' => 'MANUAL',
                    'CREATE_USER' => $username,
                    'CREATE_DATE' => new DateTime(),
                    'UPDATE_USER' => $username,
                    'UPDATE_DATE' => new DateTime()
                ];
    
                if ($dokumenPart) {
                    $detail_data = array_merge($detail_data, [
                        // Uncomment if needed:
                        // 'HSCODE' => $dokumenPart->POS_TARIF,
                        // 'CIF' => $dokumenPart->CIF,
                        // 'CIF_RUPIAH' => $dokumenPart->CIF_RUPIAH,
                        // 'FOB' => $dokumenPart->FOB,
                        // 'FREIGHT' => $dokumenPart->FREIGHT,
                        // 'KODE_VALUTA' => $dokumenPart->KODE_VALUTA,
                        // 'NDPBM' => $dokumenPart->NDPBM,
                        // 'HARGA_SATUAN' => $dokumenPart->HARGA_SATUAN,
                        // 'HARGA_INVOICE' => $dokumenPart->HARGA_INVOICE,
                        // 'HARGA_PENYERAHAN' => $dokumenPart->HARGA_PENYERAHAN
                    ]);
                }
    
                DB::table('wh_inv_in_detail')->insert($detail_data);
            }
    
            DB::commit();
    
            $getdata = DB::table('wh_inv_in_header')
                ->where('RECEIPT_NO', $receiptNo)
                ->first();
    
            return json_encode([
                'success' => 'true',
                'message' => 'Data berhasil diperbarui',
                'vdata' => json_encode($getdata)
            ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return json_encode([
                'success' => 'false',
                'message' => 'Data gagal diperbarui: ' . $e->getMessage(),
                'vdata' => null
            ]);
        }
    }
    
    public static function delete_data($param)
    {
        $vdata = json_decode($param['vdata'], true);
    
        DB::beginTransaction();
    
        try {
            // Lakukan penghapusan pada tabel wh_inv_in_header dan wh_inv_in_detail
            DB::table('wh_inv_in_header')
                ->where('receipt_no', $vdata['receipt_no'])
                ->delete();
    
            DB::table('wh_inv_in_detail')
                ->where('receipt_no', $vdata['receipt_no'])
                ->delete();
    
            // Commit transaksi jika berhasil
            DB::commit();
    
            return json_encode([
                'success' => 'true',
                'message' => 'Data berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            // Rollback transaksi jika ada kesalahan
            DB::rollBack();
            return json_encode([
                'success' => 'false',
                'message' => 'Data gagal dihapus: ' . $e->getMessage()
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
                $rowArray = (array) $row;
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
    public static function receiving_edit_item($param)
{
    $vdata = json_decode($param['vdata'], true);
    $isEditMode = isset($vdata['id']) && $vdata['id'] > 0;

    $SQL = "
        DECLARE @vnomoraju varchar(30)='" . $vdata['nomor_aju'] . "';
        DECLARE @vreceipt_no varchar(30)='" . $vdata['receipt_no'] . "';

        WITH dokumen_edit AS (
            SELECT a.nomor_aju, a.invoice_no, a.seri_barang, MAX(a.part_no) AS part_no, a.mapp_partno, SUM(ISNULL(a.receipt_qty, 0)) AS qty_input
            FROM wh_inv_in_detail a
            WHERE
                a.nomor_aju = @vnomoraju AND
                a.receipt_no = @vreceipt_no
            GROUP BY
                a.nomor_aju, a.invoice_no, a.mapp_partno, a.seri_barang
        ),
        dokumen_in AS (
            SELECT a.nomor_aju, a.invoice_no, a.seri_barang, MAX(a.part_no) AS part_no, a.mapp_partno, SUM(ISNULL(a.receipt_qty, 0)) AS in_qty
            FROM wh_inv_in_detail a
            WHERE
                a.nomor_aju = @vnomoraju AND
                a.receipt_no <> @vreceipt_no
            GROUP BY
                a.nomor_aju, a.invoice_no, a.mapp_partno, a.seri_barang
        ),
        dokumen_gabung AS (
            SELECT a.nomor_aju, a.invoice_no, a.invoice_date, a.mapp_partno, ISNULL(b.part_no, a.mapp_partno) AS part_no,
                a.seri_barang,
                a.part_name,
                a.group_item,
                a.jumlah_satuan AS invoice_qty, 
                ISNULL(b.in_qty, 0) AS in_qty, 
                ISNULL(a.jumlah_satuan, 0) - ISNULL(b.in_qty, 0) AS sisa_qty,
                ISNULL(c.qty_input, 0) AS input_qty
            FROM [pabean].[dbo].vw_wh_dokumen_part_material a
            LEFT JOIN dokumen_in b ON a.nomor_aju = b.nomor_aju AND a.invoice_no = b.invoice_no AND a.mapp_partno = b.mapp_partno AND a.seri_barang = b.seri_barang
            LEFT JOIN dokumen_edit c ON a.nomor_aju = c.nomor_aju AND a.invoice_no = c.invoice_no AND a.mapp_partno = c.mapp_partno AND a.seri_barang = c.seri_barang
            WHERE
                a.nomor_aju = @vnomoraju AND
                (" . ($isEditMode ? "EXISTS (
                    SELECT 1 FROM wh_inv_in_detail x 
                    WHERE x.nomor_aju = a.nomor_aju 
                    AND x.invoice_no = a.invoice_no 
                    AND x.mapp_partno = a.mapp_partno 
                    AND x.seri_barang = a.seri_barang 
                    AND x.receipt_no = @vreceipt_no
                )" : "1=1") . ")
        )
        SELECT * FROM dokumen_gabung" . 
        (!$isEditMode ? " WHERE sisa_qty > 0" : "");

    $result = DB::select($SQL);
    return json_encode($result);
}
}
