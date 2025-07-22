<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoomController extends Controller{
    
    use ApiResponseTrait;
    public function index(Request $request){
        $validated = Validator::make($request->all(),[
            'id' => "required",
        ]);
        if($validated->fails()){
            return $this->apiResponse(null, $validated->errors(),'failure', 400);
        }        
        $user = User::findOrFail($request->input('id'));
        $rooms = $user->rooms()
        ->with(['messages','users' => function ($query) use ($user) {
            $query->where('users.id', '!=', $user->id);
        }])
        ->orderByDesc(function ($query) {
        $query->select('created_at')
              ->from('messages')
              ->whereColumn('messages.room_id', 'rooms.id')
              ->latest()
              ->limit(1);
    })->get();
        return $this->apiResponse($rooms, 'Ok', 'success', 200);
    }

    public function create(Request $request){
        $validated = Validator::make($request->all(),[
            "sender_users_id" => 'required|exists:users,id',
            "resiver_users_id" => 'required|exists:users,id',
        ]);
        if($validated->fails()){
            return $this->apiResponse(null, $validated->errors(), 'failure', 400);
        }
        $user1 = $request->input('sender_users_id');
        $user2 = $request->input('resiver_users_id');
        $ids = [$user1, $user2];
        sort($ids);
        $roomName = "room_{$ids[0]},{$ids[1]}";
         $room = Room::firstOrCreate(
            ['name' => $roomName]
         );
         $room->users()->sync($ids);
         return $this->apiResponse($room, '', 'success', 201);
    }
    /**
     * Display the specified resource.
     */
    public function show(room $room)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(room $room)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, room $room)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(room $room)
    {
        //
    }
}
