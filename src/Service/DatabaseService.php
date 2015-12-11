<?php
/**
 * Created by PhpStorm.
 * User: Pierre-Yves
 * Date: 10/12/2015
 * Time: 21:52
 */

namespace pygillier\Chert\Service;


class DatabaseService
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * @param $link
     * @return mixed
     */
    public function insertLink($link)
    {
        $this->db->insert('url', array( 'url' => $link));

        // Returns an url with given ID
        return $this->db->lastInsertId();
    }
}