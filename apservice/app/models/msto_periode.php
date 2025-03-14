<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Carbon\Carbon;
use DateTime;

class Msto_periode extends Model
{
    public static function handleAction($method, $param)
    {
        switch ($method) {
            case 'read_data':
                return self::read_data($param);
            default:
                return ['error' => 'Action not recognized'];
        }
    }

    public static function read_data($param)
    {
        $query = DB::table('sto_periode')
            ->select('*')
            ->orderBy('create_date', 'desc');

        if (array_key_exists('keywhere', $param)) {
            $keyval = json_decode($param['keywhere'], true);
            foreach ($keyval as $key => $val) {
                $query->where($val['property'], $val['value']);
            }
        }

        if (array_key_exists('filter', $param)) {
            $keyval = json_decode($param['filter'], true);
            foreach ($keyval as $key => $val) {
                $colname = ['update_date', 'create_date', 'tgl_mulai', 'tgl_selesai'];

                if (in_array($val['property'], $colname)) {
                    if (isset($val['operator']) && $val['operator'] === 'eq') {
                        $dateValue = date('Y-m-d', strtotime($val['value']));
                        $query->whereRaw("CAST(" . $val['property'] . " AS DATE) = ?", [$dateValue]);
                    } else {
                        $query->whereRaw("FORMAT(" . $val['property'] . ", 'yyyy-MM-dd') LIKE ?", ['%' . $val['value'] . '%']);
                    }
                } else {
                    $query->whereRaw("UPPER(" . $val['property'] . ") LIKE ?", ['%' . strtoupper($val['value']) . '%']);
                }
            }
        }

        $count = $query->count();

        if (array_key_exists('limit', $param)) {
            $query->offset($param['start'])->limit($param['limit']);
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
    public static function read_sto_data_item($param)
    {
        $vdata = json_decode($param['vdata'], true);
        $query = DB::table('sto_periode_data')
            ->select('*')
            ->where('period', $vdata['period'])
            ->orderBy('create_date', 'desc');

        if (array_key_exists('keywhere', $param)) {
            $keyval = json_decode($param['keywhere'], true);
            foreach ($keyval as $key => $val) {
                $query->where($val['property'], $val['value']);
            }
        }

        if (array_key_exists('filter', $param)) {
            $keyval = json_decode($param['filter'], true);
            foreach ($keyval as $key => $val) {
                $colname = ['update_date', 'create_date', 'tgl_mulai', 'tgl_selesai'];

                if (in_array($val['property'], $colname)) {
                    if (isset($val['operator']) && $val['operator'] === 'eq') {
                        $dateValue = date('Y-m-d', strtotime($val['value']));
                        $query->whereRaw("CAST(" . $val['property'] . " AS DATE) = ?", [$dateValue]);
                    } else {
                        $query->whereRaw("FORMAT(" . $val['property'] . ", 'yyyy-MM-dd') LIKE ?", ['%' . $val['value'] . '%']);
                    }
                } else {
                    $query->whereRaw("UPPER(" . $val['property'] . ") LIKE ?", ['%' . strtoupper($val['value']) . '%']);
                }
            }
        }

        $count = $query->count();

        if (array_key_exists('limit', $param)) {
            $query->offset($param['start'])->limit($param['limit']);
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
        date_default_timezone_set('Asia/Jakarta');
        $vdata = json_decode($param['vdata'], true);
        $period = 'PRD-' . date('ymd-His') . date('d');

        $field = array(
            'period' => $period,
            'tgl_mulai' => $vdata['tgl_mulai'],
            'tgl_selesai' => $vdata['tgl_selesai'],
            'status' => 'OPEN',
        );
        $syscreate = array(
            'create_user' => $param['VUSERLOGIN'],
            'create_date' => DB::raw('GETDATE()')
        );
        $sysupdate = array(
            'update_user' => $param['VUSERLOGIN'],
            'update_date' => DB::raw('GETDATE()')
        );

        if (!empty($vdata['id'])) {
            $updatedGroup = DB::table('sto_periode')->where('id', $vdata['id'])->update(array_filter(array_merge(array(
                'tgl_mulai' => $vdata['tgl_mulai'],
                'tgl_selesai' => $vdata['tgl_selesai'],
            ), $sysupdate)));
            if ($updatedGroup) {
                return json_encode(['success' => "true", 'message' => 'Update Data Success']);
            } else {
                return json_encode(['success' => "false", 'message' => 'Update Data Failed']);
            }
        } else {
            DB::table('sto_periode')->update(['status' => 'CLOSE']);
            $createdGroup = DB::table('sto_periode')->insert(array_filter(array_merge($field, $syscreate)));

            if ($createdGroup) {
                DB::insert("
                    INSERT INTO sto_periode_data (
                        period, receipt_no, receipt_date, invoice_no, invoice_date,
                        part_no, mapp_partno, part_name, group_item,
                        invoice_qty, receipt_qty, menu_input, jenis_input,
                        sumber_data, bc_type, nomor_aju, tanggal_aju,
                        nomor_daftar, tanggal_daftar, seri_barang, ctn_qty,
                        packing_qty, barcode, barcode_seqno, rack_no,
                        actual_qty, create_user, create_date
                    )
                    SELECT
                        ?, receipt_no, receipt_date, invoice_no, invoice_date,
                        part_no, mapp_partno, part_name, group_item,
                        invoice_qty, receipt_qty, menu_input, jenis_input,
                        sumber_data, bc_type, nomor_aju, tanggal_aju,
                        nomor_daftar, tanggal_daftar, seri_barang, ctn_qty,
                        packing_qty, barcode, barcode_seqno, rack_no,
                        actual_qty, ?, GETDATE()
                    FROM wh_inbound
                ", [$period, $param['VUSERLOGIN']]);

                return json_encode(['success' => "true", 'message' => 'Add Data Success']);
            } else {
                return json_encode(['success' => "false", 'message' => 'Add Data Failed']);
            }
        }
    }

    public static function delete_data($param)
    {
        $vdata = json_decode($param['vdata'], true);
        try {
            $deleted = DB::table('sto_periode')->where('id', $vdata['id'])->delete();

            if ($deleted) {
                return json_encode([
                    'success' => "true",
                    'message' => 'Data berhasil dihapus'
                ]);
            } else {
                return json_encode([
                    'success' => "false",
                    'message' => 'Gagal menghapus data'
                ]);
            }
        } catch (\Exception $e) {
            return json_encode([
                'success' => "false",
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public static function syncronize_sto_data($param)
    {
        $vdata = json_decode($param['vdata'], true);
        DB::beginTransaction();
        try {
            DB::statement("
            MERGE INTO sto_periode_data AS target
            USING (
                SELECT
                    ?, receipt_no, receipt_date, invoice_no, invoice_date,
                    part_no, mapp_partno, part_name, group_item,
                    invoice_qty, receipt_qty, menu_input, jenis_input,
                    sumber_data, bc_type, nomor_aju, tanggal_aju,
                    nomor_daftar, tanggal_daftar, seri_barang, ctn_qty,
                    packing_qty, barcode, barcode_seqno, rack_no,
                    actual_qty, ?, GETDATE()
                FROM wh_inbound
            ) AS source (
                period, receipt_no, receipt_date, invoice_no, invoice_date,
                part_no, mapp_partno, part_name, group_item,
                invoice_qty, receipt_qty, menu_input, jenis_input,
                sumber_data, bc_type, nomor_aju, tanggal_aju,
                nomor_daftar, tanggal_daftar, seri_barang, ctn_qty,
                packing_qty, barcode, barcode_seqno, rack_no,
                actual_qty, create_user, create_date
            )
            ON (target.period = source.period AND target.receipt_no = source.receipt_no)
            WHEN MATCHED THEN
                UPDATE SET
                    target.receipt_date = source.receipt_date,
                    target.invoice_no = source.invoice_no,
                    target.invoice_date = source.invoice_date,
                    target.part_no = source.part_no,
                    target.mapp_partno = source.mapp_partno,
                    target.part_name = source.part_name,
                    target.group_item = source.group_item,
                    target.invoice_qty = source.invoice_qty,
                    target.receipt_qty = source.receipt_qty,
                    target.menu_input = source.menu_input,
                    target.jenis_input = source.jenis_input,
                    target.sumber_data = source.sumber_data,
                    target.bc_type = source.bc_type,
                    target.nomor_aju = source.nomor_aju,
                    target.tanggal_aju = source.tanggal_aju,
                    target.nomor_daftar = source.nomor_daftar,
                    target.tanggal_daftar = source.tanggal_daftar,
                    target.seri_barang = source.seri_barang,
                    target.ctn_qty = source.ctn_qty,
                    target.packing_qty = source.packing_qty,
                    target.barcode = source.barcode,
                    target.barcode_seqno = source.barcode_seqno,
                    target.rack_no = source.rack_no,
                    target.actual_qty = source.actual_qty,
                    target.update_user = source.create_user,
                    target.update_date = GETDATE()
            WHEN NOT MATCHED THEN
                INSERT (
                    period, receipt_no, receipt_date, invoice_no, invoice_date,
                    part_no, mapp_partno, part_name, group_item,
                    invoice_qty, receipt_qty, menu_input, jenis_input,
                    sumber_data, bc_type, nomor_aju, tanggal_aju,
                    nomor_daftar, tanggal_daftar, seri_barang, ctn_qty,
                    packing_qty, barcode, barcode_seqno, rack_no,
                    actual_qty, create_user, create_date
                )
                VALUES (
                    source.period, source.receipt_no, source.receipt_date, source.invoice_no, source.invoice_date,
                    source.part_no, source.mapp_partno, source.part_name, source.group_item,
                    source.invoice_qty, source.receipt_qty, source.menu_input, source.jenis_input,
                    source.sumber_data, source.bc_type, source.nomor_aju, source.tanggal_aju,
                    source.nomor_daftar, source.tanggal_daftar, source.seri_barang, source.ctn_qty,
                    source.packing_qty, source.barcode, source.barcode_seqno, source.rack_no,
                    source.actual_qty, source.create_user, source.create_date
                );
            ", [$vdata['period'], $param['VUSERLOGIN']]);

            DB::commit();
            return json_encode([
                'success' => "true",
                'message' => 'Data berhasil disyncronize'
            ]);
        } catch (\Throwable $e) {
            DB::rollback();
            return json_encode([
                'success' => "false",
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    // public static function download_data($param)
    // {
    //     $query = DB::table('sto_periode')
    //         ->select(
    //             'period',
    //             'tgl_mulai',
    //             'tgl_selesai',
    //             'status',
    //             'create_user',
    //             'create_date',
    //             'update_user',
    //             'update_date',
    //         );

    //     if (array_key_exists('filter', $param)) {
    //         $keyval = json_decode($param['filter'], true);
    //         foreach ($keyval as $key => $val) {
    //             $query->where($val['property'], 'like', '%' . $val['value'] . '%');
    //         }
    //     }

    //     if (array_key_exists('sort', $param)) {
    //         $keyval = json_decode($param['sort'], true);
    //         foreach ($keyval as $key => $val) {
    //             $query->orderBy($val['property'], $val['direction']);
    //         }
    //     }

    //     $rows = $query->get()->toArray();

    //     $writer = WriterEntityFactory::createXLSXWriter();

    //     $date = new DateTime();
    //     $vfilename = "result_period_data_download_" . $date->format('Y_m_d_H_i_s') . ".xlsx";
    //     $outputFilePath = base_path("z_download/" . $vfilename);

    //     $writer->openToFile($outputFilePath);

    //     $firstSheet = $writer->getCurrentSheet();
    //     $firstSheet->setName('Data Period');
    //     $header = ['Periode', 'Tanggal Mulai', 'Tanggal Selesai', 'Status', 'Create User', 'Create Date', 'Update User', 'Update Date'];
    //     $writer->addRow(WriterEntityFactory::createRowFromArray($header));
    //     foreach ($rows as $data) {
    //         $writer->addRow(WriterEntityFactory::createRowFromArray((array) $data));
    //     }

    //     $writer->setCurrentSheet($firstSheet);

    //     $writer->close();

    //     $hasil = [
    //         'success' => "true",
    //         'remark' => 'File Download',
    //         'filename' => 'apservice/z_download/' . $vfilename
    //     ];

    //     return json_encode($hasil);
    // }
    public static function download_sto_data_by_period($param)
    {
        $vdata = json_decode($param['vdata'], true);

        $query = DB::select("
            SELECT
                period,
                receipt_no,
                receipt_date,
                invoice_no,
                invoice_date,
                part_no,
                mapp_partno,
                part_name,
                group_item,
                invoice_qty,
                receipt_qty,
                menu_input,
                jenis_input,
                sumber_data,
                bc_type,
                nomor_aju,
                tanggal_aju,
                nomor_daftar,
                tanggal_daftar,
                seri_barang,
                ctn_qty,
                packing_qty,
                barcode,
                barcode_seqno,
                rack_no,
                actual_qty,
                sto_qty,
                sto_mobile_id,
                sto_scan_date,
                sto_scan_count,
                create_user,
                create_date,
                update_user,
                update_date
            FROM sto_periode_data
            WHERE period = ?
        ", [$vdata['period']]);

        $templatePath = base_path("../z_tempfile/temp_stodata_by_periode.xlsx");

        $spreadsheet = new Spreadsheet();
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($templatePath);
        $worksheet = $spreadsheet->getActiveSheet();

        $no = 2;
        foreach ($query as $key) {
            $worksheet->setCellValueExplicit("A" . $no, $key->period, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $worksheet->setCellValue("B" . $no, $key->receipt_no);
            $worksheet->setCellValue("C" . $no, $key->receipt_date);
            $worksheet->setCellValue("D" . $no, $key->invoice_no);
            $worksheet->setCellValue("E" . $no, $key->invoice_date);
            $worksheet->setCellValue("F" . $no, $key->part_no);
            $worksheet->setCellValue("G" . $no, $key->mapp_partno);
            $worksheet->setCellValueExplicit("H" . $no, $key->part_name, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $worksheet->setCellValueExplicit("I" . $no, $key->group_item, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $worksheet->setCellValue("J" . $no, $key->invoice_qty);
            $worksheet->setCellValue("K" . $no, $key->receipt_qty);
            $worksheet->setCellValueExplicit("L" . $no, $key->menu_input, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $worksheet->setCellValueExplicit("M" . $no, $key->jenis_input, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $worksheet->setCellValueExplicit("N" . $no, $key->sumber_data, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $worksheet->setCellValueExplicit("O" . $no, $key->bc_type, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $worksheet->setCellValue("P" . $no, $key->nomor_aju);
            $worksheet->setCellValue("Q" . $no, $key->tanggal_aju);
            $worksheet->setCellValue("R" . $no, $key->nomor_daftar);
            $worksheet->setCellValue("S" . $no, $key->tanggal_daftar);
            $worksheet->setCellValue("T" . $no, $key->seri_barang);
            $worksheet->setCellValue("U" . $no, $key->ctn_qty);
            $worksheet->setCellValue("V" . $no, $key->packing_qty);
            $worksheet->setCellValueExplicit("W" . $no, $key->barcode, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $worksheet->setCellValue("X" . $no, $key->barcode_seqno);
            $worksheet->setCellValue("Y" . $no, $key->rack_no);
            $worksheet->setCellValue("Z" . $no, $key->actual_qty);
            $worksheet->setCellValue("AA" . $no, $key->sto_qty);
            $worksheet->setCellValue("BA" . $no, $key->sto_mobile_id);
            $worksheet->setCellValue("CA" . $no, $key->sto_scan_date);
            $worksheet->setCellValue("DA" . $no, $key->sto_scan_count);
            $worksheet->setCellValueExplicit("EA" . $no, $key->create_user, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $worksheet->setCellValue("FA" . $no, $key->create_date);
            $worksheet->setCellValueExplicit("GA" . $no, $key->update_user, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $worksheet->setCellValue("HA" . $no, $key->update_date);
            $no++;
        }

        $date = new DateTime();
        $vfilename = "result_sto_data_byperiod_download_" . $date->format('Y_m_d_H_i_s') . ".xlsx";
        $outputFilePath = base_path("../z_download/" . $vfilename);

        $writer = new Xlsx($spreadsheet);
        $writer->save($outputFilePath);

        $hasil = [
            'success' => "true",
            'remark' => 'File Download',
            'filename' => 'z_download/' . $vfilename
        ];

        return json_encode($hasil);
    }

    public static function download_data($param)
    {
        $query = DB::select("
            SELECT
                period,
                tgl_mulai,
                tgl_selesai,
                status
            FROM sto_periode
        ");

        $templatePath = base_path("../z_tempfile/temp_all_periode.xlsx");

        $spreadsheet = new Spreadsheet();
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($templatePath);
        $worksheet = $spreadsheet->getActiveSheet();

        $no = 2;
        foreach ($query as $key) {
            $worksheet->setCellValueExplicit('A' . $no, $key->period, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $worksheet->setCellValue('B' . $no, $key->tgl_mulai);
            $worksheet->setCellValue('C' . $no, $key->tgl_selesai);
            $worksheet->setCellValueExplicit('D' . $no, $key->status, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $no++;
        }

        $date = new DateTime();
        $vfilename = "sto_periode_download_" . $date->format('Y_m_d_H_i_s') . ".xlsx";
        $outputFilePath = base_path("../z_download/" . $vfilename);

        $writer = new Xlsx($spreadsheet);
        $writer->save($outputFilePath);

        $hasil = [
            'success' => "true",
            'remark' => 'File Download',
            'filename' => 'z_download/' . $vfilename
        ];

        return json_encode($hasil);
    }
}
