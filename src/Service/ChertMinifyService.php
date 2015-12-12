<?php

namespace pygillier\Chert\Service;

use pygillier\Chert\Exception;

class ChertMinifyService
{
    private $cnx;
    private $hash_service;
    
    public function __construct(\Doctrine\DBAL\Connection $cnx, HashService $hash_service)
    {
        $this->cnx= $cnx;
        $this->hash_service = $hash_service;
    }
    
    /**
     * Store and save provided link in database
     *
     * @param string $link The link to save
     * @return string Link's associated hash
     */
    public function minify($link)
    {
        $this->cnx->insert('url', array( 'url' => $link));
        $id = $this->cnx->lastInsertId();
        
        return $this->hash_service->getHash($id);
    }
    
    public function expand($hash)
    {
        $id = $this->hash_service->getValue($hash);
		
		$sql = "SELECT * FROM url WHERE id = ?";
		$link = $this->cnx->fetchAssoc($sql, array($id));

		if(false === $link)
		{
			throw new Exception("No URL found for hash : ${hash}");
		}
        
        return $link;
    }
	
	public function getAll($with_hashes = false)
	{
		$sql = "SELECT * from url";
		$links = $this->cnx->fetchAll($sql);
		
		if($with_hashes === true)
		{
			return array_map(function($item){
				$item['hash'] = $this->hash_service->getHash($item['id']);
				return $item;
			}, $links);
		}
		
		return $links;
	}
	
	public function countLinks()
	{
		$sql = "SELECT COUNT(*) AS TOTAL from url";
		$result = $this->cnx->fetchAssoc($sql);
		
		return $result['TOTAL'];
	}
}
