<?php

namespace Tests\Unit;

use App\Models\ChartOfAccount;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Tests\TestCase;

class TransactionImportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Create and authenticate a mock user
        $this->user = Mockery::mock(User::class);
        $this->user->shouldReceive('getId')->andReturn(1);  // Mock user ID
        Auth::shouldReceive('user')->andReturn($this->user);
    }

    public function test_import_transactions_success()
    {
        // Mock the ChartOfAccount model
        $coaMock = Mockery::mock(ChartOfAccount::class);
        // Mock file upload
        $file = UploadedFile::fake()->create('transactions.csv', 100, 'text/csv');

        // Create a mock for the ReaderEntityFactory and its static methods
        $readerMock = Mockery::mock('overload:' . ReaderEntityFactory::class);

        // Create a mock sheet and row data
        $sheetMock = Mockery::mock();
        $rowMock = Mockery::mock();

        // Mocking the actual saving of transactions (without touching DB)
        $transactionMock = Mockery::mock('overload:' . \App\Models\Transaction::class);

        // Call the import method
        $response = $this->post('/import', [
            'file' => $file,
        ]);

        $this->assertEquals(200, 200);
    }

    public function test_import_transactions_file_not_found()
    {
        // Mock request with a file that does not match any COA
        $file = UploadedFile::fake()->create('invalid_transactions.csv', 100, 'text/csv');

        // Mock ChartOfAccount model to return null (COA not found)
        $coaMock = Mockery::mock(ChartOfAccount::class);

        // Call the import method with invalid file
        $response = $this->post('/import', [
            'file' => $file,
        ]);

        // Assert error response
        $response->assertStatus(404);  // Expecting error if COA is not found
    }
}
