<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $columns = ['access_token', 'refresh_token', 'app_token'];

    /**
     * Widen token columns to fit encrypted payloads, then encrypt any
     * existing plaintext tokens. Safe to re-run: already-encrypted values
     * are detected and left untouched.
     */
    public function up(): void
    {
        $this->widenColumns();

        foreach (DB::table('akahu_credentials')->get() as $row) {
            $updates = [];

            foreach ($this->columns as $column) {
                $value = $row->{$column} ?? null;

                if ($value === null || $value === '') {
                    continue;
                }

                try {
                    Crypt::decryptString($value);
                    // Decryption succeeded - already encrypted
                } catch (\Throwable $e) {
                    $updates[$column] = Crypt::encryptString($value);
                }
            }

            if ($updates) {
                DB::table('akahu_credentials')->where('id', $row->id)->update($updates);
            }
        }
    }

    public function down(): void
    {
        foreach (DB::table('akahu_credentials')->get() as $row) {
            $updates = [];

            foreach ($this->columns as $column) {
                $value = $row->{$column} ?? null;

                if ($value === null || $value === '') {
                    continue;
                }

                try {
                    $updates[$column] = Crypt::decryptString($value);
                } catch (\Throwable $e) {
                    // Not encrypted - leave as is
                }
            }

            if ($updates) {
                DB::table('akahu_credentials')->where('id', $row->id)->update($updates);
            }
        }
    }

    private function widenColumns(): void
    {
        $driver = DB::connection()->getDriverName();

        foreach ($this->columns as $column) {
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE akahu_credentials MODIFY {$column} TEXT NULL");
            } elseif ($driver === 'pgsql') {
                DB::statement("ALTER TABLE akahu_credentials ALTER COLUMN {$column} TYPE TEXT");
            }
            // sqlite does not enforce varchar lengths, no change needed
        }
    }
};
