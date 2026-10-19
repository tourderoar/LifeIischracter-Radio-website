<?php
/**
 * Life is Character Radio - Database Connection
 * 
 * Location: /includes/db.php
 * 
 * PDO database connection with error handling and prepared statement support
 */

// Include guard
if (defined('LICR_DB_LOADED')) return;
define('LICR_DB_LOADED', true);

// Ensure config is loaded
if (!defined('DB_HOST')) require_once __DIR__ . '/config.php';

/**
 * Get PDO database connection (singleton pattern)
 * 
 * @return PDO Database connection
 * @throws PDOException If connection fails
 */
if (!function_exists('getDB')) {
function getDB(): PDO {
    static $pdo = null;
    
    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false
        ];
        
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            $pdo->exec("SET time_zone = '+00:00'");
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                error_log("Database Connection Error: " . $e->getMessage());
                die("Database connection failed. Check your configuration.");
            }
            die("A database error occurred. Please try again later.");
        }
    }
    
    return $pdo;
}
}

/**
 * Execute a query and return results
 * 
 * @param string $sql SQL query with placeholders
 * @param array $params Parameters for prepared statement
 * @return array Query results
 */
if (!function_exists('dbQuery')) {
function dbQuery(string $sql, array $params = []): array {
    $stmt = getDB()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}
}

/**
 * Execute query and return single row
 * 
 * @param string $sql SQL query with placeholders
 * @param array $params Parameters for prepared statement
 * @return array|null Single row or null if not found
 */
if (!function_exists('dbQueryOne')) {
function dbQueryOne(string $sql, array $params = []): ?array {
    $stmt = getDB()->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetch();
    return $result ?: null;
}
}

/**
 * Execute INSERT/UPDATE/DELETE query
 * 
 * @param string $sql SQL query with placeholders
 * @param array $params Parameters for prepared statement
 * @return int|bool Last insert ID for INSERT, affected rows for UPDATE/DELETE
 */
if (!function_exists('dbExecute')) {
function dbExecute(string $sql, array $params = []): int|bool {
    $stmt = getDB()->prepare($sql);
    $result = $stmt->execute($params);
    
    // Return last insert ID for INSERT queries
    if (stripos(trim($sql), 'INSERT') === 0) {
        return (int)getDB()->lastInsertId();
    }
    
    // Return affected rows for UPDATE/DELETE
    return $stmt->rowCount();
}
}

/**
 * Begin database transaction
 */
if (!function_exists('dbBeginTransaction')) {
function dbBeginTransaction(): void {
    getDB()->beginTransaction();
}
}

/**
 * Commit database transaction
 */
if (!function_exists('dbCommit')) {
function dbCommit(): void {
    getDB()->commit();
}
}

/**
 * Rollback database transaction
 */
if (!function_exists('dbRollback')) {
function dbRollback(): void {
    getDB()->rollBack();
}
}

/**
 * Check if database connection is available
 * 
 * @return bool True if connected
 */
if (!function_exists('dbConnected')) {
function dbConnected(): bool {
    try {
        getDB()->query('SELECT 1');
        return true;
    } catch (PDOException $e) {
        return false;
    }
}
}
