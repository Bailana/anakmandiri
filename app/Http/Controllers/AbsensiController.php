<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\AnakDidik;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;

class AbsensiController extends Controller
{
  public function __construct()
  {
    $this->middleware('role:admin,guru,terapis');
  }

  /**
   * Display a listing of absensis
   */
  public function index(Request $request)
  {
    $user = Auth::user();

    // Get all students for the current teacher/therapist
    $anakDidikQuery = AnakDidik::query();

    if ($user->role === 'guru' || $user->role === 'terapis') {
      // Get employee data
      $karyawan = Karyawan::where('user_id', $user->id)->first();
      if (!$karyawan) {
        // User bukan karyawan, kembalikan empty
        $anakDidikQuery->whereRaw('1 = 0');
      } else {
        // Get all students assigned to this teacher
        $anakDidikQuery->where('guru_fokus_id', $karyawan->id);
      }
    }

    // Apply search filter
    if ($request->filled('search')) {
      $search = $request->search;
      $anakDidikQuery->where(function ($q) use ($search) {
        $q->where('nama', 'like', "%{$search}%")
          ->orWhere('nis', 'like', "%{$search}%");
      });
    }

    // Get date filter - default to current month
    if ($request->filled('tanggal')) {
      // Handle both date (YYYY-MM-DD) and month (YYYY-MM) formats
      $tanggalInput = $request->tanggal;
      if (preg_match('/^\d{4}-\d{2}$/', $tanggalInput)) {
        // Month format (YYYY-MM)
        $bulan = (int) substr($tanggalInput, 5, 2);
        $tahun = (int) substr($tanggalInput, 0, 4);
        $tanggalFilter = $tanggalInput . '-01';
      } else {
        // Date format (YYYY-MM-DD)
        $tanggalFilter = $tanggalInput;
        $bulan = \Carbon\Carbon::parse($tanggalInput)->month;
        $tahun = \Carbon\Carbon::parse($tanggalInput)->year;
      }
    } else {
      // Default to current month
      $tanggalFilter = now()->toDateString();
      $bulan = now()->month;
      $tahun = now()->year;
    }

    // Don't paginate yet - get all matching students first
    $allAnakDidiks = $anakDidikQuery->orderBy('nama', 'asc')->get();

    // Get today's attendance for status column
    $todayDate = now()->toDateString();
    $todayAbsensis = Absensi::whereDate('tanggal', $todayDate)
      ->whereIn('anak_didik_id', $allAnakDidiks->pluck('id'))
      ->get()
      ->keyBy('anak_didik_id');

    // Get attendance summary for ALL students in the selected month
    $absensiSummary = [];
    foreach ($allAnakDidiks as $anak) {
      $absensis = Absensi::where('anak_didik_id', $anak->id)
        ->whereYear('tanggal', $tahun)
        ->whereMonth('tanggal', $bulan)
        ->get();

      $absensiSummary[$anak->id] = [
        'hadir' => $absensis->where('status', 'hadir')->count(),
        'izin' => $absensis->where('status', 'izin')->count(),
        'alfa' => $absensis->where('status', 'alfa')->count(),
      ];
    }

    // Apply status filter if specified
    $statusFilter = $request->input('status');

    if ($statusFilter) {
      $allAnakDidiks = $allAnakDidiks->filter(function ($anak) use ($absensiSummary, $statusFilter) {
        $summary = $absensiSummary[$anak->id] ?? null;
        if (!$summary) return false;

        return $summary[$statusFilter] > 0;
      })->values();
    }

    // Now paginate the filtered results
    $perPage = 10;
    $page = $request->get('page', 1);
    $items = $allAnakDidiks->slice(($page - 1) * $perPage, $perPage)->values();

    $anakDidiks = new \Illuminate\Pagination\LengthAwarePaginator(
      $items,
      $allAnakDidiks->count(),
      $perPage,
      $page,
      [
        'path' => $request->url(),
        'query' => $request->query(),
      ]
    );

    return view('content.absensi.index', [
      'anakDidiks' => $anakDidiks,
      'absensiSummary' => $absensiSummary,
      'todayAbsensis' => $todayAbsensis,
      'todayDate' => $todayDate,
      'tanggalFilter' => $tanggalFilter,
      'bulan' => $bulan,
      'tahun' => $tahun,
    ]);
  }

