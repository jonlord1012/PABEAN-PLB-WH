<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use App\Custom\userJWT;
use Tymon\JWTAuth\Facades\JWTAuth;


class Mlogin extends Model
{

  public static function handleAction($method, $params)
  {
    switch ($method) {
      case 'login':
        return self::login($params);
      case 'reload':
        return self::reload($params);
      default:
        return ['error' => 'Action not recognized'];
    }
  }

  // Fungsi login
  public static function login($params)
  {
    $data = array(
      $params["username"],
      $params["password"]
    );
    $SQL_CALLSP = DB::select('EXEC SP_USERLOGIN @VUSERNAME=?,@VUSERPASSWORD=?', $data);
    $ndata = (array) $SQL_CALLSP[0];

    if ($ndata['success'] == 'true') {
      // Buat token JWT
      $cpUserJWT = new userJWT($params["username"], $params["password"]);  // Sesuaikan nama kolomnya
      $token = JWTAuth::fromUser($cpUserJWT);

      // Jika token berhasil dibuat, kembalikan respons sukses beserta token
      $hasil = array(
        'success' => 'true',
        'message' => 'Login Success..',
        'token' => $token,
        'dtmenu' => $ndata['dtmenu'],
        'profile' => $ndata['dtprofile']
      );
    } else {
      $hasil = array(
        'success' => 'false',
        'message' => 'User atau Password salah..'
      );
    }

    return json_encode($hasil);
  }
  public static function reload($params)
  {
    $data = array(
      $params["username"]
    );
    $SQL_CALLSP = DB::select('EXEC SP_USERLOGIN_RELOAD @VUSERNAME=?', $data);
    $ndata = (array) $SQL_CALLSP[0];
    if ($ndata['success'] == 'true') {
      $hasil = array(
        'success' => 'true',
        'message' => 'Login Success..',
        'dtmenu' => $ndata['dtmenu'],
        'dtmenu_header' => $ndata['dtmenu_header'],
        'profile' => $ndata['dtprofile']
      );
    } else {
      $hasil = array(
        'success' => 'false',
        'message' => 'User atau Password salah..'
      );
    }

    return json_encode($hasil);
  }
}
