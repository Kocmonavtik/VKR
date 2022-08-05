<?php

namespace App\Controller\Admin;

use App\Entity\ReportProduct;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ReportProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ReportProduct::class;
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
