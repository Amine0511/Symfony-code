<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250511075105 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE critique (id INT AUTO_INCREMENT NOT NULL, livre_id INT NOT NULL, utilisateur_id INT NOT NULL, commentaire LONGTEXT NOT NULL, note INT NOT NULL, date_creation DATETIME NOT NULL, INDEX IDX_1F95032437D925CB (livre_id), INDEX IDX_1F950324FB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE critique ADD CONSTRAINT FK_1F95032437D925CB FOREIGN KEY (livre_id) REFERENCES livre (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE critique ADD CONSTRAINT FK_1F950324FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE critique DROP FOREIGN KEY FK_1F95032437D925CB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE critique DROP FOREIGN KEY FK_1F950324FB88E14F
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE critique
        SQL);
    }
}
