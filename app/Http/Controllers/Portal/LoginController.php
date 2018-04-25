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

  /** Override
   * Index method displaying login view or app based on fact if user is already
   * authenticated.
   *
   * @return \Illuminate\Http\RedirectResponse
   */
  public function login()
  {
      if (Auth::user()) {
          return redirect()->route('voyager.dashboard');
      }

      return view('login');
  }

  /** Override
   * Method dealing with login form POST action. It attempts to authenticate user
   * in Origam and then in Laravel Auth impelementation.
   *
   * @param \Illuminate\Http\Request $request
   *
   * @return \Illuminate\Http\RedirectResponse
   */
  public function postLogin(Request $request)
  {
      $this->validateLogin($request);

      if ($this->hasTooManyLoginAttempts($request)) {
          $this->fireLockoutEvent($request);

          return $this->sendLockoutResponse($request);
      }

      if (env('ORIGAM_DISABLE_AUTH')) {
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
      } else {
        $credentials = $this->credentials($request);

        if ($this->guard()->attempt($credentials, $request->has('remember'))) {
            return $this->sendLoginResponse($request);
        }

        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
      }
  }

  /** Override
   * Method used for authenticating user with Origam. If it is succesfull, it
   * returns Origam token cookie.
   *
   * @param \Illuminate\Http\Request $request
   *
   * @return Symfony\Component\HttpFoundation\Cookie
   */
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
    } else {
      return null;
    }
  }

  /** Override
   * Method is used for authenticate user in Laravel and chain Origam cookie.
   *
   * @param \Illuminate\Http\Request $request
   * @param $cookie
   *
   * @return Symfony\Component\HttpFoundation\Cookie
   */
  public function sendLoginResponse(Request $request, $cookie = null)
  {
      $request->session()->regenerate();

      $this->clearLoginAttempts($request);

      return $this->authenticated($request, $this->guard()->user())
              ?: redirect()->intended($this->redirectPath())->withCookie($cookie);
  }

  /** Override
   * Method for retrieving credentials from Request object.
   *
   * @param \Illuminate\Http\Request $request
   *
   * @return Object holding credentials
   */
  public function credentials(Request $request)
  {
    return $request->only($this->username(), 'password');
  }

  /** Override
   * This method define which column is used as username. It can be email,
   * login, username, whatever.
   *
   * @return String
   */
  public function username()
  {
      return 'login';
  }

  /** Override
   * Logout destroys session and set logout of user. Then redirect to root
   * (login view)
   *
   * @param \Illuminate\Http\Request $request
   *
   * @return \Illuminate\Http\RedirectResponse
   */
  public function logout(Request $request)
  {
      $this->guard()->logout();

      $request->session()->invalidate();

      return redirect('/');
  }
}
