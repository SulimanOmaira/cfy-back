<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\messages;
use Illuminate\Http\Request;
use Kreait\Firebase\Database;

class ChatController extends Controller
{
    protected $firebase;

    public function __construct(Database $firebase)
    {
        $this->firebase = $firebase;
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'sender_id' => 'required|integer',
            'receiver_id' => 'required|integer',
            'message' => 'required|string',
        ]);

        // حفظ الرسالة في MySQL
        $message = messages::create($request->only('sender_id', 'receiver_id', 'message'));

        // إرسال إشعار إلى Firebase
        $this->firebase->getReference('chat/room_' . $request->receiver_id)
            ->push([
                'sender_id' => $request->sender_id,
                'message' => $request->message,
                'timestamp' => now()->timestamp,
            ]);

        return response()->json(['status' => 'Message sent successfully!', 'data' => $message]);
    }

    public function getMessages(Request $request)
    {
        $request->validate([
            'sender_id' => 'required|integer',
            'receiver_id' => 'required|integer',
        ]);

        $messages = messages::where(function ($query) use ($request) {
            $query->where('sender_id', $request->sender_id)
                  ->where('receiver_id', $request->receiver_id);
        })->orWhere(function ($query) use ($request) {
            $query->where('sender_id', $request->receiver_id)
                  ->where('receiver_id', $request->sender_id);
        })->get();

        return response()->json($messages);
    }
}

