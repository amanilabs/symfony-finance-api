<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251102215706 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transaction ADD owner_id INT NOT NULL');
        $this->addSql('ALTER TABLE transaction ADD transaction DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE transaction ADD string VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE transaction ADD type VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D17E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_723705D17E3C61F9 ON transaction (owner_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE transaction DROP CONSTRAINT FK_723705D17E3C61F9');
        $this->addSql('DROP INDEX IDX_723705D17E3C61F9');
        $this->addSql('ALTER TABLE transaction DROP owner_id');
        $this->addSql('ALTER TABLE transaction DROP transaction');
        $this->addSql('ALTER TABLE transaction DROP string');
        $this->addSql('ALTER TABLE transaction DROP type');
    }
}
