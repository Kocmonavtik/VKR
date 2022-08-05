<?php

namespace App\Controller\Admin;

use App\Entity\AdditionalInfo;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class AdditionalInfoCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return AdditionalInfo::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('product'),
            AssociationField::new('store'),
            ChoiceField::new('status')->setChoices([
                'Активна' => 'complete',
                'Скрыта' => 'hide'
            ]),
            DateField::new('dateUpdate'),
            TextField::new('url'),
            NumberField::new('averageRating')->hideWhenCreating()->hideWhenUpdating()
            /*image*/
        ];
    }

}
