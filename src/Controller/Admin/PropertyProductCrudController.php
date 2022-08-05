<?php

namespace App\Controller\Admin;

use App\Entity\PropertyProduct;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PropertyProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PropertyProduct::class;
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
