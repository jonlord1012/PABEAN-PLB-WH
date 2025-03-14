<?php

namespace App\Models;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use DateTime;
use Throwable;
use Carbon\Carbon;

class Mdoc_ba_sto extends Model
{
    public static function handleAction($method, $param)
    {
        switch ($method) {
            case 'read_data':
                return self::read_data($param);
            case 'read_item_part':
                return self::read_item_part($param);
            case 'read_data_item_period':
                return self::read_data_item_period($param);
            case 'read_data_item_detail':
                return self::read_data_item_detail($param);
            default:
                return ['error' => 'Action not recognized'];
        }
    }
    public static function read_data($param)
    {

        $query = DB::table('ba_sto_header as a')
            ->select('a.*')
            ->join('cpuser_department as b', 'a.dept_code', '=', 'b.userdept')
            ->where('b.userlogin', '=', $param['VUSERLOGIN']);


        if (array_key_exists('keywhere', $param)) {
            $keyval = json_decode($param['keywhere'], true);
            foreach ($keyval as $key => $val) {
                $query->where($val['property'], $val['value']);
            }
        }

        if (array_key_exists('filter', $param)) {
            $keyval = json_decode($param['filter'], true);
            foreach ($keyval as $key => $val) {
                $colname = ['syscreatedate', 'sysupdatedate'];
                if (in_array($val['property'], $colname)) {
                    // format create date
                    $query->whereRaw("FORMAT(" . $val['property'] . ", 'yyyy-MM-dd HH:mm:ss') LIKE ?", ['%' . $val['value'] . '%']);
                } else {
                    // cek apakah value numeric, tidak pakai upper. jika bukan numeric pakai uppper
                    if (is_numeric($val['value'])) {
                        $query->where($val['property'], 'LIKE', '%' . $val['value'] . '%');
                    } else {
                        $query->whereRaw("UPPER(" . $val['property'] . ") LIKE ?", ['%' . strtoupper($val['value']) . '%']);
                    }
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
        } else {
            $query->orderBy('syscreatedate', 'desc');
        }

        $rows = $query->get();
        return json_encode([
            'TotalRows' => $count,
            'Rows' => $rows
        ]);
    }
    public static function read_data_item_asset($param)
    {
        $vdata = json_decode($param['vdata'], true);
        $query = DB::table('ba_sto_detail')
            ->select('*')
            ->where('dokumen_no', "=", $vdata['dokumen_no']);

        if (array_key_exists('keywhere', $param)) {
            $keyval = json_decode($param['keywhere'], true);
            foreach ($keyval as $key => $val) {
                $query->where($val['property'], $val['value']);
            }
        }

        if (array_key_exists('filter', $param)) {
            $keyval = json_decode($param['filter'], true);
            foreach ($keyval as $key => $val) {
                $colname = ['syscreatedate', 'sysupdatedate'];
                if (in_array($val['property'], $colname)) {
                    // format create date
                    $query->whereRaw("FORMAT(" . $val['property'] . ", 'yyyy-MM-dd HH:mm:ss') LIKE ?", ['%' . $val['value'] . '%']);
                } else {
                    // cek apakah value numeric, tidak pakai upper. jika bukan numeric pakai uppper
                    if (is_numeric($val['value'])) {
                        $query->where($val['property'], 'LIKE', '%' . $val['value'] . '%');
                    } else {
                        $query->whereRaw("UPPER(" . $val['property'] . ") LIKE ?", ['%' . strtoupper($val['value']) . '%']);
                    }
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
        } else {
            $query->orderBy('asset_no', "ASC");
        }
        $rows = $query->get();
        return json_encode([
            'TotalRows' => $count,
            'Rows' => $rows
        ]);
    }

    public static function load_period_aktif($param)
    {
        $getdata = DB::table('speriod')
            ->where('status', '=', 'OPEN')->first();
        if ($getdata) {
            return json_encode([
                'success' => 'true',
                'message' => 'Data ditampilkan',
                'vdata' => json_encode((array) $getdata)
            ]);
        } else {
            return json_encode([
                'success' => 'false',
                'message' => 'Periode aktif tidak ditemukan',
                'vdata' => null
            ]);
        }
    }

    public static function load_edit_dokumen($param)
    {
        $vdata = json_decode($param['vdata'], true);
        $getdata = DB::table('ba_sto_header')->where('tbid', '=', $vdata['tbid'])->first();
        return json_encode([
            'success' => 'true',
            'message' => 'Data ditampilkan',
            'vdata' => json_encode((array) $getdata)
        ]);
    }
    public static function read_data_item_period($param)
    {
        $vdata = json_decode($param['vdata'], true);

        $SQL = "
            SELECT
                asset_category,
                COUNT(*) as daftar_amount,
                COUNT(CASE WHEN user_scan IS NOT NULL AND scan_date IS NOT NULL THEN 1 END) as aktual_amount,
                COUNT(CASE WHEN user_scan IS NULL AND scan_date IS NULL THEN 1 END) as selisih_amount,
                COUNT(CASE WHEN asset_condition = 'BAGUS - ACTIVE' THEN 1 END) AS bagus_active_amount,
                COUNT(CASE WHEN asset_condition = 'BAGUS - IDLE' THEN 1 END) AS idle_temporary_amount,
                COUNT(CASE WHEN asset_condition = 'IDLE - PERMANENT' THEN 1 END) AS idle_permanent_amount,
                COUNT(CASE WHEN asset_condition = 'RUSAK' THEN 1 END) AS rusak_amount
            FROM stodata WHERE period = ?
            AND asset_pic_dept = ?
            GROUP BY asset_category
        ";
        $result = DB::select($SQL, [$vdata['period'], $vdata['dept_name']]);
        return json_encode([
            'TotalRows' => count($result),
            'Rows' => $result
        ]);
    }
    public static function read_data_item_detail($param)
    {
        $vdata = json_decode($param['vdata'], true);
        $getdata = DB::table('ba_sto_detail')->where(['dokumen_no' => $vdata['dokumen_no'], 'period' => $vdata['period']])->get();
        return json_encode([
            'TotalRows' => $getdata->count(),
            'Rows' => $getdata
        ]);
    }

    public static function dokumen_save($param)
    {
        $vdata = json_decode($param['vdata'], true);
        if ($vdata['tbid'] === 0) {
            return self::proses_dokumen_insert($param);
        } else {
            return self::proses_dokumen_update($param);
        }
    }
    public static function proses_dokumen_insert($param)
    {
        try {
            DB::beginTransaction();

            $vdata = json_decode($param['vdata'], true);
            $vitem = json_decode($param['vitem'], true);

            if ($vdata['dept_code'] === '') {
                return json_encode([
                    'success' => 'false',
                    'message' => 'Data gagal disimpan, pilih department lebih dulu',
                    'vdata' => null
                ]);
            }

            $dept_name = DB::select("SELECT deptname FROM vw_department WHERE deptcode = ? LIMIT 1", [$vdata['dept_code']])[0]->deptname;
            $dokumen_no = DB::select("SELECT generate_ba_sto_no('" . $vdata['dept_code'] . "')")[0]->generate_ba_sto_no;

            $CHECKperiod = DB::table('ba_sto_header')->where(['period' => $vdata['period'], 'dept_code' => $vdata['dept_code']])->first();
            if ($CHECKperiod) {
                DB::rollBack();
                return json_encode([
                    'success' => 'false',
                    'message' => 'Periode saat ini untuk department "' . $dept_name . '" sedang dalam proses',
                    'vdata' => null
                ]);
            }

            $SQLproses = DB::table('ba_sto_header')->insertGetId([
                'period' => $vdata['period'],
                'dokumen_no' => $dokumen_no,
                'dokumen_date' => $vdata['dokumen_date'],
                'dept_code' => $vdata['dept_code'],
                'dept_name' => $dept_name,
                'syscreateuser' => $param['VUSERLOGIN']
            ], 'tbid');

            if ($SQLproses) {
                foreach ($vitem as $item) {
                    DB::table('ba_sto_detail')->insertGetId([
                        'dokumen_no' => $dokumen_no,
                        'period' => $vdata['period'],
                        'asset_category' => $item['asset_category'],
                        'daftar_amount' => $item['daftar_amount'],
                        'aktual_amount' => $item['aktual_amount'],
                        'selisih_amount' => $item['selisih_amount'],
                        'bagus_active_amount' => $item['bagus_active_amount'],
                        'idle_permanent_amount' => $item['idle_permanent_amount'],
                        'idle_temporary_amount' => $item['idle_temporary_amount'],
                        'rusak_amount' => $item['rusak_amount'],
                        'syscreateuser' => $param['VUSERLOGIN'],
                    ], 'tbid');
                }

                $getdata = DB::table('ba_sto_header')->where('tbid', $SQLproses)->first();
                DB::commit();
                return json_encode([
                    'success' => 'true',
                    'message' => 'Data berhasil disimpan',
                    'vdata' => json_encode((array) $getdata)
                ]);
            } else {
                DB::rollBack();
                return json_encode([
                    'success' => 'false',
                    'message' => 'Data gagal disimpan',
                    'vdata' => null
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return json_encode([
                'success' => 'false',
                'message' => 'Error: ' . $e->getMessage(),
                'vdata' => null
            ]);
        }
    }

    public static function proses_dokumen_update($param)
    {
        try {
            DB::beginTransaction();

            $vdata = json_decode($param['vdata'], true);
            $vitem = json_decode($param['vitem'], true);

            if ($vdata['dept_code'] === '') {
                return json_encode([
                    'success' => 'false',
                    'message' => 'Data gagal disimpan, pilih department lebih dulu',
                    'vdata' => null
                ]);
            }

            $SQLproses = DB::table('ba_sto_header')
                ->where("tbid", $vdata["tbid"])
                ->update([
                    'period' => $vdata['period'],
                    'dokumen_no' => $vdata['dokumen_no'],
                    'dokumen_date' => $vdata['dokumen_date'],
                    'dept_code' => $vdata['dept_code'],
                    'dept_name' => DB::raw("(SELECT deptname FROM vw_department where deptcode='" . $vdata['dept_code'] . "' LIMIT 1)"),
                    'sysupdateuser' => $param['VUSERLOGIN'],
                    'sysupdatedate' => DB::raw("(select now())"),
                ]);

            if ($SQLproses > 0) {
                DB::table('ba_sto_detail')->where('dokumen_no', $vdata['dokumen_no'])->delete();
                foreach ($vitem as $item) {
                    DB::table('ba_sto_detail')->insertGetId([
                        'dokumen_no' => $vdata['dokumen_no'],
                        'period' => $vdata['period'],
                        'asset_category' => $item['asset_category'],
                        'daftar_amount' => $item['daftar_amount'],
                        'aktual_amount' => $item['aktual_amount'],
                        'selisih_amount' => $item['selisih_amount'],
                        'bagus_active_amount' => $item['bagus_active_amount'],
                        'idle_permanent_amount' => $item['idle_permanent_amount'],
                        'idle_temporary_amount' => $item['idle_temporary_amount'],
                        'rusak_amount' => $item['rusak_amount'],
                        'syscreateuser' => $param['VUSERLOGIN'],
                    ], 'tbid');
                }

                $getdata = DB::table('ba_sto_header')->where('tbid', $vdata['tbid'])->first();
                DB::commit();
                return json_encode([
                    'success' => 'true',
                    'message' => 'Data berhasil disimpan',
                    'vdata' => json_encode((array) $getdata)
                ]);
            } else {
                DB::rollBack();
                return json_encode([
                    'success' => 'false',
                    'message' => 'Data gagal disimpan',
                    'vdata' => null
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return json_encode([
                'success' => 'false',
                'message' => 'Error: ' . $e->getMessage(),
                'vdata' => null
            ]);
        }
    }
    public static function dokumen_posting($param)
    {
        $vdata = json_decode($param['vdata'], true);
        $SQLproses = DB::table('ba_sto_header')
            ->where("tbid", $vdata["tbid"])
            ->update([
                'dokumen_status' => 'POSTING',
                'dokumen_posting_user' => $param['VUSERLOGIN'],
                'dokumen_posting_date' => DB::raw("(select now())"),
            ]);
        if ($SQLproses > 0) {
            $getdata = DB::table('ba_sto_header')->where('tbid', $vdata["tbid"])->first();
            return json_encode([
                'success' => 'true',
                'message' => 'Data berhasil disimpan',
                'vdata' => json_encode((array) $getdata)
            ]);
        } else {
            return json_encode([
                'success' => 'false',
                'message' => 'Data gagal disimpan',
                'vdata' => null
            ]);
        }
    }

    public static function view_pdf($param)
    {
        $vdata = json_decode($param['vdata'], true);
        $vheader = DB::table('ba_sto_header')->where('tbid', '=', $vdata['tbid'])->first();
        $vbody = DB::table('ba_sto_detail')
            ->where('dokumen_no', '=', $vheader->dokumen_no)->get();

        $html = '
                <div style="float: left; width: 70%">&nbsp;</div>
               <div style="width: 40%; float: right;">
            </div>
                <div style="width: 80%">
                <table style="font-size:14pt; font-weight:bold; border-collapse: collapse; width: 100%;">
                    <tbody>
                        <tr>
                            <td style="text-align: center; padding: 3.5px;width:30%;">
                                <u style="text-decoration:underline;">BERITA ACARA STO FIXED ASSET<u>
                                <p style="text-decoration:none; font-weight:normal; ">Tahun : <span style="color:blue;">' . Carbon::parse($vheader->dokumen_date)->format('Y') . '</span><p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                </div>


                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-top: 10px; gap: 20px;">
                    <div style="float: left; width: 60%">
                        <table  style="border: 1px solid; float: left;">
                            <tbody>
                                <tr>
                                    <td style="height: 20px;">FA Form 02-01</td>
                                </tr>
                            </tbody>
                        </table>
                        <table style=" border: none; margin-top:10px;">
                            <tbody>
                                <tr>
                                    <td style="font-weight:normal; padding: 3.5px; width:25%;">DATE</td>
                                    <td style="font-weight:normal; padding: 3.5px; width:5%;">:</td>
                                    <td style="padding: 2.5px; width:70%; color:blue; font-weight:bold;">' . Carbon::parse($vheader->dokumen_date)->format('d/m/Y') . '</td>
                                </tr>
                                <tr>p
                                    <td style="font-weight:normal; padding: 3.5px; width:25%;">DEPT</td>
                                    <td style="font-weight:normal; padding: 3.5px; width:5%;">:</td>
                                    <td style="padding: 2.5px; width:70%; color:black; font-weight:bold;">' . $vheader->dept_name . '</td>
                                </tr>
                            </tbody>
                        </table>
                        </div>
                        <div style="float: left; width: 40%">
                            <table style="width: 100%; border-collapse: collapse; float: left;">
                                <tr>
                                    <td style="padding: 3px; border: 1px solid black; text-align: center;">Approved</td>
                                    <td style="padding: 3px; border: 1px solid black; text-align: center;">Verified</td>
                                    <td style="padding: 3px; border: 1px solid black; text-align: center;">Checked</td>
                                    <td style="padding: 3px; border: 1px solid black; text-align: center;">Pendamping</td>
                                    <td style="padding: 3px; border: 1px solid black; text-align: center;">Penghitung</td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px; border: 1px solid black; text-align: center; height: 50px; width: 20%;"></td>
                                    <td style="padding: 10px; border: 1px solid black; text-align: center; height: 50px; width: 20%;"></td>
                                    <td style="padding: 10px; border: 1px solid black; text-align: center; height: 50px; width: 20%;"></td>
                                    <td style="padding: 10px; border: 1px solid black; text-align: center; height: 50px; width: 20%;"></td>
                                    <td style="padding: 10px; border: 1px solid black; text-align: center; height: 50px; width: 20%;"></td>
                                </tr>
                                <tr>
                                    <td style="padding: 3px; border: 1px solid black; text-align: center;">FM/DFM</td>
                                    <td style="padding: 3px; border: 1px solid black; text-align: center;">GM-MGR</td>
                                    <td style="padding: 3px; border: 1px solid black; text-align: center;">SSPV/SPV</td>
                                    <td style="padding: 3px; border: 1px solid black; text-align: center;">FIN</td>
                                    <td style="padding: 3px; border: 1px solid black; text-align: center;">PIC</td>
                                </tr>
                            </table>
                        </div>

                    </div>

                    <!-- Data Table -->
                <div style="border: 1px solid #000; margin-top: 15px;">
                    <div style="width: 100%; padding: 0 15px 15px 15px">
                    <p style="margin-left:30px;">Dengan ini menyatakan bahwa pada tanggal <span style="color: blue; font-weight: bold">' . Carbon::parse($vheader->dokumen_date)->format('d/m/Y') . '</span>, telah selesai dilaksanakan proses pengecekan/penghitungan asset tetap</p>
                    <p style="margin-left:30px;">dengan summary sebagai berikut :</p>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr>
                                <th rowspan="2" style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 5%;  font-size: 11pt">No</th>
                                <th rowspan="2" style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 40%; font-size: 11pt">Kategori Asset</th>
                                <th colspan="3" style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 26%; font-size: 11pt">Jumlah</th>
                                <th colspan="4" style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 29%; font-size: 11pt">Kondisi Aktual</th>
                            </tr>
                            <tr>
                                <th style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 10%; font-size: 11pt;">Daftar</th>
                                <th style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 10%; font-size: 11pt;">Aktual</th>
                                <th style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 10%; font-size: 11pt;">Selisih</th>
                                <th style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 10%; font-size: 11pt;">Bagus Aktif</th>
                                <th style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 10%; font-size: 11pt;">Idle Permanent</th>
                                <th style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 10%; font-size: 11pt;">Idle Temporary</th>
                                <th style="border: 1px solid #000; padding: 4px; font-weight: normal; width: 10%; font-size: 11pt;">Rusak</th>
                            </tr>
                            </thead>

                        <tbody>
                            ';

        $nocount = 1;

        foreach ($vbody as $item) {

            $html .= '
                        <tr>
                            <td style="border: 1px solid #000; padding: 4px; font-size: 9pt;">' . $nocount . '</td>
                            <td style="border: 1px solid #000; padding: 4px; font-size: 9pt;">' . $item->asset_category . '</td>
                            <td style="border: 1px solid #000; padding: 4px; font-size: 9pt; text-align:right;">' . $item->daftar_amount . '</td>
                            <td style="border: 1px solid #000; padding: 4px; font-size: 9pt; text-align:right;">' . $item->aktual_amount . '</td>
                            <td style="border: 1px solid #000; padding: 4px; font-size: 9pt; text-align:right;">' . $item->selisih_amount . '</td>
                            <td style="border: 1px solid #000; padding: 4px; font-size: 9pt; text-align:right;">' . $item->bagus_active_amount . '</td>
                            <td style="border: 1px solid #000; padding: 4px; font-size: 9pt; text-align:right;">' . $item->idle_permanent_amount . '</td>
                            <td style="border: 1px solid #000; padding: 4px; font-size: 9pt; text-align:right;">' . $item->idle_temporary_amount . '</td>
                            <td style="border: 1px solid #000; padding: 4px; font-size: 9pt; text-align:right;">' . $item->rusak_amount . '</td>
                        </tr>
                                    ';
            $nocount++;
        }



        $html .= '
                        </tbody>
                    </table>
                    </div>
                 </div>
               </div>

                <div style="border: 1px solid #000; margin-top: 15px;">
                    <div style="width: 100%; padding: 0 15px 35px 15px">
                    <p style="margin-left:30px;">Directors comment :</p>
                    </div>
                </div>

            </div>
            </div>
        </div>
        </div>
        <!-- Original Notice -->
        <div style="text-align: right; font-style: italic; margin-top: 10px">Original to finance & acc. dept</div>
        </div>
    ';


        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'c',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 5,
            'margin_bottom' => 10,
            'default_font_size' => 10,
            'default_font' => 'helvetica'
        ]);

        $mpdf->AddPage('L');
        $mpdf->SetWatermarkText("BA STO");
        $mpdf->showWatermarkText = true;
        $mpdf->watermark_font = 'DejaVuSansCondensed';
        $mpdf->watermarkTextAlpha = 0.23;
        $mpdf->SetDisplayMode('fullpage');
        $mpdf->WriteHTML($html);




        // Output PDF ke variabel base64
        ob_start();
        $mpdf->Output();
        $pdfData = ob_get_contents();
        ob_end_clean();

        // Konversi data PDF ke base64
        $base64Data = base64_encode($pdfData);

        $hasil = array(
            'pdf' => $base64Data
        );
        return json_encode($hasil);
    }
}
