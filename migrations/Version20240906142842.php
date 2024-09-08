<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240906142842 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE weekly_plan (id INT AUTO_INCREMENT NOT NULL, developer_id INT DEFAULT NULL, task_id INT DEFAULT NULL, start_date DATETIME NOT NULL, end_date DATETIME NOT NULL, INDEX IDX_3643E74D64DD9267 (developer_id), INDEX IDX_3643E74D8DB60186 (task_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE weekly_plan ADD CONSTRAINT FK_3643E74D64DD9267 FOREIGN KEY (developer_id) REFERENCES developer (id)');
        $this->addSql('ALTER TABLE weekly_plan ADD CONSTRAINT FK_3643E74D8DB60186 FOREIGN KEY (task_id) REFERENCES task (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE weekly_plan DROP FOREIGN KEY FK_3643E74D64DD9267');
        $this->addSql('ALTER TABLE weekly_plan DROP FOREIGN KEY FK_3643E74D8DB60186');
        $this->addSql('DROP TABLE weekly_plan');
    }
}
