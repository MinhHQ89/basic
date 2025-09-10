<?php
/**
 * Class Database - Handle all database operations
 * Using PDO MySQL instead of mysqli
 * Separating database logic from Model
 */

// Include database configuration
require_once __DIR__ . '/config.php';

class Database {
    private $pdo;
    private $host;
    private $username;
    private $password;
    private $dbname;
    private $port;
    
    // Constants for configuration
    const DEFAULT_CHARSET = 'utf8mb4';
    
    /**
     * Initialize database connection with PDO
     * @param string $host Database server host (default from config.php)
     * @param string $username Database login username (default from config.php)
     * @param string $password Database password (default from config.php)
     * @param string $dbname Database name (default from config.php)
     * @param int $port Database server port (default from config.php)
     */
    public function __construct($host = null, $username = null, $password = null, $dbname = null, $port = null) {
        $this->host = $host ?: DB_HOST;
        $this->username = $username ?: DB_USERNAME;
        $this->password = $password ?: DB_PASSWORD;
        $this->dbname = $dbname ?: DB_NAME;
        $this->port = $port ?: DB_PORT;
        
        $this->connect();
    }
    
    /**
     * Connect to database
     */
    private function connect() {
        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbname};charset=" . self::DEFAULT_CHARSET;
            
            $this->pdo = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            throw new Exception("Connection failed: " . $e->getMessage());
        }
    }
    
    /**
     * Execute SELECT query and return result array
     * @param string $sql SQL statement
     * @param array $params Parameters for prepared statement
     * @return array Result array
     */
    public function select($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("SELECT query error: " . $e->getMessage());
        }
    }
    
    /**
     * Execute SELECT query and return 1 row
     * @param string $sql SQL statement
     * @param array $params Parameters for prepared statement
     * @return array|null Single row result or null
     */
    public function selectOne($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result !== false ? $result : null;
        } catch (PDOException $e) {
            throw new Exception("SELECT ONE query error: " . $e->getMessage());
        }
    }
    
    
    /**
     * Execute any SQL query
     * @param string $sql SQL statement
     * @param array $params Parameters for prepared statement
     * @return bool True if successful
     */
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute($params);
            return $result !== false;
        } catch (PDOException $e) {
            throw new Exception("Query execution error: " . $e->getMessage());
        }
    }
    
    /**
     * Get last inserted ID
     * @return string Last inserted ID
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Get row count from last query
     * @return int Row count
     */
    public function rowCount() {
        return $this->pdo->rowCount();
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        return $this->pdo->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->pdo->rollback();
    }
    
    /**
     * Check if in transaction
     * @return bool True if in transaction
     */
    public function inTransaction() {
        return $this->pdo->inTransaction();
    }
    
    /**
     * Get PDO instance
     * @return PDO PDO instance
     */
    public function getPdo() {
        return $this->pdo;
    }
    
    /**
     * Check connection
     * @return bool True if connected
     */
    public function isConnected() {
        return $this->pdo !== null;
    }
    
    /**
     * Close database connection (PDO auto-closes)
     */
    public function close() {
        $this->pdo = null;
    }
    
    /**
     * Destructor - ensure connection is closed
     */
    public function __destruct() {
        $this->close();
    }
}
?>