  /**
   * Show the form for creating a new absensi
   */
  public function create()
  {
    $user = Auth::user();

    // Cari Karyawan berdasarkan user_id
    $karyawan = Karyawan::where('user_id', $user->id)->first();

    if (!$karyawan) {
      return redirect()->route('absensi.index')
        ->with('error', 'Data karyawan Anda tidak ditemukan.');
    }

    // Ambil daftar anak didik yang menjadi tanggung jawab guru/terapis ini
    $anakDidiks = AnakDidik::where('guru_fokus_id', $karyawan->id)
      ->orderBy('nama', 'asc')
      ->get();

    if ($anakDidiks->isEmpty()) {
      return redirect()->route('absensi.index')
        ->with('error', 'Anda tidak memiliki anak didik yang ditugaskan.');
    }

    return view('content.absensi.create', [
      'anakDidiks' => $anakDidiks,
      'jenisTandaFisik' => Absensi::getJenisTandaFisikOptions(),
    ]);
  }

  /**
   * Store a newly created absensi
   */
  public function store(Request $request)
  {
    try {
      $user = Auth::user();

      // Validasi base - hanya anak_didik_id yang selalu wajib
      $rules = [
        'anak_didik_id' => 'required|exists:anak_didiks,id',
        'is_izin' => 'nullable|boolean',
      ];

      // Jika izin dicentang, hanya keterangan yang wajib
      if ($request->filled('is_izin')) {
        $rules['keterangan'] = 'required|string|max:500';
      } else {
        // Jika tidak izin, maka kondisi fisik dan verifikasi wajib
        $rules['keterangan'] = 'nullable|string|max:500';
        $rules['kondisi_fisik'] = 'required|in:baik,ada_tanda';
        $rules['nama_pengantar'] = 'required|string|max:100';
        $rules['signature_pengantar'] = 'required|string';
      }

      // Jika ada tanda fisik, validasi jenis tanda fisik dan foto
      if ($request->kondisi_fisik === 'ada_tanda') {
        $rules['jenis_tanda_fisik.*'] = 'required|in:lebam,luka_gores,luka_terbuka,bengkak,ruam,bekas_gigitan,luka_bakar,bekas_cakar,luka_lama';
        $rules['keterangan_tanda_fisik'] = 'required|string|max:500';
        $rules['foto_bukti'] = 'required|array|min:1';
        $rules['foto_bukti.*'] = 'image|mimes:jpeg,png,jpg,gif|max:5120';
        $rules['lokasi_luka'] = 'required|string';
      }

      $validated = $request->validate($rules);

      // Validasi bahwa guru hanya bisa mengabsensi anak didiknya sendiri
      $anakDidik = AnakDidik::findOrFail($request->anak_didik_id);
      $karyawan = Karyawan::where('user_id', $user->id)->first();

      if (!$karyawan || $anakDidik->guru_fokus_id !== $karyawan->id) {
        if ($user->role !== 'admin') {
          return redirect()->back()
            ->with('error', 'Anda tidak berhak mengabsensi anak didik ini.');
        }
      }

      // Set tanggal otomatis ke hari ini
      $tanggalHariIni = now()->toDateString();

      // Cek apakah sudah ada absensi untuk hari yang sama
      $existingAbsensi = Absensi::where('anak_didik_id', $request->anak_didik_id)
        ->whereDate('tanggal', $tanggalHariIni)
        ->first();

      if ($existingAbsensi) {
        return redirect()->back()
          ->with('error', 'Absensi untuk anak didik ini pada tanggal hari ini sudah ada.');
      }

      // Prepare data untuk insert
      // Status otomatis di-set ke 'hadir' saat form disubmit, atau 'izin' jika checkbox dicentang
      $status = $request->filled('is_izin') ? 'izin' : 'hadir';

      $data = [
        'anak_didik_id' => $request->anak_didik_id,
        'user_id' => $user->id,
        'tanggal' => $tanggalHariIni,
        'status' => $status,
        'kondisi_fisik' => $request->kondisi_fisik,
        'keterangan' => $request->keterangan,
      ];

      // Handle signature dan kondisi fisik hanya jika TIDAK izin
      if (!$request->filled('is_izin')) {
        // Jika ada tanda fisik, simpan detail tanda fisik dan foto
        if ($request->kondisi_fisik === 'ada_tanda') {
          // Konversi array jenis_tanda_fisik ke string comma-separated
          $jenisTandaFisikArray = $request->jenis_tanda_fisik ?? [];
          if (is_array($jenisTandaFisikArray)) {
            $data['jenis_tanda_fisik'] = implode(',', $jenisTandaFisikArray);
          } else {
            $data['jenis_tanda_fisik'] = (string) $jenisTandaFisikArray;
          }

          $data['keterangan_tanda_fisik'] = $request->keterangan_tanda_fisik;

          // Handle lokasi_luka - pastikan selalu array
          $lokasiLukaInput = $request->input('lokasi_luka');
          if (is_string($lokasiLukaInput)) {
            $lokasiLuka = @json_decode($lokasiLukaInput, true);
            if (!is_array($lokasiLuka)) {
              $lokasiLuka = [];
            }
          } else {
            $lokasiLuka = is_array($lokasiLukaInput) ? $lokasiLukaInput : [];
          }
          $data['lokasi_luka'] = $lokasiLuka;

          // Save multiple fotos
          if ($request->hasFile('foto_bukti')) {
            $fotoPaths = [];
            $files = $request->file('foto_bukti');

            // Pastikan $files adalah array
            if (!is_array($files)) {
              $files = [$files];
            }

            foreach ($files as $file) {
              if ($file && $file->isValid()) {
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('absensi/bukti', $filename, 'public');
                if ($path) {
                  $fotoPaths[] = $path;
                }
              }
            }

            // Hanya set jika ada foto yang berhasil disimpan
            if (count($fotoPaths) > 0) {
              $data['foto_bukti'] = $fotoPaths;
              $data['waktu_foto'] = now();
            }
          }
        }

        // Simpan signature dan nama pengantar
        $data['nama_pengantar'] = $request->nama_pengantar;

        // Save signature (base64 data)
        if ($request->signature_pengantar) {
          // Remove data:image/png;base64, prefix jika ada
          $signatureData = $request->signature_pengantar;
          if (strpos($signatureData, 'data:') === 0) {
            // Decode base64 image dan save ke storage
            $image = str_replace('data:image/png;base64,', '', $signatureData);
            $image = str_replace(' ', '+', $image);
            $fileName = 'absensi/signatures/' . uniqid() . '.png';
            Storage::disk('public')->put($fileName, base64_decode($image));
            $data['signature_pengantar'] = $fileName;
          }
        }
      }

      $absensi = Absensi::create($data);

      return redirect()->route('absensi.index')
        ->with('success', 'Absensi berhasil ditambahkan.');
    } catch (\Exception $e) {
      \Log::error('Error storing absensi: ' . $e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
      ]);

      return redirect()->back()
        ->withInput()
        ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
  }

