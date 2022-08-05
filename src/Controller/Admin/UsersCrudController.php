<?php

namespace App\Controller\Admin;

use App\Entity\Users;
use Doctrine\DBAL\Types\TextType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UsersCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Users::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            //IdField::new('id'),
            TextField::new('email'),
            TextField::new('password')->hideOnIndex(),
            ChoiceField::new('gender')->setChoices([
                'Мужской' => 'male',
                'Женский' => 'female',
                'Не указано' => 'secret'
            ]),
            TextField::new('name'),
            TextField::new('avatar')

        ];
    }
}
