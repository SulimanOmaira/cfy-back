<?php

use App\Http\Controllers\Api\CarsController;
use App\Http\Controllers\API\DescriptionsController;
use App\Http\Controllers\API\MessagesController;
use App\Http\Controllers\API\PostsController;
use App\Http\Controllers\API\PostsSavedController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\RoomController;
use App\Http\Controllers\API\ViewsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::post('login', [AuthController::class, 'login']);
// Route::middleware('auth:api')->get('user', [AuthController::class, 'getAuthenticatedUser']);

Route::prefix('auth')->middleware('api')->group(function () { // DONE
    Route::post('/login',[AuthController::class,'login']);
    Route::post('/register',[AuthController::class,'register']);
    Route::post('/logout',[AuthController::class,'logout']);
});

Route::prefix('auth')->middleware('jwt.verify')->group(function () { // NOT DONE
    Route::post('/refresh',[AuthController::class,'refresh'])->middleware('jwt.verify');
    Route::post('/user-profile',[AuthController::class,'userProfile']);
    Route::get('/user-informations',[AuthController::class,'userInformations']);
    Route::post('/my-profile',[AuthController::class,'myProfile']);
    Route::get('/all',[AuthController::class,'index']);
    Route::post('/one',[AuthController::class,'show']);
    Route::post('/add',[AuthController::class,'store']);
    Route::post('/update-name',[AuthController::class,'updateName']);
    Route::post('/validate-password',[AuthController::class,'validatePassword']);
    Route::post('/update-password',[AuthController::class,'updatePassword']);
    Route::post('/update-city',[AuthController::class,'updateCity']);
    Route::post('/update-image',[AuthController::class,'updateImage']);
    Route::post('/delete',[AuthController::class,'destroy']);
});


Route::prefix('cars')->middleware('jwt.verify')->group(function () { // DONE
    Route::get('/all',[CarsController::class,'index']);
    Route::post('/one',[CarsController::class,'show']);
    Route::post('/add',[CarsController::class,'store']);
    Route::post('/update',[CarsController::class,'update']);
    Route::post('/delete',[CarsController::class,'destroy']);
});
Route::prefix('posts')->middleware('jwt.verify')->group(function () { // DONE
    Route::get('/all',[PostsController::class,'index']);
    Route::get('/all-post',[ViewsController::class,'postsWithDescr']); 
    Route::post('/one-post',[ViewsController::class,'onePostsWithDescr']);
    Route::post('/add-post',[ViewsController::class,'addPostWithDescriptions']);
    Route::post('/update-post',[ViewsController::class,'updatePostWithDescriptions']);
    Route::post('/one',[PostsController::class,'show']);
    Route::post('/add',[PostsController::class,'store']);
    Route::post('/update',[PostsController::class,'update']);
    Route::post('/delete',[PostsController::class,'destroy']);
    Route::post('/search',[ViewsController::class,'getPostsByNames']);
    
});
Route::prefix('descriptions')->middleware('jwt.verify')->group(function () { //DONE
    Route::get('/all',[DescriptionsController::class,'index']);
    Route::post('/one',[DescriptionsController::class,'show']);
    Route::post('/add',[DescriptionsController::class,'store']);
    Route::post('/update',[DescriptionsController::class,'update']);
    Route::post('/delete',[DescriptionsController::class,'destroy']);
});
Route::prefix('messages')->middleware('jwt.verify')->group(function () { // DONE
    Route::post('/all',[MessagesController::class,'index']);
    Route::post('/one',[MessagesController::class,'show']);
    Route::post('/add',[MessagesController::class,'store']);
    Route::post('/update',[MessagesController::class,'update']);
    Route::post('/delete',[MessagesController::class,'destroy']);
});
Route::prefix('room')->middleware('jwt.verify')->group(function () { // DONE
    Route::post('/all',[RoomController::class,'index']);
    Route::post('/one',[RoomController::class,'show']);
    Route::post('/add',[RoomController::class,'store']);
    Route::post('/update',[RoomController::class,'update']);
    Route::post('/delete',[RoomController::class,'destroy']);
});
Route::prefix('posts-saved')->middleware('jwt.verify')->group(function () { //Done
    Route::get('/all',[PostsSavedController::class,'index']);
    Route::post('/one',[PostsSavedController::class,'show']);
    Route::post('/add',[PostsSavedController::class,'store']);
    Route::post('/update',[PostsSavedController::class,'update']);
    Route::post('/delete',[PostsSavedController::class,'destroy']);
});

Route::prefix('payment')->middleware('jwt.verify')->group(function () {
    Route::post('/create-intent', [PaymentController::class, 'createPaymentIntent']);
});

Route::get('/alls-post',[ViewsController::class,'postsWithDescr']); 