<?php

namespace App\Http\Controllers;

use App\Http\Requests\Category\StoreCategoryReq;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(){
        return response()->json(
        Category::all()
        );

    }
    public function indexActive(){
        $categories = Category::where('is_active', true)->get();
        return response()->json($categories);
    }
    public function showBySlug(Category $category){
        if (!$category->is_active){
            return response()->json(['message' => 'Kategori tidak ditemukan '], 404);
        }
        return response() -> json($category);
    }

    public function store(StoreCategoryReq $request){
        $category = Category::create($request->validated());
        return response()->json($category, 201);
    }

    public function show(Category $category){
        return response()->json($category);
    }
    public function update(UpdateCategoryRequest $request, Category $category){
        $category -> update($request->validated());
        return response()->json($category, );
    }
    public function destroy(Category $category){
        $category->delete();
        return response()->json(['message' => 'Category Deleted']);
    }

}
