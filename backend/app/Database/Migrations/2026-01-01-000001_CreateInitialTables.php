<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInitialTables extends Migration
{
    public function up()
    {
        // Users (auth)
        $this->forge->addField([
            'id'              => ['type' => 'CHAR', 'constraint' => 36],
            'name'            => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'email'           => ['type' => 'VARCHAR', 'constraint' => 255],
            'emailVerified'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'image'           => ['type' => 'TEXT', 'null' => true],
            'stripeCustomerId' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'createdAt'       => ['type' => 'DATETIME', 'null' => true],
            'updatedAt'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('email');
        $this->forge->createTable('user', true);

        // Sessions
        $this->forge->addField([
            'id'        => ['type' => 'CHAR', 'constraint' => 36],
            'expiresAt' => ['type' => 'DATETIME'],
            'token'     => ['type' => 'VARCHAR', 'constraint' => 255],
            'createdAt' => ['type' => 'DATETIME'],
            'updatedAt' => ['type' => 'DATETIME'],
            'ipAddress' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'userAgent' => ['type' => 'TEXT', 'null' => true],
            'userId'    => ['type' => 'CHAR', 'constraint' => 36],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('token', false, true);
        $this->forge->addKey('userId');
        $this->forge->createTable('session', true);

        // Accounts
        $this->forge->addField([
            'id'                    => ['type' => 'CHAR', 'constraint' => 36],
            'accountId'             => ['type' => 'VARCHAR', 'constraint' => 255],
            'providerId'            => ['type' => 'VARCHAR', 'constraint' => 255],
            'userId'                => ['type' => 'CHAR', 'constraint' => 36],
            'accessToken'           => ['type' => 'TEXT', 'null' => true],
            'refreshToken'          => ['type' => 'TEXT', 'null' => true],
            'idToken'               => ['type' => 'TEXT', 'null' => true],
            'accessTokenExpiresAt'  => ['type' => 'DATETIME', 'null' => true],
            'refreshTokenExpiresAt' => ['type' => 'DATETIME', 'null' => true],
            'scope'                 => ['type' => 'TEXT', 'null' => true],
            'password'              => ['type' => 'TEXT', 'null' => true],
            'createdAt'             => ['type' => 'DATETIME'],
            'updatedAt'             => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('userId');
        $this->forge->createTable('account', true);

        // Verifications
        $this->forge->addField([
            'id'         => ['type' => 'CHAR', 'constraint' => 36],
            'identifier' => ['type' => 'VARCHAR', 'constraint' => 255],
            'value'      => ['type' => 'TEXT'],
            'expiresAt'  => ['type' => 'DATETIME'],
            'createdAt'  => ['type' => 'DATETIME', 'null' => true],
            'updatedAt'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('verification', true);

        // Plans
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 20],
            'displayName' => ['type' => 'VARCHAR', 'constraint' => 50],
            'price'      => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'currency'   => ['type' => 'VARCHAR', 'constraint' => 3, 'default' => 'IDR'],
            'boardLimit' => ['type' => 'INT', 'constraint' => 11, 'default' => 3],
            'memberLimit' => ['type' => 'INT', 'constraint' => 11, 'default' => 3],
            'workspaceLimit' => ['type' => 'INT', 'constraint' => 11, 'default' => 3],
            'storageLimit'   => ['type' => 'BIGINT', 'default' => 10485760],
            'features'       => ['type' => 'JSON', 'null' => true],
            'isActive'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'createdAt'      => ['type' => 'DATETIME', 'null' => true],
            'updatedAt'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('name', false, true);
        $this->forge->createTable('plans', true);

        // Workspaces
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'publicId'    => ['type' => 'VARCHAR', 'constraint' => 12],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'slug'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'description' => ['type' => 'TEXT', 'null' => true],
            'plan'        => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'free'],
            'createdBy'   => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
            'createdAt'   => ['type' => 'DATETIME', 'null' => true],
            'updatedAt'   => ['type' => 'DATETIME', 'null' => true],
            'deletedAt'   => ['type' => 'DATETIME', 'null' => true],
            'deletedBy'   => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('publicId', false, true);
        $this->forge->addKey('slug', false, true);
        $this->forge->createTable('workspace', true);

        // Workspace Members
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'publicId'    => ['type' => 'VARCHAR', 'constraint' => 12],
            'userId'      => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
            'workspaceId' => ['type' => 'INT', 'constraint' => 11],
            'createdBy'   => ['type' => 'CHAR', 'constraint' => 36],
            'createdAt'   => ['type' => 'DATETIME', 'null' => true],
            'updatedAt'   => ['type' => 'DATETIME', 'null' => true],
            'deletedAt'   => ['type' => 'DATETIME', 'null' => true],
            'deletedBy'   => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
            'role'        => ['type' => 'ENUM', 'constraint' => ['admin', 'member', 'guest'], 'default' => 'member'],
            'status'      => ['type' => 'ENUM', 'constraint' => ['active', 'invited', 'paused', 'pending'], 'default' => 'pending'],
            'email'       => ['type' => 'VARCHAR', 'constraint' => 255],
            'roleId'      => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'inviteCode'  => ['type' => 'VARCHAR', 'constraint' => 32, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('publicId', false, true);
        $this->forge->addKey('inviteCode', false, true);
        $this->forge->createTable('workspace_members', true);

        // Boards
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'publicId'    => ['type' => 'VARCHAR', 'constraint' => 12],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'slug'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'description' => ['type' => 'TEXT', 'null' => true],
            'workspaceId' => ['type' => 'INT', 'constraint' => 11],
            'visibility'  => ['type' => 'ENUM', 'constraint' => ['private', 'public'], 'default' => 'private'],
            'type'        => ['type' => 'ENUM', 'constraint' => ['regular', 'template'], 'default' => 'regular'],
            'createdBy'   => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
            'createdAt'   => ['type' => 'DATETIME', 'null' => true],
            'updatedAt'   => ['type' => 'DATETIME', 'null' => true],
            'deletedAt'   => ['type' => 'DATETIME', 'null' => true],
            'deletedBy'   => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('publicId', false, true);
        $this->forge->createTable('board', true);

        // Lists
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'publicId'    => ['type' => 'VARCHAR', 'constraint' => 12],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'index'       => ['type' => 'INT', 'constraint' => 11],
            'createdBy'   => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
            'createdAt'   => ['type' => 'DATETIME', 'null' => true],
            'updatedAt'   => ['type' => 'DATETIME', 'null' => true],
            'deletedAt'   => ['type' => 'DATETIME', 'null' => true],
            'deletedBy'   => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
            'boardId'     => ['type' => 'INT', 'constraint' => 11],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('publicId', false, true);
        $this->forge->createTable('list', true);

        // Cards
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'publicId'    => ['type' => 'VARCHAR', 'constraint' => 12],
            'title'       => ['type' => 'TEXT'],
            'description' => ['type' => 'TEXT', 'null' => true],
            'index'       => ['type' => 'INT', 'constraint' => 11],
            'createdBy'   => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
            'createdAt'   => ['type' => 'DATETIME', 'null' => true],
            'updatedAt'   => ['type' => 'DATETIME', 'null' => true],
            'deletedAt'   => ['type' => 'DATETIME', 'null' => true],
            'deletedBy'   => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
            'listId'      => ['type' => 'INT', 'constraint' => 11],
            'dueDate'     => ['type' => 'DATETIME', 'null' => true],
            'cardNumber'  => ['type' => 'INT', 'constraint' => 11, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('publicId', false, true);
        $this->forge->createTable('card', true);

        // Labels
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'publicId'    => ['type' => 'VARCHAR', 'constraint' => 12],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'colourCode'  => ['type' => 'VARCHAR', 'constraint' => 7, 'null' => true],
            'createdBy'   => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
            'createdAt'   => ['type' => 'DATETIME', 'null' => true],
            'updatedAt'   => ['type' => 'DATETIME', 'null' => true],
            'boardId'     => ['type' => 'INT', 'constraint' => 11],
            'deletedAt'   => ['type' => 'DATETIME', 'null' => true],
            'deletedBy'   => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('publicId', false, true);
        $this->forge->createTable('label', true);

        // Card Labels (pivot)
        $this->forge->addField([
            'cardId'  => ['type' => 'INT', 'constraint' => 11],
            'labelId' => ['type' => 'INT', 'constraint' => 11],
        ]);
        $this->forge->addKey(['cardId', 'labelId'], false, true);
        $this->forge->createTable('_card_labels', true);

        // Card Members (pivot)
        $this->forge->addField([
            'cardId'            => ['type' => 'INT', 'constraint' => 11],
            'workspaceMemberId' => ['type' => 'INT', 'constraint' => 11],
        ]);
        $this->forge->addKey(['cardId', 'workspaceMemberId'], false, true);
        $this->forge->createTable('_card_workspace_members', true);

        // Checklists
        $this->forge->addField([
            'id'        => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'publicId'  => ['type' => 'VARCHAR', 'constraint' => 12],
            'name'      => ['type' => 'VARCHAR', 'constraint' => 255],
            'index'     => ['type' => 'INT', 'constraint' => 11],
            'cardId'    => ['type' => 'INT', 'constraint' => 11],
            'createdBy' => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
            'createdAt' => ['type' => 'DATETIME', 'null' => true],
            'updatedAt' => ['type' => 'DATETIME', 'null' => true],
            'deletedAt' => ['type' => 'DATETIME', 'null' => true],
            'deletedBy' => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('publicId', false, true);
        $this->forge->createTable('card_checklist', true);

        // Checklist Items
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'publicId'    => ['type' => 'VARCHAR', 'constraint' => 12],
            'title'       => ['type' => 'VARCHAR', 'constraint' => 500],
            'completed'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'index'       => ['type' => 'INT', 'constraint' => 11],
            'checklistId' => ['type' => 'INT', 'constraint' => 11],
            'createdBy'   => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
            'createdAt'   => ['type' => 'DATETIME', 'null' => true],
            'updatedAt'   => ['type' => 'DATETIME', 'null' => true],
            'deletedAt'   => ['type' => 'DATETIME', 'null' => true],
            'deletedBy'   => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('publicId', false, true);
        $this->forge->createTable('card_checklist_item', true);

        // Comments
        $this->forge->addField([
            'id'        => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'publicId'  => ['type' => 'VARCHAR', 'constraint' => 12],
            'comment'   => ['type' => 'TEXT'],
            'cardId'    => ['type' => 'INT', 'constraint' => 11],
            'createdBy' => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
            'createdAt' => ['type' => 'DATETIME', 'null' => true],
            'updatedAt' => ['type' => 'DATETIME', 'null' => true],
            'deletedAt' => ['type' => 'DATETIME', 'null' => true],
            'deletedBy' => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('publicId', false, true);
        $this->forge->createTable('card_comments', true);

        // Activities
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'publicId'          => ['type' => 'VARCHAR', 'constraint' => 12],
            'type'              => ['type' => 'VARCHAR', 'constraint' => 50],
            'cardId'            => ['type' => 'INT', 'constraint' => 11],
            'fromIndex'         => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'toIndex'           => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'fromListId'        => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'toListId'          => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'labelId'           => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'workspaceMemberId' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'fromTitle'         => ['type' => 'TEXT', 'null' => true],
            'toTitle'           => ['type' => 'TEXT', 'null' => true],
            'fromDescription'   => ['type' => 'TEXT', 'null' => true],
            'toDescription'     => ['type' => 'TEXT', 'null' => true],
            'createdBy'         => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
            'createdAt'         => ['type' => 'DATETIME', 'null' => true],
            'commentId'         => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'fromComment'       => ['type' => 'TEXT', 'null' => true],
            'toComment'         => ['type' => 'TEXT', 'null' => true],
            'sourceBoardId'     => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'fromDueDate'       => ['type' => 'DATETIME', 'null' => true],
            'toDueDate'         => ['type' => 'DATETIME', 'null' => true],
            'attachmentId'      => ['type' => 'INT', 'constraint' => 11, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('publicId', false, true);
        $this->forge->createTable('card_activity', true);

        // Attachments
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'publicId'         => ['type' => 'VARCHAR', 'constraint' => 12],
            'cardId'           => ['type' => 'INT', 'constraint' => 11],
            'filename'         => ['type' => 'VARCHAR', 'constraint' => 255],
            'originalFilename' => ['type' => 'VARCHAR', 'constraint' => 255],
            'contentType'      => ['type' => 'VARCHAR', 'constraint' => 100],
            'size'             => ['type' => 'INT', 'constraint' => 11],
            'path'             => ['type' => 'VARCHAR', 'constraint' => 500],
            'createdBy'        => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
            'createdAt'        => ['type' => 'DATETIME', 'null' => true],
            'deletedAt'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('publicId', false, true);
        $this->forge->createTable('card_attachment', true);

        // API Keys
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'publicId'    => ['type' => 'VARCHAR', 'constraint' => 12],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'keyHash'     => ['type' => 'TEXT'],
            'keyPrefix'   => ['type' => 'VARCHAR', 'constraint' => 16],
            'permissions' => ['type' => 'TEXT', 'default' => '["read"]'],
            'active'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'workspaceId' => ['type' => 'INT', 'constraint' => 11],
            'createdBy'   => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
            'createdAt'   => ['type' => 'DATETIME', 'null' => true],
            'lastUsedAt'  => ['type' => 'DATETIME', 'null' => true],
            'revokedAt'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('publicId', false, true);
        $this->forge->createTable('api_keys', true);

        // Webhooks
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'publicId'          => ['type' => 'VARCHAR', 'constraint' => 12],
            'name'              => ['type' => 'VARCHAR', 'constraint' => 255],
            'url'               => ['type' => 'TEXT'],
            'events'            => ['type' => 'TEXT', 'default' => '[]'],
            'active'            => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'workspaceId'       => ['type' => 'INT', 'constraint' => 11],
            'createdBy'         => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
            'createdAt'         => ['type' => 'DATETIME', 'null' => true],
            'lastDeliveryAt'    => ['type' => 'DATETIME', 'null' => true],
            'lastDeliveryStatus' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('publicId', false, true);
        $this->forge->createTable('webhooks', true);

        // Integrations
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'integrationId'  => ['type' => 'VARCHAR', 'constraint' => 50],
            'workspaceId'    => ['type' => 'INT', 'constraint' => 11],
            'connected'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'config'         => ['type' => 'TEXT', 'null' => true],
            'connectedBy'    => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
            'connectedAt'    => ['type' => 'DATETIME', 'null' => true],
            'updatedAt'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('workspace_integrations', true);

        // Notifications
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'publicId'    => ['type' => 'VARCHAR', 'constraint' => 12],
            'userId'      => ['type' => 'CHAR', 'constraint' => 36],
            'workspaceId' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'type'        => ['type' => 'VARCHAR', 'constraint' => 50],
            'title'       => ['type' => 'VARCHAR', 'constraint' => 255],
            'entityType'  => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'entityId'    => ['type' => 'VARCHAR', 'constraint' => 12, 'null' => true],
            'entityUrl'   => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'data'        => ['type' => 'JSON', 'null' => true],
            'read'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'createdBy'   => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
            'createdAt'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('publicId', false, true);
        $this->forge->createTable('notifications', true);

        // Subscriptions
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'publicId'       => ['type' => 'VARCHAR', 'constraint' => 12],
            'workspaceId'    => ['type' => 'INT', 'constraint' => 11],
            'plan'           => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'free'],
            'status'         => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'active'],
            'startDate'      => ['type' => 'DATETIME'],
            'endDate'        => ['type' => 'DATETIME', 'null' => true],
            'midtransOrderId' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'paymentAmount'  => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'createdAt'      => ['type' => 'DATETIME', 'null' => true],
            'updatedAt'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('publicId', false, true);
        $this->forge->addKey('midtransOrderId', false, true);
        $this->forge->createTable('subscriptions', true);
    }

    public function down()
    {
        $this->forge->dropTable('subscriptions', true);
        $this->forge->dropTable('notifications', true);
        $this->forge->dropTable('workspace_integrations', true);
        $this->forge->dropTable('webhooks', true);
        $this->forge->dropTable('api_keys', true);
        $this->forge->dropTable('card_attachment', true);
        $this->forge->dropTable('card_activity', true);
        $this->forge->dropTable('card_comments', true);
        $this->forge->dropTable('card_checklist_item', true);
        $this->forge->dropTable('card_checklist', true);
        $this->forge->dropTable('_card_workspace_members', true);
        $this->forge->dropTable('_card_labels', true);
        $this->forge->dropTable('label', true);
        $this->forge->dropTable('card', true);
        $this->forge->dropTable('list', true);
        $this->forge->dropTable('board', true);
        $this->forge->dropTable('workspace_members', true);
        $this->forge->dropTable('workspace', true);
        $this->forge->dropTable('plans', true);
        $this->forge->dropTable('verification', true);
        $this->forge->dropTable('account', true);
        $this->forge->dropTable('session', true);
        $this->forge->dropTable('user', true);
    }
}
