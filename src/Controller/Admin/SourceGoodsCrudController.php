<?php

namespace App\Controller\Admin;

use App\Entity\SourceGoods;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SourceGoodsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SourceGoods::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('customer'),
            AssociationField::new('store'),
            TextField::new('url'),
            ChoiceField::new('status')->setChoices([
                'Нет источника' => 'nothing',
                'В очереди' => 'progress',
                'Ошибка при обработке' => 'error',
                'Обработана' => 'processed'
            ])

        ];
    }

}
