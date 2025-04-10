<?php

declare(strict_types=1);

namespace docker;

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
        $clientTable = $schema->createTable('client');
        $clientTable->addColumn('id', 'integer', ['autoincrement' => true]);
        $clientTable->addColumn('first_name', 'string', ['length' => 255]);
        $clientTable->addColumn('last_name', 'string', ['length' => 255]);
        $clientTable->addColumn('email', 'string', ['length' => 255]);
        $clientTable->addColumn('created_at', 'datetime_immutable');
        $clientTable->addColumn('updated_at', 'datetime_immutable');
        $clientTable->setPrimaryKey(['id']);
        $clientTable->addUniqueIndex(['email']);

        $accountTable = $schema->createTable('account');
        $accountTable->addColumn('id', 'integer', ['autoincrement' => true]);
        $accountTable->addColumn('client_id', 'integer');
        $accountTable->addColumn('number', 'string', ['length' => 255]);
        $accountTable->addColumn('currency', 'integer');
        $accountTable->addColumn('balance', 'decimal', ['precision' => 15, 'scale' => 2]);
        $accountTable->addColumn('created_at', 'datetime_immutable');
        $accountTable->addColumn('updated_at', 'datetime_immutable');
        $accountTable->setPrimaryKey(['id']);
        $accountTable->addIndex(['client_id']);
        $accountTable->addUniqueIndex(['number']);
        $accountTable->addForeignKeyConstraint('client', ['client_id'], ['id']);

        $transactionTable = $schema->createTable('transaction');
        $transactionTable->addColumn('id', 'integer', ['autoincrement' => true]);
        $transactionTable->addColumn('from_account_id', 'integer');
        $transactionTable->addColumn('to_account_id', 'integer');
        $transactionTable->addColumn('from_amount', 'decimal', ['precision' => 15, 'scale' => 2]);
        $transactionTable->addColumn('to_amount', 'decimal', ['precision' => 15, 'scale' => 2]);
        $transactionTable->addColumn('exchange_rate', 'decimal', ['precision' => 15, 'scale' => 15]);
        $transactionTable->addColumn('reference', 'string', ['length' => 255]);
        $transactionTable->addColumn('status', 'integer');
        $transactionTable->addColumn('created_at', 'datetime_immutable');
        $transactionTable->setPrimaryKey(['id']);
        $transactionTable->addIndex(['from_account_id']);
        $transactionTable->addIndex(['to_account_id']);
        $transactionTable->addUniqueIndex(['reference']);
        $transactionTable->addForeignKeyConstraint('account', ['from_account_id'], ['id']);
        $transactionTable->addForeignKeyConstraint('account', ['to_account_id'], ['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('transaction');
        $schema->dropTable('account');
        $schema->dropTable('client');
    }
}
