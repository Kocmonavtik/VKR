<?php

namespace App\Controller\Admin;

use App\Entity\Application;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ApplicationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Application::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('customer'),
            TextField::new('fullName'),
            TextField::new('nameStore'),
            TextField::new('urlStore'),
            ChoiceField::new('status')->setChoices([
                'Отправлена' => 'send',
                'На рассмотрении' => 'consideration',
                'Отказано' => 'rejected',
                'Принята' => 'accepted'
            ])
        ];
    }

}
