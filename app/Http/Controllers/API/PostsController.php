<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Api\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Models\posts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostsController extends Controller
{
    use ApiResponseTrait;

    public function index(){
        $posts = posts::get();
        foreach ($posts as $post) {
            if ($post->image) {
                $post->image = array_map(function($path) {
                    return Storage::url($path);
                }, json_decode($post->image, true));
            }
            if ($post->image_out) {
                $post->image_out = array_map(function($path) {
                    return Storage::url($path);
                }, json_decode($post->image_out, true));
            }
            if ($post->image_in) {
                $post->image_in = array_map(function($path) {
                    return Storage::url($path);
                }, json_decode($post->image_in, true));
            }
        }
        return $this->apiResponse($posts, 'ok', 'success', 201);
    }

    // Add Post
    public function store(Request $request)
    {
        try{
            $validator = Validator::make($request->all(),[
                "image_out.*" => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                "image_out" => 'required|array', 
                "image_in.*" => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                "image_in" => 'required|array',
                "title" => 'required|max:100',
                "users_id" => 'required',
            ]);
            if($validator->fails()){
                return $this->apiResponse(null,$validator->errors(),'failure', 400);
            }
            $imageInFiles = $request->file('image_in');
            $imageOutFiles = $request->file('image_out');
            if (!$imageInFiles || !$imageOutFiles) {
                return $this->apiResponse(null, 'Images are required', 'failure', 400);
            }

            $folderin = $this->getFolderByType('post/in');
            $folderout = $this->getFolderByType('post/out');
            $imagePathin = $this->saveImage($request->file('image_in'), $folderin);
            $imagePathout = $this->saveImage($request->file('image_out'), $folderout);
    
            $post = posts::create(array_merge(
                $validator->validated(), 
                ['image_in' => json_encode($imagePathin)] ,
                ['image_out' => json_encode($imagePathout)]
            ));
            if ($post->image_out) {
                $post->image_out = array_map(function($path) {
                    return Storage::url($path);
                }, json_decode($post->image_out, true));
            }
            if ($post->image_in) {
                $post->image_in = array_map(function($path) {
                    return Storage::url($path);
                }, json_decode($post->image_in, true));
            }            
            if($post){
                return $this->apiResponse($post,'ok','success', 200);
                }
            return $this->apiResponse(null,'This posts Not Saved','failure', 404);
        } catch(\Exception $e){
            return $this->apiResponse(null, $e->getMessage(), 'failure', 400);
        }
    }

    // View Post
    public function show(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                "id" => 'required|integer|exists:posts,id', 
            ]);
            if($validator->fails()){
                return $this->apiResponse(null,$validator->errors(),'failure', 400);
            }
            $post = posts::find($request->input('id'))->get();
            if($post){
                $data = $this->imageDecode($post);
                return $this->apiResponse($data, 'ok', 'success', 200);
            }
            return $this->apiResponse(null, 'This post Not Found', 'failure', 404);
        }catch(\Exception $e){
            return $this->apiResponse(null, $e->getMessage(), 'faliure', 500);
        }
    }

    // Update Post
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(),[
            "image_in*" => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            "image_out*" => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            "title" => 'required|max:100',
            "id" => 'required|integer|exists:posts,id', 
            ]);
        if($validator->fails()){
            return $this->apiResponse(null,$validator->errors(),'failure', 400);
        }
        $post = posts::find($request->id);
        if($post){
           $folder = $this->getFolderByType('post');
           $imagePath = $this->updateImage($request->file('image'), $post->image, $folder);
           $post->update([
               'image' => $imagePath,
               'title' => $request->title,
           ]);
           $post->image = Storage::url($post->image);
            return $this->apiResponse($post,'This posts updated successfully','success', 200);    
        }
        else{
        return $this->apiResponse(null,'This posts Not found','failure', 404);
        }
    }
    // Delete Post
    public function destroy(Request $request){
        $validator = Validator::make($request->all(),[
            "id" => 'required', 
        ]);
        if($validator->fails()){
            return $this->apiResponse(null,$validator->errors(),'failure', 400);
        }
        
        $post = posts::find($request->id);
        if (!$post) {
            return $this->apiResponse(null, 'This post was not found', 'failure', 404);
        }
        if ($post->users_id !== auth()->id()) {
            return $this->apiResponse(null, 'You are not authorized to delete this post', 'failure', 403);
        }

        $this->deleteImage($post->image);
        $post->delete();
        return $this->apiResponse(null,'This posts deleted successfully','success', 200);   
        
        // return $this->apiResponse(null,'This posts deleted failure','failure', 404);    
    }
}