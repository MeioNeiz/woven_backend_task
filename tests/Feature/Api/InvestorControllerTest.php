<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use App\Models\Investor;
use App\Models\Investment;

class InvestorControllerTest extends TestCase {
    use RefreshDatabase;

    public function test_import_endpoint_accepts_csv_file() {
        $file = UploadedFile::fake()->createWithContent(
            'test.csv',
            "investor_id,name,age,investment_amount,investment_date\n" .
                "1,John Doe,30,5000,2024-01-15\n"
        );

        $response = $this->post('/api/investors/import', [
            'file' => $file
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['imported', 'errors', 'total_errors']);
    }

    public function test_import_rejects_non_csv_file() {
        $file = UploadedFile::fake()->create('document.txt', 10, 'text/plain');

        $response = $this->postJson('/api/investors/import', [
            'file' => $file
        ]);

        $response->assertStatus(422);
    }

    public function test_average_age_endpoint() {
        Investor::create(['investor_id' => '1', 'name' => 'John', 'age' => 30]);
        Investor::create(['investor_id' => '2', 'name' => 'Jane', 'age' => 40]);

        $response = $this->get('/api/investors/stats/average-age');

        $response->assertStatus(200);
        $response->assertJsonPath('average_age', 35);
    }

    public function test_get_all_investors_endpoint() {
        Investor::create(['investor_id' => '1', 'name' => 'John', 'age' => 30]);

        $response = $this->get('/api/investors');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => ['*' => ['investor_id', 'name', 'age']]]);
    }
}
