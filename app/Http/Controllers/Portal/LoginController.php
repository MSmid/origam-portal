<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use TCG\Voyager\Facades\Voyager;
use GuzzleHttp\Client;
use TCG\Voyager\Http\Controllers\VoyagerAuthController as BaseVoyagerAuthController;
use Symfony\Component\HttpFoundation\Cookie;

class LoginController extends BaseVoyagerAuthController
{

  public function postLogin(Request $request)
  {
      $this->validateLogin($request);

      if ($this->hasTooManyLoginAttempts($request)) {
          $this->fireLockoutEvent($request);

          return $this->sendLockoutResponse($request);
      }

      $cookie = $this->origamLogin($request);

      if($cookie) {
        $credentials = $this->credentials($request);

        if ($this->guard()->attempt($credentials, $request->has('remember'))) {
            return $this->sendLoginResponse($request, $cookie);
        }
      } else {
        $this->incrementLoginAttempts($request);
        return $this->sendFailedLoginResponse($request);
      }


      // return redirect('portal')->withCookie($cookie);

  }

  public function origamLogin(Request $request)
  {
    $username = $request->input('email');
    $password = $request->input('password');

    $client = new Client();

    $res = $client->request('GET', 'http://localhost/4isp/AjaxLogin', [
        'query' => [
            'username' => $username,
            'password' => $password,
        ]
    ]);

    if (strlen($res->getHeaders()["Set-Cookie"][0]) > 52) {
      return cookie('4isp', $res->getHeaders()["Set-Cookie"][0]);
    }
  }

  public function sendLoginResponse(Request $request, $cookie = null)
  {
      $request->session()->regenerate();

      $this->clearLoginAttempts($request);

      return $this->authenticated($request, $this->guard()->user())
              ?: redirect()->intended($this->redirectPath())->withCookie($cookie);
  }
}
