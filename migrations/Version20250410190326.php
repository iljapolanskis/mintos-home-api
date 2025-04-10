<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250410190326 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add createdAt & updatedAt';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
        ALTER TABLE user 
            ADD created_at DATETIME COMMENT '(DC2Type:datetime_immutable)',
            ADD updated_at DATETIME COMMENT '(DC2Type:datetime_immutable)'
        SQL
        );

        $this->addSql(<<<'SQL'
            UPDATE user SET created_at = NOW(), updated_at = NOW()
        SQL
        );

        $this->addSql(<<<'SQL'
            ALTER TABLE user 
                CHANGE created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                CHANGE updated_at updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP created_at, DROP updated_at
        SQL
        );
    }
}
