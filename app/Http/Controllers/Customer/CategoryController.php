<?php

namespace App\Http\Controllers\Customer;
use App\Http\Resources\Customer\CategoryResource;
use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
   public function allCategory()
{
    return CategoryResource::collection(
        ServiceCategory::all()
    );
}

public function showCategory(ServiceCategory $serviceCategory)
{
    return new CategoryResource($serviceCategory);
}

public function searchCategory(Request $request)
{
    $keyword = trim($request->keyword);

    $categories = ServiceCategory::where(function ($query) use ($keyword) {

        if (is_numeric($keyword)) {
            $query->orWhere('id', $keyword);
        }

        $query->orWhere('name', 'LIKE', '%' . $keyword . '%');

    })->get();

    return CategoryResource::collection($categories);
}





}
