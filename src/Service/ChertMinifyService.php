<?php

namespace pygillier\Chert\Service;

use pygillier\Chert\Exception;
use Doctrine\DBAL\Connection;
use Symfony\Component\Validator\Constraints as Assert;

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
	
	private $validator_service;
    
    public function __construct(Connection $cnx, HashService $hash_service, $validator_service)
    {
        $this->cnx= $cnx;
        $this->hash_service = $hash_service;
		$this->validator_service = $validator_service;
    }
    
    /**
     * Store and save provided link in database
     *
     * @param string $link The link to save
     * @return string Link's associated hash
     */
    public function minify($link)
    {
		// Validation 
		$errors = $this->validator_service->validateValue($link, new Assert\Url());
		
		if(count($errors) == 0)
		{
			$this->cnx->insert('url', array( 'url' => $link));
        	$id = $this->cnx->lastInsertId();
        
        	return $this->hash_service->getHash($id);	
		}
		else
		{
			throw new Exception("Given link is invalid".(string) $errors);
		}
        
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
		$qb = $this->cnx->createQueryBuilder()
			->select('u.id, u.url, u.created_at')
			->from('url', 'u')
		;
		$links = $qb->execute()->fetchAll();
		
		return $links;
	}

	/**
	 * Returns a paginated listing of records
	 *
	 * Records are ordered by creation date.
	 *
	 * @param int $offset Where to start the listing
	 * @param int $limit
	 * @return array
	 */
	public function getListing($offset=0, $limit=10)
	{
		$queryBuilder = $this->cnx->createQueryBuilder()
			->select('*')
			->from("url", "u")
			->orderBy("u.created_at", "ASC")
			->setFirstResult($limit * $offset)
			->setMaxResults($limit);

		return $queryBuilder->execute()->fetchAll();
	}
	
	/*
	 * Return number of links in database
	 *
	 * @return int The count
	 */
	public function countLinks()
	{
		$sql = "SELECT COUNT(*) AS total from url";
		$result = $this->cnx->executeQuery($sql)->fetch();
		
		return $result['total'];
	}
}
