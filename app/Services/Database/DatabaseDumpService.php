<?php

declare(strict_types=1);

namespace App\Services\Database;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DatabaseDumpService
{
    /** Формирует SQL-дамп структуры и данных текущей БД */
    public function generate(): string
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();

        $lines = [
            '-- Выгрузка базы данных',
            '-- Подключение: '.$connection->getName(),
            '-- Драйвер: '.$driver,
            '-- Сформировано: '.now()->toDateTimeString(),
            '',
        ];

        $dumpLines = match ($driver) {
            'sqlite' => $this->dumpSqlite($connection),
            'mysql' => $this->dumpMysql($connection),
            default => throw new RuntimeException("Драйвер БД [{$driver}] не поддерживается для выгрузки."),
        };

        return implode(PHP_EOL, [...$lines, ...$dumpLines]);
    }

    /** @return list<string> */
    private function dumpSqlite(Connection $connection): array
    {
        $lines = ['PRAGMA foreign_keys=OFF;', ''];

        $tables = $connection->select(
            "SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name",
        );

        foreach ($tables as $table) {
            $tableName = (string) $table->name;
            $create = $connection->selectOne(
                "SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ?",
                [$tableName],
            );

            if ($create?->sql === null) {
                continue;
            }

            $lines[] = '-- Таблица: '.$tableName;
            $lines[] = (string) $create->sql.';';
            $lines[] = '';
            $lines = array_merge($lines, $this->dumpTableInserts($connection, $tableName));
            $lines[] = '';
        }

        $lines[] = 'PRAGMA foreign_keys=ON;';

        return $lines;
    }

    /** @return list<string> */
    private function dumpMysql(Connection $connection): array
    {
        $lines = ['SET FOREIGN_KEY_CHECKS=0;', ''];

        $database = $connection->getDatabaseName();
        $tables = $connection->select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"');

        foreach ($tables as $table) {
            $tableName = (string) ($table->{'Tables_in_'.$database} ?? array_values((array) $table)[0]);
            $create = $connection->selectOne('SHOW CREATE TABLE `'.str_replace('`', '``', $tableName).'`');
            $createSql = $create->{'Create Table'} ?? null;

            if ($createSql === null) {
                continue;
            }

            $lines[] = '-- Таблица: '.$tableName;
            $lines[] = (string) $createSql.';';
            $lines[] = '';
            $lines = array_merge($lines, $this->dumpTableInserts($connection, $tableName));
            $lines[] = '';
        }

        $lines[] = 'SET FOREIGN_KEY_CHECKS=1;';

        return $lines;
    }

    /** @return list<string> */
    private function dumpTableInserts(Connection $connection, string $tableName): array
    {
        $rows = $connection->table($tableName)->get();

        if ($rows->isEmpty()) {
            $lines = ['-- Нет данных'];
            $lines[] = '';

            return $lines;
        }

        $columns = array_keys((array) $rows->first());
        $columnList = implode(', ', array_map(
            fn (string $column): string => $this->quoteIdentifier($connection, $column),
            $columns,
        ));

        $lines = [];

        foreach ($rows as $row) {
            $values = [];

            foreach ($columns as $column) {
                $values[] = $this->quoteValue($connection, ((array) $row)[$column] ?? null);
            }

            $lines[] = sprintf(
                'INSERT INTO %s (%s) VALUES (%s);',
                $this->quoteIdentifier($connection, $tableName),
                $columnList,
                implode(', ', $values),
            );
        }

        return $lines;
    }

    private function quoteIdentifier(Connection $connection, string $identifier): string
    {
        return match ($connection->getDriverName()) {
            'mysql' => '`'.str_replace('`', '``', $identifier).'`',
            default => '"'.str_replace('"', '""', $identifier).'"',
        };
    }

    private function quoteValue(Connection $connection, mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return $connection->getPdo()->quote((string) $value);
    }
}
