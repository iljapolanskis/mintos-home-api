<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250410130142 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create initial schema for API tokens and users';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
        CREATE TABLE api_token (id INT AUTO_INCREMENT NOT NULL,
             user_id INT NOT NULL,
             token VARCHAR(255) NOT NULL,
             expires_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
             created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
             UNIQUE INDEX UNIQ_7BA2F5EB5F37A13B (token),
             INDEX IDX_7BA2F5EBA76ED395 (user_id),
             PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL
        );
        $this->addSql(<<<'SQL'
        CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL,
             client_id INT DEFAULT NULL,
             email VARCHAR(255) NOT NULL,
             password VARCHAR(255) NOT NULL,
             roles JSON NOT NULL,
             UNIQUE INDEX UNIQ_8D93D64919EB6921 (client_id),
             PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE api_token ADD CONSTRAINT FK_7BA2F5EBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD CONSTRAINT FK_8D93D64919EB6921 FOREIGN KEY (client_id) REFERENCES client (id)
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE api_token DROP FOREIGN KEY FK_7BA2F5EBA76ED395
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP FOREIGN KEY FK_8D93D64919EB6921
        SQL
        );
        $this->addSql(<<<'SQL'
            DROP TABLE api_token
        SQL
        );
        $this->addSql(<<<'SQL'
            DROP TABLE user
        SQL
        );
    }
}