  /**
   * Show the form for editing an absensi
   */
  public function edit($id)
  {
    $absensi = Absensi::with('anakDidik')->findOrFail($id);
    $user = Auth::user();

    // Validasi bahwa hanya pembuat absensi atau admin yang bisa edit
    if ($absensi->user_id !== $user->id && $user->role !== 'admin') {
      abort(403, 'Anda tidak berhak mengubah absensi ini.');
    }

    // Cari karyawan berdasarkan user_id absensi (bukan user yang login)
    $karyawan = Karyawan::where('user_id', $absensi->user_id)->first();

    $anakDidiks = [];
    if ($karyawan) {
      $anakDidiks = AnakDidik::where('guru_fokus_id', $karyawan->id)
        ->orderBy('nama', 'asc')
        ->get();
    }

    return view('content.absensi.edit', [
      'absensi' => $absensi,
      'anakDidiks' => $anakDidiks,
      'jenisTandaFisik' => Absensi::getJenisTandaFisikOptions(),
    ]);
  }
  /**
   * Update an absensi
   */
  public function update(Request $request, $id)
  {
    $absensi = Absensi::findOrFail($id);
    $user = Auth::user();

    // Validasi bahwa hanya pembuat absensi atau admin yang bisa update
    if ($absensi->user_id !== $user->id && $user->role !== 'admin') {
      return redirect()->back()
        ->with('error', 'Anda tidak berhak mengubah absensi ini.');
    }

    $editType = $request->input('edit_type', 'absensi');

    if ($editType === 'penjemputan') {
      // Update data penjemputan
      $request->validate([
        'nama_penjemput' => 'required|string|max:255',
        'signature_penjemput' => 'nullable|string',
        'foto_penjemput.*' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        'keterangan_penjemput' => 'nullable|string',
        'keep_signature' => 'nullable|boolean',
      ]);

      $updateData = [
        'nama_penjemput' => $request->nama_penjemput,
        'keterangan_penjemput' => $request->keterangan_penjemput,
      ];

      // Handle signature
      if (!$request->has('keep_signature') || !$request->keep_signature) {
        if ($request->filled('signature_penjemput')) {
          // Delete old signature
          if ($absensi->signature_penjemput) {
            \Storage::disk('public')->delete($absensi->signature_penjemput);
          }

          // Save new signature
          $signatureData = $request->signature_penjemput;
          $signatureData = preg_replace('/^data:image\/\w+;base64,/', '', $signatureData);
          $signatureData = base64_decode($signatureData);

          $signatureFileName = 'signature_penjemput_' . time() . '_' . uniqid() . '.png';
          $signaturePath = 'absensi/' . $signatureFileName;

          \Storage::disk('public')->put($signaturePath, $signatureData);
          $updateData['signature_penjemput'] = $signaturePath;
        }
      }

      // Handle photos - combine existing and new
      $existingPhotos = $request->input('existing_foto_penjemput', []);
      $newPhotos = [];

      if ($request->hasFile('foto_penjemput')) {
        foreach ($request->file('foto_penjemput') as $index => $foto) {
          if ($foto && $foto->isValid()) {
            $fileName = 'jemput_' . time() . '_' . $index . '.' . $foto->getClientOriginalExtension();
            $path = $foto->storeAs('absensi', $fileName, 'public');
            $newPhotos[] = $path;
          }
        }
      }

      // Delete photos that were removed
      if ($absensi->foto_penjemput) {
        foreach ((array)$absensi->foto_penjemput as $oldPhoto) {
          if (!in_array($oldPhoto, $existingPhotos)) {
            \Storage::disk('public')->delete($oldPhoto);
          }
        }
      }

      // Merge existing and new photos
      $allPhotos = array_merge($existingPhotos, $newPhotos);
      $updateData['foto_penjemput'] = !empty($allPhotos) ? $allPhotos : null;

      $absensi->update($updateData);

      return redirect()->route('absensi.index')
        ->with('success', 'Data penjemputan berhasil diperbarui.');
    } else {
      // Update data absensi biasa
      $validationRules = [
        'anak_didik_id' => 'required|exists:anak_didiks,id',
        'tanggal' => 'required|date',
        'status' => 'required|in:hadir,izin,alfa',
        'keterangan' => 'nullable|string|max:500',
        'kondisi_fisik' => 'nullable|in:baik,ada_tanda',
        'jenis_tanda_fisik' => 'nullable|array',
        'keterangan_tanda_fisik' => 'nullable|string',
        'lokasi_luka' => 'nullable|string',
        'foto_bukti.*' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        'nama_pengantar' => 'nullable|string|max:255',
        'signature_pengantar' => 'nullable|string',
        'keep_signature_pengantar' => 'nullable|boolean',
      ];

      $request->validate($validationRules);

      // Cek apakah ada absensi lain untuk hari yang sama
      $existingAbsensi = Absensi::where('anak_didik_id', $request->anak_didik_id)
        ->whereDate('tanggal', $request->tanggal)
        ->where('id', '!=', $id)
        ->first();

      if ($existingAbsensi) {
        return redirect()->back()
          ->with('error', 'Absensi untuk anak didik ini pada tanggal tersebut sudah ada.');
      }

      $updateData = [
        'anak_didik_id' => $request->anak_didik_id,
        'tanggal' => $request->tanggal,
        'status' => $request->status,
        'keterangan' => $request->keterangan,
        'kondisi_fisik' => $request->kondisi_fisik ?? 'baik',
        'nama_pengantar' => $request->nama_pengantar,
      ];

      // Handle kondisi fisik data
      if ($request->kondisi_fisik === 'ada_tanda') {
        $updateData['jenis_tanda_fisik'] = $request->has('jenis_tanda_fisik')
          ? implode(', ', $request->jenis_tanda_fisik)
          : null;
        $updateData['keterangan_tanda_fisik'] = $request->keterangan_tanda_fisik;

        // Handle lokasi luka
        $lokasiLuka = $request->lokasi_luka ? json_decode($request->lokasi_luka, true) : [];
        $updateData['lokasi_luka'] = !empty($lokasiLuka) ? $lokasiLuka : null;

        // Handle foto bukti - combine existing and new
        $existingFotoBukti = $request->input('existing_foto_bukti', []);
        $newFotoBukti = [];

        if ($request->hasFile('foto_bukti')) {
          foreach ($request->file('foto_bukti') as $index => $foto) {
            if ($foto && $foto->isValid()) {
              $fileName = 'bukti_' . time() . '_' . $index . '.' . $foto->getClientOriginalExtension();
              $path = $foto->storeAs('absensi', $fileName, 'public');
              $newFotoBukti[] = $path;
            }
          }
        }

        // Delete photos that were removed
        if ($absensi->foto_bukti) {
          foreach ((array)$absensi->foto_bukti as $oldPhoto) {
            if (!in_array($oldPhoto, $existingFotoBukti)) {
              \Storage::disk('public')->delete($oldPhoto);
            }
          }
        }

        // Merge existing and new photos
        $allFotoBukti = array_merge($existingFotoBukti, $newFotoBukti);
        $updateData['foto_bukti'] = !empty($allFotoBukti) ? $allFotoBukti : null;
        $updateData['waktu_foto'] = !empty($newFotoBukti) ? now() : $absensi->waktu_foto;
      } else {
        // Reset tanda fisik data if kondisi_fisik is baik
        $updateData['jenis_tanda_fisik'] = null;
        $updateData['keterangan_tanda_fisik'] = null;
        $updateData['lokasi_luka'] = null;

        // Delete old foto_bukti if exists
        if ($absensi->foto_bukti) {
          foreach ((array)$absensi->foto_bukti as $oldPhoto) {
            \Storage::disk('public')->delete($oldPhoto);
          }
        }
        $updateData['foto_bukti'] = null;
        $updateData['waktu_foto'] = null;
      }

      // Handle signature pengantar
      if (!$request->has('keep_signature_pengantar') || !$request->keep_signature_pengantar) {
        if ($request->filled('signature_pengantar')) {
          // Delete old signature
          if ($absensi->signature_pengantar) {
            \Storage::disk('public')->delete($absensi->signature_pengantar);
          }

          // Save new signature
          $signatureData = $request->signature_pengantar;
          $signatureData = preg_replace('/^data:image\/\w+;base64,/', '', $signatureData);
          $signatureData = base64_decode($signatureData);

          $signatureFileName = 'signature_pengantar_' . time() . '_' . uniqid() . '.png';
          $signaturePath = 'absensi/' . $signatureFileName;

          \Storage::disk('public')->put($signaturePath, $signatureData);
          $updateData['signature_pengantar'] = $signaturePath;
        }
      }

      $absensi->update($updateData);

      return redirect()->route('absensi.index')
        ->with('success', 'Absensi berhasil diperbarui.');
    }
  }

