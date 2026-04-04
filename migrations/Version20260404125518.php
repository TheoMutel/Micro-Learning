<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260404125518 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "user" RENAME TO app_user');
        $this->addSql('ALTER INDEX uniq_8d93d649e7927c74 RENAME TO UNIQ_USER_EMAIL');
        $this->addSql('ALTER TABLE tutorial DROP CONSTRAINT fk_c66bffe9f675f31b');
        $this->addSql('ALTER TABLE tutorial ADD CONSTRAINT FK_C66BFFE9F675F31B FOREIGN KEY (author_id) REFERENCES app_user (id) NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tutorial DROP CONSTRAINT FK_C66BFFE9F675F31B');
        $this->addSql('ALTER INDEX UNIQ_USER_EMAIL RENAME TO uniq_8d93d649e7927c74');
        $this->addSql('ALTER TABLE app_user RENAME TO "user"');
        $this->addSql('ALTER TABLE tutorial ADD CONSTRAINT fk_c66bffe9f675f31b FOREIGN KEY (author_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
