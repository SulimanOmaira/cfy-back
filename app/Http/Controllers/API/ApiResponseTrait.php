<?php
namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Storage;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

use function Laravel\Prompts\error;

trait ApiResponseTrait{
    public function apiResponse($data = null, $message = null, $statusRequest = null, $statusCode = null){
        $array = [
            'status'=>$statusRequest,
            'message'=>$message,
            'data'=>$data,
        ];
        return response()->json($array, $statusCode);
    }

    public function saveImage($files, $folder) {
        $paths = [];
        
        if (is_array($files)) {
            foreach ($files as $file) {
                $randomNumber = rand(100000, 999999);
                $fileName = $randomNumber . '_' . $file->getClientOriginalName();
                $paths[] = $file->storeAs($folder, $fileName, 'public');
            }
        } else {
            $randomNumber = rand(100000, 999999);
            $fileName = $randomNumber . '_' . $files->getClientOriginalName();
            $paths[] = $files->storeAs($folder, $fileName, 'public');
        }
        return $paths; 
    }
    
    // public function updateImage($files, $currentPaths, $folder) {
    //     $paths = [];

    //     if ($currentPaths) {
    //         if (is_array($currentPaths)) {
    //             foreach ($currentPaths as $path) {
    //                 Storage::disk('public')->delete($path);
    //             }
    //         } else {
    //             Storage::disk('public')->delete($currentPaths);
    //         }
    //         if ($files) {
    //             $paths = $this->saveImage($files, $folder);
    //         }
    //     }   
    //     return $paths;
    // }

    // public function updateImage($files, $currentPaths, $folder) {
    //     $paths = is_array($currentPaths) ? $currentPaths : ($currentPaths ? [$currentPaths] : []);
    
    //     if ($files) {
    //         // حفظ الصور الجديدة
    //         $newPaths = $this->saveImage($files, $folder);
    
    //         // دمج المسارات القديمة مع الجديدة دون حذف القديمة إذا لم يتم استبدالها
    //         // $paths = array_merge($paths, $newPaths);
    //     }
    
    //     return $paths;
    // }


    // public function updateImage($files, $currentPaths, $folder) {
    //     // فك تشفير المسارات الحالية إذا كانت مخزنة بصيغة JSON
    //     $currentPaths = is_string($currentPaths) ? json_decode($currentPaths, true) : $currentPaths;
    //     $currentPaths = is_array($currentPaths) ? $currentPaths : [];
    
    //     // حذف الصور القديمة إذا تم رفع صور جديدة
    //     if ($files) {
    //         foreach ($currentPaths as $path) {
    //             Storage::disk('public')->delete($path);
    //         }
            
    //         // حفظ الصور الجديدة
    //         $newPaths = $this->saveImage($files, $folder);
    
    //         // تحديث المسارات وإرجاعها بشكل JSON
    //         return json_encode($newPaths);
    //     }
    
    //     // إرجاع الصور القديمة في حال عدم تعديلها
    //     return json_encode($currentPaths);
    // }



    // public function updateImage($files, $currentPaths, $folder) {
    //     // فك تشفير المسارات الحالية إذا كانت بصيغة JSON
    //     $currentPaths = is_string($currentPaths) ? json_decode($currentPaths, true) : $currentPaths;
    //     $currentPaths = is_array($currentPaths) ? $currentPaths : [];
    
    //     // إذا لم يتم رفع صور جديدة، إرجاع الصور القديمة كما هي
    //     if (!$files) {
    //         return json_encode($currentPaths);
    //     }
    
    //     // حفظ الصور الجديدة
    //     $newPaths = $this->saveImage($files, $folder);
    
    //     // مقارنة المسارات الجديدة مع القديمة
    //     if ($newPaths == $currentPaths) {

    //         return json_encode($currentPaths); // الصور لم تتغير
    //     }
    
    //     // حذف الصور القديمة فقط إذا كانت مختلفة
    //     foreach ($currentPaths as $path) {
    //         if (!in_array($path, $newPaths)) {
    //             Storage::disk('public')->delete($path);
    //         }
    //     }
    
    //     return json_encode($newPaths);
    // }
    




