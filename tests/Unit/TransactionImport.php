<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\ChartOfAccount;
use App\Models\Transaction;
use App\Http\Controllers\TransactionController;
use Mockery;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;

test('import transactions successfully', function () {
    // Fake storage
    Storage::fake('public');

    // Fake user auth
    $user = \App\Models\User::factory()->create();
    Auth::shouldReceive('user')->andReturn($user);

    // Create fake file
    $file = UploadedFile::fake()->create('test.xlsx');

    // Mock request
    $request = Mockery::mock(\Illuminate\Http\Request::class);
    $request->shouldReceive('validate')->once()->andReturn([
        'file' => $file
    ]);
    $request->shouldReceive('file')->once()->andReturn($file);

    // Mock reader
    $reader = Mockery::mock();
    $reader->shouldReceive('open')->once();
    $sheet = Mockery::mock();
    $row1 = Mockery::mock();
    $row1->shouldReceive('toArray')->andReturn(['code-001', 'desc', 1000, 0, '2024-01-01']);

    $sheet->shouldReceive('getRowIterator')->andReturn(new ArrayIterator([$row1]));

    $reader->shouldReceive('getSheetIterator')->andReturn(new ArrayIterator([$sheet]));
    $reader->shouldReceive('close')->once();

    // Override ReaderEntityFactory
    ReaderEntityFactory::shouldReceive('createReaderFromFile')->andReturn($reader);

    // Create dummy COA
    ChartOfAccount::factory()->create([
        'code' => 'code-001',
        'user_id' => $user->id,
    ]);

    // Execute controller
    $controller = new TransactionController();
    $response = $controller->import($request);

    // Assert success
    expect($response->getData())->toMatchArray([
        'status' => true,
    ]);

    expect(Transaction::where('coa_code', 'code-001')->exists())->toBeTrue();
});

test('import transactions fails and catches exception', function () {
    // Fake user
    $user = \App\Models\User::factory()->create();
    Auth::shouldReceive('user')->andReturn($user);

    // Mock request, tapi throw exception di validate
    $request = Mockery::mock(\Illuminate\Http\Request::class);
    $request->shouldReceive('validate')->andThrow(new Exception('Something went wrong'));

    // Execute controller
    $controller = new TransactionController();
    $response = $controller->import($request);

    // Assert failure
    expect($response->getData())->toMatchArray([
        'status' => false,
    ]);

    expect($response->getData())->toHaveKey('message');
});
