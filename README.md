# Woven Data Investment Service

A Laravel backend service for importing investor data from CSV files and exposing it through RESTful APIs

## Quick Start

### Prerequisites
- Docker Desktop installed and running

### Installation & Running

1. **Clone the repository**
```bash
git clone https://github.com/MeioNeiz/woven_backend_task
cd woven_backend_task
```

2. **Copy environment file**
```bash
cp .env.example .env
```

3. **Build and start Docker containers**
```bash
docker-compose up -d
```

4. **Generate app key**
```bash
docker-compose exec app php artisan key:generate
```

5. **Run migrations**
```bash
docker-compose exec app php artisan migrate
```

6. **Verify it's working**
```bash
curl http://localhost:8000/api/investors/stats/average-age
```

Result: {"average_age":0}
---

## Architecture

### Directory Structure

woven_backend_task/
├── app/
│   ├── Http/Controllers/Api/
│   │   └── InvestorController.php
│   ├── Services/
│   │   ├── CsvImportService.php
│   │   └── InvestmentService.php
│   └── Models/
│       ├── Investor.php
│       └── Investment.php
├── database/
│   └── migrations/
│       ├── create_investors_table.php
│       └── create_investments_table.php
├── routes/
│   └── api.php
├── tests/
│   ├── Feature/Api/
│   │   └── InvestorControllerTest.php
│   └── Unit/Services/
│       └── CsvImportServiceTest.php
├── Dockerfile
└── docker-compose.yml

## CSV Format

The import endpoint expects a CSV file with the following structure:

```csv
investor_id,name,age,investment_amount,investment_date
1,John Doe,30,5000,2024-01-15
2,Jane Smith,28,7500,15-01-2024
```

## API Endpoints

### Import CSV
**POST** `/api/investors/import`
- **Body:** `multipart/form-data` with `file` field
- **Response:**
```json
{
  "imported": 150,
  "errors": [],
  "total_errors": 0
}
```

### Get Average Age
**GET** `/api/investors/stats/average-age`
- **Response:**
```json
{
  "average_age": 35
}
```

### Get Average Investment Amount
**GET** `/api/investors/stats/average-investment`
- **Response:**
```json
{
  "average_investment_amount": 7500
}
```

### Get Total Investments
**GET** `/api/investors/stats/total-investments`
- **Response:**
```json
{
  "total_investments": 1250
}
```

### Get All Investors
**GET** `/api/investors?per_page=50`
- **Query Params:** `per_page` (optional, default: 50)
- **Response:** Paginated JSON with investor details and their investments

## Technical Decisions

### PHP Version
PHP 8.4 - Second most recent version - hopefully stable

### Docker
Ensures consistent development environment across all systems

### Performance Optimisations
**Problem:** Original implementation would use `firstOrCreate()` in a loop
- 10,000 rows = 10,000+ database queries
- Slow and resource-intensive

**Solution:** Batch `upsert()` and `insert()`
- 10,000 rows = 3 database queries
- Critical for handling 10k+ records efficiently

## Running Tests

Run tests with:
```bash
docker-compose exec app php artisan test
```

## Debugging

XDebug is configured in the docker container

## Future improvements

### Testing

- Increase test coverage such as for the InvestmentService.php
- Add more edge case tests
- Add test db so we dont just wipe the db every time we run a test :D
- Add performance tests, we know we may have 10k+ so test it to see how it handles it
- Can benchmark endpoints 

### Features
- CSV export endpoint
- Import history/audit log

### Performance

- Caching for aggregate endpoints
- Laravel queue for async imports (only required for very large csvs)
- Database indexing optimisation

### Security

- API token authentication (Laravel Sanctum)
- Rate limiting on import endpoint

### TODO

- API documentation (Swagger/OpenAPI)
- Logging

## Assumptions

- No API authentication required
- One investment per date per investor (as per brief)

## Implementation Status

**Completed:**
- CSV import endpoint with validation
- Batch processing for scalability
- All three aggregate endpoints
- Get all investors endpoint with pagination
- Service-oriented architecture
- Basic unit and feature tests
- Docker setup

**Not completed:**
- CSV export functionality
- Comprehensive test coverage
- Queue-based import for large csvs