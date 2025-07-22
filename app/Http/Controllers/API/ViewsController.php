<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\cars;
use App\Models\descriptions;
use App\Models\posts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ViewsController extends Controller
{
    use ApiResponseTrait;
// Get all Posts With Descripyions
    public function postsWithDescr(){
        try{
            $userId = auth()->id(); 
            $data = posts::with([
                'descriptions',
                'descriptions.cars', 
                'user', 
            ])
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
// Get One Post With Description
    public function onePostsWithDescr(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'id' => 'required',
            ]);
            if($validator->fails()){
                return $this->apiResponse(null,$validator->errors(),'failure');
            }
            $data = posts::with(['descriptions', 'descriptions.cars', 'user'])
            ->where('id', $request->input('id'))
            ->get(); 
            if($data){
                $data = $this->imageDecode($data);
                return $this->apiResponse($data, 'View created or replaced successfully.', 'success', 200);
            }
            return $this->apiResponse(null, 'Not Found', 'faliure', 404);
        }catch (\Exception $e){
            return $this->apiResponse(null, $e->getMessage(), 'faliure', 500);
        }
    }
// Filter Post By Car Name
    public function getCarsByNames(Request $request){
        $carNames = $request->input('type');

        if (!is_array($carNames) || empty($carNames)) {
            return response()->json(['message' => 'Please provide valid car names.'], 400);
        }

        $cars = cars::whereIn('type', $carNames)->get();

        return response()->json($cars, 200);
    }
// Filter Posts By Name
    public function getPostsByNames(Request $request){
        try{
            $userId = auth()->id(); 
            $validator = Validator::make($request->all(),[
                'type' => 'required',
            ]);
            if($validator->fails()){
                return $this->apiResponse(null,$validator->errors(),'failure', 400);
            }
            $types = (array) $request->input('type');

            $data = Posts::whereHas((
                'descriptions.cars'
            )
            , function ($query) use ($types) {
            $query->whereIn('type',  $types);
        })
        ->with(['descriptions', 'descriptions.cars', 'user'])
        ->withCount([
            'postsSaved as is_saved' => function ($query) use ($userId) {
                $query->where('users_id', $userId);
            }
        ])
        ->orderBy('created_at', 'desc')
        ->get();
            if($data->isEmpty()){
                return $this->apiResponse(null, 'ssss', 'failure', 404);
            }
            $data = $this->imageDecode($data);
            return $this->apiResponse($data, '', 'success', 200);

        }
        catch(\Exception $e){
            return $this->apiResponse(null, $e->getMessage(), 'faliure', 500);
        }
    }
// Add Post With Descriptions
    public function addPostWithDescriptions(Request $request){
        try{
            $postValidator = Validator::make($request->all(),[
                // Post
                "image_out.*" => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                "image_out" => 'required|array', 
                "image_in.*" => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                "image_in" => 'required|array',
                "title" => 'required|max:100',
            ]);
            $descriptionValidator = Validator::make($request->all(),[
                // Descriptions
                "name" => 'required|max:100',
                "model" => 'required|integer',
                "eng_capacity" => 'required',
                "dis_travel" => 'required|integer',
                "price" => 'required|numeric',
                "type_car" => 'required|string',
                "fuel_consumption" => 'required',
                "drive_system" => 'required',
                "number_Seats" => 'required',
                "cruise_control_system" => 'required',
                "cars_id" => 'required',
            ]);

            if($postValidator->fails() || $descriptionValidator->fails()){
                $errors = array_merge($postValidator->errors()->toArray(),
                 $descriptionValidator->errors()->toArray());
                return $this->apiResponse(null,$errors,'failure', 400);
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
            $id = auth()->id();

            $post = posts::create(array_merge(
                $postValidator->validated(), 
                ['image_in' => json_encode($imagePathin)] ,
                ['image_out' => json_encode($imagePathout)],
                ['users_id' => $id],
            ));
            $desc = descriptions::create(array_merge($request->all(), ['posts_id' => $post->id]));

            if($desc && $post){
                return $this->apiResponse(null,'ok','success', 200);
                }
            return $this->apiResponse(null,'This Description Not Saved','failure', 404);
        }catch (\Exception $e){
            return $this->apiResponse(null, $e->getMessage(), 'faliure', 500);
        }
    }

// Update Post with Decriptions
    public function updatePostWithDescriptions(Request $request) {
        try {
            $validator = Validator::make($request->all(),[
                'id' => 'required',
            ]);
            if($validator->fails()){
                return $this->apiResponse(null,$validator->errors(),'failure', 400);
            }
            $post = posts::find($request->input('id'));
            if (!$post) {
                return $this->apiResponse(null, 'Post not found', 'failure', 404);
            }
            
            $desc = descriptions::where('posts_id', $post->id)->first();
            if (!$desc) {
                return $this->apiResponse(null, 'Description not found', 'failure', 404);
            }

            $postValidator = Validator::make($request->all(), [
                "image_out.*" => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                "image_out" => 'nullable|array',
                "image_in.*" => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                "image_in" => 'nullable|array',
                "title" => 'required|max:100',
            ]);
            
            $descriptionValidator = Validator::make($request->all(), [
                "name" => 'required|max:100',
                "model" => 'required|integer',
                "eng_capacity" => 'required',
                "dis_travel" => 'required|integer',
                "price" => 'required|numeric',
                "type_car" => 'required|string',
                "fuel_consumption" => 'required',
                "drive_system" => 'required',
                "number_Seats" => 'required',
                "cruise_control_system" => 'required',
                "cars_id" => 'required',
            ]);

            if ($postValidator->fails() || $descriptionValidator->fails()) {
                $errors = array_merge($postValidator->errors()->toArray(), $descriptionValidator->errors()->toArray());
                return $this->apiResponse(null, $errors, 'failure', 400);
            }
            
            if ($request->hasFile('image_in')) {
                $folder = $this->getFolderByType('post/in');
                $imagePathIN = $this->updateImage($request->file('image_in'), $post->image_in, $folder);
                // $post->image_in = json_encode($imagePathin);
                $post->update([
                    'image_in' => $imagePathIN,
                ]);
            }
            
            if ($request->hasFile('image_out')) {
                $folderout = $this->getFolderByType('post/out');
                $imagePathout = $this->updateImage($request->file('image_out'), $post->image_out, $folderout);
                // $post->image_out = json_encode($imagePathout);
                $post->update([
                    'image_out' => $imagePathout,
                ]);
            }
            
            // $post->title = $request->title;
            // $post->save();
            
            $desc->update($request->all());
            $post->image = Storage::url($post->image);
            return $this->apiResponse(null, 'Updated successfully', 'success', 200);
        } catch (\Exception $e) {
            return $this->apiResponse(null, $e->getMessage(), 'failure', 500);
        }
    }

}
