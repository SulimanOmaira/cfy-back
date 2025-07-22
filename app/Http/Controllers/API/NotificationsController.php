<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\ApiResponseTrait;
use App\Models\messages;
use App\Models\Notifications;
use App\Models\posts;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationsController extends Controller
{
    use ApiResponseTrait;
    
    public function store(Request $request){
        $validated = Validator::make($request->all(),[
            'users_id' => 'required|integer',
            'view_users_id' => 'required|integer',
            'title' => 'required|string',
            'body' => 'required|string',
        ]);
        if($validated->fails()){
            return $this->apiResponse(null,$validated->errors(),'failure', 400);
        }
        $notify = Notifications::create($request->all());
        if($notify){
            $this->sendNotification($request->input('users_id'), $request->input('title'), $request->input('body'));
            return $this->apiResponse($notify,'ok','success', 200);
            }
        return $this->apiResponse(null,'This Messages Not Saved','failure', 404);
    }   
    
    public function show(Request $request){
        

        $user = User::with('notifications')->find($request->input('id'));
        $notifications = $user->notifications;

        $user = User::find($request->input('id'));
        if (!$user) {
            return $this->apiResponse(null, 'User not found', 'failure', 400);
        }
        // $notifications = $user->notifications()->with('reference')->get();
        return $this->apiResponse($notifications, '', 'success', 200);
    }

    public function destroy(Request $request)
    {
        $notification = Notifications::find($request->input('id'));

        if (!$notification) {
            return $this->apiResponse(null, 'Notification not found', 'failure', 404);
        }
        $notification->delete();
        return $this->apiResponse(null, 'Notification deleted successfully', 'success', 200);
    }
}