<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190322085621 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sf_order RENAME INDEX fk_6148ee624c3a3bb TO IDX_6148EE624C3A3BB');
        $this->addSql('ALTER TABLE product DROP position');
        $this->addSql('ALTER TABLE shipping_log DROP FOREIGN KEY FK_57C5FD8B33E1689A');
        $this->addSql('ALTER TABLE shipping_log DROP FOREIGN KEY FK_57C5FD8B676C7AF5');
        $this->addSql('ALTER TABLE shipping_log ADD CONSTRAINT FK_57C5FD8B33E1689A FOREIGN KEY (command_id) REFERENCES sf_order (id)');
        $this->addSql('ALTER TABLE shipping_log ADD CONSTRAINT FK_57C5FD8B676C7AF5 FOREIGN KEY (messenger_id) REFERENCES sf_user (id)');
        $this->addSql('ALTER TABLE sf_user CHANGE roles roles JSON NOT NULL, CHANGE date_created date_created DATETIME NOT NULL, CHANGE date_updated date_updated DATETIME NOT NULL, CHANGE state state TINYINT(1) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_88D5A8C0E7927C74 ON sf_user (email)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE product ADD position INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sf_order RENAME INDEX idx_6148ee624c3a3bb TO FK_6148EE624C3A3BB');
        $this->addSql('DROP INDEX UNIQ_88D5A8C0E7927C74 ON sf_user');
        $this->addSql('ALTER TABLE sf_user CHANGE roles roles TEXT NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE state state TINYINT(1) DEFAULT NULL, CHANGE date_created date_created DATETIME DEFAULT NULL, CHANGE date_updated date_updated DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE shipping_log DROP FOREIGN KEY FK_57C5FD8B676C7AF5');
        $this->addSql('ALTER TABLE shipping_log DROP FOREIGN KEY FK_57C5FD8B33E1689A');
        $this->addSql('ALTER TABLE shipping_log ADD CONSTRAINT FK_57C5FD8B676C7AF5 FOREIGN KEY (messenger_id) REFERENCES sf_user (id) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE shipping_log ADD CONSTRAINT FK_57C5FD8B33E1689A FOREIGN KEY (command_id) REFERENCES sf_order (id) ON UPDATE CASCADE ON DELETE CASCADE');
    }
}
