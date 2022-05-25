<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AboutController extends AbstractController
{
    /**
     * @Route("/about", name="app_about")
     * @IsGranted("ROLE_USER")
     */
    public function index(): Response
    {
        //$this->denyAccessUnlessGranted('ROLE_USER');
        return $this->render('about/index.html.twig', [
            'controller_name' => 'AboutController',
        ]);

       /* {% if is_granted('ROLE_ADMIN') %}
        <li class="nav-item">
                            <a class="nav-link" href="{{ path('admin_dashboard') }}">Admin</a>
                        </li>
                        {% endif %}
       is_granted('IS_AUTHENTICATED_FULLY'):// после авторизации текущей сессии
       IS_AUTHENTICATED_REMEMBERED после ремембер ми и заходе в браузер
       */

    }
}
