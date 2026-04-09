<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\FingerspotDevice;
use App\Models\FingerspotUser;
use App\Models\Karyawan;
use App\Services\Fingerspot\FingerApiService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FingerUserController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $deviceId = $request->get('device_id');
        $privilege = trim((string) $request->get('privilege', ''));

        $query = FingerspotUser::query()
            ->with('device')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('pin', 'like', "%{$search}%")
                        ->orWhere('nama', 'like', "%{$search}%")
                        ->orWhere('privilege', 'like', "%{$search}%")
                        ->orWhere('rfid', 'like', "%{$search}%");
                });
            })
            ->when($deviceId, fn ($q) => $q->where('device_id', $deviceId))
            ->when($privilege !== '', fn ($q) => $q->where('privilege', $privilege))
            ->latest();

        $items = $query->paginate(12)->withQueryString();

        $deviceOptions = FingerspotDevice::query()
            ->orderBy('nama')
            ->get();

        $totalUserMesin = FingerspotUser::count();
        $totalSynced = FingerspotUser::whereNotNull('synced_at')->count();
        $totalWithFace = FingerspotUser::where('face_template_count', '>', 0)->count();
        $totalWithFinger = FingerspotUser::where('finger_template_count', '>', 0)->count();

        return view('master.user-mesin.index', compact(
            'items',
            'search',
            'deviceId',
            'privilege',
            'deviceOptions',
            'totalUserMesin',
            'totalSynced',
            'totalWithFace',
            'totalWithFinger'
        ));
    }

    public function create()
    {
        $deviceOptions = FingerspotDevice::query()
            ->orderBy('nama')
            ->get();

        $privilegeOptions = $this->privilegeOptions();

        return view('master.user-mesin.create', compact('deviceOptions', 'privilegeOptions'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateForm($request);

        FingerspotUser::create($validated);

        return redirect()
            ->route('master.user-mesin.index')
            ->with('success', 'User mesin berhasil ditambahkan.');
    }

    public function edit(FingerspotUser $userMesin)
    {
        $deviceOptions = FingerspotDevice::query()
            ->orderBy('nama')
            ->get();

        $privilegeOptions = $this->privilegeOptions();

        return view('master.user-mesin.edit', compact('userMesin', 'deviceOptions', 'privilegeOptions'));
    }

    public function update(Request $request, FingerspotUser $userMesin)
    {
        $validated = $this->validateForm($request, $userMesin->id);

        $userMesin->update($validated);

        return redirect()
            ->route('master.user-mesin.index')
            ->with('success', 'User mesin berhasil diperbarui.');
    }

    public function destroy(FingerspotUser $userMesin)
    {
        $userMesin->delete();

        return redirect()
            ->route('master.user-mesin.index')
            ->with('success', 'User mesin berhasil dihapus.');
    }

    public function requestUserinfo(FingerspotUser $userMesin, FingerApiService $api)
    {
        if (! $userMesin->device) {
            return back()->with('error', 'User mesin belum terhubung dengan device.');
        }

        $result = $api->getUserinfo($userMesin->device, $userMesin->pin);

        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function getUserinfoMassal(Request $request, FingerApiService $api)
    {
        $validated = $request->validate([
            'device_id' => ['nullable', 'exists:fingerspot_devices,id'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
            'only_empty_name' => ['nullable', 'boolean'],
        ]);

        $deviceId = $validated['device_id'] ?? null;
        $limit = (int) ($validated['limit'] ?? 10);
        $onlyEmptyName = (bool) ($validated['only_empty_name'] ?? true);

        $query = FingerspotUser::query()
            ->with('device')
            ->when($deviceId, fn ($q) => $q->where('device_id', $deviceId))
            ->when($onlyEmptyName, function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNull('nama')
                        ->orWhere('nama', '');
                });
            })
            ->orderBy('device_id')
            ->orderBy('pin');

        $users = $query->limit($limit)->get();

        if ($users->isEmpty()) {
            return back()->with('error', 'Tidak ada user mesin yang sesuai untuk diproses.');
        }

        $berhasil = 0;
        $gagal = 0;
        $dilewati = 0;
        $processedPins = [];

        foreach ($users as $userMesin) {
            if (! $userMesin->device) {
                $dilewati++;
                continue;
            }

            if (! $userMesin->pin || trim((string) $userMesin->pin) === '') {
                $dilewati++;
                continue;
            }

            $result = $api->getUserinfo($userMesin->device, $userMesin->pin);

            if ($result['ok']) {
                $berhasil++;
                $processedPins[] = $userMesin->pin;
            } else {
                $gagal++;
            }

            usleep(300000);
        }

        $pesan = "Get Userinfo Massal selesai. Berhasil dikirim: {$berhasil}, Gagal: {$gagal}, Dilewati: {$dilewati}.";
        if (! empty($processedPins)) {
            $pesan .= ' PIN: ' . implode(', ', array_slice($processedPins, 0, 10));
            if (count($processedPins) > 10) {
                $pesan .= ' ...';
            }
        }

        return back()->with('success', $pesan);
    }

    public function pushSetUserinfo(FingerspotUser $userMesin, FingerApiService $api)
    {
        if (! $userMesin->device) {
            return back()->with('error', 'User mesin belum terhubung dengan device.');
        }

        $result = $api->setUserinfo($userMesin->device, [
            'pin' => $userMesin->pin,
            'nama' => $userMesin->nama,
            'privilege' => $userMesin->privilege,
            'password' => $userMesin->password,
            'rfid' => $userMesin->rfid,
            'face_template_count' => $userMesin->face_template_count,
            'finger_template_count' => $userMesin->finger_template_count,
            'vein_template_count' => $userMesin->vein_template_count,
        ]);

        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function deleteFromDevice(FingerspotUser $userMesin, FingerApiService $api)
    {
        if (! $userMesin->device) {
            return back()->with('error', 'User mesin belum terhubung dengan device.');
        }

        $result = $api->deleteUserinfo($userMesin->device, $userMesin->pin);

        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function syncToKaryawan(FingerspotUser $userMesin)
    {
        $pin = trim((string) $userMesin->pin);

        if ($pin === '') {
            return back()->with('error', 'PIN user mesin kosong, tidak bisa dijadikan master karyawan.');
        }

        $namaMesin = $this->nullableTrim($userMesin->nama);

        $karyawan = Karyawan::query()
            ->where('pin_fingerspot', $pin)
            ->first();

        if (! $karyawan) {
            Karyawan::create([
                'nama' => $namaMesin ?: 'USER PIN ' . $pin,
                'pin_fingerspot' => $pin,
                'device_id' => $userMesin->device_id,
                'kategori_karyawan_id' => null,
                'jabatan' => null,
                'tanggal_masuk' => null,
                'status_kerja' => null,
                'is_active' => true,
            ]);

            return back()->with('success', 'User mesin berhasil dijadikan master karyawan baru.');
        }

        $payload = [];

        if ((! $karyawan->nama || trim($karyawan->nama) === '') && $namaMesin) {
            $payload['nama'] = $namaMesin;
        }

        if (! $karyawan->device_id && $userMesin->device_id) {
            $payload['device_id'] = $userMesin->device_id;
        }

        if (! $karyawan->pin_fingerspot) {
            $payload['pin_fingerspot'] = $pin;
        }

        if (! empty($payload)) {
            $karyawan->update($payload);

            return back()->with('success', 'Master karyawan sudah ada, data kosong berhasil dilengkapi dari user mesin.');
        }

        return back()->with('success', 'Master karyawan dengan PIN ini sudah ada, tidak ada perubahan yang diperlukan.');
    }

    public function syncMassalToKaryawan()
    {
        $users = FingerspotUser::query()
            ->orderBy('device_id')
            ->orderBy('pin')
            ->get();

        $dibuat = 0;
        $dilewati = 0;
        $tanpaPin = 0;

        foreach ($users as $userMesin) {
            $pin = trim((string) $userMesin->pin);

            if ($pin === '') {
                $tanpaPin++;
                continue;
            }

            $sudahAda = Karyawan::query()
                ->where('pin_fingerspot', $pin)
                ->exists();

            if ($sudahAda) {
                $dilewati++;
                continue;
            }

            $namaMesin = $this->nullableTrim($userMesin->nama);

            Karyawan::create([
                'nama' => $namaMesin ?: 'USER PIN ' . $pin,
                'pin_fingerspot' => $pin,
                'device_id' => $userMesin->device_id,
                'kategori_karyawan_id' => null,
                'jabatan' => null,
                'tanggal_masuk' => null,
                'status_kerja' => null,
                'is_active' => true,
            ]);

            $dibuat++;
        }

        return back()->with(
            'success',
            "Sync massal selesai. Dibuat: {$dibuat}, Dilewati: {$dilewati}, Tanpa PIN: {$tanpaPin}."
        );
    }

    public function showMutasiForm(FingerspotUser $userMesin)
{
    $deviceOptions = FingerspotDevice::query()
        ->where('id', '!=', $userMesin->device_id)
        ->orderBy('nama')
        ->get();

    $konflikDevices = FingerspotUser::query()
        ->where('pin', $userMesin->pin)
        ->where('device_id', '!=', $userMesin->device_id)
        ->with('device')
        ->get();

    return view('master.user-mesin.mutasi', compact('userMesin', 'deviceOptions', 'konflikDevices'));
}

public function mutasiDevice(Request $request, FingerspotUser $userMesin, FingerApiService $api)
{
    $validated = $request->validate([
        'device_tujuan_id' => ['required', 'exists:fingerspot_devices,id', 'different:user_device_id'],
        'pin_baru' => ['nullable', 'string', 'max:50'],
        'hapus_dari_device_lama' => ['nullable', 'boolean'],
        'update_master_karyawan' => ['nullable', 'boolean'],
    ]);

    $deviceAsal = $userMesin->device;
    $deviceTujuan = FingerspotDevice::findOrFail($validated['device_tujuan_id']);

    if (!$deviceAsal) {
        return back()->with('error', 'Device asal user mesin tidak ditemukan.');
    }

    if ($deviceAsal->id === $deviceTujuan->id) {
        return back()->with('error', 'Device tujuan harus berbeda dari device asal.');
    }

    if (!$userMesin->template) {
        return back()->with('error', 'Template tidak ditemukan di raw_json. Jalankan Get Userinfo dulu sebelum mutasi.');
    }

    $pinTujuan = trim($validated['pin_baru'] ?: $userMesin->pin);

    $existingTarget = FingerspotUser::query()
        ->where('device_id', $deviceTujuan->id)
        ->where('pin', $pinTujuan)
        ->first();

    if ($existingTarget) {
        return back()->with('error', 'PIN ' . $pinTujuan . ' sudah dipakai di device tujuan oleh user lain. Gunakan PIN baru yang berbeda.');
    }

    $payload = [
        'pin' => (string) $pinTujuan,
        'name' => $userMesin->api_name,
        'privilege' => $userMesin->api_privilege,
        'password' => $userMesin->api_password,
        'rfid' => $userMesin->api_rfid,
        'face' => $userMesin->api_face,
        'finger' => $userMesin->api_finger,
        'vein' => $userMesin->api_vein,
        'template' => $userMesin->template,
    ];

    DB::beginTransaction();

    try {
        $pushResult = $api->setUserinfo($deviceTujuan, $payload);

        if (!($pushResult['ok'] ?? false)) {
            DB::rollBack();
            return back()->with('error', $pushResult['message'] ?? 'Mutasi gagal dikirim ke device tujuan.');
        }

        FingerspotUser::create([
            'device_id' => $deviceTujuan->id,
            'pin' => $pinTujuan,
            'nama' => $userMesin->nama,
            'privilege' => $userMesin->privilege,
            'password' => $userMesin->password,
            'rfid' => $userMesin->rfid,
            'face_template_count' => $userMesin->face_template_count,
            'finger_template_count' => $userMesin->finger_template_count,
            'vein_template_count' => $userMesin->vein_template_count,
            'raw_json' => $userMesin->raw_json,
            'synced_at' => now(),
        ]);

        if ((bool) ($validated['update_master_karyawan'] ?? false)) {
            Karyawan::query()
                ->where('pin_fingerspot', $userMesin->pin)
                ->update([
                    'device_id' => $deviceTujuan->id,
                    'pin_fingerspot' => $pinTujuan,
                ]);
        }

        if ((bool) ($validated['hapus_dari_device_lama'] ?? false)) {
            $deleteResult = $api->deleteUserinfo($deviceAsal, $userMesin->pin);

            if ($deleteResult['ok'] ?? false) {
                $userMesin->delete();
            }
        }

        DB::commit();

        return redirect()
            ->route('master.user-mesin.index')
            ->with('success', 'Mutasi user mesin berhasil ke device tujuan: ' . $deviceTujuan->nama . ' dengan PIN ' . $pinTujuan);
    } catch (\Throwable $e) {
        DB::rollBack();
        return back()->with('error', 'Mutasi gagal: ' . $e->getMessage());
    }
}

    protected function validateForm(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'device_id' => ['required', 'exists:fingerspot_devices,id'],
            'pin' => [
                'required',
                'string',
                'max:255',
                Rule::unique('fingerspot_users')
                    ->where(fn ($q) => $q->where('device_id', $request->device_id))
                    ->ignore($ignoreId),
            ],
            'nama' => ['nullable', 'string', 'max:255'],
            'privilege' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'rfid' => ['nullable', 'string', 'max:255'],
            'face_template_count' => ['nullable', 'integer', 'min:0'],
            'finger_template_count' => ['nullable', 'integer', 'min:0'],
            'vein_template_count' => ['nullable', 'integer', 'min:0'],
            'synced_at' => ['nullable', 'date'],
        ]);

        return [
            'device_id' => (int) $validated['device_id'],
            'pin' => trim($validated['pin']),
            'nama' => $this->nullableTrim($validated['nama'] ?? null),
            'privilege' => $this->nullableTrim($validated['privilege'] ?? null),
            'password' => $this->nullableTrim($validated['password'] ?? null),
            'rfid' => $this->nullableTrim($validated['rfid'] ?? null),
            'face_template_count' => (int) ($validated['face_template_count'] ?? 0),
            'finger_template_count' => (int) ($validated['finger_template_count'] ?? 0),
            'vein_template_count' => (int) ($validated['vein_template_count'] ?? 0),
            'synced_at' => $validated['synced_at'] ?? null,
        ];
    }

    protected function nullableTrim(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    protected function privilegeOptions(): array
    {
        return [
            '0' => 'User',
            '14' => 'Admin',
        ];
    }
}