<?php

namespace App\Controller\Admin;

use App\Entity\ReportProduct;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ReportProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ReportProduct::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('customer'),
            AssociationField::new('additionalInfo'),
            TextField::new('text')
        ];
    }
}