  /**
   * Delete an absensi
   */
  public function destroy($id)
  {
    $absensi = Absensi::findOrFail($id);
    $user = Auth::user();

    // Validasi bahwa hanya pembuat absensi atau admin yang bisa delete
    if ($absensi->user_id !== $user->id && $user->role !== 'admin') {
      return response()->json([
        'success' => false,
        'message' => 'Anda tidak berhak menghapus absensi ini.'
      ], 403);
    }

    $absensi->delete();

    return response()->json([
      'success' => true,
      'message' => 'Absensi berhasil dihapus.'
    ]);
  }

  /**
   * Show detail absensi
   */
  public function showDetail($id)
  {
    $absensi = Absensi::with('anakDidik', 'guru')->findOrFail($id);

    return view('content.absensi.detail', [
      'absensi' => $absensi,
    ]);
  }

  /**
   * Get riwayat absensi untuk anak didik tertentu, grouped by month
   */
  public function getRiwayatAbsensi($anakDidikId)
  {
    $anakDidik = AnakDidik::findOrFail($anakDidikId);

    $absensiList = Absensi::where('anak_didik_id', $anakDidikId)
      ->with(['guru'])
      ->orderBy('tanggal', 'desc')
      ->get();

    if ($absensiList->isEmpty()) {
      return response()->json([
        'success' => true,
        'nama_anak' => $anakDidik->nama,
        'riwayat' => []
      ]);
    }

    // Group absensi by month and year
    $groupedByMonth = $absensiList->groupBy(function ($absensi) {
      return $absensi->tanggal->format('Y-m'); // Group by YYYY-MM
    });

    $riwayat = [];
    foreach ($groupedByMonth as $monthKey => $items) {
      // Parse month for display
      $dateObj = \Carbon\Carbon::createFromFormat('Y-m', $monthKey);
      $monthName = $dateObj->locale('id')->translatedFormat('F Y'); // e.g., "Februari 2026"

      $riwayat[] = [
        'month' => $monthName,
        'month_key' => $monthKey,
        'items' => $items->map(function ($absensi) {
          // Check if pickup notes are missing and date has passed
          $tanggalAbsensi = $absensi->tanggal;
          $today = \Carbon\Carbon::now()->startOfDay();
          $isPast = $tanggalAbsensi->isBefore($today);

          // Check if has pickup data - waktu_jemput should not be null
          $hasPickupData = ($absensi->waktu_jemput !== null);

          // Needs pickup data if: date is past AND no pickup data AND status is hadir
          $needsPickupData = $isPast && !$hasPickupData && $absensi->status === 'hadir';

          return [
            'id' => $absensi->id,
            'tanggal' => $absensi->tanggal->format('Y-m-d'),
            'tanggal_formatted' => $absensi->tanggal->locale('id')->translatedFormat('l, d F Y'),
            'status' => $absensi->status,
            'kondisi_fisik' => $absensi->kondisi_fisik,
            'jenis_tanda_fisik' => $absensi->jenis_tanda_fisik,
            'jenis_tanda_fisik_label' => $absensi->jenis_tanda_fisik_label,
            'keterangan' => $absensi->keterangan,
            'foto_bukti' => $absensi->foto_bukti,
            'guru_nama' => $absensi->guru ? $absensi->guru->name : '-',
            'waktu_jemput' => $absensi->waktu_jemput,
            'keterangan_penjemput' => $absensi->keterangan_penjemput,
            'needs_pickup_data' => $needsPickupData,
          ];
        })->values()
      ];
    }

    return response()->json([
      'success' => true,
      'nama_anak' => $anakDidik->nama,
      'riwayat' => $riwayat
    ]);
  }

