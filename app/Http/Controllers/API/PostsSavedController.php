<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Api\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Models\posts;
use App\Models\posts_saved;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostsSavedController extends Controller
{
    use ApiResponseTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $userId = auth()->id(); 
            $data = posts::with([
                'descriptions',
                'descriptions.cars',
                'user',
            ])
            ->whereHas('postsSaved')    
            ->withCount([
                'postsSaved as is_saved' => function ($query) use ($userId) {
                    $query->where('users_id', $userId);
                }
            ])
            ->having('is_saved', '>', 0)
            ->orderBy('created_at', 'desc')
            ->get();
            $data = $this->imageDecode($data);
            return $this->apiResponse($data, 'View created or replaced successfully.', 'success', 200);
        }catch (\Exception $e){
            return $this->apiResponse(null, $e->getMessage(), 'faliure', 500);
        }
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            "posts_id" => 'required',
            "users_id" => 'required',
        ]);

        if($validator->fails()){
            return $this->apiResponse(null,$validator->errors(),'failure', 400);
        }

        $message = posts_saved::create($request->all());
        if($message){
            return $this->apiResponse($message,'ok','success', 200);
            }
        return $this->apiResponse(null,'This saved Not Saved','failure', 404);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $validator = Validator::make($request->all(),[
            "id" => 'required',
        ]);
        if($validator->fails()){
            return $this->apiResponse(null,$validator->errors() ,'failure', 400);
        }
        $id = $request->input('id');
        $saved = posts_saved::find($id);
        if($saved){
        return $this->apiResponse($saved,'ok','success', 200);
        }
    return $this->apiResponse(null,'This saved Not Found','failure', 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(),[
            "id" => 'required',
            "posts_id" => 'required',
            "users_id" => 'required',
            ]);
        if($validator->fails()){
            return $this->apiResponse(null,$validator->errors(),'failure', 400);
        }
        $message = posts_saved::find($request->id);
        if($message){
            $message->update($request->all());
            return $this->apiResponse(null,'This saved updated successfully','success', 200);    
        }
        else{
        return $this->apiResponse(null,'This saved Not found','failure', 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request){
        $userId = auth()->id(); 
        $validator = Validator::make($request->all(),[
            'id' => 'required',
        ]);

        if($validator->fails()){
            return $this->apiResponse(null,$validator->errors() ,'failure', 400);
        }


        $savedPost = posts_saved::where('posts_id', $request->id)
        ->where('users_id', $userId)
        ->first();

        if (!$savedPost) {
        return $this->apiResponse(null, 'Post not found or unauthorized', 'failure', 403);
        }

        $savedPost->delete();
        return $this->apiResponse(null, 'This saved post deleted successfully', 'success', 200);

        // $message = posts_saved::find($request->id);
        // if($message){
        // $message->destroy($request->input('id'));
        // return $this->apiResponse(null,'This saved deleted successfully','success', 200);   
        // }
        // return $this->apiResponse(null,'This saved deleted failure','failure', 404);    
    }
}
