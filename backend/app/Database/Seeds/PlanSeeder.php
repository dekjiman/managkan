<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('plans')->insertBatch([
            [
                'name'           => 'free',
                'displayName'    => 'Free',
                'price'          => 0,
                'currency'       => 'IDR',
                'boardLimit'     => 3,
                'memberLimit'    => 3,
                'workspaceLimit' => 3,
                'storageLimit'   => 10485760,
                'features'       => json_encode(['basic_boards', 'basic_members']),
                'isActive'       => 1,
                'createdAt'      => date('Y-m-d H:i:s'),
            ],
            [
                'name'           => 'team',
                'displayName'    => 'Team',
                'price'          => 50000,
                'currency'       => 'IDR',
                'boardLimit'     => 20,
                'memberLimit'    => 20,
                'workspaceLimit' => 10,
                'storageLimit'   => 104857600,
                'features'       => json_encode(['unlimited_boards', 'unlimited_members', 'api_access', 'webhooks']),
                'isActive'       => 1,
                'createdAt'      => date('Y-m-d H:i:s'),
            ],
            [
                'name'           => 'pro',
                'displayName'    => 'Professional',
                'price'          => 150000,
                'currency'       => 'IDR',
                'boardLimit'     => -1,
                'memberLimit'    => -1,
                'workspaceLimit' => -1,
                'storageLimit'   => 1073741824,
                'features'       => json_encode(['unlimited_all', 'api_access', 'webhooks', 'advanced_reports', 'priority_support']),
                'isActive'       => 1,
                'createdAt'      => date('Y-m-d H:i:s'),
            ],
        ]);
    }
}
