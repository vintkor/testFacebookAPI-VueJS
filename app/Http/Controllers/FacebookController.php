<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SammyK;
use Facebook\Exceptions\FacebookSDKException;
use Session;
use Auth;
use DB;
use Response;

class FacebookController extends Controller
{
    private $callback_url;

    public function __construct()
    {
        $this->callback_url = env('FACEBOOK_CALLBACK');
    }

    public function index(SammyK\LaravelFacebookSdk\LaravelFacebookSdk $fb, Request $request)
    {
        // Send an array of permissions to request
        $login_url = $fb->getRedirectLoginHelper()->getLoginUrl($this->callback_url, ['email']);

       // $request->session()->flush();
        // dd($request->session()->all());

        return view('welcome', ['login_url' => $login_url]);
    }

    public function callback(SammyK\LaravelFacebookSdk\LaravelFacebookSdk $fb, Request $request)
    {
        if ( $request->get('hub_verify_token') == 'testfacebookapi' && $request->get('hub_mode') == 'subscribe') {
            return ( response( $request->get('hub_challenge') ) );
        }

        try {
            $token = $fb->getJavaScriptHelper();
        } catch (FacebookSDKException $e) {
            dd($e->getMessage());
        }

        $helper = $fb->getJavaScriptHelper();

        try {
            $accessToken = $helper->getAccessToken();
        } catch(FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        if (! isset($accessToken)) {
            echo 'No cookie set or no OAuth data could be obtained from cookie.';
            exit;
        }

        $token = $accessToken;

        if (! $token->isLongLived()) {
            $oauth_client = $fb->getOAuth2Client();

            try {
                $token = $oauth_client->getLongLivedAccessToken($token);
            } catch (FacebookSDKException $e) {
                dd($e->getMessage());
            }
        }

        $fb->setDefaultAccessToken($token);

        $_SESSION['fb_access_token'] = (string) $accessToken;

        try {
            $response = $fb->get('/me?fields=id,name,email');
            $posts = $fb->get('/me/feed?fields=description,comments,shares,likes,picture,created_time,message&limit=5');
        } catch (FacebookSDKException $e) {
            dd($e->getMessage());
        }

        $facebook_user = $response->getGraphUser();
        $user_posts = $posts->getGraphEdge();

        $back_data = array([
            'user_data' => $facebook_user->asArray(),
            'user_posts' => $user_posts->asArray()
        ]);

        $table = DB::table('users');
        $user = $table->where('email', $facebook_user['email'])->first();

        if($user == null) {
            $new_user = $table->insertGetId([
                'name' => $facebook_user['name'],
                'email' => $facebook_user['email']
            ]);
            Auth::loginUsingId($new_user);
        } else {
            Auth::loginUsingId($user->id);
        }

        return response()->json($back_data);
    }

    public function POSTcallback()
    {
        // Обработка Fasebook Webhooks
    }
}
