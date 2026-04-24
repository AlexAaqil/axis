<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\YearRequest;
use App\Http\Resources\Api\V1\YearResource;
use App\Models\Year;

class YearController extends Controller
{
    public function index()
    {
        $years = Year::orderBy('year', 'desc')->get();
        
        return YearResource::collection($years);
    }

    public function store(YearRequest $request)
    {
        $year = Year::create($request->validated());
        
        return (new YearResource($year))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Year $year)
    {
        return new YearResource($year);
    }

    public function update(YearRequest $request, Year $year)
    {
        $year->update($request->validated());
        
        return new YearResource($year);
    }

    public function destroy(Year $year)
    {
        $year->delete();
        
        return response()->json(null, 204);
    }
}