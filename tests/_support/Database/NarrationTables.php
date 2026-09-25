<?php

namespace Tests\Support\Database;

use CodeIgniter\Database\BaseConnection;
use Config\Database;

/**
 * Tabel ringkas (SQLite, grup `tests`) untuk uji narasi naskah: levels,
 * dialogues, media_assets, audio_assets, audit_logs, research_studies, dan
 * game_releases (keduanya dibaca layout panel). Kolom yang dibaca/ditulis kode sama dengan
 * migration MySQL. levels diisi tiga wilayah; baris dialogues diisi
 * pemanggil (StorySync).
 */
trait NarrationTables
{
    /** @var list<string> */
    private array $narrationTables = ['dialogues', 'levels', 'media_assets', 'audio_assets', 'audit_logs', 'research_studies', 'game_releases'];

    private function createNarrationTables(BaseConnection $db): void
    {
        $forge = Database::forge('tests');
        $this->dropNarrationTables();

        $forge->addField([
            'id'       => ['type' => 'INTEGER', 'auto_increment' => true],
            'sequence' => ['type' => 'INTEGER'],
            'code'     => ['type' => 'VARCHAR', 'constraint' => 30],
            'name_id'  => ['type' => 'VARCHAR', 'constraint' => 100],
            // Media wilayah: dibaca daftar kelengkapan aset
            'map_media_id'        => ['type' => 'INTEGER', 'null' => true],
            'background_media_id' => ['type' => 'INTEGER', 'null' => true],
            'badge_media_id'      => ['type' => 'INTEGER', 'null' => true],
        ])->addPrimaryKey('id')->createTable('levels');

        $forge->addField([
            'id'                  => ['type' => 'INTEGER', 'auto_increment' => true],
            'level_id'            => ['type' => 'INTEGER', 'null' => true],
            'context_code'        => ['type' => 'VARCHAR', 'constraint' => 50],
            'sequence'            => ['type' => 'INTEGER'],
            'character_code'      => ['type' => 'VARCHAR', 'constraint' => 30],
            'pose'                => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'effect'              => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'title_id'            => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'title_en'            => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'text_id'             => ['type' => 'TEXT'],
            'text_en'             => ['type' => 'TEXT', 'null' => true],
            'audio_id_asset_id'   => ['type' => 'INTEGER', 'null' => true],
            'audio_en_asset_id'   => ['type' => 'INTEGER', 'null' => true],
            'background_media_id' => ['type' => 'INTEGER', 'null' => true],
            'is_active'           => ['type' => 'INTEGER', 'default' => 1],
        ])->addPrimaryKey('id')->createTable('dialogues');

        $forge->addField([
            'id'           => ['type' => 'INTEGER', 'auto_increment' => true],
            'asset_key'    => ['type' => 'VARCHAR', 'constraint' => 160],
            'asset_type'   => ['type' => 'VARCHAR', 'constraint' => 20],
            'storage_path' => ['type' => 'VARCHAR', 'constraint' => 500],
            'mime_type'    => ['type' => 'VARCHAR', 'constraint' => 100],
            'file_size'    => ['type' => 'INTEGER', 'null' => true],
            'sha256'       => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'width_px'     => ['type' => 'INTEGER', 'null' => true],
            'height_px'    => ['type' => 'INTEGER', 'null' => true],
            'locale'       => ['type' => 'VARCHAR', 'constraint' => 5, 'null' => true],
            'credit'       => ['type' => 'VARCHAR', 'constraint' => 300, 'null' => true],
            'version'      => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => '1'],
            'is_active'    => ['type' => 'INTEGER', 'default' => 1],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ])->addPrimaryKey('id')->addUniqueKey('asset_key')->createTable('media_assets');

        $forge->addField([
            'id'                => ['type' => 'INTEGER', 'auto_increment' => true],
            'media_asset_id'    => ['type' => 'INTEGER'],
            'locale'            => ['type' => 'VARCHAR', 'constraint' => 5],
            'character_code'    => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'context_code'      => ['type' => 'VARCHAR', 'constraint' => 80],
            'transcript'        => ['type' => 'TEXT'],
            'production_method' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'own_recording'],
            'voice_profile'     => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'duration_ms'       => ['type' => 'INTEGER', 'null' => true],
            'approval_status'   => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'draft'],
            'approved_by'       => ['type' => 'INTEGER', 'null' => true],
            'approved_at'       => ['type' => 'DATETIME', 'null' => true],
            'version'           => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => '1'],
        ])->addPrimaryKey('id')->addUniqueKey('media_asset_id')->createTable('audio_assets');

        $forge->addField([
            'id'            => ['type' => 'INTEGER', 'auto_increment' => true],
            'staff_user_id' => ['type' => 'INTEGER', 'null' => true],
            'action'        => ['type' => 'VARCHAR', 'constraint' => 50],
            'target_type'   => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'target_id'     => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'metadata_json' => ['type' => 'TEXT', 'null' => true],
            'ip_hash'       => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'occurred_at'   => ['type' => 'DATETIME', 'null' => true],
        ])->addPrimaryKey('id')->createTable('audit_logs');

        foreach (['temanggung' => 'Temanggung', 'magelang' => 'Magelang', 'wonosobo' => 'Wonosobo'] as $code => $name) {
            $db->table('levels')->insert(['sequence' => $db->table('levels')->countAllResults() + 1, 'code' => $code, 'name_id' => $name]);
        }

        $forge->addField([
            'id'     => ['type' => 'INTEGER', 'auto_increment' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'draft'],
        ])->addPrimaryKey('id')->createTable('research_studies');

        $forge->addField([
            'id'              => ['type' => 'INTEGER', 'auto_increment' => true],
            'content_version' => ['type' => 'VARCHAR', 'constraint' => 20],
            'is_active'       => ['type' => 'INTEGER', 'default' => 0],
        ])->addPrimaryKey('id')->createTable('game_releases');
    }

    private function dropNarrationTables(): void
    {
        $forge = Database::forge('tests');

        foreach ($this->narrationTables as $table) {
            $forge->dropTable($table, true);
        }
    }
}
