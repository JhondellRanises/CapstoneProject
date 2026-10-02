<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\Exceptions\PageNotFoundException;

class ResourcesController extends BaseController
{
    private array $modules = [
        'trips' => [
            'title' => 'Trips', 'table' => 'trips',
            'columns' => ['id' => 'Trip ID', 'origin' => 'Origin', 'destination' => 'Destination', 'departure_date' => 'Date', 'departure_time' => 'Time', 'driver_name' => 'Driver', 'plate_number' => 'Van', 'available_seats' => 'Seats', 'status' => 'Status'],
            'fields' => [
                'origin' => ['Origin', 'text', true], 'destination' => ['Destination', 'text', true],
                'departure_date' => ['Departure date', 'date', true], 'departure_time' => ['Departure time', 'time', true],
                'driver_id' => ['Assigned driver', 'driver', false], 'van_id' => ['Assigned van', 'van', false],
                'available_seats' => ['Available seats', 'number', true],
                'status' => ['Trip status', 'trip_status', true],
            ],
        ],
        'bookings' => [
            'title' => 'Bookings', 'table' => 'bookings',
            'columns' => ['id' => 'Booking ID', 'passenger_name' => 'Passenger', 'trip_route' => 'Trip', 'booking_date' => 'Booking date', 'seats' => 'Seats', 'pickup_location' => 'Pickup', 'dropoff_location' => 'Drop-off', 'status' => 'Status'],
            'fields' => [
                'passenger_id' => ['Passenger', 'passenger', true], 'trip_id' => ['Trip', 'trip', true],
                'booking_date' => ['Booking date', 'datetime-local', true], 'seats' => ['Number of seats', 'number', true],
                'pickup_location' => ['Pickup location', 'text', true], 'dropoff_location' => ['Drop-off location', 'text', true],
                'status' => ['Booking status', 'booking_status', true],
            ],
        ],
        'schedules' => [
            'title' => 'Schedules', 'table' => 'schedules',
            'columns' => ['id' => 'ID', 'origin' => 'Origin', 'destination' => 'Destination', 'departure_time' => 'Departure time', 'available_seats' => 'Seats', 'driver_name' => 'Driver', 'plate_number' => 'Van', 'status' => 'Status'],
            'fields' => [
                'origin' => ['Origin', 'text', true], 'destination' => ['Destination', 'text', true],
                'departure_time' => ['Departure time', 'time', true], 'available_seats' => ['Available seats', 'number', true],
                'driver_id' => ['Assigned driver', 'driver', false], 'van_id' => ['Assigned van', 'van', false],
                'status' => ['Schedule status', 'schedule_status', true],
            ],
        ],
        'drivers' => [
            'title' => 'Drivers', 'table' => 'drivers',
            'columns' => ['id' => 'Driver ID', 'name' => 'Full name', 'contact_number' => 'Contact', 'email' => 'Email', 'is_active' => 'Account status'],
            'fields' => [
                'name' => ['Full name', 'text', true], 'email' => ['Email', 'email', true],
                'password' => ['Initial password', 'password', true], 'contact_number' => ['Contact number', 'text', false],
                'license_number' => ['License number', 'text', false], 'is_active' => ['Account status', 'active', true],
            ],
        ],
        'vans' => [
            'title' => 'Vans', 'table' => 'vans',
            'columns' => ['id' => 'Van ID', 'plate_number' => 'Plate number', 'description' => 'Description', 'seat_capacity' => 'Seat capacity', 'status' => 'Status'],
            'fields' => [
                'plate_number' => ['Plate number', 'text', true], 'description' => ['Van name / description', 'text', false],
                'seat_capacity' => ['Seat capacity', 'number', true], 'status' => ['Van status', 'van_status', true],
            ],
        ],
        'passengers' => [
            'title' => 'Passengers', 'table' => 'passengers',
            'columns' => ['id' => 'Passenger ID', 'name' => 'Full name', 'contact_number' => 'Contact', 'email' => 'Email', 'is_active' => 'Account status'],
            'fields' => [
                'name' => ['Full name', 'text', true], 'email' => ['Email', 'email', true],
                'password' => ['Initial password', 'password', true], 'contact_number' => ['Contact number', 'text', false],
                'is_active' => ['Account status', 'active', true],
            ],
        ],
        'users-roles' => [
            'title' => 'Users & Roles', 'table' => 'users',
            'columns' => ['id' => 'User ID', 'name' => 'Full name', 'email' => 'Email', 'role' => 'Role', 'is_active' => 'Account status'],
            'fields' => [
                'name' => ['Full name', 'text', true], 'email' => ['Email', 'email', true],
                'password' => ['Password', 'password', false], 'role' => ['Role', 'role', true],
                'is_active' => ['Account status', 'active', true],
            ],
        ],
    ];

