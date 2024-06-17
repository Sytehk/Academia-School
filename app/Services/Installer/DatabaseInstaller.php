<?php

namespace App\Services\Installer;

use Exception;
use PDO;
use PDOException;

class DatabaseInstaller
{
    /**
     * @throws Exception on any invalid/unsafe credential
     */
    public static function connect(string $host, string $port, string $name, string $user, string $password): PDO
    {
        // DSN parameters are interpolated into the connection string itself (not
        // SQL), so a value containing ";" could redirect the DSN to a different
        // host/db (PDO DSN injection). Reject anything with DSN-special chars.
        foreach (['host' => $host, 'port' => $port, 'name' => $name, 'user' => $user] as $label => $value) {
            if (preg_match('/[;\'"\\\\]/', (string) $value)) {
                throw new Exception("Invalid character in database {$label}.");
            }
        }

        if ($port !== '' && ! ctype_digit((string) $port)) {
            throw new Exception('Database port must be numeric.');
        }

        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

        try {
            return new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ]);
        } catch (PDOException $e) {
            throw new Exception(self::friendlyConnectionError($e));
        }
    }

    private static function friendlyConnectionError(PDOException $e): string
    {
        $message = $e->getMessage();

        return match (true) {
            str_contains($message, 'Access denied') => 'Access denied: check the database username and password.',
            str_contains($message, 'Unknown database') => 'The specified database does not exist. Create it first or choose an existing one.',
            str_contains($message, 'Connection refused') => 'Connection refused: check the host and port, and that MySQL is running.',
            str_contains($message, 'timed out') || str_contains($message, 'timeout') => 'Connection timed out: check the host/port and firewall settings.',
            str_contains($message, 'Host') && str_contains($message, 'not allowed') => 'This database host does not allow connections from this server.',
            default => 'Could not connect to the database with the given credentials.',
        };
    }

    /**
     * Confirm the supplied credentials can actually perform the operations the
     * installer needs (CREATE/ALTER/DROP/INDEX/INSERT/UPDATE/DELETE), so we fail
     * before wiping/importing anything rather than mid-import.
     *
     * @return array<string,bool>
     */
    public static function checkPrivileges(PDO $pdo, string $database): array
    {
        $probe = '__install_privilege_probe';
        $results = [
            'CREATE TABLE' => false,
            'ALTER TABLE' => false,
            'CREATE INDEX' => false,
            'INSERT' => false,
            'UPDATE' => false,
            'DELETE' => false,
            'DROP TABLE' => false,
        ];

        try {
            $pdo->exec("DROP TABLE IF EXISTS `{$probe}`");
            $pdo->exec("CREATE TABLE `{$probe}` (id INT PRIMARY KEY, val VARCHAR(10))");
            $results['CREATE TABLE'] = true;

            $pdo->exec("ALTER TABLE `{$probe}` ADD COLUMN extra INT NULL");
            $results['ALTER TABLE'] = true;

            $pdo->exec("CREATE INDEX idx_val ON `{$probe}` (val)");
            $results['CREATE INDEX'] = true;

            $pdo->exec("INSERT INTO `{$probe}` (id, val) VALUES (1, 'a')");
            $results['INSERT'] = true;

            $pdo->exec("UPDATE `{$probe}` SET val = 'b' WHERE id = 1");
            $results['UPDATE'] = true;

            $pdo->exec("DELETE FROM `{$probe}` WHERE id = 1");
            $results['DELETE'] = true;

            $pdo->exec("DROP TABLE `{$probe}`");
            $results['DROP TABLE'] = true;
        } catch (PDOException $e) {
            // Best-effort cleanup; leave $results reflecting where it stopped.
            try {
                $pdo->exec("DROP TABLE IF EXISTS `{$probe}`");
            } catch (PDOException) {
            }
        }

        return $results;
    }

    /**
     * Detect a non-empty database that isn't just our own prior (failed)
     * install attempt, so we never silently wipe unrelated/production data.
     */
    public static function tableCount(PDO $pdo, string $database): int
    {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ?');
        $stmt->execute([$database]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Import a SQL dump statement-by-statement using PDO's native
     * multi-statement-free execution, so a "--" or "/* *\/"-like sequence
     * inside string data is never mistaken for a real comment, and a
     * semicolon inside a string literal never causes a mis-split.
     *
     * @throws Exception on the first failing statement
     */
    public static function importSqlFile(PDO $pdo, string $path): void
    {
        if (! file_exists($path)) {
            throw new Exception("SQL file not found at: {$path}");
        }

        $pdo->exec('SET FOREIGN_KEY_CHECKS=0');

        try {
            foreach (self::splitStatements(file_get_contents($path)) as $i => $statement) {
                $statement = trim($statement);
                if ($statement === '') {
                    continue;
                }

                try {
                    $pdo->exec($statement);
                } catch (PDOException $e) {
                    throw new Exception(
                        'Failed executing SQL statement #'.($i + 1).': '.substr($statement, 0, 200).
                        ' ... Error: '.$e->getMessage()
                    );
                }
            }
        } finally {
            $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    /**
     * A small state-machine SQL splitter: tracks whether we're inside a
     * single/double-quoted string or a backtick identifier, and only treats
     * "--", "#" and "/* *\/" as comments and ";" as a terminator when not
     * inside a string. This avoids corrupting demo data containing those
     * byte sequences, unlike a regex-based comment strip.
     *
     * @return string[]
     */
    private static function splitStatements(string $sql): array
    {
        $statements = [];
        $current = '';
        $len = strlen($sql);
        $inString = null; // one of null, "'", '"', '`'
        $i = 0;

        while ($i < $len) {
            $char = $sql[$i];
            $next = $sql[$i + 1] ?? '';

            if ($inString !== null) {
                $current .= $char;
                if ($char === '\\' && $inString !== '`') {
                    // Escaped char inside a string: consume it verbatim too.
                    if ($i + 1 < $len) {
                        $current .= $next;
                        $i += 2;

                        continue;
                    }
                } elseif ($char === $inString) {
                    $inString = null;
                }
                $i++;

                continue;
            }

            if ($char === "'" || $char === '"' || $char === '`') {
                $inString = $char;
                $current .= $char;
                $i++;

                continue;
            }

            // Line comments: -- (followed by whitespace) or #
            if (($char === '-' && $next === '-' && (($sql[$i + 2] ?? ' ') === ' ' || ($sql[$i + 2] ?? ' ') === "\t")) || $char === '#') {
                while ($i < $len && $sql[$i] !== "\n") {
                    $i++;
                }

                continue;
            }

            // Block comments: /* ... */
            if ($char === '/' && $next === '*') {
                $end = strpos($sql, '*/', $i + 2);
                $i = $end === false ? $len : $end + 2;

                continue;
            }

            if ($char === ';') {
                $statements[] = $current;
                $current = '';
                $i++;

                continue;
            }

            $current .= $char;
            $i++;
        }

        if (trim($current) !== '') {
            $statements[] = $current;
        }

        return $statements;
    }
}
