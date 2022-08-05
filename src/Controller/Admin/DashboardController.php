<?php

namespace App\Controller\Admin;

use App\Entity\AdditionalInfo;
use App\Entity\Application;
use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Manufacturer;
use App\Entity\Product;
use App\Entity\Property;
use App\Entity\PropertyProduct;
use App\Entity\Rating;
use App\Entity\ReportComment;
use App\Entity\ReportProduct;
use App\Entity\SourceGoods;
use App\Entity\Statistic;
use App\Entity\Store;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Users;

class DashboardController extends AbstractDashboardController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
       /* return parent::index();*/
        $routeBuilder = $this->container->get(AdminUrlGenerator::class);
        $url = $routeBuilder->setController(UsersCrudController::class)->generateUrl();
        return $this->redirect($url);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('App');
    }

    public function configureMenuItems(): iterable
    {
       /* yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');*/
        yield MenuItem::linkToRoute('Back to the website', 'fas fa-home', 'app_product_index');
        yield MenuItem::linkToCrud('Пользователи', 'fas fa-map-market-alt', Users::class);
        yield MenuItem::linkToCrud('Товары', 'fas fa-map-market-alt', Product::class);
        yield MenuItem::linkToCrud('Характеристики', 'fas fa-map-market-alt', Property::class);
        yield MenuItem::linkToCrud('Значения характеристик', 'fas fa-map-market-alt', PropertyProduct::class);
        yield MenuItem::linkToCrud('Производители', 'fas fa-map-market-alt', Manufacturer::class);
        yield MenuItem::linkToCrud('Категории', 'fas fa-map-market-alt', Category::class);
        yield MenuItem::linkToCrud('Предложения', 'fas fa-map-market-alt', AdditionalInfo::class);
        yield MenuItem::linkToCrud('Магазины', 'fas fa-map-market-alt', Store::class);
        yield MenuItem::linkToCrud('Рейтинги', 'fas fa-map-market-alt', Rating::class);
        yield MenuItem::linkToCrud('Комментарии', 'fas fa-map-market-alt', Comment::class);
        yield MenuItem::linkToCrud('Жалобы на отзывы', 'fas fa-map-market-alt', ReportComment::class);
        yield MenuItem::linkToCrud('Жалобы на продукты', 'fas fa-map-market-alt', ReportProduct::class);
        yield MenuItem::linkToCrud('Заявки', 'fas fa-map-market-alt', Application::class);
        yield MenuItem::linkToCrud('Источники данных', 'fas fa-map-market-alt', SourceGoods::class);
        yield MenuItem::linkToCrud('Статистика', 'fas fa-map-market-alt', Statistic::class);

        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
    }
}
