<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260524183023 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE entry_point_availability (id INT AUTO_INCREMENT NOT NULL, entry_point_id INT NOT NULL, monitored_date_id INT NOT NULL, available_count INT NOT NULL, last_scraped_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_86B26128AE6F2EEA (entry_point_id), INDEX IDX_86B261288BA0DB59 (monitored_date_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE monitored_date (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_C09AFD7DA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE entry_point_availability ADD CONSTRAINT FK_86B26128AE6F2EEA FOREIGN KEY (entry_point_id) REFERENCES entry_point (id)');
        $this->addSql('ALTER TABLE entry_point_availability ADD CONSTRAINT FK_86B261288BA0DB59 FOREIGN KEY (monitored_date_id) REFERENCES monitored_date (id)');
        $this->addSql('ALTER TABLE monitored_date ADD CONSTRAINT FK_C09AFD7DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entry_point_availability DROP FOREIGN KEY FK_86B26128AE6F2EEA');
        $this->addSql('ALTER TABLE entry_point_availability DROP FOREIGN KEY FK_86B261288BA0DB59');
        $this->addSql('ALTER TABLE monitored_date DROP FOREIGN KEY FK_C09AFD7DA76ED395');
        $this->addSql('DROP TABLE entry_point_availability');
        $this->addSql('DROP TABLE monitored_date');
    }
}
