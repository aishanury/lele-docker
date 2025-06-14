<?php

namespace App\Http\Controllers;

use App\Enums\CategoryType;
use App\Helpers\ResponseHelper;
use App\Models\Category;
use App\Models\ChartOfAccount;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ChartOfAccountController extends Controller
{
    
    public function index() {
        $totals = \App\Models\Transaction::selectRaw('SUM(credit) as total_credit, SUM(debit) as total_debit')
                ->where('user_id', Auth::user()->id)
                ->first();
        $chartOfAccounts = ChartOfAccount::with('category')->where('user_id', Auth::user()->id)->get();

    return view('coa.index', compact('chartOfAccounts', 'totals'));
    }

    public function create() {
        $categories = Category::select('name')->where('user_id', Auth::user()->id)->get();
        $categoryOptions = collect($categories)->pluck('name', 'name')->toArray(); 

        return view('coa.create', compact('categoryOptions'));
    }

    public function store(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'code' => ['required', 'unique:chart_of_accounts', 'integer'],
                'name' => ['required'],
                'category_name' => ['required']
            ]);

            if ($validator->fails()) {
                return ResponseHelper::SendValidationError($validator->errors());
            }

            ChartOfAccount::create([
                'user_id' => Auth::user()->id,
                'code' => $request->code,
                'name' => $request->name,
                'category_name' => $request->category_name
            ]);

            return ResponseHelper::SendSuccess("create coa successfully");

        } catch(Exception $error) {
            return ResponseHelper::SendInternalServerError($error);
        }
    }

    public function edit(ChartOfAccount $chartOfAccount) {
        $categories = Category::where('user_id', Auth::user()->id)->get();
        $categoryOptions = collect($categories)->pluck('name', 'name')->toArray(); 

        return view('coa.edit', compact('categoryOptions', 'chartOfAccount'));
    }

    public function update(Request $request, ChartOfAccount $chartOfAccount) {
        try {

            $validator = Validator::make($request->all(), [
                'code' => ['required', Rule::unique('chart_of_accounts', 'code')->ignore($chartOfAccount->code, 'code'), 'integer'],
                'name' => ['required'],
                'category_name' => ['required']
            ]);

            if ($validator->fails()) {
                return ResponseHelper::SendValidationError($validator->errors());
            }

            $chartOfAccount->update([
                'code' => $request->code,
                'name' => $request->name,
                'category_name' => $request->category_name
            ]);

            return ResponseHelper::SendSuccess("update coa successfully", [
                'code' => $chartOfAccount->code,
                'name' => $chartOfAccount->name,
                'category_name' => $chartOfAccount->category_name
            ]);

        } catch(Exception $error) {
            return ResponseHelper::SendInternalServerError($error);
        }
    }

    public function destroy(int $code) {
        try {
            ChartOfAccount::destroy($code);

            return ResponseHelper::SendSuccess("delete coa successfully");
        } catch (QueryException $error) {
            if ($error->getCode() == "23000") {
                return ResponseHelper::SendErrorMessage("Cannot delete this category because it is linked to other records. Remove the related records first.", 409);
            }
        } catch(Exception $error) {
            return ResponseHelper::SendInternalServerError($error);
        }
    }

}
