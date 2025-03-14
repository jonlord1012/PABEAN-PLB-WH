<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; // Ini adalah namespace yang benar
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;

use Illuminate\Foundation\Application;
class C_home extends Controller
{

    public function checkLaravelVersion()
    {
        return response()->json([
            'laravel_version' => Application::VERSION
        ]);
    }

    public function index()
    {
        return response()->json(['message' => 'Tunas Data Kreasi...']);
    }
    public function check_db()
    {
        try {
            // Jalankan query sederhana untuk memeriksa koneksi
            DB::connection()->getPdo();
            return response()->json([
                'success' => true,
                'message' => 'Koneksi ke database berhasil.'
            ], 200);
        } catch (\Exception $e) {
            // Jika terjadi kesalahan, tampilkan pesan error
            return response()->json([
                'success' => false,
                'message' => 'Gagal terhubung ke database: ' . $e->getMessage()
            ], 500);
        }
    }
    public function reload(Request $request)
    {
        $payload = JWTAuth::parseToken()->getPayload()->toArray();
        $method = 'reload';
        $data = array(
            'username' => $payload['sub']
        );
        $result = \App\Models\Mlogin::handleAction($method, $data);

        print ($result);
    }
    public function send_email()
    {
        $hasil = array(
            'success' => 'true',
            'message' => 'reload'
        );
        return response()->json($hasil);
    }
    public function auth(Request $request)
    {
        $payload = $request->json()->all();
        $method = $payload['method'];
        $result = \App\Models\Mlogin::handleAction($method, $payload['data']);

        print ($result);
    }
    public function general($A, $B, Request $request)
    {
        $jwt_data = JWTAuth::parseToken()->getPayload()->toArray();
        $payload = $request->all();
        $payload['VUSERLOGIN'] = $jwt_data['sub'];

        $modelClass = "\\App\\Models\\" . ucfirst('M' . $A);
        // check Class Model
        if (!class_exists($modelClass)) {
            return response()->json([
                'success' => 'false',
                'message' => 'Model not found'
            ], 404);
        }
        $method = 'handleAction';

        if ("{$A}s" === $B) {
            $method = 'handleAction';
        } else {
            $method = $payload['method'];
        }
        // check Method
        if (!method_exists($modelClass, $method)) {
            return response()->json([
                'success' => 'false',
                'message' => "Method '$method' not found in model M'$A'"
            ], 404);
        }


        try {
            if ("{$A}s" === $B) {
                // When method is handleAction, pass both the method from payload and the payload data
                $result = $modelClass::$method($payload['method'], $payload);
            } else {
                // For other methods, pass the payload as is
                $method = $payload['method'];
                $result = $modelClass::$method($payload);
            }
            print ($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => 'false',
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function download_file($filename)
    {
        try {
            $path = base_path('z_download/' . $filename);
            if (file_exists($path)) {
                return response()->download($path);
            } else {
                return response()->json(
                    [
                        'location_file' => $path,
                        'error' => 'File not found.'
                    ],
                    404
                );
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => 'false',
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