    public function updateImage($files, $currentPaths, $folder) {
        $currentPaths = is_string($currentPaths) ? json_decode($currentPaths, true) : $currentPaths;
        $currentPaths = is_array($currentPaths) ? $currentPaths : [];
    
        if (!$files) {
            return json_encode($currentPaths);
        }
    
        // حساب Hash الصور القديمة
        $oldHashes = [];
        foreach ($currentPaths as $path) {
            $fullPath = storage_path('app/public/' . $path);
            if (file_exists($fullPath)) {
                $oldHashes[$path] = hash_file('md5', $fullPath);
            }
        }
    
        // حفظ الصور الجديدة
        $newPaths = $this->saveImage($files, $folder);
        $newHashes = [];
        foreach ($newPaths as $path) {
            $fullPath = storage_path('app/public/' . $path);
            if (file_exists($fullPath)) {
                $newHashes[$path] = hash_file('md5', $fullPath);
            }
        }
    
        // مقارنة Hash الصور الجديدة مع القديمة
        if ($newHashes == $oldHashes) {
            return json_encode($currentPaths); // لم يحدث تغيير
        }
    
        // حذف الصور القديمة فقط إذا لم تكن ضمن الصور الجديدة
        foreach ($currentPaths as $path) {
            if (!in_array(hash_file('md5', storage_path('app/public/' . $path)), $newHashes)) {
                Storage::disk('public')->delete($path);
            }
        }
    
        return json_encode($newPaths);
    }
    



    
    public function deleteImage($paths) {
        if ($paths) {
            if (is_array($paths)) {
                foreach ($paths as $path) {
                    Storage::disk('public')->delete($path);
                }
            } else {
                Storage::disk('public')->delete($paths);
            }
        }
    }

    public function getFolderByType($type) {
        switch ($type) {
            case 'user':
                return 'users_images';
            case 'post/in':
                return 'images_in';
            case 'post/out':
                return 'images_out';
            case 'car':
                return 'cars';
            default:
                return 'general_images';
        }
    }

    public function sendNotification($topic, $title, $body, $messageSend = null){
        try {
            $firebase = (new Factory)->withServiceAccount(storage_path('app/firebase/firebase.json'));
            $messaging = $firebase->createMessaging();
            $message = CloudMessage::withTarget('topic', $topic)->withNotification([
                'title' => $title,
                'body' => $body,
            ])->withData([
                'id' => $messageSend->id,
                'text' => $messageSend->text,
                'state' => $messageSend->state,
                'sender_users_id' => $messageSend->sender_users_id,
                'resiver_users_id' => $messageSend->resiver_users_id,
                'room_id' => $messageSend->room_id,
                'created_at' => $messageSend->created_at,
                'updated_at' => $messageSend->updated_at,
            //     "messages" => [
            //         [
            //             "id" => 9,
            //             "text" => "Hellossssssssssssssssssssssss",
            //             "state" => "seen",
            //             "sender_users_id" => 3,
            //             "resiver_users_id" => 1,
            //             "room_id" => 2,
            //             "created_at" => "2025-01-31 19:56:20",
            //             "updated_at" => "2025-01-31 19:56:20"
            //         ]
            //     ],
            //     "users" => [
            //         [
            //             "id" => 1,
            //             "name" => "test",
            //             "email" => "test@gmail.com",
            //             "password" => "$2y$12$8Qb6wbR2OneMnnfnq7ZY1.I5iICG/wtRtR6ppI53m9oJYG4VWRbjC",
            //             "city" => "damascus",
            //             "image" => "users_images/122046_1f62ebfa56e54222e066d7ac252dfa48.jpg",
            //             "created_at" => "2025-01-30 19:54:08",
            //             "updated_at" => "2025-01-30 19:54:08",
            //             "pivot" => [
            //                 "rooms_id" => 2,
            //                 "users_id" => 1
            //             ]
            //         ]
            //     ]
            ]);
            $messaging->send($message);
            $data = [
                'message' => 'OK!', 
                'data' => $message,
            ];
            return $this->apiResponse($data, 'Firebase is working correctly!', 'success', 200);
        } catch (\Exception $e) {
            return $this->apiResponse(null, $e->getMessage(), 'failure', 400);
        }
    }

    public function imageDecode($data){
        $data->map(function ($item) {
            $images = json_decode($item->image_in, true);
            $imagesOut = json_decode($item->image_out, true);

                if (is_array($images)) {
                $item->image_in = array_map(function ($image) {
                    return Storage::url($image);
                }, $images);
            }

            if (is_array($imagesOut)) {
                $item->image_out = array_map(function ($image) {
                    return Storage::url($image);
                }, $imagesOut);
            }
            return $item;    
            }

        );
        return $data;
    }
}