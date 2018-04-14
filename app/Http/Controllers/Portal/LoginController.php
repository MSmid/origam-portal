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
  use AuthenticatesUsers;

  public function login()
  {
      if (Auth::user()) {
          return redirect()->route('voyager.dashboard');
      }

      return view('login');
  }

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
        //Password is okay for Origam but not for Laravel Auth
        return $this->sendFailedLoginResponse($request);
      }

      $this->incrementLoginAttempts($request);
      return $this->sendFailedLoginResponse($request);
  }

  public function origamLogin(Request $request)
  {
    $username = $request->input($this->username());
    $password = $request->input('password');

    $client = new Client();

    $res = $client->request('GET', env('ORIGAM_BASE_URL') . '/AjaxLogin', [
        'query' => [
            'username' => $username,
            'password' => $password,
        ]
    ]);

    if (strlen($res->getHeaders()["Set-Cookie"][0]) > 52) {
      $lifetime = 60 * 24 * 365 * 2; //function requires lifetime in minutes, set to two years
      return cookie('4isp', $res->getHeaders()["Set-Cookie"][0], $lifetime);
    }
  }

  public function sendLoginResponse(Request $request, $cookie = null)
  {
      $request->session()->regenerate();

      $this->clearLoginAttempts($request);

      return $this->authenticated($request, $this->guard()->user())
              ?: redirect()->intended($this->redirectPath())->withCookie($cookie);
  }

  public function credentials(Request $request)
  {
    return $request->only($this->username(), 'password');
  }

  public function username()
  {
      return 'login';
  }

  public function logout(Request $request)
  {
      $this->guard()->logout();

      $request->session()->invalidate();

      return redirect('/');
  }
}
