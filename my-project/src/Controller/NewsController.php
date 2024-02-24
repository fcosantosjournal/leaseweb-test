<?php

    namespace App\Controller;

    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use App\Models\NewsModel;

    class NewsController extends AbstractController
    {
        public $news;
        public $entityManager;

        public function __construct(EntityManagerInterface $entityManager) 
        {   
            $this->entityManager = $entityManager;
        }
        public function getNewsHome()
        {   
            $news = new NewsModel($this->entityManager);
            $news = $news->getLastFourNews();
            return $news;
        }
            
    }