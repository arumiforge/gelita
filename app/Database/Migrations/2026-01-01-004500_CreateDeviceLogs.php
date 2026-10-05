<?php

namespace App\Database\Migrations;

use App\Database\GelitaMigration;

/**
 * Perombakan kelas 5 · Dok 1 §5.6 — `device_logs`: satu baris setiap siswa
 * mendaftar, masuk, atau melanjutkan sesi. Diisi App\Libraries\DeviceProfile
 * (matomo/device-detector + Client Hints + sinyal browser). IP mentah tidak
 * pernah disimpan, hanya `ip_hash`.
 */
class CreateDeviceLogs extends GelitaMigration
{
    public function up(): void
    {
        $str = static fn (int $len): array => ['type' => 'VARCHAR', 'constraint' => $len, 'null' => true];
        $small = ['type' => 'SMALLINT', 'unsigned' => true, 'null' => true];

        $this->forge->addField([
            'id'                => $this->id(),
            'session_id'        => $this->ref(),
            'participant_id'    => $this->ref(),
            'via'               => ['type' => 'VARCHAR', 'constraint' => 20],
            'user_agent'        => $str(512),
            'device_type'       => $str(30),
            'device_brand'      => $str(60),
            'device_model'      => $str(100),
            'os_name'           => $str(60),
            'os_version'        => $str(40),
            'os_platform'       => $str(20),
            'browser_name'      => $str(60),
            'browser_version'   => $str(40),
            'browser_engine'    => $str(40),
            'is_bot'            => $this->flag(0),
            'client_hints_json' => $this->json(),
            'screen_w'          => $small,
            'screen_h'          => $small,
            'viewport_w'        => $small,
            'viewport_h'        => $small,
            'pixel_ratio'       => ['type' => 'DECIMAL', 'constraint' => '4,2', 'null' => true],
            'touch_points'      => ['type' => 'TINYINT', 'unsigned' => true, 'null' => true],
            'orientation'       => $str(20),
            'is_standalone'     => $this->nullableFlag(),
            'language'          => $str(20),
            'timezone'          => $str(60),
            'corrected'         => $str(40),
            'detector'          => $str(40),
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
