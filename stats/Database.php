<?php

/**
 * WPИ-XM Server Stack
 * Copyright © 2010 - 2015 Jens-André Koch <jakoch@web.de>
 * http://wpn-xm.org/
 *
 * This source file is subject to the terms of the MIT license.
 * For full copyright and license information, view the bundled LICENSE file.
 */

/**
 * A database class to store and retrieve API stats using PDO/SQLite.
 */
class Database
{

    /**
     * @var resource PDO (Database connection resource object)
     */
    private $db;

    public function __construct()
    {
        if ($this->db === null) {
            $this->connect();
            //$this->createSchema();
        }
    }

    public function connect()
    {
        $sqliteDatabaseFile = __DIR__ . '/stats.sqlite.db';

        try {
            // open the database
            // tell PDO to disable emulated prepared statements and use real prepared statements
            // set error mode to exceptions
            $this->db = new PDO('sqlite:'.$sqliteDatabaseFile);
            $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new \Exception('Exception : ' . $e->getMessage());
        }
    }

    public function createSchema()
    {
        $schema = "CREATE TABLE IF NOT EXISTS
  	    	downloads (
  	    		id INTEGER PRIMARY KEY AUTOINCREMENT,
  	    		url TEXT,
  	    		component TEXT,
  	    		version TEXT,
  	    		bitsize TEXT,
  	    		phpversion TEXT,
  	    		date TEXT,
  	    		referer TEXT
  	    	)
  	   	";

        $this->db->exec($schema);
    }

    public function insertDownload($url, $component, $version, $bitsize, $phpversion, $referer)
    {
        $query = "INSERT INTO downloads (url, component, version, bitsize, phpversion, date, referer)
          VALUES (:url, :component, :version, :bitsize, :phpversion, (strftime('%Y-%m-%d %H:%M:%f','now','localtime')), :referer)";

        $stmt = $this->db->prepare($query);

        $stmt->bindParam(':url', $url);
        $stmt->bindParam(':component', $component);
        $stmt->bindParam(':version', $version);
        $stmt->bindParam(':bitsize', $bitsize);
        $stmt->bindParam(':phpversion', $phpversion);
        $stmt->bindParam(':referer', $referer);

        $stmt->execute();
    }

    public function closeDatabaseConnection()
    {
        try {
            $this->db = null;
        } catch (PDOException $e) {
            throw new \Exception('Exception : ' . $e->getMessage());
        }
    }

    public function __destruct()
    {
        $this->closeDatabaseConnection();
    }
}
