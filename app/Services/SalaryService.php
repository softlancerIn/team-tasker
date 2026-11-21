<?php

namespace App\Services;

use App\Models\EmployeeSalary;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalaryService
{
    /**
     * Purani active salary ko (effective_from - 1 din) par close karta hai
     * aur nayi salary create karta hai.
     */
    public function setSalary(int $userId, array $data): EmployeeSalary
    {
        $workingDays = array_values(array_unique(array_map('intval', $data['working_days'])));

        return DB::transaction(function () use ($userId, $data, $workingDays) {
            $newEffFrom = Carbon::parse($data['effective_from'])->toDateString();

            // Agar user ki current salary ka effective_from change kiya gaya hai aur amount same hai
            // ya usi date/later date par already active record hai, use handle karo
            EmployeeSalary::query()
                ->where('user_id', $userId)
                ->where('is_active', true)
                ->whereNull('effective_until')
                ->whereDate('effective_from', '<', $newEffFrom)
                ->update([
                    'effective_until' => Carbon::parse($newEffFrom)->subDay()->toDateString(),
                    'is_active' => false,
                ]);

            // Agar exact same effective_from ya future date par existing active salary hai, to use deactivate karo taaki duplicate/overlap na ho
            EmployeeSalary::query()
                ->where('user_id', $userId)
                ->where('is_active', true)
                ->whereDate('effective_from', '>=', $newEffFrom)
                ->update([
                    'is_active' => false,
                    'effective_until' => $newEffFrom,
                ]);

            return EmployeeSalary::create([
                'user_id' => $userId,
                'monthly_salary' => $data['monthly_salary'],
                'working_days' => $workingDays,
                'effective_from' => $newEffFrom,
                'effective_until' => $data['effective_until'] ?? null,
                'is_active' => true,
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }
}