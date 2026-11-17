<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDO;

class ImportSqliteData extends Command
{
    protected $signature = 'saji:import-sqlite {source : Path to the preserved SQLite database}';

    protected $description = 'Import existing local Saji Baik data into an empty MySQL database without removing the source';

    public function handle(): int
    {
        if (! app()->isLocal() || DB::connection()->getDriverName() !== 'mysql') {
            $this->error('Only available on local MySQL.');

            return self::FAILURE;
        }
        $path = realpath($this->argument('source'));
        if (! $path || ! is_file($path)) {
            $this->error('SQLite source not found.');

            return self::FAILURE;
        }
        $tables = ['users', 'outlets', 'creators', 'meals', 'orders'];
        foreach ($tables as $table) {
            if (DB::table($table)->exists()) {
                $this->error('Target tables must be empty; no data was changed.');

                return self::FAILURE;
            }
        }
        $source = new PDO('sqlite:'.$path);
        DB::transaction(function () use ($tables, $source): void {
            foreach ($tables as $table) {
                $rows = $source->query('SELECT * FROM '.$table)->fetchAll(PDO::FETCH_ASSOC);
                foreach (array_chunk($rows, 100) as $chunk) {
                    DB::table($table)->insert($chunk);
                }
                $this->line($table.': '.count($rows).' records imported.');
            }
            DB::table('orders')->where('status', 'completed')->whereNull('completed_at')->update(['completed_at' => DB::raw('updated_at')]);
        });
        $this->info('Import completed. Source database remains intact.');

        return self::SUCCESS;
    }
}
