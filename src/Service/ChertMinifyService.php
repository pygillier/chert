<?php

namespace pygillier\Chert\Service;

use pygillier\Chert\Exception;
use Doctrine\DBAL\Connection;

class ChertMinifyService
{
	/**
	 * @var \Doctrine\DBAL\Connection
     */
	private $cnx;
	/**
	 * @var HashService
     */
	private $hash_service;
    
    public function __construct(Connection $cnx, HashService $hash_service)
    {
        $this->cnx= $cnx;
        $this->hash_service = $hash_service;
    }
    
    /**
     * Store and save provided link in database
     *
     * @param string $link The link to save
     * @return string Link's associated hash
	 *
	 * TODO : Refactor for parameter sanitization
	 * TODO : Refactor for Assertion of correct URL
     */
    public function minify($link)
    {
        $this->cnx->insert('url', array( 'url' => $link));
        $id = $this->cnx->lastInsertId();
        
        return $this->hash_service->getHash($id);
    }

	/**
     * Returns a record from its hash
     *
	 * @param string $hash The hash to lookup
	 * @return array The record from database
	 * @throws \pygillier\Chert\Exception if no record was found.
     */
	public function expand($hash)
    {
        $id = $this->hash_service->getValue($hash);

        $qb = $this->cnx->createQueryBuilder()
            ->select('u.id','u.url', 'u.created_at')
            ->from("url", "u")
            ->where('id = :id')
            ->setParameter('id', $id)
            ;

		$link = $qb->execute()->fetch(\PDO::FETCH_ASSOC);

		if(false === $link)
		{
			throw new Exception("No URL found for hash : ${hash}");
		}
        
        return $link;
    }
	
	public function getAll()
	{
		$sql = "SELECT * from url";
		$links = $this->cnx->fetchAll($sql);
		
		return $links;
	}

	public function getListing($offset, $limit)
	{
		$queryBuilder = $this->cnx->createQueryBuilder();

		$queryBuilder
			->select('*')
			->from("url", "u")
			->setFirstResult($limit * $offset)
			->setMaxResults($limit);

		return $queryBuilder->execute()->fetchAll();
	}
	
	public function countLinks()
	{
		$sql = "SELECT COUNT(*) AS TOTAL from url";
		$result = $this->cnx->fetchAssoc($sql);
		
		return $result['TOTAL'];
	}
}
