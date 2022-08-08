<?php

namespace App\Controller;

use App\Entity\Application;
use App\Entity\SourceGoods;
use App\Entity\Store;
use App\Entity\Users;
use App\Repository\UsersRepository;
use App\Service\SearchFunctions;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    private const UPLOAD_DIR = __DIR__ . '/../../public/upload/avatar';
    private const UPLOAD_DIR_LOGO = __DIR__ . '/../../public/upload/storeLogo';
    private const DELETE_IMAGE = __DIR__ . '/../../public/upload/';
    private SearchFunctions $searchFunctions;
    private ManagerRegistry $doctrine;
    private UserPasswordHasherInterface $passwordHasher;
    public function __construct(
        SearchFunctions $searchFunctions,
        ManagerRegistry $doctrine,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->searchFunctions = $searchFunctions;
        $this->doctrine = $doctrine;
        $this->passwordHasher = $passwordHasher;
    }
    /**
     * @Route("/profile", name="app_profile")
     */
    public function index(): Response
    {
        $items = $this->searchFunctions->getCategories();
        $user = $this->getUser();
        $application = false;
        if (count($user->getApplications()) !== 0) {
            $application = true;
        }
        return $this->render('profile/index.html.twig', [
            'categories' => $items,
            'controller_name' => 'ProfileController',
            'application' => $application
        ]);
    }


    /**
     * @Route("/profile/source/change", name="app_change_source", methods={"GET", "POST"})
     */
    public function changeSource(Request $request): JsonResponse
    {
        try {
            $id = $request->query->get('id');
            $url = $request->query->get('url');
            $manager = $this->doctrine->getManager();
            $source = $manager->getRepository(SourceGoods::class)->find((int) $id);
            $source->setStatus('progress')
                ->setUrl($url);
            $manager->persist($source);
            $manager->flush();
            $result = 200;
        } catch (\Exception $e) {
            $result = 'Непредвиденная ошибка';
        }
        return $this->json([
            'result' => $result
        ]);
    }

    /**
     * @Route("/profile/application/send", name="app_send_application", methods={"GET", "POST"})
     */
    public function sendApplication(Request $request): JsonResponse
    {
        $application = new Application();
        $store = new Store();
        $user = $this->getUser();
        $elementList = $request->request->all();
        $manager = $this->doctrine->getManager();
        $storeCheck = $manager->getRepository(Store::class)->findOneBy(['nameStore' => $elementList['nameStore']]);
        if (!empty($storeCheck)) {
            return $this->json([
                'result' => 'Магазин с называнием ' . $elementList['nameStore'] . ' уже присутствует в агрегаторе',
            ]);
        }

        $application->setCustomer($this->getUser())
            ->setStatus('send')
            ->setUrlStore($elementList['urlStore'])
            ->setNameStore($elementList['nameStore'])
            ->setFullName($elementList['fullName']);
        $store->setNameStore($elementList['nameStore'])
            ->setUrlStore($elementList['urlStore'])
            ->setCustomer($this->getUser());

        $file = $request->files->get('logoFile');
        if (empty($file)) {
            return $this->json([
                'result' => 'Логотип магазина не установлен!'
            ]);
        }

        $filename = $file->getClientOriginalName();
        $formatFile = explode('.', $filename);
        $newFileName = sha1($elementList['nameStore'] . uniqid('', true)) . '.' . $formatFile[1];
        $file->move(self::UPLOAD_DIR_LOGO, $newFileName);
        $store->setLogo('storeLogo/' . $newFileName);

        $manager->persist($application);
        $manager->persist($store);
        $manager->flush();

        return  $this->json([
            'result' => 200,
            'nameStore' => $elementList['nameStore'],
            'fullName' => $elementList['fullName'],
            'url' => $elementList['urlStore'],
            'status' => 'Отправлена'
        ]);
    }

    /**
     * @Route("/profile/changePass", name="app_change_pass_profile", methods={"GET", "POST"})
     */
    public function changePass(Request $request): JsonResponse
    {
        $manager = $this->doctrine->getManager();
        $elementList = $request->request->all();
        $user = $this->getUser();
        $result = null;

        if (!$this->passwordHasher->isPasswordValid($user, $elementList['currentPass'])) {
            $result = 'Текущий пароль введен не верно!';
            return $this->json([
                'result' => $result
            ]);
        }
        if ($elementList['newPass'] != $elementList['repeatPass']) {
            $result = 'Новый пароль не совпадает';
            return $this->json([
                'result' => $result
            ]);
        }
        $manager = $this->doctrine->getManager();
        $user->setPassword($this->passwordHasher->hashPassword($user, $elementList['newPass']));
        $manager->persist($user);
        $manager->flush();
        return  $this->json([
            'result' => 200
        ]);
    }


    /**
     * @Route("/profile/save", name="app_save_profile", methods={"GET", "POST"})
     */
    public function saveProfile(Request $request): JsonResponse
    {
        $elementList = $request->request->all();
        $user = $this->getUser();
        $file = $request->files->get('file');
        //$file = $request->files->get('file')
        $manager = $this->doctrine->getManager();
        $filesystem = new Filesystem();
        $user1 = new Users();
        $user = $this->getUser();
        $user->setName($elementList['name']);
        $user->setGender($elementList['gender']);
        $email = null;
        $avatar = $user->getAvatar();
        $name = $elementList['name'];
        $gender = $elementList['gender'];
        $result = null;

        if ($user->getEmail() == $elementList['email']) {
            $email = $elementList['email'];
        } else {
            if ($manager->getRepository(Users::class)->findOneBy(['email' => $elementList['email']])) {
                $result = 'Пользователь с эл. почтой: ' . $elementList['email'] . ' уже зарегистрирован';
                return  $this->json([
                    'result' => $result,
                ]);
            }
            $user->setEmail($elementList['email']);
            $email = $elementList['email'];
        }
        if (empty($file)) {
        } else {
            $filename = $file->getClientOriginalName();
            $formatFile = explode('.', $filename);
            $newFileName = sha1($user->getEmail() . uniqid('', true)) . '.' . $formatFile[1];
            $file->move(self::UPLOAD_DIR, $newFileName);
            if ($user->getAvatar() === 'avatar/img.png') {
                $user->setAvatar('avatar/' . $newFileName);
                $avatar = $user->getAvatar();
            } else {
                $filesystem->remove(self::DELETE_IMAGE . $user->getAvatar());
                $user->setAvatar('avatar/' . $newFileName);
                $avatar = $user->getAvatar();
            }
        }
        $manager->persist($user);
        $manager->flush();

        return  $this->json([
            'email' => $email,
            'avatar' => $avatar,
            'name' => $name,
            'result' => $result,
        ]);
    }
}
