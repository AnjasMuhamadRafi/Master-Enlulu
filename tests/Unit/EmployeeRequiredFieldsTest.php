<?php

namespace Tests\Unit;

use App\Http\Controllers\EmployeeController;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class EmployeeRequiredFieldsTest extends TestCase
{
    public function test_master_data_rules_only_require_identity_fields(): void
    {
        $method = new ReflectionMethod(EmployeeController::class, 'masterDataRules');
        $method->setAccessible(true);

        $rules = $method->invoke(new EmployeeController());
        $requiredFields = array_keys(array_filter(
            $rules,
            fn (string $rule): bool => in_array('required', explode('|', $rule), true)
        ));

        $this->assertSame([
            'nama_ktp',
            'tempat_lahir',
            'tanggal_lahir',
        ], $requiredFields);
    }

    #[DataProvider('employeeForms')]
    public function test_employee_forms_only_mark_four_fields_as_required(string $view): void
    {
        $contents = file_get_contents(dirname(__DIR__, 2) . "/resources/views/employee/{$view}.blade.php");

        preg_match_all('/\brequired\b/i', $contents, $requiredMatches, PREG_OFFSET_CAPTURE);
        $requiredFields = array_map(function (array $match) use ($contents): string {
            $context = substr($contents, max(0, $match[1] - 500), min(500, $match[1]));
            preg_match_all('/\bname="([^"]+)"/i', $context, $nameMatches);

            return end($nameMatches[1]);
        }, $requiredMatches[0]);

        $this->assertSame([
            'nik',
            'nama_ktp',
            'tempat_lahir',
            'tanggal_lahir',
        ], $requiredFields);
    }

    public static function employeeForms(): array
    {
        return [
            'create' => ['create'],
            'edit' => ['edit'],
        ];
    }
}
