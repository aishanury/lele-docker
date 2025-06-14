<?php

namespace App\Http\Controllers;

use App\Enums\CategoryType;
use App\Helpers\ResponseHelper;
use App\Models\Category;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{

    public function index()
    {
        $categories = Category::where('user_id', Auth::user()->id)->get();

        return view('category.index', compact('categories'));
    }

    public function create()
    {
        return view('category.create');
    }
    public function store(Request $request)
    {

        try {
            $validator = Validator::make($request->all(),[
                'name' => ['required', 'unique:categories'],
                'type' => ['required', Rule::enum(CategoryType::class)]
            ]);

            if ($validator->fails()) {
                return ResponseHelper::SendValidationError($validator->errors());
            }
    
            Category::create([
                'name' => $request->name,
                'type' => $request->type,
                'user_id' => Auth::user()->id
            ]);
            
            return ResponseHelper::SendSuccess("create category successfully");
        } catch(Exception $error) {
            return ResponseHelper::SendInternalServerError($error);
        }
    }

    public function edit(Category $category)
    {
        return view('category.edit', compact('category'));
    }
    public function update(Request $request, Category $category)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => ['required', Rule::unique('categories', 'name')->ignore($category->name, 'name')],
                'type' => ['required', Rule::enum(CategoryType::class)]
            ]);

            if ($validator->fails()) {
                return ResponseHelper::SendValidationError($validator->failed());
            }
    
            $category->update([
                'name' => $request->name,
                'type' => $request->type
            ]);
            
            return ResponseHelper::SendSuccess("update category successfully", [
                "name" => $category->name,
                "type" => $category->type
            ]);

        } catch(Exception $error) {
            return ResponseHelper::SendInternalServerError($error);
        }

    }
    public function destroy(string $name)
    {
        try {
            Category::destroy($name);

            return ResponseHelper::SendSuccess("delete category successfully");
        } catch (QueryException $error) {
            if ($error->getCode() == "23000") {
                return ResponseHelper::SendErrorMessage("Cannot delete this category because it is linked to other records. Remove the related records first.", 409);
            }
        } catch(Exception $error) {
            return ResponseHelper::SendInternalServerError($error);
        }
    }
}
