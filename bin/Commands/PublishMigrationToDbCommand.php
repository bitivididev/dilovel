<?php


namespace Bin\Commands;

use App\Components\Database\Migration\CallMigrationObjects;
use App\Components\Database\Migration\MigrationStorage;
use App\Components\Database\PDOAdaptor;
use Bin\Components\ColorConsole;
use Bin\Components\CommandInterface;

class PublishMigrationToDbCommand implements CommandInterface
{
    protected string $description = 'create all tables from migrations';


    public function handle(?array $parameters): void
    {
        CallMigrationObjects::create();
        $migrations = MigrationStorage::all();
        $tables = array_keys($migrations);

        $this->createTable($tables, $migrations);
    }

    private function createTable(array $tables, array $migrations): void
    {
        $withoutLengths = ['TEXT', 'LONGTEXT'];
        foreach ($tables as $table) {
            $sql = "CREATE TABLE IF NOT EXISTS $table (";
            foreach ($migrations[$table] as $key => $value) {
                if (in_array($value['type'], $withoutLengths, true)) {
                    $sql .= sprintf(
                        '%s %s %s %s,',
                        $value['column_name'],
                        $value['type'],
                        (int) $value['nullable']===1 ? 'not null':'',
                        (int)$value['unique']===1 ? 'unique' :'',
                    );
                } else {
                    $sql .= sprintf(
                        '%s %s(%d) %s %s,',
                        $value['column_name'],
                        $value['type'],
                        $value['length'],
                        $value['nullable']===false ? 'not null':'',
                        $value['unique']===true ? 'unique' :'',
                    );
                }
            }
            $sql = rtrim($sql, ',');
            $sql .= ')';

            echo PDOAdaptor::connection($migrations[$table][0]['connection_name'])->exec($sql) ?: ColorConsole::getInstance()->getColoredString("$table migrated\n", 'green');
        }
    }
}
