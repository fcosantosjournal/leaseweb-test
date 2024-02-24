<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Models\ServerModel;
use Symfony\Component\HttpFoundation\Request;

class ServersController extends AbstractController
{   
  private $serverModel;
  private $servers;
  private $filters;
  private $filterBy;


  public function __construct(ServerModel $serverModel)
  {
      $this->serverModel = $serverModel;
  }

  /**
   * @Route("/servers", name="servers")
   */
  public function index(): Response
  { 
    $this->servers = $this->serverModel->getServers();
    $this->filters = $this->serverModel->getFiltersFromServers($this->servers);

    return $this->render('servers.html.twig', [
        'servers' => $this->servers,
        'filters' => $this->filters
    ]);
  }

  /**
   * @Route("/filtered-servers", name="filtered-servers")
   */
  public function dataFiltered(Request $request): Response
  {   
    $this->filterBy = $request->request->all();
    
    $this->servers = $this->serverModel->getServers();
    $filters = $this->serverModel->getFiltersFromServers($this->servers);
    $this->servers = $this->serverModel->applyFilters($this->servers, $this->filterBy);

    return $this->render('filtered-servers.html.twig', [
        'filterBy' => $this->filterBy,
        'servers' => $this->servers,
        'filters' => $filters
    ]);
  }

  /**
   * @Route("/api/filter-servers", name="api-filter-servers", methods={"POST"})
   */
  public function apiFilterServers(Request $request): Response
  {   
    $this->filterBy = $request->request->all();
    
    $this->servers = $this->serverModel->getServers();
    $this->servers = $this->serverModel->applyFilters($this->servers, $this->filterBy);

    return $this->json($this->servers);
  }
}
