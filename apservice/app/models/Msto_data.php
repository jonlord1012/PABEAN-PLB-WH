<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use DateTime;
use Carbon\Carbon;

class Msto_data extends Model
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
    public static function read_data_by_rack($param)
    {
        $activePeriod = DB::table('sto_periode')
            ->where('status', 'OPEN')
            ->first();

        $query = DB::table('mst_rack as a')
            ->select([
                "a.rack_no",
                "a.rack_location",
                "a.rack_category",
                "B.*"
            ])
            ->leftJoin('sto_periode_data as b', 'b.rack_no', '=', 'a.rack_no')
            ->where("period", $activePeriod->period);

        if (array_key_exists('keywhere', $param)) {
            $keyval = json_decode($param['keywhere'], true);
            foreach ($keyval as $key => $val) {
                $query->where($val['property'], $val['value']);
            }
        }

        if (array_key_exists('filter', $param)) {
            $keyval = json_decode($param['filter'], true);

            foreach ($keyval as $key => $val) {
                $colname = ['create_date', 'update_date'];
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
            'Rows' => $rows,
            'periodeactive' => $activePeriod ? $activePeriod->period : null, // Active period name
            'tgl_mulai' => $activePeriod ? Carbon::parse($activePeriod->tgl_mulai)->format("d - F - Y") : null,
            'tgl_selesai' => $activePeriod ? Carbon::parse($activePeriod->tgl_selesai)->format("d - F - Y") : null,
        ]);
    }
    public static function read_data_belum_sto($param)
    {
        $activePeriod = DB::table('sto_periode')
            ->where('status', 'OPEN')
            ->first();

        $query = DB::table('sto_periode_data')->select('*')
            ->where([
                "period" => $activePeriod->period,
                "sto_scan_date" => null
            ]);

        if (array_key_exists('keywhere', $param)) {
            $keyval = json_decode($param['keywhere'], true);
            foreach ($keyval as $key => $val) {
                $query->where($val['property'], $val['value']);
            }
        }

        if (array_key_exists('filter', $param)) {
            $keyval = json_decode($param['filter'], true);

            foreach ($keyval as $key => $val) {
                $colname = ['create_date', 'update_date'];
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
            'Rows' => $rows,
        ]);
    }
    public static function read_data_tidak_sesuai_qty($param)
    {
        $activePeriod = DB::table('sto_periode')
            ->where('status', 'OPEN')
            ->first();

        $query = DB::table('sto_periode_data')
            ->select('*')
            ->where("period", "=", $activePeriod->period)
            ->whereRaw('actual_qty <> sto_qty');

        if (array_key_exists('keywhere', $param)) {
            $keyval = json_decode($param['keywhere'], true);
            foreach ($keyval as $key => $val) {
                $query->where($val['property'], $val['value']);
            }
        }

        if (array_key_exists('filter', $param)) {
            $keyval = json_decode($param['filter'], true);

            foreach ($keyval as $key => $val) {
                $colname = ['create_date', 'update_date'];
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
            'Rows' => $rows,
        ]);
    }

    public static function save_data($param)
    {
        $vdata = json_decode($param['data'], true);

        $field = array(
            'periodename' => date('Ymd', strtotime($vdata['periodestart'])),
            'periodestart' => $vdata['periodestart'],
            'status' => true,
            'remark' => $vdata['remark'],
            'syscreateuser' => $param['VUSERLOGIN'],
            'syscreatedate' => date('Y-m-d h:i:s')
        );
        if (!empty($vdata['periodeid'])) {
            $updatedGroup = DB::table('sto_periode_data')->where('periodeid', $vdata['periodeid'])->update(array_filter($field));

            if ($updatedGroup) {
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
            DB::table('sto_periode_data')->update(['status' => false]);
            $createdGroup = DB::table('sto_periode_data')->insert(array_filter($field));

            if ($createdGroup) {
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
        $vdata = json_decode($param['data'], true);

        if (!isset($vdata['periodeid'])) {
            return json_encode([
                'success' => false,
                'message' => ' ID is required'
            ]);
        }

        if ($vdata['status'] == 1) {
            return json_encode([
                'success' => false,
                'message' => 'Cannot delete active periode'
            ]);
        }

        $deleted = DB::table('sto_periode_data')->where('periodeid', $vdata['periodeid'])->delete();

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

    public static function download_report_data($param)
    {
        ini_set('max_execution_time', 240); // durasi 4 menit
        date_default_timezone_set('Asia/Jakarta');

        $vdata = json_decode($param['vdata'], true);
        $periode = $vdata['periodeactive'];

        $dt_sto_byrack = DB::select("
            SELECT
                a.rack_no,
                a.rack_location,
                a.rack_category,
                b.period,
                b.receipt_no,
                b.receipt_date,
                b.invoice_no,
                b.invoice_date,
                b.part_no,
                b.mapp_partno,
                b.part_name,
                b.group_item,
                b.invoice_qty,
                b.receipt_qty,
                b.menu_input,
                b.jenis_input,
                b.sumber_data,
                b.bc_type,
                b.nomor_aju,
                b.tanggal_aju,
                b.nomor_daftar,
                b.tanggal_daftar,
                b.seri_barang,
                b.ctn_qty,
                b.packing_qty,
                b.barcode,
                b.barcode_seqno,
                b.actual_qty,
                b.sto_qty,
                b.sto_mobile_id,
                b.sto_scan_date,
                b.sto_scan_count,
                b.create_user,
                b.create_date,
                b.update_user,
                b.update_date
            FROM mst_rack AS a
            LEFT JOIN sto_periode_data AS b ON b.rack_no = a.rack_no
            WHERE b.period = ?
        ", [$periode]);

        $dt_belum_sto = DB::select("
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
            AND sto_scan_date IS NULL
        ", [$periode]);

        $dt_unmatch_qty = DB::select("
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
        AND actual_qty <> sto_qty
        ", [$periode]);

        $templatePath = base_path("../z_tempfile/temp_stodata_all.xlsx");
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($templatePath);

        $sheet1 = $spreadsheet->setActiveSheetIndex(0);
        $sheet1->setTitle('STO By Rack');
        $row = 2;
        foreach ($dt_sto_byrack as $data) {
            $col = 'A';
            foreach ((array) $data as $value) {
                $sheet1->setCellValueExplicit($col++ . $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            }
            $row++;
        }

        $spreadsheet->createSheet();
        $sheet2 = $spreadsheet->setActiveSheetIndex(1);
        $sheet2->setTitle('Belum STO');
        $row = 2;
        foreach ($dt_belum_sto as $data) {
            $col = 'A';
            foreach ((array) $data as $value) {
                $sheet2->setCellValueExplicit($col++ . $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            }
            $row++;
        }

        $spreadsheet->createSheet();
        $sheet3 = $spreadsheet->setActiveSheetIndex(2);
        $sheet3->setTitle('STO Qty Tidak Sama');
        $row = 2;
        foreach ($dt_unmatch_qty as $data) {
            $col = 'A';
            foreach ((array) $data as $value) {
                $sheet3->setCellValueExplicit($col++ . $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            }
            $row++;
        }

        $date = new DateTime();
        $vfilename = "result_sto_data_all_" . $date->format('Y_m_d_H_i_s') . ".xlsx";
        $outputFilePath = base_path("../z_download/" . $vfilename);

        $writer = new Xlsx($spreadsheet);
        $writer->save($outputFilePath);

        $hasil = [
            'success' => 'true',
            'remark' => 'File Download',
            'filename' => 'z_download/' . $vfilename
        ];

        return json_encode($hasil);
    }

}
