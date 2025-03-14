<?php

namespace App\Custom;

use Tymon\JWTAuth\Contracts\JWTSubject;

class userJWT implements JWTSubject
{
  protected $userlogin;
  protected $password;

  public function __construct($userlogin, $password)
  {
    $this->userlogin = $userlogin;
    $this->password = $password;
  }

  /**
   * Get the identifier that will be stored in the JWT subject claim.
   *
   * @return mixed
   */
  public function getJWTIdentifier()
  {
    return $this->userlogin;  // Gunakan userlogin sebagai identifikasi unik
  }

  /**
   * Return a key value array, containing any custom claims to be added to the JWT.
   *
   * @return array
   */
  public function getJWTCustomClaims()
  {
    return [];
  }

  // Getter for userlogin
  public function getUserlogin()
  {
    return $this->userlogin;
  }

  // Getter for password
  public function getPassword()
  {
    return $this->password;
  }
}
