<?php

/**
 * WPИ-XM Server Stack
 * Copyright © 2010 - 2017 Jens A. Koch <jakoch@web.de>
 * http://wpn-xm.org/
 *
 * This source file is subject to the terms of the MIT license.
 * For full copyright and license information, view the bundled LICENSE file.
 */

/**
 * A database class to store and retrieve Github Downloads Stats using PDO/SQLite.
 */
class GithubDownloadStatsDatabase
{

    /**
     * @var resource PDO (Database connection resource object)
     */
    private $db;

    public function __construct()
    {
        if ($this->db === null) {
            $this->connect();
            $this->createSchema();
        }
    }

    public function connect()
    {
        $sqliteDatabaseFile = dirname(__DIR__) . '/stats/github_download_stats.sqlite.db';

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
        /**
         * Github API Keys:
         * assets[name]           = assetname
         * assets[download_count] = downloads
         */ 
        $schema = "CREATE TABLE IF NOT EXISTS
            daily_downloads (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
            date TEXT,
                assetname TEXT,
            downloads INTEGER
            )
        ";

        $this->db->exec($schema);
    }

    public function insertDownload($assetname, $downloads)
    {
        $query = "INSERT INTO downloads (date, assetname, downloads)
          VALUES ((strftime('%Y-%m-%d','now','localtime')), :assetname, :downloads)";

        $stmt = $this->db->prepare($query);

        $stmt->bindParam(':assetname', $assetname, PDO::PARAM_STR);
        $stmt->bindParam(':downloads', $downloads, PDO::PARAM_INT);

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

new GithubDownloadStatsDatabase;