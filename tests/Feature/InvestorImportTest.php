<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

class InvestorImportTest extends TestCase {
    use RefreshDatabase;

    public function test_import_csv(): void {
        $file = UploadedFile::fake()->createWithContent(
            'investors.csv',
            "investor_id,name,age,investment_amount,investment_date\n1,John Doe,30,5000,2024-01-01\n2,Jane Smith,28,7500,2024-01-02"
        );

        $response = $this->postJson('/api/investors/import', [
            'file' => $file
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('investors', ['investor_id' => 1]);
        $this->assertDatabaseHas('investments', [
            'amount' => 5000,
            'investment_date' => '2024-01-01'
        ]);
    }
}
