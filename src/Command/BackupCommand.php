<?php

// src/Command/BackupCommand.php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Dotenv\Dotenv;
use PDO;

class BackupCommand extends Command
{
    protected static $defaultName = 'app:backup-db';

    protected function configure(): void
    {
        $this
            ->setName('app:backup-db')
            ->setDescription('Creates a backup of the database.')
            ->setHelp('This command allows you to create a backup of the database in backup.sql file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Load .env file
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__.'/../../.env');
        $databaseUrl = $_ENV['DATABASE_URL'];
        if (!$databaseUrl) {
            throw new \RuntimeException('DATABASE_URL environment variable is not set.');
        }

        // Parse DATABASE_URL
        $dbConfig = parse_url($databaseUrl);
        $dsn = sprintf('mysql:host=%s;dbname=%s', $dbConfig['host'], trim($dbConfig['path'], '/'));
        $user = $dbConfig['user'] ?? null;
        $password = $dbConfig['pass'] ?? null;

        // Connect to the database
        $pdo = new PDO($dsn, $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Open file to write backup
        $file = fopen('backup.sql', 'w');

        // Fetch all table names
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            // Write DROP TABLE statement
            fwrite($file, "DROP TABLE IF EXISTS `$table`;\n");

            // Fetch CREATE TABLE statement
            $createTable = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
            fwrite($file, $createTable['Create Table'] . ";\n\n");

            // Fetch all rows from table
            $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                // Prepare INSERT statement for each row
                $cols = implode("`, `", array_keys($row));
                $vals = implode("', '", array_values(array_map(function ($value) use ($pdo) {
                    return $pdo->quote($value);
                }, $row)));
                fwrite($file, "INSERT INTO `$table` (`$cols`) VALUES ($vals);\n");
            }

            fwrite($file, "\n\n");
        }

        fclose($file);

        $output->writeln('Database backup successful.');

        return Command::SUCCESS;
    }


    
}