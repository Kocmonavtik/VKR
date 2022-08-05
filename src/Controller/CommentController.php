<?php

namespace App\Controller;

use App\Entity\AdditionalInfo;
use App\Entity\Comment;
use App\Entity\Rating;
use App\Entity\ReportComment;
use App\Entity\ReportProduct;
use App\Entity\Users;
use App\Form\CommentType;
use App\Repository\AdditionalInfoRepository;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use App\Repository\ProductRepository;
use App\Repository\PropertyProductRepository;
use App\Repository\RatingRepository;
use App\Service\SearchFunctions;
use App\Service\ServiceRepository;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/comment")
 */
class CommentController extends AbstractController
{
    private AdditionalInfoRepository $additionalInfoRepository;
    private ServiceRepository $serviceRepository;
    private ManagerRegistry $doctrine;
    private RatingRepository $ratingRepository;

    public function __construct(
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        PaginatorInterface $paginator,
        AdditionalInfoRepository $additionalInfoRepository,
        SearchFunctions $searchFunctions,
        PropertyProductRepository $propertyProductRepository,
        ServiceRepository $serviceRepository,
        ManagerRegistry $doctrine,
        RatingRepository $ratingRepository
    ) {
        $this->additionalInfoRepository = $additionalInfoRepository;
        $this->serviceRepository = $serviceRepository;
        $this->doctrine = $doctrine;
        $this->ratingRepository = $ratingRepository;
    }


    /**
     * @Route("/edit", name="app_comment_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request): JsonResponse
    {
        $commentId = $request->query->get('id');
        $offerId = $request->query->get('offer');
      /*  $rating = $request->query->get('rating');*/
        $text = $request->query->get('text');
        $ratingId = $request->query->get('ratingId');

        $commentManager = $this->doctrine->getManager();
        $comment = $commentManager->getRepository(Comment::class)->find((int) $commentId);
        $rating = $commentManager->getRepository(Rating::class)->find((int) $ratingId);
        $offerNew = $this->additionalInfoRepository->find((int) $offerId);
        $offerOld = $rating->getAdditionalInfo();


