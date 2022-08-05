<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\RegistrationFormType;
use App\Repository\UsersRepository;
use App\Security\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Service\SearchFunctions;

class RegistrationController extends AbstractController
{
    private SearchFunctions $searchFunctions;
    public function __construct(SearchFunctions $searchFunctions)
    {
        $this->searchFunctions = $searchFunctions;
    }
    /**
     * @Route("/register", name="app_register")
     */
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        UserAuthenticatorInterface $userAuthenticator,
        LoginFormAuthenticator $authenticator,
        EntityManagerInterface $entityManager,
        UsersRepository $usersRepository
    ): Response {
        if ($this->getUser()) {
            return new RedirectResponse($this->generateUrl('app_product_index'));
        }
        $user = new Users();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            /*if($usersRepository->findOneBy(['email'=>$user->getEmail()])){
                return $this->render('registration/register.html.twig', [
                    'registrationForm'=> $form->createView(),
                ])
            }*/
            $user->setAvatar('avatar/img.png');
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }
        $items = $this->searchFunctions->getCategories();

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'categories' => $items,
        ]);
    }
}