  /**
   * Export all absensi data to PDF
   * Admin only
   */
  public function exportPdf(Request $request)
  {
    // Get all active students with guru fokus assigned
    $anakDidiks = AnakDidik::with('guruFokus')
      ->whereNotNull('guru_fokus_id')
      ->where('status', 'aktif')
      ->orderBy('nama', 'asc')
      ->get();

    // Get date range (default: current month)
    $startDate = $request->filled('start_date') ? $request->start_date : now()->startOfMonth()->toDateString();
    $endDate = $request->filled('end_date') ? $request->end_date : now()->endOfMonth()->toDateString();

    // Get all absensi data within date range for active students with guru fokus
    $absensis = Absensi::with(['anakDidik', 'guru'])
      ->join('anak_didiks', 'absensis.anak_didik_id', '=', 'anak_didiks.id')
      ->whereNotNull('anak_didiks.guru_fokus_id')
      ->where('anak_didiks.status', 'aktif')
      ->whereBetween('absensis.tanggal', [$startDate, $endDate])
      ->select('absensis.*')
      ->get()
      ->keyBy(function ($item) {
        return $item->tanggal->format('Y-m-d') . '_' . $item->anak_didik_id;
      });

    // Generate all date-student combinations
    $dateRange = [];
    $currentDate = \Carbon\Carbon::parse($startDate);
    $endDateCarbon = \Carbon\Carbon::parse($endDate);

    while ($currentDate->lte($endDateCarbon)) {
      $dateRange[] = $currentDate->format('Y-m-d');
      $currentDate->addDay();
    }

    // Build complete data array
    $completeData = [];
    foreach ($dateRange as $date) {
      foreach ($anakDidiks as $anak) {
        $key = $date . '_' . $anak->id;
        $absensi = $absensis->get($key);

        $completeData[] = [
          'tanggal' => \Carbon\Carbon::parse($date),
          'anak_didik' => $anak,
          'absensi' => $absensi,
        ];
      }
    }

    // Calculate summary per anak didik
    $summaryPerAnak = [];
    foreach ($anakDidiks as $anak) {
      $hadirCount = 0;
      $izinCount = 0;
      $alfaCount = 0;

      foreach ($dateRange as $date) {
        $key = $date . '_' . $anak->id;
        $absensi = $absensis->get($key);

        if ($absensi) {
          if ($absensi->status === 'hadir') $hadirCount++;
          elseif ($absensi->status === 'izin') $izinCount++;
          elseif ($absensi->status === 'alfa') $alfaCount++;
        }
      }

      $summaryPerAnak[] = [
        'anak_didik' => $anak,
        'hadir' => $hadirCount,
        'izin' => $izinCount,
        'alfa' => $alfaCount,
        'total' => $hadirCount + $izinCount + $alfaCount,
      ];
    }

    return view('content.absensi.export-pdf', [
      'completeData' => $completeData,
      'summaryPerAnak' => $summaryPerAnak,
      'startDate' => $startDate,
      'endDate' => $endDate,
      'anakDidiks' => $anakDidiks,
    ]);
  }