    public function index(string $slug)
    {
        if ($slug === 'live-tracking') {
            return $this->trackingPage();
        }
        if ($slug === 'reports') {
            return $this->reportsPage();
        }
        $module = $this->module($slug);

        return view('admin/resource_list', [
            'title' => $module['title'], 'slug' => $slug,
            'columns' => $module['columns'], 'rows' => $this->listRows($slug),
        ]);
    }

    public function new(string $slug)
    {
        return $this->formPage($slug, null);
    }

    public function create(string $slug)
    {
        $module = $this->module($slug);
        if (! $this->validateInput($slug, null)) {
            return $this->formPage($slug, null, $this->validator);
        }

        $db = db_connect();
        $values = $this->submittedValues($slug, null);
        if ($slug === 'bookings' && ($bookingError = $this->bookingCapacityError($values))) {
            return redirect()->back()->withInput()->with('error', $bookingError);
        }
        if (in_array($slug, ['trips', 'schedules'], true) && ($capacityError = $this->assignedVanCapacityError($values))) {
            return redirect()->back()->withInput()->with('error', $capacityError);
        }
        if ($slug === 'trips' && ($bookingError = $this->tripBookingCapacityError($values['available_seats']))) {
            return redirect()->back()->withInput()->with('error', $bookingError);
        }
        $db->transStart();
        if (in_array($slug, ['drivers', 'passengers'], true)) {
            $role = $slug === 'drivers' ? 'Driver' : 'Passenger';
            $userId = $this->createUser($role, $values);
            $profile = [
                'user_id' => $userId,
                'contact_number' => $values['contact_number'] ?? null,
                'is_active' => $values['is_active'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            if ($slug === 'drivers') {
                $profile['license_number'] = $values['license_number'] ?? null;
            }
            $db->table($module['table'])->insert($profile);
        } else {
            if ($slug === 'users-roles') {
                $userId = $this->createUser($values['role'], $values);
                $this->syncRoleProfile($values['role'], $userId, (int) $values['is_active']);
            } else {
                $db->table($module['table'])->insert($values + ['created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
            }
        }
        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Could not save this record. Check for duplicate email or plate number.');
        }

        return redirect()->to(site_url('admin/' . $slug))->with('message', $module['title'] . ' record created.');
    }

    public function edit(string $slug, int $id)
    {
        return $this->formPage($slug, $id);
    }

    public function passengerBookings(int $id)
    {
        $passenger = db_connect()->table('passengers p')->select('p.id,u.name,u.email,p.contact_number')
            ->join('users u', 'u.id=p.user_id')->where('p.id', $id)->get()->getRowArray();
        if (! $passenger) {
            throw PageNotFoundException::forPageNotFound();
        }

        $bookings = db_connect()->table('bookings b')->select("b.id,b.booking_date,b.seats,b.pickup_location,b.dropoff_location,b.status,CONCAT(t.origin,' → ',t.destination) AS trip_route,t.departure_date")
            ->join('trips t', 't.id=b.trip_id')->where('b.passenger_id', $id)->orderBy('b.booking_date', 'DESC')->get()->getResultArray();

        return view('admin/passenger_bookings', ['passenger' => $passenger, 'rows' => $bookings]);
    }

    public function update(string $slug, int $id)
    {
        $module = $this->module($slug);
        $record = $this->getRecord($slug, $id);
        if (! $record) {
            throw PageNotFoundException::forPageNotFound();
        }
        if ($slug === 'users-roles' && (int) $id === (int) session()->get('user_id')) {
            $postedRole = $this->request->getPost('role');
            $postedActive = $this->request->getPost('is_active');
            if ($postedRole !== 'Admin' || ! $postedActive) {
                return redirect()->back()->withInput()->with('error', 'You cannot remove administrator access from your own account.');
            }
        }
        if (! $this->validateInput($slug, $id)) {
            return $this->formPage($slug, $id, $this->validator);
        }

        $db = db_connect();
        $values = $this->submittedValues($slug, $id);
        if ($slug === 'bookings' && ($bookingError = $this->bookingCapacityError($values, $id))) {
            return redirect()->back()->withInput()->with('error', $bookingError);
        }
        if (in_array($slug, ['trips', 'schedules'], true) && ($capacityError = $this->assignedVanCapacityError($values))) {
            return redirect()->back()->withInput()->with('error', $capacityError);
        }
        if ($slug === 'trips' && ($bookingError = $this->tripBookingCapacityError($values['available_seats'], $id))) {
            return redirect()->back()->withInput()->with('error', $bookingError);
        }
        $db->transStart();
        if (in_array($slug, ['drivers', 'passengers'], true)) {
            $userValues = ['name' => $values['name'], 'email' => $values['email'], 'is_active' => $values['is_active'], 'updated_at' => date('Y-m-d H:i:s')];
            if ($values['password'] !== '') {
                $userValues['password_hash'] = password_hash($values['password'], PASSWORD_DEFAULT);
            }
            $db->table('users')->where('id', $record['user_id'])->update($userValues);
            unset($values['name'], $values['email'], $values['password']);
            $values['updated_at'] = date('Y-m-d H:i:s');
            $db->table($module['table'])->where('id', $id)->update($values);
        } elseif ($slug === 'users-roles') {
            $oldRole = $record['role'];
            if ($values['password'] === '') {
                unset($values['password']);
            } else {
                $values['password_hash'] = password_hash($values['password'], PASSWORD_DEFAULT);
                unset($values['password']);
            }
            $values['updated_at'] = date('Y-m-d H:i:s');
            $db->table('users')->where('id', $id)->update($values);
            if ($oldRole !== $values['role']) {
                $this->syncRoleProfile($oldRole, $id, 0);
            }
            $this->syncRoleProfile($values['role'], $id, (int) $values['is_active']);
        } else {
            $values['updated_at'] = date('Y-m-d H:i:s');
            $db->table($module['table'])->where('id', $id)->update($values);
        }
        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Could not update this record. Check for duplicate email or plate number.');
        }

        if ($slug === 'users-roles' && (int) $id === (int) session()->get('user_id')) {
            session()->set('user_name', $values['name']);
        }

        return redirect()->to(site_url('admin/' . $slug))->with('message', $module['title'] . ' record updated.');
    }

    public function deactivate(string $slug, int $id)
    {
        $module = $this->module($slug);
        $db = db_connect();
        $record = $this->getRecord($slug, $id);
        if (! $record) {
            throw PageNotFoundException::forPageNotFound();
        }

        if ($slug === 'users-roles' && (int) $id === (int) session()->get('user_id')) {
            return redirect()->back()->with('error', 'You cannot deactivate your own admin account.');
        }

        $db->transStart();
        if (in_array($slug, ['drivers', 'passengers'], true)) {
            $db->table($module['table'])->where('id', $id)->update(['is_active' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
            $db->table('users')->where('id', $record['user_id'])->update(['is_active' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
        } elseif ($slug === 'users-roles' || $slug === 'vans') {
            $column = $slug === 'vans' ? 'status' : 'is_active';
            $inactiveValue = $slug === 'vans' ? 'Inactive' : 0;
            $db->table($module['table'])->where('id', $id)->update([$column => $inactiveValue, 'updated_at' => date('Y-m-d H:i:s')]);
            if ($slug === 'users-roles') {
                $this->syncRoleProfile($record['role'], $id, 0);
            }
        } elseif ($slug === 'schedules') {
            $db->table('schedules')->where('id', $id)->update(['status' => 'Inactive', 'updated_at' => date('Y-m-d H:i:s')]);
        } elseif ($slug === 'trips' || $slug === 'bookings') {
            $db->table($module['table'])->where('id', $id)->update(['status' => 'Cancelled', 'updated_at' => date('Y-m-d H:i:s')]);
            if ($slug === 'trips') {
                $db->table('bookings')->where('trip_id', $id)->whereIn('status', ['Pending', 'Confirmed'])
                    ->update(['status' => 'Cancelled', 'updated_at' => date('Y-m-d H:i:s')]);
            }
        }
        $db->transComplete();

        return redirect()->to(site_url('admin/' . $slug))->with('message', $module['title'] . ' record deactivated or cancelled.');
    }

    private function module(string $slug): array
    {
        if (! isset($this->modules[$slug])) {
            throw PageNotFoundException::forPageNotFound();
        }
        return $this->modules[$slug];
    }

    private function listRows(string $slug): array
    {
        $db = db_connect();
        return match ($slug) {
            'drivers' => $db->table('drivers d')->select('d.id,d.user_id,u.name,u.email,d.contact_number,d.license_number,d.is_active')->join('users u', 'u.id=d.user_id')->orderBy('d.id', 'DESC')->get()->getResultArray(),
            'passengers' => $db->table('passengers p')->select('p.id,p.user_id,u.name,u.email,p.contact_number,p.is_active')->join('users u', 'u.id=p.user_id')->orderBy('p.id', 'DESC')->get()->getResultArray(),
            'trips' => $db->table('trips t')->select('t.*,u.name AS driver_name,v.plate_number')->join('drivers d', 'd.id=t.driver_id', 'left')->join('users u', 'u.id=d.user_id', 'left')->join('vans v', 'v.id=t.van_id', 'left')->orderBy('t.departure_date', 'DESC')->get()->getResultArray(),
            'schedules' => $db->table('schedules s')->select('s.*,u.name AS driver_name,v.plate_number')->join('drivers d', 'd.id=s.driver_id', 'left')->join('users u', 'u.id=d.user_id', 'left')->join('vans v', 'v.id=s.van_id', 'left')->orderBy('s.id', 'DESC')->get()->getResultArray(),
            'bookings' => $db->table('bookings b')->select("b.*,u.name AS passenger_name,CONCAT(t.origin,' → ',t.destination) AS trip_route")->join('passengers p', 'p.id=b.passenger_id')->join('users u', 'u.id=p.user_id')->join('trips t', 't.id=b.trip_id')->orderBy('b.id', 'DESC')->get()->getResultArray(),
            default => $db->table($this->module($slug)['table'])->orderBy('id', 'DESC')->get()->getResultArray(),
        };
    }

    private function getRecord(string $slug, int $id): ?array
    {
        $module = $this->module($slug);
        if (in_array($slug, ['drivers', 'passengers'], true)) {
            $table = $module['table'];
            $row = db_connect()->table($table . ' p')->select('p.*,u.name,u.email,u.is_active AS user_active')
                ->join('users u', 'u.id=p.user_id')->where('p.id', $id)->get()->getRowArray();
            if ($row) {
                $row['is_active'] = $row['user_active'];
            }
            return $row ?: null;
        }
        return db_connect()->table($module['table'])->where('id', $id)->get()->getRowArray() ?: null;
    }

    private function formPage(string $slug, ?int $id, $validation = null)
    {
        $module = $this->module($slug);
        if (in_array($slug, ['live-tracking', 'reports'], true)) {
            throw PageNotFoundException::forPageNotFound();
        }
        $record = $id ? $this->getRecord($slug, $id) : [];
        if ($id && ! $record) {
            throw PageNotFoundException::forPageNotFound();
        }
        if ($validation) {
            foreach (array_keys($module['fields']) as $fieldName) {
                if ($fieldName !== 'password' && $this->request->getPost($fieldName) !== null) {
                    $record[$fieldName] = $this->request->getPost($fieldName);
                }
            }
        }
        $fields = [];
        foreach ($module['fields'] as $name => [$label, $type, $required]) {
            $isRequired = $name === 'password' ? ! $id : $required;
            $value = $record[$name] ?? '';
            if ($type === 'datetime-local' && $value !== '') {
                $value = str_replace(' ', 'T', substr((string) $value, 0, 16));
            }
            $fields[] = ['name' => $name, 'label' => $label, 'type' => $type, 'required' => $isRequired, 'value' => $value];
        }

        return view('admin/resource_form', [
            'title' => $module['title'], 'slug' => $slug, 'id' => $id,
            'fields' => $fields, 'options' => $this->selectOptions(), 'validation' => $validation,
        ]);
    }

    private function selectOptions(): array
    {
        $db = db_connect();
        $options = [
            'role' => ['Admin' => 'Admin', 'Dispatcher' => 'Dispatcher', 'Driver' => 'Driver', 'Passenger' => 'Passenger'],
            'active' => [1 => 'Active', 0 => 'Inactive'],
            'trip_status' => array_combine(['Scheduled', 'Boarding', 'Departed', 'Ongoing', 'Completed', 'Cancelled'], ['Scheduled', 'Boarding', 'Departed', 'Ongoing', 'Completed', 'Cancelled']),
            'booking_status' => array_combine(['Pending', 'Confirmed', 'Cancelled', 'Completed'], ['Pending', 'Confirmed', 'Cancelled', 'Completed']),
            'schedule_status' => ['Active' => 'Active', 'Inactive' => 'Inactive'],
            'van_status' => array_combine(['Available', 'In Use', 'Maintenance', 'Inactive'], ['Available', 'In Use', 'Maintenance', 'Inactive']),
            'driver' => [], 'van' => [], 'passenger' => [], 'trip' => [],
        ];
        foreach ($db->table('drivers d')->select('d.id,u.name')->join('users u', 'u.id=d.user_id')->where('d.is_active', 1)->get()->getResultArray() as $row) {
            $options['driver'][$row['id']] = $row['name'];
        }
        foreach ($db->table('vans')->whereIn('status', ['Available', 'In Use'])->get()->getResultArray() as $row) {
            $options['van'][$row['id']] = $row['plate_number'] . ' (' . $row['seat_capacity'] . ' seats)';
        }
        foreach ($db->table('passengers p')->select('p.id,u.name,u.email')->join('users u', 'u.id=p.user_id')->where('p.is_active', 1)->get()->getResultArray() as $row) {
            $options['passenger'][$row['id']] = $row['name'] . ' (' . $row['email'] . ')';
        }
        foreach ($db->table('trips')->whereNotIn('status', ['Cancelled', 'Completed'])->get()->getResultArray() as $row) {
            $options['trip'][$row['id']] = $row['origin'] . ' → ' . $row['destination'] . ' (' . $row['departure_date'] . ')';
        }
        return $options;
    }

    private function validateInput(string $slug, ?int $id): bool
    {
        $rules = [];
        foreach ($this->module($slug)['fields'] as $name => [$label, $type, $required]) {
            if ($name === 'password') {
                $rules[$name] = ($id ? 'permit_empty' : 'required') . '|min_length[12]|max_length[255]';
            } elseif ($type === 'email') {
                $rules[$name] = 'required|valid_email|max_length[190]';
            } elseif ($type === 'number') {
                $rules[$name] = ($required ? 'required|' : 'permit_empty|') . 'is_natural_no_zero';
            } elseif ($type === 'date') {
                $rules[$name] = 'required|valid_date[Y-m-d]';
            } elseif ($type === 'time') {
                $rules[$name] = 'required|valid_date[H:i]';
            } elseif ($type === 'role') {
                $rules[$name] = 'required|in_list[Admin,Dispatcher,Driver,Passenger]';
            } elseif ($type === 'active') {
                $rules[$name] = 'required|in_list[0,1]';
            } elseif ($type === 'trip_status') {
                $rules[$name] = 'required|in_list[Scheduled,Boarding,Departed,Ongoing,Completed,Cancelled]';
            } elseif ($type === 'booking_status') {
                $rules[$name] = 'required|in_list[Pending,Confirmed,Cancelled,Completed]';
            } elseif ($type === 'schedule_status') {
                $rules[$name] = 'required|in_list[Active,Inactive]';
            } elseif ($type === 'van_status') {
                $rules[$name] = 'required|in_list[Available,In Use,Maintenance,Inactive]';
            } elseif (in_array($type, ['driver', 'van', 'passenger', 'trip'], true)) {
                $rules[$name] = ($required ? 'required|' : 'permit_empty|') . 'is_natural_no_zero';
            } else {
                $maxLength = match ($name) {
                    'name', 'origin', 'destination', 'description' => 120,
                    'contact_number', 'plate_number' => 30,
                    'license_number' => 80,
                    'pickup_location', 'dropoff_location' => 160,
                    default => 190,
                };
                $rules[$name] = ($required ? 'required|' : 'permit_empty|') . 'max_length[' . $maxLength . ']';
            }
        }
        if (in_array($slug, ['drivers', 'passengers', 'users-roles'], true)) {
            $existingUserId = $id;
            if ($id && in_array($slug, ['drivers', 'passengers'], true)) {
                $existingUserId = $this->getRecord($slug, $id)['user_id'] ?? 0;
            }
            $rules['email'] .= $id
                ? '|is_unique[users.email,id,' . (int) $existingUserId . ']'
                : '|is_unique[users.email]';
        } elseif ($slug === 'vans') {
            $rules['plate_number'] = ($id ? 'required|' : 'required|') . 'max_length[30]|is_unique[vans.plate_number,id,' . (int) ($id ?? 0) . ']';
        }
        return $this->validate($rules);
    }

    private function submittedValues(string $slug, ?int $id): array
    {
        $values = [];
        foreach ($this->module($slug)['fields'] as $name => [, $type]) {
            $value = $this->request->getPost($name);
            if ($type === 'datetime-local' && is_string($value)) {
                $value = str_replace('T', ' ', $value);
            }
            if ($type === 'active') {
                $value = $value ? 1 : 0;
            }
            if (in_array($type, ['driver', 'van', 'passenger', 'trip'], true) && $value === '') {
                $value = null;
            }
            if (in_array($name, ['email'], true)) {
                $value = strtolower(trim((string) $value));
            }
            $values[$name] = is_string($value) ? trim($value) : $value;
        }
        if (in_array($slug, ['drivers', 'passengers'], true)) {
            $values['password'] = $values['password'] ?? '';
        }
        return $values;
    }

    private function createUser(string $role, array $values): int
    {
        $now = date('Y-m-d H:i:s');
        db_connect()->table('users')->insert([
            'name' => $values['name'], 'email' => $values['email'],
            'password_hash' => password_hash($values['password'], PASSWORD_DEFAULT),
            'role' => $role, 'is_active' => $values['is_active'], 'created_at' => $now, 'updated_at' => $now,
        ]);
        return (int) db_connect()->insertID();
    }

    private function syncRoleProfile(string $role, int $userId, int $active): void
    {
        if (! in_array($role, ['Driver', 'Passenger'], true)) {
            return;
        }

        $table = $role === 'Driver' ? 'drivers' : 'passengers';
        $profile = db_connect()->table($table)->where('user_id', $userId)->get()->getRowArray();
        $values = ['is_active' => $active, 'updated_at' => date('Y-m-d H:i:s')];
        if ($profile) {
            db_connect()->table($table)->where('id', $profile['id'])->update($values);
            return;
        }

        $values += [
            'user_id' => $userId,
            'contact_number' => null,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        if ($table === 'drivers') {
            $values['license_number'] = null;
        }
        db_connect()->table($table)->insert($values);
    }

    private function bookingCapacityError(array $values, ?int $ignoreBookingId = null): ?string
    {
        if (! in_array($values['status'], ['Pending', 'Confirmed'], true)) {
            return null;
        }
        $db = db_connect();
        $trip = $db->table('trips')->where('id', $values['trip_id'])->get()->getRowArray();
        if (! $trip || ! in_array($trip['status'], ['Scheduled', 'Boarding'], true)) {
            return 'Bookings can only be added to trips that are scheduled or boarding.';
        }

        $builder = $db->table('bookings')->selectSum('seats', 'reserved_seats')
            ->where('trip_id', $values['trip_id'])->whereIn('status', ['Pending', 'Confirmed']);
        if ($ignoreBookingId) {
            $builder->where('id !=', $ignoreBookingId);
        }
        $reserved = (int) ($builder->get()->getRowArray()['reserved_seats'] ?? 0);
        if ($reserved + (int) $values['seats'] > (int) $trip['available_seats']) {
            return 'There are not enough seats left on this trip.';
        }
        return null;
    }

    private function assignedVanCapacityError(array $values): ?string
    {
        if (empty($values['van_id']) || empty($values['available_seats'])) {
            return null;
        }
        $van = db_connect()->table('vans')->where('id', $values['van_id'])->get()->getRowArray();
        if ($van && (int) $values['available_seats'] > (int) $van['seat_capacity']) {
            return 'The seat count cannot exceed the assigned van capacity.';
        }
        return null;
    }

    private function tripBookingCapacityError(int $seatCapacity, ?int $tripId = null): ?string
    {
        if (! $tripId) {
            return null;
        }
        $reserved = db_connect()->table('bookings')->selectSum('seats', 'reserved_seats')
            ->where('trip_id', $tripId)->whereIn('status', ['Pending', 'Confirmed'])->get()->getRowArray();
        if ((int) ($reserved['reserved_seats'] ?? 0) > $seatCapacity) {
            return 'Trip capacity cannot be lower than the seats already booked.';
        }
        return null;
    }

    private function trackingPage()
    {
        $rows = db_connect()->table('trips t')->select('t.id,t.origin,t.destination,t.departure_date,t.status,u.name AS driver_name,v.plate_number,l.latitude,l.longitude,l.recorded_at')
            ->join('drivers d', 'd.id=t.driver_id', 'left')->join('users u', 'u.id=d.user_id', 'left')
            ->join('vans v', 'v.id=t.van_id', 'left')->join('(SELECT trip_id, MAX(recorded_at) AS last_time FROM trip_locations GROUP BY trip_id) latest', 'latest.trip_id=t.id', 'left', false)
            ->join('trip_locations l', 'l.trip_id=t.id AND l.recorded_at=latest.last_time', 'left', false)
            ->whereIn('t.status', ['Boarding', 'Departed', 'Ongoing'])->orderBy('t.departure_date', 'ASC')->get()->getResultArray();
        return view('admin/tracking', ['title' => 'Live Tracking', 'activePage' => 'live-tracking', 'rows' => $rows]);
    }

    private function reportsPage()
    {
        $types = [
            'bookings' => 'Booking Report',
            'trips' => 'Trip Report',
            'passengers' => 'Passenger Report',
            'completed-trips' => 'Completed Trips',
            'cancelled-bookings' => 'Cancelled Bookings',
        ];
        $type = (string) $this->request->getGet('type');
        if (! isset($types[$type])) {
            $type = 'bookings';
        }
        $from = $this->request->getGet('from');
        $to = $this->request->getGet('to');
        $db = db_connect();
        if ($type === 'passengers') {
            $columns = ['id' => 'Passenger ID', 'name' => 'Passenger', 'email' => 'Email', 'contact_number' => 'Contact', 'is_active' => 'Status'];
            $rows = $db->table('passengers p')->select('p.id,u.name,u.email,p.contact_number,p.is_active')->join('users u', 'u.id=p.user_id')->orderBy('p.id', 'DESC')->get()->getResultArray();
        } elseif (in_array($type, ['trips', 'completed-trips'], true)) {
            $columns = ['id' => 'Trip ID', 'origin' => 'Origin', 'destination' => 'Destination', 'departure_date' => 'Date', 'status' => 'Status'];
            $builder = $db->table('trips')->select('id,origin,destination,departure_date,status');
            if ($type === 'completed-trips') $builder->where('status', 'Completed');
            if ($from) $builder->where('departure_date >=', $from);
            if ($to) $builder->where('departure_date <=', $to);
            $rows = $builder->orderBy('departure_date', 'DESC')->get()->getResultArray();
        } else {
            $columns = ['id' => 'Booking ID', 'booking_date' => 'Date', 'passenger_name' => 'Passenger', 'trip_route' => 'Trip', 'seats' => 'Seats', 'status' => 'Status'];
            $builder = $db->table('bookings b')->select("b.id,b.booking_date,b.seats,b.status,u.name AS passenger_name,CONCAT(t.origin,' → ',t.destination) AS trip_route")
                ->join('passengers p', 'p.id=b.passenger_id')->join('users u', 'u.id=p.user_id')->join('trips t', 't.id=b.trip_id');
            if ($type === 'cancelled-bookings') $builder->where('b.status', 'Cancelled');
            if ($from) $builder->where('DATE(b.booking_date) >=', $from, false);
            if ($to) $builder->where('DATE(b.booking_date) <=', $to, false);
            $rows = $builder->orderBy('b.booking_date', 'DESC')->get()->getResultArray();
        }
        return view('admin/reports', ['title' => 'Reports', 'activePage' => 'reports', 'rows' => $rows, 'columns' => $columns, 'types' => $types, 'type' => $type, 'from' => $from, 'to' => $to]);
    }
}
