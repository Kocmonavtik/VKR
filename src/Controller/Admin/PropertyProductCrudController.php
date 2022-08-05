<?php

namespace App\Controller\Admin;

use App\Entity\PropertyProduct;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PropertyProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PropertyProduct::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
           AssociationField::new('property'),
            AssociationField::new('product'),
            TextField::new('value')
        ];
    }

}
