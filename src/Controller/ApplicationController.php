<?php

namespace App\Controller;

use App\Entity\Application;
use App\Entity\SourceGoods;
use App\Entity\Store;
use App\Form\ApplicationType;
use App\Repository\ApplicationRepository;
use App\Service\SearchFunctions;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use function MongoDB\Driver\Monitoring\removeSubscriber;

/**
 * @Route("/application")
 */
class ApplicationController extends AbstractController
{
    private $searchFunctions;
    private ManagerRegistry $doctrine;
    public function __construct(
        SearchFunctions $searchFunctions,
        ManagerRegistry $doctrine
    ) {
        $this->searchFunctions = $searchFunctions;
        $this->doctrine = $doctrine;
    }

    /**
     * @Route("/", name="app_application_index", methods={"GET"})
     */
    public function index(ApplicationRepository $applicationRepository): Response
    {
        $items = $this->searchFunctions->getCategories();
        return $this->render('application/index.html.twig', [
            'applications' => $applicationRepository->findAll(),
            'categories' => $items
        ]);
    }

    /**
     * @Route("/new", name="app_application_new", methods={"GET", "POST"})
     */
    public function new(Request $request, ApplicationRepository $applicationRepository): Response
    {
        $items = $this->searchFunctions->getCategories();
        $application = new Application();
        $form = $this->createForm(ApplicationType::class, $application);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $application->setCustomer($this->getUser());
            $applicationRepository->add($application, true);

            return $this->redirectToRoute('app_application_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('application/new.html.twig', [
            'application' => $application,
            'form' => $form,
            'categories' => $items
        ]);
    }
    /**
     * @Route("/accept", name="app_application_accept", methods={"GET", "POST"})
     */
    public function acceptApplication(Request $request): JsonResponse
    {
        try {
            $id = $request->query->get('id');
            $manager = $this->doctrine->getManager();
            $application = $manager->getRepository(Application::class)->find((int) $id);
            $application->setStatus('accepted');
            $store = $manager->getRepository(Store::class)->findOneBy(['nameStore' => $application->getNameStore()]);
            $user = $application->getCustomer();
            $roles = $user->getRoles();
            if (array_search('ROLE_ADMIN', $roles) !== false || array_search('ROLE_MODER', $roles) !== false) {
            } else {
                $user->setRoles(['ROLE_CLIENT']);
            }
            $source = new SourceGoods();
            $source->setStatus('nothing')
                ->setCustomer($user)
                ->setStore($store)
                ->setUrl('');

            $manager->persist($source);
            $manager->persist($user);
            $manager->persist($application);
            $manager->flush();
            $result = 200;
        } catch (\Exception $e) {
            $result = 'Непредвиденная ошибка';
        }
        return $this->json([
            'result' => $result,
        ]);
    }

    /**
     * @Route("/reject", name="app_application_reject", methods={"GET", "POST"})
     */
    public function rejectApplication(Request $request): JsonResponse
    {
        try {
            $id = $request->query->get('id');
            $manager = $this->doctrine->getManager();
            $application = $manager->getRepository(Application::class)->find((int) $id);
            $application->setStatus('rejected');
            $manager->persist($application);
            $manager->flush();
            $result = 200;
        } catch (\Exception $e) {
            $result = 'Непредвиденная ошибка';
        }
        return $this->json([
            'result' => $result,
        ]);
    }

    /**
     * @Route("/considered", name="app_application_considered", methods={"GET", "POST"})
     */
    public function consideredApplication(Request $request): JsonResponse
    {
        try {
            $id = $request->query->get('id');
            $manager = $this->doctrine->getManager();
            $application = $manager->getRepository(Application::class)->find((int) $id);
            $application->setStatus('consideration');
            $manager->persist($application);
            $manager->flush();
            $result = 200;
        } catch (\Exception $e) {
            $result = 'Непредвиденная ошибка';
        }
        return $this->json([
            'result' => $result,
        ]);
    }

    /**
     * @Route("/{id}", name="app_application_show", methods={"GET"})
     */
  /*  public function show(Application $application): Response
    {
        $items = $this->searchFunctions->getCategories();
        return $this->render('application/show.html.twig', [
            'application' => $application,
            'categories' => $items
        ]);
    }*/

    /**
     * @Route("/{id}/edit", name="app_application_edit", methods={"GET", "POST"})
     */
   /* public function edit(Request $request, Application $application, ApplicationRepository $applicationRepository): Response
    {
        $items = $this->searchFunctions->getCategories();
        $form = $this->createForm(ApplicationType::class, $application);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $applicationRepository->add($application, true);

            return $this->redirectToRoute('app_application_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('application/edit.html.twig', [
            'application' => $application,
            'form' => $form,
            'categories' => $items
        ]);
    }*/

    /**
     * @Route("/{id}", name="app_application_delete", methods={"POST"})
     */
 /*   public function delete(Request $request, Application $application, ApplicationRepository $applicationRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $application->getId(), $request->request->get('_token'))) {
            $applicationRepository->remove($application, true);
        }

        return $this->redirectToRoute('app_application_index', [], Response::HTTP_SEE_OTHER);
    }*/
}
