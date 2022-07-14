<?php

namespace App\Controller;

use App\Service\SearchFunctions;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    private $searchFunctions;
    public function __construct(
        SearchFunctions $searchFunctions
    )
    {
        $this->searchFunctions=$searchFunctions;
    }
    /**
     * @Route("/profile", name="app_profile")
     */
    public function index(): Response
    {
        $items = $this->searchFunctions->getCategories();
        return $this->render('profile/index.html.twig', [
            'categories' => $items,
            'controller_name' => 'ProfileController',
        ]);
    }
    /**
     * @Route("/profile/application", name="app_application")
     */
    public function application():Response
    {

    }
}
