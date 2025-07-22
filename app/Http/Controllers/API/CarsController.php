<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\cars;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CarsController extends Controller
{
    use ApiResponseTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cars = Cars::get()->map(function ($car) {
            $car->image = Storage::url($car->image);
            return $car;
        });
        return $this->apiResponse($cars, 'ok', 'success', 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            $validator = Validator::make($request->all(),[
                'type' => 'required|max:100',
                "image" => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
    
            if($validator->fails()){
                return $this->apiResponse(null,$validator->errors(),'failure');
            }
            $imageOutFiles = $request->file('image');
            if (!$imageOutFiles) {
                return $this->apiResponse(null, 'Images are required', 'failure', 400);
            }
            
            $folder = $this->getFolderByType('car');
            $imagePath = $this->saveImage($request->file('image'), $folder);
            
            $car = Cars::create(array_merge(
            $validator->validated(), 
                ['image' => $imagePath[0]]
            ));
            if($car){
                return $this->apiResponse($car,'ok','success', 200);
                }
            return $this->apiResponse(null,'This Car Not Saved','failure', 404);
        } catch(\Exception $e){
            return $this->apiResponse(null, $e->getMessage(), 'failure', 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $id = $request->id;
        $car = Cars::find($id);
        if($car){
        return $this->apiResponse($car,'ok','success', 200);
        }
    return $this->apiResponse(null,'This Car Not Found','failure', 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'type' => 'required|max:100',
            'id' => 'required',
        ]);
        if($validator->fails()){
            return $this->apiResponse(null,$validator->errors(),'failure', 400);
        }
        $car = Cars::find($request->input('id'));
        if($car){
            $car->update($request->all());
            return $this->apiResponse($car,'This Car updated successfully','success', 200);    
        }
        else{
        return $this->apiResponse(null,'This Car Not found','failure', 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $car = Cars::find($request->input('id'));
    if(!$car){
    return $this->apiResponse(null,'This Car deleted failure','failure', 404);    
    }
    // $car->destroy($request->input('id'));
    $car->delete();
    return $this->apiResponse(null,'This Car deleted successfully','success', 200);   
    }
}
