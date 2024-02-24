<?php

    namespace App\Models;

    use Doctrine\ORM\EntityManagerInterface;

    class NewsModel
    {
        public $news;
        public $entityManager;

        public function __construct(EntityManagerInterface $entityManager) 
        {   
            $this->entityManager = $entityManager; 
        }

        public function simpleSqlQuery($sql)
        {   
            $entity = $this->entityManager->getConnection();
            $stmt = $entity->prepare($sql);
            $news = $stmt->executeQuery();
            $news = $news->fetchAllAssociative();
            
            return $news;
        }

        public function getLastFourNews()
        {       
            $sql = 'SELECT * FROM news ORDER BY id DESC LIMIT 4';            
            return $this->simpleSqlQuery($sql);
        }
    }