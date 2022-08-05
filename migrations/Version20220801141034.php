<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220801141034 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE statistic ADD product_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE statistic ADD CONSTRAINT FK_649B469C4584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_649B469C4584665A ON statistic (product_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE statistic DROP CONSTRAINT FK_649B469C4584665A');
        $this->addSql('DROP INDEX IDX_649B469C4584665A');
        $this->addSql('ALTER TABLE statistic DROP product_id');
    }
}