        if (!$comment) {
            $result = 'Ошибка при обновлении комментария';
        } else {
            $rating->setAdditionalInfo($offerNew);
            $comment->setDate(new \DateTime('now'));
            $comment->setText($text);
            $comment->setAdditionalInfo($offerNew);
            $commentManager->flush();
            $offerNew->setAverageRating($this->additionalInfoRepository->recalcRating($offerNew));
            $offerOld->setAverageRating($this->additionalInfoRepository->recalcRating($offerOld));

            $commentManager->persist($offerNew);
            $commentManager->persist($offerOld);
            $commentManager->flush();

            $result = 200;
        }
        return $this->json([
            'result' => $result,
            'oldOfferId' => $offerOld->getId(),
            'ratingNew' => $offerNew->getAverageRating(),
            'ratingOld' => $offerOld->getAverageRating()

        ]);
    }
    /**
     * @Route("/editRating", name="app_rating_edit", methods={"GET", "POST"})
     */
    public function ratingEdit(Request $request): JsonResponse
    {
        $ratingId = $request->query->get('ratingId');
        $value = $request->query->get('value');
        $offerId = $request->query->get('offerId');
        $commentId = $request->query->get('commentId');
        $manager = $this->doctrine->getManager();
        $rating = $manager->getRepository(Rating::class)->find((int) $ratingId);
      /*  $offer = $this->additionalInfoRepository->find((integer) $offerId);*/
     /*   $offerManager = $this->doctrine->getManager();*/
        //$offerNew = $manager->getRepository(AdditionalInfo::class)->find((int) $offerId);
        $offerNew = $this->additionalInfoRepository->find((int) $offerId);
        $offerOld = $rating->getAdditionalInfo();
        $comment = $manager->getRepository(Comment::class)->find((int) $commentId);

        if (!$rating || !$offerNew) {
            $result = '400';
        } else {
            $rating->setAdditionalInfo($offerNew);
            $rating->setDate(new \DateTime('now'));
            $rating->setEvaluation((int) $value);
            $comment->setAdditionalInfo($offerNew);
            $manager->flush();

            $offerNew->setAverageRating($this->additionalInfoRepository->recalcRating($offerNew));
            $offerOld->setAverageRating($this->additionalInfoRepository->recalcRating($offerOld));
            $manager->persist($offerNew);
            $manager->persist($offerOld);
            $manager->flush();

            $result = 200;
        }
        return $this->json([
            'result' => $result,
            'oldOfferId' => $offerOld->getId(),
            'ratingNew' => $offerNew->getAverageRating(),
            'ratingOld' => $offerOld->getAverageRating(),
        ]);
    }

    /**
     * @Route("/new", name="app_comment_new", methods={"GET", "POST"})
     */
    public function new(Request $request): JsonResponse
    {
        $value = $request->query->get('value');
        $text = $request->query->get('text');
        $offerId = $request->query->get('offerId');
        $manager = $this->doctrine->getManager();
        $rating = new Rating();
        $offer = $manager->getRepository(AdditionalInfo::class)->find((int) $offerId);

        $rating->setAdditionalInfo($offer);
        $rating->setEvaluation((int)$value);
        $rating->setDate(new \DateTime('now'));
        $rating->setCustomer($this->getUser());

        $comment = new Comment();
        $comment->setCustomer($this->getUser());
        $comment->setDate(new \DateTime('now'));
        $comment->setAdditionalInfo($offer);
        $comment->setText($text);
        $manager->persist($rating);
        $manager->persist($comment);
        $manager->flush();
        $offer->setAverageRating($this->additionalInfoRepository->recalcRating($offer));
        $manager->flush();
       /* $user = new Users();
        $user->getAvatar()*/
        $date = $comment->getDate();
        $dateFormat = $date->format('d-m-Y, H:i');


        return $this->json([
            'commentId' => $comment->getId(),
            'ratingId' => $rating->getId(),
            'avgRating' => $offer->getAverageRating(),
            'comment' => [
                'id' => $comment->getId(),
                'date' => $dateFormat,
                'text' => $comment->getText(),
                'name' => $this->getUser()->getName(),
                'avatar' => $this->getUser()->getAvatar(),
            ]
        ]);
    }
    /**
     * @Route("/response/new", name="app_response_new", methods={"GET", "POST"})
     */
    public function newResponse(Request $request): JsonResponse
    {
        $id = $request->query->get('id');
        $text = $request->query->get('text');
        $manager = $this->doctrine->getManager();
        $response = new Comment();
        $originalComment = $manager->getRepository(CommentRepository::class)->find((int) $id);
        $response->setCustomer($this->getUser())
            ->setText($text)
            ->setDate(new \DateTime('now'))
            ->setResponse($originalComment)
            ->setAdditionalInfo($originalComment->getAdditionalInfo());
        $date = $response->getDate();
        $dateFormat = $date->format('d-m-Y, H:i');
        $manager->persist($response);
        $manager->flush();


        return $this->json([
            'avatar' => $this->getUser()->getAvatar(),
            'name' => $this->getUser()->getName(),
            'date' => $dateFormat,
            'id' => $response->getId()
        ]);
    }
    /**
     * @Route("/sendReportComment", name="app_report_comment", methods={"GET", "POST"})
     */
    public function sendReportComment(Request $request): JsonResponse
    {
        $manager = $this->doctrine->getManager();
        $reportComment = new ReportComment();
        $id = $request->query->get('id');
        $text = $request->query->get('text');
        $comment = $manager->getRepository(Comment::class)->find((int) $id);
        $reportComment->setText($text)
            ->setCustomer($this->getUser())
            ->setComment($comment);
        $manager->persist($reportComment);
        $manager->flush();

        return $this->json([
            'result' => 200,
            'text' => 'Жалоба успешно отправлена'
        ]);
    }
    /**
     * @Route("/sendReportOffer", name="app_report_offer", methods={"GET", "POST"})
     */
    public function sendReportOffer(Request $request): JsonResponse
    {
        $manager = $this->doctrine->getManager();
        $reportOffer = new ReportProduct();
        $id = $request->query->get('id');
        $text = $request->query->get('text');
        $offer = $manager->getRepository(AdditionalInfo::class)->find((int) $id);
        $reportOffer->setText($text)
            ->setCustomer($this->getUser())
            ->setAdditionalInfo($offer);
        $manager->persist($reportOffer);
        $manager->flush();

        return $this->json([
            'result' => 200,
            'text' => 'Жалоба успешно отправлена'
        ]);
    }
}
