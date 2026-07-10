<?php

namespace App\Http\Controllers\User\Auth;

use App\Models\Pin;
use App\Models\User;
use App\Lib\Intended;
use App\Constants\Status;
use App\Models\UserExtra;
use App\Models\UserLogin;
use Illuminate\Http\Request;
use App\Models\AdminNotification;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Auth\RegistersUsers;
use App\Services\MatrixPlacementService;

class RegisterController extends Controller
{

    use RegistersUsers;
    protected $MatrixPlacementService;

    public function __construct(MatrixPlacementService $matrixPlacementService)
    {
        parent::__construct();
        $this->matrixPlacementService = $matrixPlacementService;
    }

    public function showRegistrationForm(Request $request)
    {
        $pageTitle = "Register"; 
         $refUser = null; 
        if ($request->ref) {
            $refUser = User::where('username', $request->ref)->first();
            if ($refUser == null) {
                $notify[] = ['error', 'Invalid Referral link.'];
                return redirect()->route('home')->withNotify($notify);
            }

            $refUser = User::where('username', $request->ref)->where('status', Status::USER_ACTIVE)->first();

            if ($refUser == null) {
                $notify[] = ['error', 'Your Referral is not active. pls contact  Admin or your Referral'];
                return redirect()->route('home')->withNotify($notify);
            }

            
        } 

        Intended::identifyRoute();
        return view('Template::user.auth.register', compact('pageTitle', 'refUser'));
    }


    protected function validator(array $data)
    {

        $passwordValidation = Password::min(6);

        if (gs('secure_password')) {
            $passwordValidation = $passwordValidation->mixedCase()->numbers()->symbols()->uncompromised();
        }

        $agree = 'nullable';
        if (gs('agree')) {
            $agree = 'required'; 
        }
        

            $validate     = Validator::make($data, [
                'referBy'      => 'sometimes|string|max:160',
                'firstname' => 'required',
                'lastname'  => 'required',
                'parent'       => 'sometimes|required|string',
                'position'     => 'sometimes|required|string|max:6',
                'username'     => 'required|unique:users|min:6',
                // 'activation_pin'     => 'required|exists:pins,pin',
                'email'     => 'required|string|email',
                'password'  => ['required', 'confirmed', $passwordValidation],
                'captcha'   => 'sometimes|required',
                'agree'     => $agree
            ], [
                'firstname.required' => 'The first name field is required',
                'lastname.required' => 'The last name field is required'
            ]);
       
        return $validate;
    }

    public function register(Request $request)
    {
        $this->validator($request->all())->validate();
        $general = gs();

        if (!gs('registration')) {
            return back();
        }

        $request->session()->regenerateToken();

        if (!verifyCaptcha()) {
            $notify[] = ['error', 'Invalid captcha provided'];
            return back()->withNotify($notify);
        }

        /*
        if (!$request->activation_pin) {
            $notify[] = ['error', 'Provide Activation Pin'];
            return back()->withNotify($notify);
        }
        $activation_pin = Pin::where('pin', $request->activation_pin)->where('status', Status::NO)->first();

        if(!$activation_pin)
        {
            $notify[] = ['error', 'Pin has already been used.'];
            return back()->withNotify($notify);
        }

        if((int)$activation_pin->amount !== (int)$general->registration_fee){

            $notify[] = ['error', 'Invalid. Pin amount does not match registration fee'];
            return back()->withNotify($notify);
        }
        */
        //dd(gs()->registration_fee);

        if($request->parent){

             $parent = User::where('username', $request->parent)->first();      

            if(!$parent){
                    $notify[] = ['error', 'Parent is not in the network..'];
                return back()->withNotify($notify)->withInput($request->all());
            }

            // later check if this parent is still in stage one
             $user_parent_matrix = Matrix::where('user_id', $parent->id)->where($request->position, 0)->first();
             //dd($user_matrix);

             // check if the parent is in the matrix before assignment to new user
            if(!$user_parent_matrix){
                $notify[] = ['error', 'Chose another position. Parent request->position is filled'];
                return back()->withNotify($notify)->withInput($request->all());
            }
              
            // all check has been completed move to assignment 

            $user->parent = $request->parent;
            $user->position = $request->position;// NOTE: to be crab later

            $parent_id = $parent->id; //set parent ID                          

        }else{
            
             $parent_id = 0; //set parent ID
        }

        if (preg_match("/[^a-z0-9_]/", trim($request->username))) {
            $notify[] = ['info', 'Username can contain only small letters, numbers and underscore.'];
            $notify[] = ['error', 'No special character, space or capital letters in username.'];
            return back()->withNotify($notify)->withInput($request->all());
        }
        

        event(new Registered($user = $this->create($request->all())));
        //dd($request);
        $this->guard()->login($user);

        return $this->registered($request, $user) ?: redirect($this->redirectPath());
    }



