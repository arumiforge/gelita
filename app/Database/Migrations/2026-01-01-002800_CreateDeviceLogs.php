<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

/**
 * `device_logs`: satu baris setiap siswa mendaftar, masuk, atau melanjutkan sesi.
 * Diisi App\Libraries\DeviceProfile (matomo/device-detector + Client Hints +
 * sinyal browser). IP mentah tidak pernah disimpan, hanya `ip_hash`.
 */
class CreateDeviceLogs extends GelitaMigration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                => $this->id(),
            'session_id'        => $this->ref(false),
            'participant_id'    => $this->ref(false),
            'via'               => ['type' => 'VARCHAR', 'constraint' => 20],
            'user_agent'        => ['type' => 'VARCHAR', 'constraint' => 512, 'null' => true],
            'device_type'       => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'device_brand'      => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'device_model'      => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'os_name'           => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'os_version'        => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'os_platform'       => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'browser_name'      => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'browser_version'   => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'browser_engine'    => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'is_bot'            => $this->flag(0),
            'client_hints_json' => $this->json(true),
            'screen_w'          => ['type' => 'SMALLINT', 'unsigned' => true, 'null' => true],
            'screen_h'          => ['type' => 'SMALLINT', 'unsigned' => true, 'null' => true],
            'viewport_w'        => ['type' => 'SMALLINT', 'unsigned' => true, 'null' => true],
            'viewport_h'        => ['type' => 'SMALLINT', 'unsigned' => true, 'null' => true],
            'pixel_ratio'       => ['type' => 'DECIMAL', 'constraint' => '4,2', 'null' => true],
            'touch_points'      => ['type' => 'TINYINT', 'unsigned' => true, 'null' => true],
            'orientation'       => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'is_standalone'     => $this->nullableFlag(),
            'language'          => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'timezone'          => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'corrected'         => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'detector'          => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'ip_hash'           => ['type' => 'CHAR', 'constraint' => 64, 'null' => true],
            'created_at'        => $this->datetimeNow(),
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('session_id');
        $this->forge->addKey(['participant_id', 'created_at']);
        $this->forge->addForeignKey('session_id', 'game_sessions', 'id', '', 'CASCADE');
        $this->createGelitaTable('device_logs');
    }

    public function down(): void
    {
        $this->dropGelitaTable('device_logs');
    }
}
