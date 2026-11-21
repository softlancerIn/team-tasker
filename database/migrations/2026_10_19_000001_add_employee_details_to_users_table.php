<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 20)->nullable();
            }

            if (!Schema::hasColumn('users', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable();
            }

            if (!Schema::hasColumn('users', 'gender')) {
                $table->string('gender', 10)->nullable();
            }

            if (!Schema::hasColumn('users', 'address')) {
                $table->text('address')->nullable();
            }

            if (!Schema::hasColumn('users', 'emergency_contact_name')) {
                $table->string('emergency_contact_name')->nullable();
            }

            if (!Schema::hasColumn('users', 'emergency_contact_phone')) {
                $table->string('emergency_contact_phone', 20)->nullable();
            }

            if (!Schema::hasColumn('users', 'employee_code')) {
                $table->string('employee_code', 50)->nullable()->unique();
            }

            if (!Schema::hasColumn('users', 'designation')) {
                $table->string('designation', 100)->nullable();
            }

            if (!Schema::hasColumn('users', 'department')) {
                $table->string('department', 100)->nullable();
            }

            if (!Schema::hasColumn('users', 'joining_date')) {
                $table->date('joining_date')->nullable();
            }

            if (!Schema::hasColumn('users', 'pan_number')) {
                $table->string('pan_number', 20)->nullable();
            }

            if (!Schema::hasColumn('users', 'aadhaar_number')) {
                $table->text('aadhaar_number')->nullable();
            }

            if (!Schema::hasColumn('users', 'bank_name')) {
                $table->string('bank_name')->nullable();
            }

            if (!Schema::hasColumn('users', 'bank_account_number')) {
                $table->text('bank_account_number')->nullable();
            }

            if (!Schema::hasColumn('users', 'bank_ifsc')) {
                $table->string('bank_ifsc', 20)->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'phone',
                'date_of_birth',
                'gender',
                'address',
                'emergency_contact_name',
                'emergency_contact_phone',
                'designation',
                'department',
                'joining_date',
                'pan_number',
                'aadhaar_number',
                'bank_name',
                'bank_account_number',
                'bank_ifsc',
                'employee_code',
            ];

            $existingColumns = array_filter(
                $columns,
                fn ($column) => Schema::hasColumn('users', $column)
            );

            if (!empty($existingColumns)) {
                $table->dropColumn(array_values($existingColumns));
            }
        });
    }
};