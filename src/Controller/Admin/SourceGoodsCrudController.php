<?php

namespace App\Controller\Admin;

use App\Entity\SourceGoods;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class SourceGoodsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SourceGoods::class;
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
