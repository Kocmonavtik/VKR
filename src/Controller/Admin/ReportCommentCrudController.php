<?php

namespace App\Controller\Admin;

use App\Entity\ReportComment;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ReportCommentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ReportComment::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('customer'),
            AssociationField::new('comment'),
            TextField::new('text')
        ];
    }

}
