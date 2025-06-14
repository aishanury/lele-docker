<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\ChartOfAccount;
use App\Models\Transaction;
use App\Rules\DebitXorCredit;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Yajra\DataTables\DataTables;

class TransactionController extends Controller
{

    public function index()
    {
        $transactions = Transaction::with('chartOfAccount')->where('user_id', Auth::user()->id)->get();
        $coa = ChartOfAccount::select(['code', 'name'])->where('user_id', Auth::user()->id)->get();
        $coaOptions = collect($coa)->pluck('code', 'name')->toArray();

        return view('transaction.index', compact('transactions', 'coaOptions'));
    }

    public function create()
    {
        $coa = ChartOfAccount::select(['code', 'name'])->where('user_id', Auth::user()->id)->get();
        $coaOptions = collect($coa)->pluck('name', 'code')->toArray();

        return view('transaction.create', compact('coaOptions'));
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'coa_code' => ['required', 'integer'],
                'date' => ['required', 'date'],
                'description' => ['required'],
                'debit' => ['integer', 'min_digits:0', new DebitXorCredit],
                'credit' => [ 'integer', 'min_digits:0'],
            ]);


            if ($validator->fails()) {
                return ResponseHelper::SendValidationError($validator->errors());
            }


            Transaction::create([
                'user_id' => Auth::user()->id,
                'coa_code' => $request->coa_code,
                'date' => $request->date,
                'debit' => $request->debit,
                'credit' => $request->credit,
                'description' => $request->description
            ]);

            return ResponseHelper::SendSuccess("create transaction successfully");
        } catch (Exception $error) {
            return ResponseHelper::SendInternalServerError($error);
        }
    }

    public function edit(Transaction $transaction)
    {
        $coa = ChartOfAccount::select(['code', 'name'])->where('user_id', Auth::user()->id)->get();
        $coaOptions = collect($coa)->pluck('name', 'code')->toArray();

        return view('transaction.edit', compact('transaction', 'coaOptions'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        try {

            $validator = Validator::make($request->all(), [
                'coa_code' => ['required', 'integer'],
                'date' => ['required', 'date'],
                'description' => ['required'],
                'debit' => ['integer', 'min_digits:0', new DebitXorCredit],
                'credit' => ['integer', 'min_digits:0']
            ]);

            if ($validator->fails()) {
                return ResponseHelper::SendValidationError($validator->errors());
            }

            $transaction->update([
                'user_id' => Auth::user()->id,
                'coa_code' => $request->coa_code,
                'date' => $request->date,
                'debit' => $request->debit,
                'credit' => $request->credit,
                'description' => $request->description
            ]);

            return ResponseHelper::SendSuccess("update transaction successfully", [
                'coa_code' => $transaction->coa_code,
                'date' => $transaction->date,
                'debit' => $transaction->debit,
                'credit' => $transaction->credit,
                'description' => $transaction->description,
            ]);
        } catch (Exception $error) {
            return ResponseHelper::SendInternalServerError($error);
        }
    }

    public function destroy(int $id)
    {
        try {
            Transaction::destroy($id);

            return ResponseHelper::SendSuccess("delete transaction successfully");
        } catch (Exception $error) {
            return ResponseHelper::SendInternalServerError($error);
        }
    }
    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,csv'
            ]);

            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $destinationPath = public_path('temp');
            $file->move($destinationPath, $filename);
            $filePath = $destinationPath . '/' . $filename;

            $reader = ReaderEntityFactory::createReaderFromFile($filePath);
            $reader->open($filePath);
            $isFirstRow = true;
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    if ($isFirstRow) {
                        $isFirstRow = false;
                        continue;
                    }
                    $cells = $row->toArray();
                    $coa = ChartOfAccount::where('code',$cells[0])->where('user_id',Auth::user()->id)->first();
                    if (!$coa) {
                        echo("coa dengan code "+$cells[0]+" tidak di temukan!");
                        continue;
                    }
                    $trx = new Transaction();
                    $trx->coa_code = $cells[0];
                    $trx->description = $cells[1];
                    $trx->debit = $cells[2];
                    $trx->credit = $cells[3];
                    $trx->date = $cells[4] != "" ? date("Y-m-d",strtotime($cells[4])) : date('Y-m-d');
                    $trx->created_at = date('Y-m-d H:i:s');
                    $trx->updated_at = date('Y-m-d H:i:s');
                    $trx->user_id = Auth::user()->id;
                    $trx->save();
                }
            }

            $reader->close();
            unlink($filePath);
            return ResponseHelper::SendSuccess("Import transaction successfully");
        } catch (Exception $error) {
            return ResponseHelper::SendInternalServerError($error);
        }
    }
    public function data()
    {
        $query = Transaction::with('chartOfAccount');

        return DataTables::of($query)
            ->addColumn('coa_code', fn($row) => $row->chartOfAccount->code)
            ->addColumn('coa_name', fn($row) => $row->chartOfAccount->name)
            ->addColumn('action', function($row) {
                $editUrl = route('transaction.edit', $row->id);
                return '
                    <a href="'.$editUrl.'" class="px-4 py-2 bg-yellow-400 text-sm font-semibold rounded-full">Edit</a>
                    <button onclick="handleDelete('.$row->id.')" class="px-4 py-2 bg-red-400 text-sm font-semibold rounded-full">Delete</button>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }
}
