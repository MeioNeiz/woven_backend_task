<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\CsvImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CsvImportServiceTest extends TestCase {
    use RefreshDatabase;

    private CsvImportService $service;

    protected function setUp(): void {
        parent::setUp();
        $this->service = new CsvImportService();
    }

    public function test_import_creates_investors_from_csv() {
        $csvContent = "investor_id,name,age,investment_amount,investment_date\n";
        $csvContent .= "1,John Doe,30,5000,2024-01-15\n";
        $csvContent .= "2,Jane Smith,28,7500,2024-02-20\n";

        $path = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($path, $csvContent);

        $result = $this->service->import($path);

        unlink($path);

        $this->assertEquals(2, $result['imported']);
        $this->assertDatabaseCount('investors', 2);
        $this->assertDatabaseCount('investments', 2);
    }

    public function test_import_handles_invalid_data() {
        $csvContent = "investor_id,name,age,investment_amount,investment_date\n";
        $csvContent .= "1,John Doe,invalid_age,5000,2024-01-15\n";
        $csvContent .= "2,Jane Smith,28,7500,2024-03-20\n";

        $path = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($path, $csvContent);

        $result = $this->service->import($path);

        unlink($path);

        $this->assertEquals(1, $result['imported']);
        $this->assertGreaterThan(0, $result['total_errors']);
    }

    public function test_duplicate_investor_ids_create_multiple_investments() {
        $csvContent = "investor_id,name,age,investment_amount,investment_date\n";
        $csvContent .= "1,John Doe,30,5000,2024-01-15\n";
        $csvContent .= "1,John Doe,30,7500,2024-02-20\n";

        $path = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($path, $csvContent);

        $result = $this->service->import($path);

        unlink($path);

        $this->assertEquals(2, $result['imported']);
        $this->assertDatabaseCount('investors', 1);
        $this->assertDatabaseCount('investments', 2);
    }
}
