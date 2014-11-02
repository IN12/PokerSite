<?php

class Database
{
    private $db;

    /**
     * PDO wrapper class constructor
     * @param string $dbname Name of the database
     * @param string $address (optional) IP address
     * @param string $username (optional)
     * @param string $password (optional)
     * @throws PDOException
     * @throws InvalidArgumentException
     */
    public function __construct($dbName, $address = "127.0.0.1",
                                $username = "root", $password = "")
    {
        if ($this->isNullOrEmpty($address) ||
            $this->isNullOrEmpty($dbName) ||
            $this->isNullOrEmpty($username))
            throw new InvalidArgumentException(
                "Required parameters were not specified.");

        // Create a new PDO instance
        try
        {
            $this->db = new PDO("mysql:host={$address};dbname={$dbName};charset=utf8",
                $username, $password,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        }
        catch (PDOException $e) { throw $e; }
    }

    /**
     * Executes an SQL select
     * @param string $sqlCommand SQL SELECT command to execute
     * @throws PDOException
     * @return array Array of anonymous objects with named properties
     */
    public function select($sqlCommand)
    {
        try
        {
            $prepStmt = $this->db->query($sqlCommand);
            $prepStmt->setFetchMode(PDO::FETCH_OBJ);
            return $prepStmt->fetchAll();
        }
        catch (PDOException $e) { throw $e; }
    }

    /**
     * Executes an SQL parameterized select
     * @param string $sqlCommand SQL SELECT command to execute
     * @param array $data Array of parameters
     * @param string $className (optional) Class to use
     * @throws PDOException
     * @return array Array of objects with named properties
     */
    public function parameterizedSelect($sqlCommand, $data, $className = "")
    {
        try
        {
            $prepStmt = $this->db->prepare($sqlCommand);
            $prepStmt->execute($data);
            if ($this->isNullOrEmpty($className))
                return $prepStmt->fetchAll(PDO::FETCH_OBJ);
            else return $prepStmt->fetchAll(PDO::FETCH_CLASS, $className);
        }
        catch (PDOException $e) { throw $e; }
    }

    /**
     * Executes an SQL select
     * @param string $sqlCommand SQL SELECT command to execute
     * @param string $className Class to use
     * @throws PDOException
     * @return array Array of specific objects with named properties
     */
    public function selectAsClassObjects($sqlCommand, $className)
    {
        try
        {
            $prepStmt = $this->db->query($sqlCommand);
            return $prepStmt->fetchAll(PDO::FETCH_CLASS, $className);
        }
        catch (PDOException $e) { throw $e; }
    }

    /**
     * Tries to insert a record
     * @param string $sqlCommand SQL INSERT / UPDATE command to execute
     * @param array $data Array of parameters
     * @return bool Returns true if data is successfully inserted
     * @throws PDOException
     */
    public function executePreparedStatement($sqlCommand, $data)
    {
        try
        {
            $prepStmt = $this->db->prepare($sqlCommand);
            $prepStmt->execute($data);
            return true;
        }
        catch (PDOException $e) { throw $e; }
    }

    /**
     * Tries to create and execute an SQL transaction
     * @param array $sqlCommands
     * @throws PDOException
     */
    public function executeTransaction($sqlCommands)
    {
        try
        {
            $this->db->beginTransaction();
            foreach ($sqlCommands as $sqlCommand)
                $this->db->exec($sqlCommand);
            $this->db->commit();
        }
        catch (PDOException $e)
        {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Tries to execute an SQL command which does not
     * return any data other than the affected rows
     * @param string $sqlCommand Command to execute
     * @return int Rows affected
     * @throws PDOException
     */
    public function executeSqlCommand($sqlCommand)
    {
        try
        {
            return $this->db->exec($sqlCommand);
        }
        catch (PDOException $e) { throw $e; }
    }

    private function isNullOrEmpty($str)
    {
        return (!isset($str) || (strlen($str) == 0));
    }
}

?>