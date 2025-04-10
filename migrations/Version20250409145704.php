<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250409145704 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create initial schema for clients, accounts and transactions';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
        CREATE TABLE account (id INT AUTO_INCREMENT NOT NULL,
             client_id INT NOT NULL,
             number VARCHAR(255) NOT NULL,
             currency INT NOT NULL,
             balance NUMERIC(15, 2) NOT NULL,
             created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
             updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
             UNIQUE INDEX UNIQ_7D3656A496901F54 (number),
             INDEX IDX_7D3656A419EB6921 (client_id),
             PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
        CREATE TABLE client (id INT AUTO_INCREMENT NOT NULL,
             first_name VARCHAR(255) NOT NULL,
             last_name VARCHAR(255) NOT NULL,
             created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
             updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
             PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
        CREATE TABLE transaction (id INT AUTO_INCREMENT NOT NULL,
             from_account_id INT NOT NULL,
             to_account_id INT NOT NULL,
             from_amount NUMERIC(15, 2) NOT NULL,
             to_amount NUMERIC(15, 2) NOT NULL,
             exchange_rate NUMERIC(15, 15) NOT NULL,
             reference VARCHAR(255) NOT NULL,
             created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
             status INT NOT NULL,
             UNIQUE INDEX UNIQ_723705D1AEA34913 (reference),
             INDEX IDX_723705D1B0CF99BD (from_account_id),
             INDEX IDX_723705D1BC58BDC7 (to_account_id),
             PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE account ADD CONSTRAINT FK_7D3656A419EB6921 FOREIGN KEY (client_id) REFERENCES client (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transaction ADD CONSTRAINT FK_723705D1B0CF99BD FOREIGN KEY (from_account_id) REFERENCES account (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transaction ADD CONSTRAINT FK_723705D1BC58BDC7 FOREIGN KEY (to_account_id) REFERENCES account (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE account DROP FOREIGN KEY FK_7D3656A419EB6921
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1B0CF99BD
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1BC58BDC7
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE account
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE client
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE transaction
        SQL);
    }
}
