<?php

namespace App\Controller\Admin;

use App\Entity\Rating;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;

class RatingCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Rating::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('customer'),
            AssociationField::new('additionalInfo'),
            NumberField::new('evaluation')->hideWhenUpdating(),
            DateField::new('date'),
        ];
    }

}
