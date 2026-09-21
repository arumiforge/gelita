<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

class AddLevelMediaForeignKeys extends GelitaMigration
{
    private const COLUMNS = ['map_media_id', 'background_media_id', 'badge_media_id'];

    public function up(): void
    {
        foreach (self::COLUMNS as $column) {
            $this->forge->addForeignKey($column, 'media_assets', 'id', '', 'SET NULL');
        }

        $this->forge->processIndexes('levels');
    }

    public function down(): void
    {
        foreach (self::COLUMNS as $column) {
            $name = 'levels_' . $column . '_foreign';
            $this->forge->dropForeignKey('levels', $name);
            // index implisit yang dibuat InnoDB untuk FK
            $this->db->query('ALTER TABLE `levels` DROP INDEX `' . $name . '`');
        }
    }
}