  public function jemput(Request $request, $id)
  {
    try {
      // Validasi input
      $request->validate([
        'nama_penjemput' => 'required|string|max:255',
        'signature_penjemput' => 'required|string',
        'foto_penjemput.*' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        'keterangan_penjemput' => 'nullable|string',
      ]);

      // Find absensi record
      $absensi = Absensi::findOrFail($id);

      // Check if already picked up
      if ($absensi->waktu_jemput) {
        return response()->json([
          'success' => false,
          'message' => 'Anak didik sudah dijemput sebelumnya'
        ], 400);
      }

      // Process signature
      $signaturePath = null;
      if ($request->filled('signature_penjemput')) {
        $signatureData = $request->signature_penjemput;
        // Remove data:image/png;base64, prefix if exists
        $signatureData = preg_replace('/^data:image\/\w+;base64,/', '', $signatureData);
        $signatureData = base64_decode($signatureData);

        $signatureFileName = 'signature_penjemput_' . time() . '_' . uniqid() . '.png';
        $signaturePath = 'absensi/' . $signatureFileName;

        \Storage::disk('public')->put($signaturePath, $signatureData);
      }

      // Process photos
      $fotoPaths = [];
      if ($request->hasFile('foto_penjemput')) {
        foreach ($request->file('foto_penjemput') as $index => $foto) {
          if ($foto && $foto->isValid()) {
            $fileName = 'jemput_' . time() . '_' . $index . '.' . $foto->getClientOriginalExtension();
            $path = $foto->storeAs('absensi', $fileName, 'public');
            $fotoPaths[] = $path;
          }
        }
      }

      // Update absensi record
      $absensi->update([
        'waktu_jemput' => now(),
        'nama_penjemput' => $request->nama_penjemput,
        'foto_penjemput' => !empty($fotoPaths) ? $fotoPaths : null,
        'signature_penjemput' => $signaturePath,
        'keterangan_penjemput' => $request->keterangan_penjemput,
      ]);

      return response()->json([
        'success' => true,
        'message' => 'Data penjemputan berhasil disimpan'
      ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
      return response()->json([
        'success' => false,
        'message' => 'Validasi gagal',
        'errors' => $e->errors()
      ], 422);
    } catch (\Exception $e) {
      \Log::error('Error saving jemput data: ' . $e->getMessage());
      return response()->json([
        'success' => false,
        'message' => 'Gagal menyimpan data penjemputan: ' . $e->getMessage()
      ], 500);
    }
  }
}
