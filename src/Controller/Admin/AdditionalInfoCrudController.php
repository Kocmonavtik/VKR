<?php

namespace App\Controller\Admin;

use App\Entity\AdditionalInfo;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class AdditionalInfoCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return AdditionalInfo::class;
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
