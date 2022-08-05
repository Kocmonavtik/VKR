<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220717133852 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE additional_info_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE application_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE category_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE comment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE manufacturer_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE product_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE property_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE property_product_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE rating_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE rating_comment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE report_comment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE report_product_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE source_goods_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE statistic_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE store_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE users_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE additional_info (id INT NOT NULL, product_id INT NOT NULL, store_id INT NOT NULL, status VARCHAR(50) NOT NULL, date_update DATE NOT NULL, price DOUBLE PRECISION NOT NULL, url VARCHAR(500) NOT NULL, average_rating DOUBLE PRECISION NOT NULL, image JSON DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E51DB7994584665A ON additional_info (product_id)');
        $this->addSql('CREATE INDEX IDX_E51DB799B092A811 ON additional_info (store_id)');
        $this->addSql('CREATE TABLE application (id INT NOT NULL, customer_id INT NOT NULL, name_store VARCHAR(100) NOT NULL, status VARCHAR(50) NOT NULL, full_name VARCHAR(255) NOT NULL, url_store VARCHAR(500) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A45BDDC19395C3F3 ON application (customer_id)');
        $this->addSql('CREATE TABLE category (id INT NOT NULL, parent_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_64C19C1727ACA70 ON category (parent_id)');
        $this->addSql('CREATE TABLE comment (id INT NOT NULL, customer_id INT NOT NULL, additional_info_id INT NOT NULL, response_id INT DEFAULT NULL, text VARCHAR(500) NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9474526C9395C3F3 ON comment (customer_id)');
        $this->addSql('CREATE INDEX IDX_9474526C5C01120C ON comment (additional_info_id)');
        $this->addSql('CREATE INDEX IDX_9474526CFBF32840 ON comment (response_id)');
        $this->addSql('CREATE TABLE manufacturer (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE product (id INT NOT NULL, manufacturer_id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D34A04ADA23B42D ON product (manufacturer_id)');
        $this->addSql('CREATE TABLE product_category (product_id INT NOT NULL, category_id INT NOT NULL, PRIMARY KEY(product_id, category_id))');
        $this->addSql('CREATE INDEX IDX_CDFC73564584665A ON product_category (product_id)');
        $this->addSql('CREATE INDEX IDX_CDFC735612469DE2 ON product_category (category_id)');
        $this->addSql('CREATE TABLE property (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE property_product (id INT NOT NULL, property_id INT NOT NULL, product_id INT NOT NULL, value VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8A874ED5549213EC ON property_product (property_id)');
        $this->addSql('CREATE INDEX IDX_8A874ED54584665A ON property_product (product_id)');
        $this->addSql('CREATE TABLE rating (id INT NOT NULL, additional_info_id INT NOT NULL, customer_id INT NOT NULL, evaluation INT NOT NULL, date DATE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D88926225C01120C ON rating (additional_info_id)');
        $this->addSql('CREATE INDEX IDX_D88926229395C3F3 ON rating (customer_id)');
        $this->addSql('CREATE TABLE rating_comment (id INT NOT NULL, customer_id INT NOT NULL, comment_id INT NOT NULL, evaluation INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7738AB149395C3F3 ON rating_comment (customer_id)');
        $this->addSql('CREATE INDEX IDX_7738AB14F8697D13 ON rating_comment (comment_id)');
        $this->addSql('CREATE TABLE report_comment (id INT NOT NULL, customer_id INT NOT NULL, comment_id INT NOT NULL, text VARCHAR(1000) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F4ED2F6C9395C3F3 ON report_comment (customer_id)');
        $this->addSql('CREATE INDEX IDX_F4ED2F6CF8697D13 ON report_comment (comment_id)');
        $this->addSql('CREATE TABLE report_product (id INT NOT NULL, additional_info_id INT NOT NULL, customer_id INT NOT NULL, text VARCHAR(1000) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B3D379AD5C01120C ON report_product (additional_info_id)');
        $this->addSql('CREATE INDEX IDX_B3D379AD9395C3F3 ON report_product (customer_id)');
        $this->addSql('CREATE TABLE source_goods (id INT NOT NULL, customer_id INT NOT NULL, store_id INT NOT NULL, url VARCHAR(500) NOT NULL, status VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9C6B62129395C3F3 ON source_goods (customer_id)');
        $this->addSql('CREATE INDEX IDX_9C6B6212B092A811 ON source_goods (store_id)');
        $this->addSql('CREATE TABLE statistic (id INT NOT NULL, additional_info_id INT NOT NULL, date_visit TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_649B469C5C01120C ON statistic (additional_info_id)');
        $this->addSql('CREATE TABLE store (id INT NOT NULL, customer_id INT NOT NULL, name_store VARCHAR(100) NOT NULL, url_store VARCHAR(500) NOT NULL, logo VARCHAR(500) NOT NULL, description VARCHAR(500) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FF5758779395C3F3 ON store (customer_id)');
        $this->addSql('CREATE TABLE users (id INT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, gender VARCHAR(6) DEFAULT NULL, name VARCHAR(50) NOT NULL, avatar VARCHAR(500) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
        $this->addSql('ALTER TABLE additional_info ADD CONSTRAINT FK_E51DB7994584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE additional_info ADD CONSTRAINT FK_E51DB799B092A811 FOREIGN KEY (store_id) REFERENCES store (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE application ADD CONSTRAINT FK_A45BDDC19395C3F3 FOREIGN KEY (customer_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C1727ACA70 FOREIGN KEY (parent_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C9395C3F3 FOREIGN KEY (customer_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C5C01120C FOREIGN KEY (additional_info_id) REFERENCES additional_info (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CFBF32840 FOREIGN KEY (response_id) REFERENCES comment (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADA23B42D FOREIGN KEY (manufacturer_id) REFERENCES manufacturer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_category ADD CONSTRAINT FK_CDFC73564584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_category ADD CONSTRAINT FK_CDFC735612469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE property_product ADD CONSTRAINT FK_8A874ED5549213EC FOREIGN KEY (property_id) REFERENCES property (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE property_product ADD CONSTRAINT FK_8A874ED54584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE rating ADD CONSTRAINT FK_D88926225C01120C FOREIGN KEY (additional_info_id) REFERENCES additional_info (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE rating ADD CONSTRAINT FK_D88926229395C3F3 FOREIGN KEY (customer_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE rating_comment ADD CONSTRAINT FK_7738AB149395C3F3 FOREIGN KEY (customer_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE rating_comment ADD CONSTRAINT FK_7738AB14F8697D13 FOREIGN KEY (comment_id) REFERENCES comment (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report_comment ADD CONSTRAINT FK_F4ED2F6C9395C3F3 FOREIGN KEY (customer_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report_comment ADD CONSTRAINT FK_F4ED2F6CF8697D13 FOREIGN KEY (comment_id) REFERENCES comment (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report_product ADD CONSTRAINT FK_B3D379AD5C01120C FOREIGN KEY (additional_info_id) REFERENCES additional_info (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE report_product ADD CONSTRAINT FK_B3D379AD9395C3F3 FOREIGN KEY (customer_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE source_goods ADD CONSTRAINT FK_9C6B62129395C3F3 FOREIGN KEY (customer_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE source_goods ADD CONSTRAINT FK_9C6B6212B092A811 FOREIGN KEY (store_id) REFERENCES store (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE statistic ADD CONSTRAINT FK_649B469C5C01120C FOREIGN KEY (additional_info_id) REFERENCES additional_info (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE store ADD CONSTRAINT FK_FF5758779395C3F3 FOREIGN KEY (customer_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE comment DROP CONSTRAINT FK_9474526C5C01120C');
        $this->addSql('ALTER TABLE rating DROP CONSTRAINT FK_D88926225C01120C');
        $this->addSql('ALTER TABLE report_product DROP CONSTRAINT FK_B3D379AD5C01120C');
        $this->addSql('ALTER TABLE statistic DROP CONSTRAINT FK_649B469C5C01120C');
        $this->addSql('ALTER TABLE category DROP CONSTRAINT FK_64C19C1727ACA70');
        $this->addSql('ALTER TABLE product_category DROP CONSTRAINT FK_CDFC735612469DE2');
        $this->addSql('ALTER TABLE comment DROP CONSTRAINT FK_9474526CFBF32840');
        $this->addSql('ALTER TABLE rating_comment DROP CONSTRAINT FK_7738AB14F8697D13');
        $this->addSql('ALTER TABLE report_comment DROP CONSTRAINT FK_F4ED2F6CF8697D13');
        $this->addSql('ALTER TABLE product DROP CONSTRAINT FK_D34A04ADA23B42D');
        $this->addSql('ALTER TABLE additional_info DROP CONSTRAINT FK_E51DB7994584665A');
        $this->addSql('ALTER TABLE product_category DROP CONSTRAINT FK_CDFC73564584665A');
        $this->addSql('ALTER TABLE property_product DROP CONSTRAINT FK_8A874ED54584665A');
        $this->addSql('ALTER TABLE property_product DROP CONSTRAINT FK_8A874ED5549213EC');
        $this->addSql('ALTER TABLE additional_info DROP CONSTRAINT FK_E51DB799B092A811');
        $this->addSql('ALTER TABLE source_goods DROP CONSTRAINT FK_9C6B6212B092A811');
        $this->addSql('ALTER TABLE application DROP CONSTRAINT FK_A45BDDC19395C3F3');
        $this->addSql('ALTER TABLE comment DROP CONSTRAINT FK_9474526C9395C3F3');
        $this->addSql('ALTER TABLE rating DROP CONSTRAINT FK_D88926229395C3F3');
        $this->addSql('ALTER TABLE rating_comment DROP CONSTRAINT FK_7738AB149395C3F3');
        $this->addSql('ALTER TABLE report_comment DROP CONSTRAINT FK_F4ED2F6C9395C3F3');
        $this->addSql('ALTER TABLE report_product DROP CONSTRAINT FK_B3D379AD9395C3F3');
        $this->addSql('ALTER TABLE source_goods DROP CONSTRAINT FK_9C6B62129395C3F3');
        $this->addSql('ALTER TABLE store DROP CONSTRAINT FK_FF5758779395C3F3');
        $this->addSql('DROP SEQUENCE additional_info_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE application_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE category_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE comment_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE manufacturer_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE product_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE property_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE property_product_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE rating_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE rating_comment_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE report_comment_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE report_product_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE source_goods_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE statistic_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE store_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE users_id_seq CASCADE');
        $this->addSql('DROP TABLE additional_info');
        $this->addSql('DROP TABLE application');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE manufacturer');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE product_category');
        $this->addSql('DROP TABLE property');
        $this->addSql('DROP TABLE property_product');
        $this->addSql('DROP TABLE rating');
        $this->addSql('DROP TABLE rating_comment');
        $this->addSql('DROP TABLE report_comment');
        $this->addSql('DROP TABLE report_product');
        $this->addSql('DROP TABLE source_goods');
        $this->addSql('DROP TABLE statistic');
        $this->addSql('DROP TABLE store');
        $this->addSql('DROP TABLE users');
    }
}
