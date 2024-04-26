<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PasswordReset;
use Auth;
use Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function register(Request $request){
        $validator = Validator::make($request->all() , [
            'name'=> 'required|string|min:2|max:100',
            'email'=> 'required|string|email|max:100|unique:users',
            'password'=>'required|string|min:6|confirmed'
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(),400);
        }
       $user = User::create([
         'name'=>$request->name,
         'email'=>$request->email,
         'password'=>Hash::make($request->password)

       ]);
        return response()->json([
         'message' => 'User Registered Successfully',
         'user'=> $user
        ]);
    }

    //method for user login
    public function login(Request $request){
        $validator = Validator::make($request->all() , [
            'email'=> 'required|string|email',
            'password'=>'required|string|min:6'
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(),400);
        }
        //checking if the token is being passed or not
        if(!$token = auth()->attempt($validator->validated()))
        {
            return response()->json(['error'=>'Unauthorized']);
        }
        return $this->respondWithToken($token);
        
    }
    //but if the user is authorized then we will generate and send the token in a strustured way
        //only the class which inherits this function will be able to run it
        protected function respondWithToken($token)
        {
            return response()->json([
                'access_token'=>$token,
                'token_type'=>'bearer',
                'expires_in'=>auth()->factory()->getTTL()*60
            ]);
        }
        public function profile()
        {
            return response()->json(auth()->user());
        }

        public function refresh()
        {
            return $this->respondWithToken(auth()->refresh());
        }

        public function logout()
        {
            auth()->logout();

            return response()->json(['message'=>'User Successfully Logged out!']);
        }
        public function forgetPassword(Request $request){
           //always use try-catch block when sending and receiving mail
            try {
                //code...
                $user = User::where('email',$request->email)->get();
                if(count($user)>0){

                    //here we will generate a token for the user who already exists in the table but has forgotten his password
                    //the token will act as the new password generated by the server
                    $token = Str::random(30);
                    // i have to dynamically generate the URL for the page my api will get attached to so im adding the below stuff
                    $domain = URL::to('/');
                    //below is the url domain and at the end i have added the token which acts like new password
                    $url = $domain.'/reset-password?token='.$token;

                    $data['url'] = $url;
                    $data['email'] = $request->email;
                    $data['title'] = 'Forget Password';
                    $data['body'] = 'Click on the link below to reset your password';

                    //here the Mail is actually a predefined precompiled class by the framwork
                    //here the ForgetPasswordMail is a view to show the user
                    Mail::send('ForgetPasswordMail',['data'=>$data], function($message) use($data){

                        $message->to($data['email'])->subject($data['title']);
                    });
                    //using the Carbon class 
                   $DateTime =  Carbon::now()->format('Y-m-d H:i:s');

                   PasswordReset::updateOrCreate(
                    //this method takes two arrays 
                    ['email' => $request->email ],
                    [
                        'email' => $request->email,
                        'token' => $token,
                        'created_at' => $DateTime
                    ]
                   );
                   return response()->json(['success'=> true,'msg'=>'Check your mail to reset the password']);

                }
                else{
                    return response()->json(['success'=> false,'msg'=>'User doesnt exist']);

                }
            } catch (\Exception $e) {
                //throw $th;
                return response()->json(['success'=> false,'msg'=>$e->getMessage()]);
            }
        }

        //writing the method for resetPasswordLoad API
        public function resetPasswordLoad(Request $request){
            $resetData=PasswordReset::where('token',$request->token)->get();
            if(isset($request->token) && count($resetData)>0){

                $user=User::where('email',$resetData[0]['email'])->get();
                return view('resetPassword',compact('user'));


            }
            else{
                return view('404');

            }

        }
        //writing the method for resetPassword API
        public function resetPassword(Request $request){

            $request->validate([

                'password'=>'required|string|min:6|confirmed'

            ]);
            $user = User::find($request->id);
            $user->password = $request->password;
            $user->save();
            PasswordReset::where('email',$user->email)->delete();

            //i can either return the view or simply write a heading for reset password
            return view('PasswordBeenSet');

        }

       /* public function liveMonitoring(Request $request) {
            global $PDO; // Assuming $PDO is the database connection object
    
            // Check if the device ID is provided
            if (!$request->has('deviceId')) {
                return response()->json(['error' => 'Device ID is missing'], 400);
            }
    
            // Fetch real-time data from the database
            $stmt = $PDO->prepare("SELECT * FROM `readings` WHERE `device_id` = :deviceId ORDER BY `id` DESC LIMIT 1");
            $stmt->execute(array(':deviceId' => $request->deviceId));
            $array = $stmt->fetch();
    
            // Handle case when power factor average is null
            if ($array['powerfactorAverage'] === null) {
                $array['powerfactorAverage'] = ($array['powerfactorPhaseR'] + $array['powerfactorPhaseY'] + $array['powerfactorPhaseB']) / 3;
            }
    
            // Format data into JSON response
            $response = [
                "gaugeChartCols" => [
                    ["id" => "", "label" => "Label", "pattern" => "", "type" => "string"],
                    ["id" => "", "label" => "Value", "pattern" => "", "type" => "number"]
                ],
                "gaugeChartRows" => [
                    [
                        "c" => [
                            ["v" => "Voltage", "f" => null],
                            [
                                "v" => round($array['averageLineNeutralVoltage'], 2),
                                "f" => number_format($array['averageLineNeutralVoltage'], 0) . " V"
                            ]
                        ]
                    ],
                    // Format other rows as needed
                ],
                // Include other sections of data (comboChartRows, lineChartRows, extras) similarly
                // Handle errors or missing data appropriately
            ];
    
            // Return JSON response
            return response()->json($response);
        }*/

        public function liveMonitoring(Request $request) {
            // Check if the device ID is provided
            if (!$request->has('deviceId')) {
                return response()->json(['error' => 'Device ID is missing'], 400);
            }
    
            // Fetch real-time data from the database using Laravel's query builder
            $reading = DB::table('readings')
                ->where('device_id', $request->deviceId)
                ->orderByDesc('id')
                ->first();
    
            // Handle case when power factor average is null
            $powerFactorAverage = $reading->powerfactorAverage ?? ($reading->powerfactorPhaseR + $reading->powerfactorPhaseY + $reading->powerfactorPhaseB) / 3;
    
            // Format data into JSON response
            $response = [
                "gaugeChartCols" => [
                    ["id" => "", "label" => "Label", "pattern" => "", "type" => "string"],
                    ["id" => "", "label" => "Value", "pattern" => "", "type" => "number"]
                ],
                "gaugeChartRows" => [
                    [
                        "c" => [
                            ["v" => "Voltage", "f" => null],
                            [
                                "v" => round($reading->averageLineNeutralVoltage, 2),
                                "f" => number_format($reading->averageLineNeutralVoltage, 0) . " V"
                            ]
                        ]
                    ],
                    // Format other rows as needed
                ],
                // Include other sections of data (comboChartRows, lineChartRows, extras) similarly
                // Handle errors or missing data appropriately
            ];
    
            // Return JSON response
            return response()->json($response);
        }
}