    protected function create(array $data)
    {
        
        if($data['referBy'] == '' || $data['referBy'] == null){
            $us = 1;
            
        }else{
            $userCheck = User::where('username', $data['referBy'])->first();
            //$pos = getPosition($userCheck->id, $data['position']);
            $us = $userCheck->id;
           
        }
        
        
        

        //User Create
        $user            = new User();
        $user->ref_by       = $us;
        $user->pos_id       = 1;
        $user->email     = strtolower($data['email']);
        $user->firstname = $data['firstname'];
        $user->lastname  = $data['lastname'];
        // $user->pin  = $data['activation_pin'];
        $user->password  = Hash::make($data['password']);
        $user->kv = gs('kv') ? Status::NO : Status::YES;
        $user->ev = gs('ev') ? Status::NO : Status::YES;
        $user->sv = gs('sv') ? Status::NO : Status::YES;
        $user->ts = Status::DISABLE;
        $user->tv = Status::ENABLE;
        $user->save();

        $adminNotification            = new AdminNotification();
        $adminNotification->user_id   = $user->id;
        $adminNotification->title     = 'New member registered';
        $adminNotification->click_url = urlPath('admin.users.detail', $user->id);
        $adminNotification->save();


        //Login Log Create
        $ip        = getRealIP();
        $exist     = UserLogin::where('user_ip', $ip)->first();
        $userLogin = new UserLogin();

        if ($exist) {
            $userLogin->longitude    = $exist->longitude;
            $userLogin->latitude     = $exist->latitude;
            $userLogin->city         = $exist->city;
            $userLogin->country_code = $exist->country_code;
            $userLogin->country      = $exist->country;
        } else {
            $info                    = json_decode(json_encode(getIpInfo()), true);
            $userLogin->longitude    = @implode(',', $info['long']);
            $userLogin->latitude     = @implode(',', $info['lat']);
            $userLogin->city         = @implode(',', $info['city']);
            $userLogin->country_code = @implode(',', $info['code']);
            $userLogin->country      = @implode(',', $info['country']);
        }

        $userAgent          = osBrowser();
        $userLogin->user_id = $user->id;
        $userLogin->user_ip = $ip;

        $userLogin->browser = @$userAgent['browser'];
        $userLogin->os      = @$userAgent['os_platform'];
        $userLogin->save();


        return $user;
    }

    public function checkUser(Request $request)
    {
        $exist['data'] = false;
        $exist['type'] = null;
        if ($request->email) { 
            $exist['data'] = User::where('email', $request->email)->exists();
            $exist['type'] = 'email';
            $exist['field'] = 'Email';
        }
        if ($request->mobile) {
            $exist['data'] = User::where('mobile', $request->mobile)->where('dial_code', $request->mobile_code)->exists();
            $exist['type'] = 'mobile';
            $exist['field'] = 'Mobile';
        }
        if ($request->username) {
            $exist['data'] = User::where('username', $request->username)->exists();
            $exist['type'] = 'username';
            $exist['field'] = 'Username';
        }
        return response($exist);
    }

    public function registered(Request $request, $user)
    {
        $user_extras = new UserExtra();
        $user_extras->user_id = $user->id;
        $user_extras->save();
        updateFreeCount($user->id);
        return to_route('user.welcome');
    }
}
