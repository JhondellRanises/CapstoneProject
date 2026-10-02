<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index(): string
    {
        return view('admin/dashboard', [
            'title' => 'Dashboard',
            'activePage' => 'dashboard',
            'counts' => [
                'Total Bookings' => $this->countRows('bookings'),
                'Active Trips' => $this->countRows('trips', ['status' => ['Boarding', 'Departed', 'Ongoing']]),
                'Total Passengers' => $this->countRows('passengers'),
                'Available Vans' => $this->countRows('vans', ['status' => 'Available']),
                'Pending Bookings' => $this->countRows('bookings', ['status' => 'Pending']),
            ],
            'recentBookings' => $this->recentBookings(),
            'activeTrips' => $this->activeTrips(),
        ]);
    }

    private function countRows(string $table, array $where = []): int
    {
        $db = db_connect();
        if (! $db->tableExists($table)) {
            return 0;
        }

        $builder = $db->table($table);
        if ($where !== [] && $db->fieldExists('status', $table)) {
            foreach ($where as $field => $value) {
                is_array($value) ? $builder->whereIn($field, $value) : $builder->where($field, $value);
            }
        }

        return (int) $builder->countAllResults();
    }

    private function recentBookings(): array
    {
        $db = db_connect();
        if (! $db->tableExists('bookings')) {
            return [];
        }

        return $db->table('bookings')->orderBy('id', 'DESC')->limit(5)->get()->getResultArray();
    }

    private function activeTrips(): array
    {
        $db = db_connect();
        if (! $db->tableExists('trips') || ! $db->fieldExists('status', 'trips')) {
            return [];
        }

        return $db->table('trips')->whereIn('status', ['Boarding', 'Departed', 'Ongoing'])
            ->orderBy('departure_date', 'ASC')->limit(5)->get()->getResultArray();
    }
}
