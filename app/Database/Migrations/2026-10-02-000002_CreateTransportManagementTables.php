<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTransportManagementTables extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'unsigned' => true],
            'contact_number' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'license_number' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'is_active' => ['type' => 'BOOLEAN', 'default' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('user_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('drivers', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'unsigned' => true],
            'contact_number' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'is_active' => ['type' => 'BOOLEAN', 'default' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('user_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('passengers', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'plate_number' => ['type' => 'VARCHAR', 'constraint' => 30],
            'description' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'seat_capacity' => ['type' => 'SMALLINT', 'unsigned' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['Available', 'In Use', 'Maintenance', 'Inactive'], 'default' => 'Available'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('plate_number');
        $this->forge->createTable('vans', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'origin' => ['type' => 'VARCHAR', 'constraint' => 120],
            'destination' => ['type' => 'VARCHAR', 'constraint' => 120],
            'departure_time' => ['type' => 'TIME'],
            'available_seats' => ['type' => 'SMALLINT', 'unsigned' => true],
            'driver_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'van_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['Active', 'Inactive'], 'default' => 'Active'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('driver_id', 'drivers', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('van_id', 'vans', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('schedules', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'origin' => ['type' => 'VARCHAR', 'constraint' => 120],
            'destination' => ['type' => 'VARCHAR', 'constraint' => 120],
            'departure_date' => ['type' => 'DATE'],
            'departure_time' => ['type' => 'TIME'],
            'driver_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'van_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'available_seats' => ['type' => 'SMALLINT', 'unsigned' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['Scheduled', 'Boarding', 'Departed', 'Ongoing', 'Completed', 'Cancelled'], 'default' => 'Scheduled'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('driver_id', 'drivers', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('van_id', 'vans', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('trips', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'passenger_id' => ['type' => 'INT', 'unsigned' => true],
            'trip_id' => ['type' => 'INT', 'unsigned' => true],
            'booking_date' => ['type' => 'DATETIME'],
            'seats' => ['type' => 'SMALLINT', 'unsigned' => true],
            'pickup_location' => ['type' => 'VARCHAR', 'constraint' => 160],
            'dropoff_location' => ['type' => 'VARCHAR', 'constraint' => 160],
            'status' => ['type' => 'ENUM', 'constraint' => ['Pending', 'Confirmed', 'Cancelled', 'Completed'], 'default' => 'Pending'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('passenger_id', 'passengers', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('trip_id', 'trips', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('bookings', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'trip_id' => ['type' => 'INT', 'unsigned' => true],
            'latitude' => ['type' => 'DECIMAL', 'constraint' => '10,7'],
            'longitude' => ['type' => 'DECIMAL', 'constraint' => '10,7'],
            'recorded_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('trip_id');
        $this->forge->addForeignKey('trip_id', 'trips', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('trip_locations', true);
    }

    public function down()
    {
        foreach (['trip_locations', 'bookings', 'trips', 'schedules', 'vans', 'passengers', 'drivers'] as $table) {
            $this->forge->dropTable($table, true);
        }
    }
}
