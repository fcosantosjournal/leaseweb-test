<?php

    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\Routing\Annotation\Route;
    use App\Controller\NewsController;
    use Doctrine\ORM\EntityManagerInterface;

    class HomeController extends AbstractController
    {
        public $data;
        public $news;
        public $entityManager;
        public $person;
        public $bank;
        public $account;

        public function __construct(EntityManagerInterface $entityManager) 
        {   
            $entityManager = $this->entityManager = $entityManager; 
        }

        /**
         * @Route("/", name="home")
         */
        public function index()
        {   
            $news = new NewsController($this->entityManager);
            $data = new \stdClass();
            $colors = new \stdClass();
            $person = new \stdClass();
            $bank = new \stdClass();
            $account = new \stdClass();

            $colors->primaryColors = [
                'red' => '#ff0000',
                'green' => '#00ff00',
                'blue' => '#0000ff'
            ];

            $person->name = 'John';
            $person->age = 30;
            $person->city = 'New York';

            $bank->name = 'Bank of America';
            $bank->address = '123 Main Street';
            $bank->city = 'New York';

            $account->number = '123456789';
            $account->balance = 1000;
            $account->bank = $bank;
            $account->person = $person;
                        
            $data->news = $news->getNewsHome();
            $data->colors = $colors;
            $data->account = $account;

            $token = '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';
            $curlAddress = 'https://api.openai.com/v1/images/generations';
            

            $request = array(
                'prompt' => 'A ENTIRE CLOCK ON 50% OF THE IMAGE',
                'n' => 2,
                'size' => '256x256'
            );

            $data_string = json_encode($request);

            $ch = curl_init($curlAddress);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $token,
                    'Content-Length: ' . strlen($data_string))
            );

            $data->result = json_decode(str_replace("\n", "", curl_exec($ch)), true);

            curl_close($ch);

            return $this->render('index.html.twig', [
                'data' => $data
            ]);
        }
    }