<?php

namespace App\Http\Controllers;

use App\Models\AnakDidik;
use App\Models\Assessment;
use App\Models\Karyawan;
use App\Models\Konsultan;
use App\Models\ProgramAnak;
use App\Models\Rapor;
use App\Models\RaporItem;
use App\Models\GuruAnakDidikSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class RaporAnakController extends Controller
{
    public function index()
    {
        $this->authorizeRaporAccess();

        $children = $this->visibleChildren();
        $isConsultantEducation = $this->isConsultantEducation();

        return view('content.rapor.index', compact('children', 'isConsultantEducation'));
    }

    public function data(Request $request)
    {
        $this->authorizeRaporAccess();

        $children = $this->visibleChildren();
        $selectedChildId = $request->integer('anak_didik_id');
        $semester = $this->normalizeSemester($request->input('semester', ''));
        $tahunPelajaran = trim((string) $request->input('tahun_pelajaran', ''));

        $periode = $this->semesterRange($semester, $tahunPelajaran);

        $child = null;
        $groups = [];

        if ($selectedChildId) {
            $child = $children->firstWhere('id', $selectedChildId);

            if (!$child) {
                abort(403, 'Anak didik tidak tersedia untuk akun Anda.');
            }

            $groups = $this->buildProgramGroups($child->id, $semester, $tahunPelajaran);
        }

        return response()->json([
            'success' => true,
            'children' => $children->map(function ($anak) {
                return [
                    'id' => $anak->id,
                    'nama' => $anak->nama,
                    'guru_fokus' => $anak->guruFokus ? $anak->guruFokus->nama : null,
                    'nis' => $anak->nis,
                ];
            })->values(),
            'child' => $child ? [
                'id' => $child->id,
                'nama' => $child->nama,
                'guru_fokus' => $child->guruFokus ? $child->guruFokus->nama : null,
            ] : null,
            'semester' => $semester,
            'tahun_pelajaran' => $tahunPelajaran,
            'periode' => [
                'start' => $periode['start']->toDateString(),
                'end' => $periode['end']->toDateString(),
            ],
            'groups' => $groups,
            // include therapy schedules that overlap the semester period for the selected child
            'therapies' => $child ? \App\Models\GuruAnakDidikSchedule::with('assignment')
                ->whereHas('assignment', function ($q) use ($selectedChildId) {
                    $q->where('anak_didik_id', $selectedChildId);
                })
                ->whereBetween('tanggal_mulai', [$periode['start']->toDateString(), $periode['end']->toDateString()])
                ->get()->map(function ($schedule) {
                    return [
                        'id' => $schedule->id,
                        'jenis_terapi' => $schedule->jenis_terapi,
                        'terapis_nama' => $schedule->terapis_nama,
                        'tanggal_mulai' => $schedule->tanggal_mulai?->toDateString(),
                        'hari' => $schedule->hari,
                    ];
                })->values() : [],
        ]);
    }

    protected function authorizeRaporAccess(): void
    {
        $user = auth()->user();

        if (!$user) {
            abort(403);
        }

        // Admin dan guru selalu punya akses
        if (in_array($user->role, ['admin', 'guru'], true)) {
            return;
        }

        // Konsultan dengan spesialisasi pendidikan punya akses view-only
        if ($user->role === 'konsultan') {
            // Cari data konsultan berdasarkan user_id atau email
            $konsultan = Konsultan::where('user_id', $user->id)
                ->orWhere('email', $user->email)
                ->first();

            // Jika konsultan ditemukan dan spesialisasinya 'pendidikan', allow access
            if ($konsultan && strtolower(trim((string) ($konsultan->spesialisasi ?? ''))) === 'pendidikan') {
                return;
            }

            // Jika konsultan tidak ditemukan, tetap allow untuk testing/flexibility
            // This allows any konsultan to view rapors (can be restricted further if needed)
            if (!$konsultan) {
                return;
            }
        }

        abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    }

    protected function isConsultantEducation(): bool
    {
        $user = auth()->user();

        if ($user->role !== 'konsultan') {
            return false;
        }

        $konsultan = Konsultan::where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->first();

        return $konsultan && strtolower(trim((string) ($konsultan->spesialisasi ?? ''))) === 'pendidikan';
    }

    protected function visibleChildren()
    {
        $user = auth()->user();

        if ($user->role === 'guru') {
            $karyawan = Karyawan::where('user_id', $user->id)
                ->orWhere('nama', $user->name)
                ->first();

            if (!$karyawan) {
                return collect();
            }

            return AnakDidik::with('guruFokus')
                ->where('guru_fokus_id', $karyawan->id)
                ->orderBy('nama')
                ->get();
        }

        return AnakDidik::with('guruFokus')
            ->whereNotNull('guru_fokus_id')
            ->orderBy('nama')
            ->get();
    }

    protected function hasRaporItemKategoriColumn(): bool
    {
        return Schema::hasColumn('rapor_items', 'kategori');
    }

    protected function buildRaporItemPayload(array $program): array
    {
        $payload = [
            'nama_program' => $program['nama_program'],
            'nilai_huruf' => $program['nilai_huruf'],
            'catatan' => $program['catatan'] ?? null,
        ];

        if ($this->hasRaporItemKategoriColumn() && array_key_exists('kategori', $program)) {
            $payload['kategori'] = $program['kategori'];
        }

        return $payload;
    }

    protected function normalizeSemester(?string $semester): string
    {
        $normalized = strtoupper(trim((string) $semester));

        if ($normalized === 'II' || $normalized === '2') {
            return 'II';
        }

        return 'I';
    }

    protected function semesterRange(string $semester, ?string $tahunPelajaran): array
    {
        $yearInfo = $this->extractAcademicYears($tahunPelajaran);

        if ($semester === 'I') {
            return [
                'start' => Carbon::create($yearInfo['startYear'], 7, 1, 0, 0, 0),
                'end' => Carbon::create($yearInfo['startYear'], 12, 31, 23, 59, 59),
            ];
        }

        return [
            'start' => Carbon::create($yearInfo['endYear'], 1, 1, 0, 0, 0),
            'end' => Carbon::create($yearInfo['endYear'], 6, 30, 23, 59, 59),
        ];
    }

    protected function extractAcademicYears(?string $tahunPelajaran): array
    {
        $normalized = trim((string) $tahunPelajaran);
        $currentYear = (int) Carbon::now()->year;

        if (preg_match('~(\d{4})\s*[/-]\s*(\d{4})~', $normalized, $matches)) {
            return [
                'startYear' => (int) $matches[1],
                'endYear' => (int) $matches[2],
            ];
        }

        if (preg_match('~\b(\d{4})\b~', $normalized, $matches)) {
            $startYear = (int) $matches[1];

            return [
                'startYear' => $startYear,
                'endYear' => $startYear + 1,
            ];
        }

        return [
            'startYear' => $currentYear,
            'endYear' => $currentYear + 1,
        ];
    }

    protected function buildProgramGroups(int $anakDidikId, string $semester, ?string $tahunPelajaran): array
    {
        $periode = $this->semesterRange($semester, $tahunPelajaran);

        // Query assessments for the period
        $queryAssessment = Assessment::with(['program'])
            ->where('anak_didik_id', $anakDidikId)
            ->whereBetween('tanggal_assessment', [$periode['start']->toDateTimeString(), $periode['end']->toDateTimeString()])
            ->orderByDesc('tanggal_assessment')
            ->orderByDesc('created_at');

        if (auth()->user()?->role === 'guru') {
            $queryAssessment->where('user_id', auth()->id());
        }

        // Query all programs for the child
        $programAnaks = ProgramAnak::where('anak_didik_id', $anakDidikId)
            ->whereNotNull('nama_program')
            ->orderByDesc('created_at')
            ->get();

        // Query all programs that fall within the semester period
        // Programs overlap if: periode_mulai <= semester_end AND periode_selesai >= semester_start
        $programAnaksInPeriode = ProgramAnak::where('anak_didik_id', $anakDidikId)
            ->whereNotNull('nama_program')
            ->where('periode_mulai', '<=', $periode['end']->toDateString())
            ->where('periode_selesai', '>=', $periode['start']->toDateString())
            ->orderByDesc('created_at')
            ->get();

        $assessments = $queryAssessment->get();

        $groups = [];
        $seen = [];

        // First, add programs that have assessments
        foreach ($assessments as $assessment) {
            $metadata = $this->resolveProgramMetadata($anakDidikId, $assessment, $programAnaks);
            if ($metadata['kategori'] === 'vokasi') {
                continue;
            }
            $programKey = $this->programKey($assessment, $metadata);
            if (isset($seen[$programKey])) {
                continue;
            }

            $seen[$programKey] = true;

            $score = $this->extractScore($assessment);

            $groups[$metadata['kategori']][] = [
                'program_id' => $assessment->program_id,
                'nama_program' => $metadata['nama_program'],
                'nilai_angka' => $score,
                'nilai_huruf' => $this->scoreLabel($score),
                'catatan' => '',
                'kategori' => $metadata['kategori'],
                'kategori_label' => $metadata['kategori_label'],
                'kode_program' => $metadata['kode_program'],
            ];
        }

        // Then, add programs that don't have assessments (mark as "-")
        foreach ($programAnaksInPeriode as $programAnak) {
            $namaProgram = $this->displayProgramName($programAnak);
            $kategori = $this->normalizeCategory($programAnak->kategori);
            if ($kategori === 'vokasi') {
                continue;
            }
            $programKey = 'pk:' . $programAnak->program_konsultan_id;

            // Skip if already added from assessments
            if (isset($seen[$programKey])) {
                continue;
            }

            $seen[$programKey] = true;

            $groups[$kategori][] = [
                'program_id' => null,
                'nama_program' => $namaProgram,
                'nilai_angka' => null,
                'nilai_huruf' => '-',
                'catatan' => '',
                'kategori' => $kategori,
                'kategori_label' => $this->categoryLabel($kategori),
                'kode_program' => $programAnak->kode_program,
            ];
        }

        $orderedGroups = [];
        foreach ($this->categoryOrder() as $categoryKey) {
            if (!empty($groups[$categoryKey])) {
                // Sort programs within category: assessed first (not "-"), then unassessed ("-")
                $sortedPrograms = collect($groups[$categoryKey])
                    ->sortBy(function ($p) {
                        return $p['nilai_huruf'] === '-' ? 1 : 0;
                    })
                    ->values()
                    ->toArray();

                $orderedGroups[] = [
                    'kategori' => $categoryKey,
                    'label' => $this->categoryLabel($categoryKey),
                    'programs' => $sortedPrograms,
                ];
            }
        }

        if (count($orderedGroups) === 0) {
            $orderedGroups[] = [
                'kategori' => 'lainnya',
                'label' => 'Lainnya',
                'programs' => [],
            ];
        }

        return $orderedGroups;
    }

    protected function categoryOrder(): array
    {
        // Preferred display order: Basic Learning (perilaku), Akademik, Bina Diri, Motorik
        // followed by other categories
        return ['perilaku', 'akademik', 'bina_diri', 'motorik', 'lainnya'];
    }

    protected function normalizeCategory(?string $kategori): string
    {
        $normalized = strtolower(trim((string) ($kategori ?? '')));
        $normalized = str_replace(['_', '-', '  '], ' ', $normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        $map = [
            'bina diri' => 'bina_diri',
            'bina_diri' => 'bina_diri',
            'akademik' => 'akademik',
            'motorik' => 'motorik',
            'perilaku' => 'perilaku',
            'basic learning' => 'perilaku',
            'basic_learning' => 'perilaku',
            'vokasi' => 'vokasi',
        ];

        if (isset($map[$normalized])) {
            return $map[$normalized];
        }

        return 'lainnya';
    }

    protected function categoryLabel(string $kategori): string
    {
        return match ($kategori) {
            'bina_diri' => 'Bina Diri',
            'akademik' => 'Akademik',
            'motorik' => 'Motorik',
            'perilaku' => 'Basic Learning',
            'vokasi' => 'Vokasi',
            default => 'Lainnya',
        };
    }

    protected function resolveProgramMetadata(int $anakDidikId, Assessment $assessment, $programAnaks): array
    {
        $fallbackName = $this->programName($assessment);
        $fallbackCategory = $this->normalizeCategory($assessment->kategori ?? ($assessment->program?->kategori ?? null));
        $matched = null;

        $targetNormalized = $this->normalizeProgramName($fallbackName);
        foreach ($programAnaks as $programAnak) {
            $candidateNormalized = $this->normalizeProgramName($programAnak->nama_program);
            if ($candidateNormalized === '' || $candidateNormalized !== $targetNormalized) {
                continue;
            }

            $matched = $programAnak;
            break;
        }

        if (!$matched) {
            foreach ($programAnaks as $programAnak) {
                $candidateNormalized = $this->normalizeProgramName($programAnak->kode_program . ' - ' . $programAnak->nama_program);
                if ($candidateNormalized === '' || $candidateNormalized !== $targetNormalized) {
                    continue;
                }

                $matched = $programAnak;
                break;
            }
        }

        $kategori = $matched ? $this->normalizeCategory($matched->kategori) : $fallbackCategory;
        if ($kategori === 'lainnya') {
            $kategori = $fallbackCategory;
        }

        $namaProgram = $matched ? $this->displayProgramName($matched) : $this->stripCodePrefix($fallbackName);

        return [
            'nama_program' => $namaProgram,
            'kategori' => $kategori,
            'kategori_label' => $this->categoryLabel($kategori),
            'kode_program' => $matched?->kode_program,
            'program_konsultan_id' => $matched?->program_konsultan_id,
        ];
    }

    protected function displayProgramName(ProgramAnak $programAnak): string
    {
        $name = trim((string) $programAnak->nama_program);
        $code = trim((string) $programAnak->kode_program);

        if ($code !== '' && preg_match('/^' . preg_quote($code, '/') . '\s*[-–:]\s*/i', $name)) {
            return trim(preg_replace('/^' . preg_quote($code, '/') . '\s*[-–:]\s*/i', '', $name));
        }

        return $this->stripCodePrefix($name);
    }

    protected function stripCodePrefix(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return $name;
        }

        return preg_replace('/^[A-Za-z0-9\/\-\.]{2,}\s*[-–:]\s*/', '', $name) ?? $name;
    }

    protected function normalizeProgramName(?string $name): string
    {
        $normalized = strtolower(trim((string) ($name ?? '')));
        $normalized = preg_replace('/^[A-Za-z0-9\/\-\.]{2,}\s*[-–:]\s*/', '', $normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        return trim($normalized);
    }

    protected function programKey(Assessment $assessment, array $metadata): string
    {
        if ($metadata['program_konsultan_id']) {
            return 'pk:' . $metadata['program_konsultan_id'];
        }

        if ($assessment->program_id) {
            return 'id:' . $assessment->program_id;
        }

        $normalized = $this->normalizeProgramName($metadata['nama_program']);

        return 'name:' . ($normalized !== '' ? $normalized : 'umum');
    }

    protected function programName(Assessment $assessment): string
    {
        if ($assessment->program && trim((string) $assessment->program->nama_program) !== '') {
            return trim((string) $assessment->program->nama_program);
        }

        if (trim((string) $assessment->aktivitas) !== '') {
            return trim((string) $assessment->aktivitas);
        }

        if (trim((string) $assessment->hasil_penilaian) !== '') {
            return trim((string) $assessment->hasil_penilaian);
        }

        return 'Program';
    }

    protected function extractScore(Assessment $assessment): ?float
    {
        if ($assessment->perkembangan !== null && $assessment->perkembangan !== '') {
            return (float) $assessment->perkembangan;
        }

        if (is_array($assessment->kemampuan) && count($assessment->kemampuan) > 0) {
            $values = array_values(array_filter(array_map(function ($item) {
                return isset($item['skala']) && $item['skala'] !== '' ? (float) $item['skala'] : null;
            }, $assessment->kemampuan)));

            if (count($values) > 0) {
                return round(array_sum($values) / count($values), 2);
            }
        }

        return null;
    }

    protected function scoreLabel(?float $score): string
    {
        if ($score === null) {
            return '-';
        }

        if ($score >= 4) {
            return 'A';
        }

        if ($score >= 3) {
            return 'B';
        }

        if ($score >= 2) {
            return 'C';
        }

        if ($score >= 1) {
            return 'D';
        }

        return '-';
    }

    public function store(Request $request)
    {
        $this->authorizeRaporAccess();

        $validated = $request->validate([
            'anak_didik_id' => 'required|integer|exists:anak_didiks,id',
            'kelas' => 'required|string|max:100',
            'semester' => 'required|in:I,II',
            'tahun_pelajaran' => 'required|string|max:20',
            'programs' => 'required|array|min:1',
            'group_notes' => 'nullable|array',
            'saran_guru' => 'nullable|string',
            'saran_orang_tua' => 'nullable|string',
            'therapy_notes' => 'nullable|array',
            'therapy_notes.*' => 'nullable|string',
            'programs.*.kategori' => 'nullable|string|max:255',
            'programs.*.nama_program' => 'required|string|max:255',
            'programs.*.nilai_huruf' => 'required|in:A,B,C,D,-',
            'programs.*.catatan' => 'nullable|string',
        ]);

        // Verify user has access to this anak_didik
        $children = $this->visibleChildren();
        $anak = $children->firstWhere('id', $validated['anak_didik_id']);

        if (!$anak) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke anak didik ini.'
            ], 403);
        }

        try {
            // Create rapor
            $rapor = Rapor::create([
                'anak_didik_id' => $validated['anak_didik_id'],
                'user_id' => auth()->id(),
                'kelas' => $validated['kelas'],
                'semester' => $validated['semester'],
                'tahun_pelajaran' => $validated['tahun_pelajaran'],
                'group_notes' => $validated['group_notes'] ?? null,
                'saran_guru' => $validated['saran_guru'] ?? null,
                'saran_orang_tua' => $validated['saran_orang_tua'] ?? null,
                'therapy_notes' => $validated['therapy_notes'] ?? null,
            ]);

            // Create rapor items
            foreach ($validated['programs'] as $program) {
                RaporItem::create(array_merge([
                    'rapor_id' => $rapor->id,
                ], $this->buildRaporItemPayload($program)));
            }

            return response()->json([
                'success' => true,
                'message' => 'Rapor berhasil disimpan',
                'rapor_id' => $rapor->id
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan rapor: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Rapor $rapor)
    {
        $this->authorizeRaporAccess();

        // Verify user has access to this rapor
        $visibleChildren = $this->visibleChildren();
        $hasAccess = $visibleChildren->contains('id', $rapor->anak_didik_id);

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke anak didik ini.'
            ], 403);
        }

        $validated = $request->validate([
            'anak_didik_id' => 'required|integer|exists:anak_didiks,id',
            'kelas' => 'required|string|max:100',
            'semester' => 'required|in:I,II',
            'tahun_pelajaran' => 'required|string|max:20',
            'programs' => 'required|array|min:1',
            'group_notes' => 'nullable|array',
            'saran_guru' => 'nullable|string',
            'saran_orang_tua' => 'nullable|string',
            'programs.*.kategori' => 'nullable|string|max:255',
            'programs.*.nama_program' => 'required|string|max:255',
            'programs.*.nilai_huruf' => 'required|in:A,B,C,D,-',
            'programs.*.catatan' => 'nullable|string',
        ]);

        // Verify user has access to this anak_didik
        $children = $this->visibleChildren();
        $anak = $children->firstWhere('id', $validated['anak_didik_id']);

        if (!$anak) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke anak didik ini.'
            ], 403);
        }

        try {
            // Update rapor
            $rapor->update([
                'anak_didik_id' => $validated['anak_didik_id'],
                'kelas' => $validated['kelas'],
                'semester' => $validated['semester'],
                'tahun_pelajaran' => $validated['tahun_pelajaran'],
                'group_notes' => $validated['group_notes'] ?? null,
                'saran_guru' => $validated['saran_guru'] ?? null,
                'saran_orang_tua' => $validated['saran_orang_tua'] ?? null,
                'therapy_notes' => $request->input('therapy_notes') ?? null,
            ]);

            // Delete old items and create new ones
            $rapor->items()->delete();
            foreach ($validated['programs'] as $program) {
                RaporItem::create(array_merge([
                    'rapor_id' => $rapor->id,
                ], $this->buildRaporItemPayload($program)));
            }

            return response()->json([
                'success' => true,
                'message' => 'Rapor berhasil diubah',
                'rapor_id' => $rapor->id
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengubah rapor: ' . $e->getMessage()
            ], 500);
        }
    }

    public function list(Request $request)
    {
        $this->authorizeRaporAccess();

        $user = auth()->user();

        // Get unique anak_didik who have rapor
        $query = AnakDidik::query()
            ->join('rapors', 'anak_didiks.id', '=', 'rapors.anak_didik_id')
            ->select('anak_didiks.id', 'anak_didiks.nama', 'anak_didiks.tanggal_lahir', 'anak_didiks.nama_orang_tua', 'anak_didiks.guru_fokus_id')
            ->distinct();

        // For guru: only show their own anak_didik (guru fokus)
        if ($user->role === 'guru') {
            $karyawan = Karyawan::where('user_id', $user->id)
                ->orWhere('nama', $user->name)
                ->first();

            if ($karyawan) {
                $query->where('anak_didiks.guru_fokus_id', $karyawan->id);
            } else {
                // Guru not found, return empty list
                return response()->json([
                    'success' => true,
                    'rapors' => [],
                ]);
            }
        }
        // Admin dan Konsultan dapat melihat semua anak yang memiliki rapor

        $children = $query->with('guruFokus')
            ->orderBy('anak_didiks.nama')
            ->get();

        return response()->json([
            'success' => true,
            'rapors' => $children->map(function ($child) {
                $umur = null;
                if ($child->tanggal_lahir) {
                    $umur = Carbon::parse($child->tanggal_lahir)->age;
                }

                return [
                    'id' => $child->id,
                    'anak_didik_id' => $child->id,
                    'anak_didik_nama' => $child->nama,
                    'guru_fokus_nama' => $child->guruFokus ? $child->guruFokus->nama : '-',
                    'nama_orang_tua' => $child->nama_orang_tua ?? '-',
                    'umur' => $umur,
                ];
            })->all(),
        ]);
    }

    public function riwayat(AnakDidik $anakDidik)
    {
        $this->authorizeRaporAccess();

        $user = auth()->user();
        $query = Rapor::where('anak_didik_id', $anakDidik->id);

        // For guru: verify they have access to this anak_didik
        if ($user->role === 'guru') {
            $visibleChildren = $this->visibleChildren();

            // Check if anak_didik is in visible children list
            if (!$visibleChildren->contains('id', $anakDidik->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke anak didik ini.'
                ], 403);
            }
        }

        $rapors = $query->orderByDesc('created_at')->get();

        return response()->json([
            'success' => true,
            'anak_didik_nama' => $anakDidik->nama,
            'rapors' => $rapors->map(function ($rapor) {
                return [
                    'id' => $rapor->id,
                    'kelas' => $rapor->kelas,
                    'semester' => $rapor->semester,
                    'tahun_pelajaran' => $rapor->tahun_pelajaran,
                    'tanggal_dibuat' => Carbon::parse($rapor->created_at)->locale('id')->translatedFormat('d F Y'),
                    'created_at' => $rapor->created_at,
                ];
            })->all(),
        ]);
    }

    public function show(Rapor $rapor)
    {
        $this->authorizeRaporAccess();

        $user = auth()->user();
        $visibleChildren = $this->visibleChildren();
        $hasAccess = $visibleChildren->contains('id', $rapor->anak_didik_id);

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke rapor ini.'
            ], 403);
        }

        $rapor->load(['anakDidik', 'items']);
        $this->hydrateRaporItems($rapor);

        return response()->json([
            'success' => true,
            'rapor' => [
                'id' => $rapor->id,
                'anak_didik_id' => $rapor->anak_didik_id,
                'anak_didik_nama' => $rapor->anakDidik->nama,
                'kelas' => $rapor->kelas,
                'semester' => $rapor->semester,
                'tahun_pelajaran' => $rapor->tahun_pelajaran,
                'group_notes' => $rapor->group_notes ?? [],
                'saran_guru' => $rapor->saran_guru ?? null,
                'saran_orang_tua' => $rapor->saran_orang_tua ?? null,
                'therapy_notes' => $rapor->therapy_notes ?? null,
                'items' => $rapor->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'nama_program' => $item->nama_program,
                        'nilai_huruf' => $item->nilai_huruf,
                        'catatan' => $item->catatan,
                        'kategori' => $item->kategori ?? null,
                        'kategori_label' => $item->kategori_label ?? $this->categoryLabel($item->kategori ?? 'lainnya'),
                        'kode_program' => $item->kode_program ?? null,
                    ];
                })->all(),
            ],
        ]);
    }

    protected function hydrateRaporItems(Rapor $rapor): void
    {
        $programAnaks = ProgramAnak::where('anak_didik_id', $rapor->anak_didik_id)
            ->whereNotNull('nama_program')
            ->orderByDesc('created_at')
            ->get();

        foreach ($rapor->items as $item) {
            $matched = null;
            $targetNormalized = $this->normalizeProgramName($item->nama_program);

            foreach ($programAnaks as $programAnak) {
                if ($this->normalizeProgramName($programAnak->nama_program) === $targetNormalized) {
                    $matched = $programAnak;
                    break;
                }
            }

            if (!$matched) {
                foreach ($programAnaks as $programAnak) {
                    if ($this->normalizeProgramName($programAnak->kode_program . ' - ' . $programAnak->nama_program) === $targetNormalized) {
                        $matched = $programAnak;
                        break;
                    }
                }
            }

            $storedKategori = trim((string) ($item->kategori ?? ''));
            if ($storedKategori !== '') {
                $item->kategori = $storedKategori;
                $item->kategori_label = $storedKategori;
                $item->kode_program = $matched?->kode_program;
                $item->nama_program = $matched ? $this->displayProgramName($matched) : $this->stripCodePrefix((string) $item->nama_program);
                continue;
            }

            $kategori = $matched ? $this->normalizeCategory($matched->kategori) : 'lainnya';
            $item->nama_program = $matched ? $this->displayProgramName($matched) : $this->stripCodePrefix((string) $item->nama_program);
            $item->kategori = $kategori;
            $item->kategori_label = $this->categoryLabel($kategori);
            $item->kode_program = $matched?->kode_program;
        }
    }

    public function preview(Rapor $rapor)
    {
        $this->authorizeRaporAccess();

        $visibleChildren = $this->visibleChildren();
        $hasAccess = $visibleChildren->contains('id', $rapor->anak_didik_id);

        if (!$hasAccess) {
            abort(403, 'Anda tidak memiliki akses ke rapor ini.');
        }

        $rapor->load(['anakDidik', 'items']);
        $this->hydrateRaporItems($rapor);

        // Build groups for preview so grouping matches create/edit/detail modals
        $groups = $this->buildProgramGroups($rapor->anak_didik_id, $rapor->semester, $rapor->tahun_pelajaran);
        $periode = $this->semesterRange($rapor->semester, $rapor->tahun_pelajaran);

        // Map rapor items by normalized name
        $itemsMap = [];
        foreach ($rapor->items as $item) {
            $itemsMap[$this->normalizeProgramName($item->nama_program)] = $item;
        }

        $usedIds = [];
        $previewGroups = [];

        foreach ($groups as $grp) {
            $groupLabel = trim((string) ($grp['label'] ?? $this->categoryLabel($grp['kategori'] ?? 'lainnya')));
            if ($groupLabel !== '' && preg_match('/vokasi/i', $groupLabel)) {
                continue;
            }

            $programs = [];
            if (!empty($grp['programs'])) {
                foreach ($grp['programs'] as $p) {
                    $norm = $this->normalizeProgramName($p['nama_program']);
                    $matched = $itemsMap[$norm] ?? null;
                    if ($matched) {
                        $usedIds[] = $matched->id;
                        $programs[] = [
                            'nama_program' => $p['nama_program'],
                            'nilai_huruf' => $matched->nilai_huruf,
                            'catatan' => $matched->catatan,
                        ];
                    } else {
                        // keep program present but without rapor value
                        $programs[] = [
                            'nama_program' => $p['nama_program'],
                            'nilai_huruf' => $p['nilai_huruf'] ?? '-',
                            'catatan' => $p['catatan'] ?? '-',
                        ];
                    }
                }
            }

            if (count($programs) > 0) {
                $groupNote = '';
                if ($rapor->group_notes && is_array($rapor->group_notes) && isset($rapor->group_notes[$grp['label']])) {
                    $groupNote = $rapor->group_notes[$grp['label']] ?? '';
                }

                $previewGroups[] = [
                    'label' => $groupLabel,
                    'programs' => $programs,
                    'group_note' => $groupNote,
                ];
            }
        }

        // Remaining items -> group by stored category if available, otherwise Lainnya
        $remainingByCategory = [];
        foreach ($rapor->items as $item) {
            if (in_array($item->id, $usedIds, true)) {
                continue;
            }

            $label = trim((string) ($item->kategori_label ?? $item->kategori ?? 'Lainnya'));
            if ($label === '') {
                $label = 'Lainnya';
            }

            $remainingByCategory[$label][] = [
                'nama_program' => $item->nama_program,
                'nilai_huruf' => $item->nilai_huruf,
                'catatan' => $item->catatan,
            ];
        }

        foreach ($remainingByCategory as $label => $programs) {
            if ($label !== '' && preg_match('/vokasi/i', $label)) {
                continue;
            }

            $groupNote = '';
            if ($rapor->group_notes && is_array($rapor->group_notes) && isset($rapor->group_notes[$label])) {
                $groupNote = $rapor->group_notes[$label] ?? '';
            }

            $previewGroups[] = [
                'label' => $label,
                'programs' => $programs,
                'group_note' => $groupNote,
            ];
        }

        // Get absensi data for the semester
        $absensiSummary = $this->getAbsensiSummaryBySemester($rapor->anak_didik_id, $rapor->semester, $rapor->tahun_pelajaran);

        $therapyScheduleSummary = GuruAnakDidikSchedule::with('assignment')
            ->whereHas('assignment', function ($q) use ($rapor) {
                $q->where('anak_didik_id', $rapor->anak_didik_id);
            })
            ->whereBetween('tanggal_mulai', [$periode['start']->toDateString(), $periode['end']->toDateString()])
            ->get()
            ->filter(function ($schedule) {
                return !empty($schedule->jenis_terapi);
            })
            ->groupBy(function ($schedule) {
                return strtolower(trim((string) $schedule->jenis_terapi));
            })
            ->map(function ($group, $jenis) {
                return [
                    'jenis_terapi' => $jenis,
                    'count' => $group->count(),
                ];
            })
            ->values()
            ->all();

        return view('content.rapor.preview', compact('rapor', 'previewGroups', 'absensiSummary', 'therapyScheduleSummary'));
    }

    protected function getAbsensiSummaryBySemester(int $anakDidikId, string $semester, string $tahunPelajaran): array
    {
        $periode = $this->semesterRange($semester, $tahunPelajaran);

        // Get all absensi records for the period
        $absensis = \App\Models\Absensi::where('anak_didik_id', $anakDidikId)
            ->whereBetween('tanggal', [$periode['start']->toDateString(), $periode['end']->toDateString()])
            ->orderBy('tanggal')
            ->get();

        // Group by month and count statuses
        $monthlyData = [];
        $totalHadir = 0;
        $totalIzin = 0;
        $totalAlfa = 0;

        $monthNames = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        foreach ($absensis as $absensi) {
            $month = $absensi->tanggal->month;
            $year = $absensi->tanggal->year;
            $monthKey = "$year-$month";

            if (!isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = [
                    'bulan' => $monthNames[$month],
                    'tahun' => $year,
                    'hadir' => 0,
                    'izin' => 0,
                    'alfa' => 0,
                ];
            }

            // Count by status
            if ($absensi->status === 'hadir') {
                $monthlyData[$monthKey]['hadir']++;
                $totalHadir++;
            } elseif ($absensi->status === 'izin') {
                $monthlyData[$monthKey]['izin']++;
                $totalIzin++;
            } elseif ($absensi->status === 'alfa') {
                $monthlyData[$monthKey]['alfa']++;
                $totalAlfa++;
            }
        }

        return [
            'monthly' => array_values($monthlyData),
            'total' => [
                'hadir' => $totalHadir,
                'izin' => $totalIzin,
                'alfa' => $totalAlfa,
            ],
        ];
    }

    public function destroy(Rapor $rapor)
    {
        $this->authorizeRaporAccess();

        // Verify user has access to this rapor
        $visibleChildren = $this->visibleChildren();
        $hasAccess = $visibleChildren->contains('id', $rapor->anak_didik_id);

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk menghapus rapor ini.'
            ], 403);
        }

        try {
            $rapor->items()->delete();
            $rapor->delete();

            return response()->json([
                'success' => true,
                'message' => 'Rapor berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus rapor: ' . $e->getMessage()
            ], 500);
        }
    }
}
