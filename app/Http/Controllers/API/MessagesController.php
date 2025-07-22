<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Models\messages;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MessagesController extends Controller{
    use ApiResponseTrait;
    protected $roomController;
    
    public function __construct(RoomController $roomController){
        $this->roomController = $roomController;
    }
    public function index(Request $request){
        $validator = Validator::make($request->all(), [
            "resiver_users_id" => "required|integer",
            "sender_users_id" => "required|integer",
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(null, $validator->errors(), 'failure', 400);
        }

        $send = $request->input('sender_users_id');
        $resive = $request->input('resiver_users_id');

        $messages = messages::where('sender_users_id', $send)
            ->where('resiver_users_id', $resive)
            ->union(
                messages::where('resiver_users_id', $send)
                    ->where('sender_users_id', $resive)
            )
            ->orderBy('created_at', 'asc')
            ->get();

        if ($messages->isNotEmpty()) {
            $user = User::find($resive);
            foreach ($messages as $message) {
                $message->user_name = $user->name; 
                $message->user_image = $user->image;
            }
            return $this->apiResponse($messages, 'ok', 'success', 200);
        }
        return $this->apiResponse(null, 'This Messages Not Found', 'failure', 404);
    }

    public function store(Request $request)
    {
        $request->merge([
            'resiver_users_id' => $request->resiver_users_id,
            'sender_users_id' => $request->sender_users_id,
        ]);
        $response = $this->roomController->create($request);
        if ($response->getStatusCode() !== 201) {
            return $response; 
        }
        $roomData = json_decode($response->getContent(), true);
        if (isset($roomData['data']['id'])) {
            $request->merge(['room_id' => $roomData['data']['id']]);
        } else {
            return $this->apiResponse(null, 'Room creation failed.', 'failure', 400);
        }    
        
        $validator = Validator::make($request->all(),[
            "text" => 'required|string',
            "state" => 'required|max:50',
            "sender_users_id" => 'required|integer',
            "resiver_users_id" => 'required|integer',
            "room_id" => 'required|integer',
        ]);
        if($validator->fails()){
            return $this->apiResponse(null,$validator->errors(),'failure', 400);
        }
        $topic = $request->resiver_users_id; 
        $user = User::find($request->sender_users_id);
        $message = messages::create($request->all());
        if($message){
            $this->sendNotification("$topic", $user->name, $request->text, $message);
            return $this->apiResponse($message,"OK",'success', 200);
            }
        return $this->apiResponse(null,'This Messages Not Saved','failure', 404);
    }

    public function show(Request $request)
    {
        $validator = Validator::make($request->all(),[
            "id" => "required",
        ]);
        if($validator->fails()){
            return $this->apiResponse(null, $validator->errors(), 'failure', 400);
        }
        $id = $request->id;
        $message = messages::find($id);
        if($message){
        return $this->apiResponse($message,'ok','success', 200);
        }
    return $this->apiResponse(null,'This Messages Not Found','failure', 404);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(),[
            "id" => 'required',
            "text" => 'required|string',
            "state" => 'required|max:50',
       ]);
        if($validator->fails()){
            return $this->apiResponse(null,$validator->errors(),'failure', 400);
        }
        $message = messages::find($request->id);
        if($message){
            $message->update($request->all());
            return $this->apiResponse($message,'This Messages updated successfully','success', 200);    
        }
        else{
        return $this->apiResponse(null,'This Messages Not found','failure', 404);
        }
    }

    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(),[
            "id" => 'required',
        ]);
        if($validator->fails()){
            return $this->apiResponse(null, $validator->errors(), 'failure', 400);
        }
        $message = messages::find($request->id);
        if($message){
        $message->destroy($request->input('id'));
        return $this->apiResponse(null,'This Messages deleted successfully','success', 200);   
        }
        return $this->apiResponse(null,'This Messages deleted failure','failure', 404);    
    }

    public function send(Request $request){
        $user = User::find($request->sender_users_id);
        $message = $this->store($request);
        $data = $message->getData();
        // return $data->data;
        return $this->sendNotification('user', $user->users_name, $request->text, $data->data);
    }        
}