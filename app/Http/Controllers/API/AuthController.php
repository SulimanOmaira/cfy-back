<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\posts;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use ApiResponseTrait;
    public function __construct(){
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }
    // Login
    public function login(Request $request){
        try{
            $validator = Validator::make($request->only('email', 'password'),[
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);
            if($validator->fails()){
                return response()->json($validator->errors(),422);
            }
            if(!$token = auth()->attempt($validator->validated())){
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            $user = auth()->user();
            $user->image = Storage::url($user->image);
            return $this->apiResponse($user, 'ok', 'success', 200)->header('Authorization', 'Bearer  ' . $token);
        } catch(\Exception $e){
            return $this->apiResponse(null, $e->getMessage(), 'failure', 400);
        }
    }
    // Register
    public function register(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'name' => 'required|string|between:2,100',
                'email' => 'required|email|max:100',
                'password' => 'required|string|min:6',
                'city' => 'required|string|max:100',
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
            if($validator->fails()){
                return $this->apiResponse(null, $validator->errors(), 'failure', 400);
            }
            $folder = $this->getFolderByType('user');
            $imagePath = $this->saveImage($request->file('image'), $folder);
            $user = User::create(array_merge(
                $validator->validated(), 
                ['password' => bcrypt($request->password), 'image' => $imagePath[0]]
            ));
            $user->image = Storage::url($user->image);
            return $this->apiResponse($user, 'User successfully registered', 'success', 201);
        } catch(\Exception $e){
            return $this->apiResponse(null, $e->getMessage(), 'failure', 400);
        }

    }
    // Logout
    public function logout(){
        try{
        auth()->logout();
        return $this->apiResponse(null, 'User successfully signed out', 'success', 200);
        }catch(\Exception $e){
            return $this->apiResponse(null, $e->getMessage(), 'failure', 400);
        }
    }
    // Refresh
    public function refresh(){
        $token = JWTAuth::getToken(); 
        $newToken = JWTAuth::refresh($token); 
        return $this->createNewToken($newToken);
    }

// My Profile
    public function myProfile(){
        try{
            $userId = auth()->user(); 
            $data = posts::with([
                'descriptions',
                'descriptions.cars', 
                'user', 
            ])->where('users_id', $userId->id)
            ->withCount([
                'postsSaved as is_saved' => function ($query) use ($userId) {
                    $query->where('users_id', $userId->id);
                }
            ])
            ->orderBy('created_at', 'desc')
            ->get();
            if($data->isNotEmpty()){
                $data = $this->imageDecode($data);
                return $this->apiResponse($data, 'View created or replaced successfully.', 'success', 200);
            }else{
                return $this->apiResponse($userId, 'View created or replaced successfully.', 'success', 200);
            }
        }catch (\Exception $e){
            return $this->apiResponse(null, $e->getMessage(), 'faliure', 500);
        }
    }
    
// User Informations
    public function userInformations(){
        $user = auth()->user();
        return $this->apiResponse($user, '', 'success', 200);
    }

// User Profile
    public function userProfile(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'id' => 'required|integer|exists:users,id'
            ]);
            if($validator->fails()){
                return $this->apiResponse(null,$validator->errors(),'failure', 400);
            }
            $userId = $request->input('id'); 
            $data = posts::with([
                'descriptions',
                'descriptions.cars', 
                'user', 
            ])->where('users_id', $userId)
            ->withCount([
                'postsSaved as is_saved' => function ($query) use ($userId) {
                    $query->where('users_id', $userId);
                }
            ])
            ->orderBy('created_at', 'desc')
            ->get();
            $data = $this->imageDecode($data);
            return $this->apiResponse($data, 'View created or replaced successfully.', 'success', 200);
        }catch (\Exception $e){
            return $this->apiResponse(null, $e->getMessage(), 'faliure', 500);
        }
    }

// Update Name 
    public function updateName(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|between:2,100',
            ]);
            if ($validator->fails()) {
                return $this->apiResponse(null, $validator->errors(), 'failure', 400);
            }
            $user = auth()->user();
            $user->update(['name' => $request->input('name')]);
            return $this->apiResponse($user, 'User name successfully updated', 'success', 200);
        } catch (\Exception $e) {
            return $this->apiResponse(null, $e->getMessage(), 'failure', 400);
        }
    }

// Update Password
    public function updatePassword(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                return $this->apiResponse(null, $validator->errors(), 'failure', 400);
            }
            $user = auth()->user();
            $user->update(['password' => bcrypt($request->input('password'))]);
            // $user->update(['password' => $request->input('password')]);

            return $this->apiResponse($user, 'User password successfully updated', 'success', 200);
        } catch (\Exception $e) {
            return $this->apiResponse(null, $e->getMessage(), 'failure', 400);
        }
    }

// update Image
    public function updateImage(Request $request) {
        try {
            // التحقق من صحة البيانات
            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($validator->fails()) {
                return $this->apiResponse(null, $validator->errors(), 'failure', 400);
            }
            $user = auth()->user();

            $folder = $this->getFolderByType('user');
            $imagePath = $this->saveImage($request->file('image'), $folder)[0]; // تخزين الصورة

            // تحديث الصورة
            $this->deleteImage($user->image);
            $user->update(['image' => $imagePath]);
            $user->image = Storage::url($user->image); // الحصول على الرابط الصحيح للصورة

            return $this->apiResponse($user, 'User image successfully updated', 'success', 200);
        } catch (\Exception $e) {
            return $this->apiResponse(null, $e->getMessage(), 'failure', 400);
        }
    }

// Update City
    public function updateCity(Request $request) {
        try {
            // التحقق من صحة البيانات
            $validator = Validator::make($request->all(), [
                'city' => 'required|string|max:100',
            ]);
            if ($validator->fails()) {
                return $this->apiResponse(null, $validator->errors(), 'failure', 400);
            }
            $user = auth()->user();
            $user->update(['city' => $request->input('city')]);
            return $this->apiResponse($user, 'User city successfully updated', 'success', 200);
        } catch (\Exception $e) {
            return $this->apiResponse(null, $e->getMessage(), 'failure', 400);
        }
    }

// Crete New Token
    protected function createNewToken($token){
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,  
            'user' => auth()->user(),  
        ]);
    }

// Validate Password
    public function validatePassword(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'password' => 'required|string|min:6|max:100',
            ]);
            if ($validator->fails()) {
                return $this->apiResponse(null, $validator->errors(), 'failure', 400);
            }
            $user = auth()->user();
            if (!Hash::check($request->password, $user->password)) {
                return $this->apiResponse(null, ['password' => 'كلمة المرور غير صحيحة'], 'failure', 401);
            }
    
            // إرجاع استجابة ناجحة إذا كانت كلمة المرور صحيحة
            return $this->apiResponse(null, 'كلمة المرور صحيحة', 'success', 200);
        }catch (\Exception $e) {
            return $this->apiResponse(null, $e->getMessage(), 'failure', 400);
        }
    }

}