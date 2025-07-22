<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\ApiResponseTrait;
use App\Http\Controllers\Controller;
use App\Models\descriptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DescriptionsController extends Controller
{
    use ApiResponseTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $desc = descriptions::get();
        return $this->apiResponse($desc, 'ok', 'success', 201);
        return response()->json();     
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
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
            "posts_id" => 'required',
        ]);

        if($validator->fails()){
            return $this->apiResponse(null,$validator->errors(),'failure', 400);
        }

        $desc = descriptions::create($request->all());
        if($desc){
            return $this->apiResponse($desc,'ok','success', 200);
            }
        return $this->apiResponse(null,'This Description Not Saved','failure', 404);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id' => 'required',
        ]);
        if($validator->fails()){
            return $this->apiResponse(null, $validator->errors(), 'failure', 400);
        }
        $id = $request->id;
        $desc = descriptions::find($id);
        if($desc){
        return $this->apiResponse($desc,'ok','success', 200);
        }
    return $this->apiResponse(null,'This Description Not Found','failure', 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(),[
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
        ]);
        if($validator->fails()){
            return $this->apiResponse(null,$validator->errors(),'failure', 400);
        }
        $desc = descriptions::find($request->id);
        if($desc){
            $desc->update($request->all());
            return $this->apiResponse(null,'This Description updated successfully','success', 200);    
        }
        else{
        return $this->apiResponse(null,'This Description Not found','failure', 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(),[
            "id" => 'required',
        ]);
        if($validator->fails()){
            return $this->apiResponse(null, $validator->errors(), 'failure', 400);
        }
        $desc = descriptions::find($request->id);
    if($desc){
    $desc->destroy($request->input('id'));
    return $this->apiResponse(null,'This Descriptions deleted successfully','success', 200);   
    }
    return $this->apiResponse(null,'This Descriptions deleted failure','failure', 404);    
    }
}
