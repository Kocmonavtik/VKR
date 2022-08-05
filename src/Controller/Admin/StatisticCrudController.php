<?php

namespace App\Controller\Admin;

use App\Entity\Statistic;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class StatisticCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Statistic::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
