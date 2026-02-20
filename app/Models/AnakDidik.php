<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class AnakDidik extends Model
{
    protected $fillable = [
        'vokasi_diikuti',
        'guru_fokus_id',
        'nama',
        'nis',
        'jenis_kelamin',
        'tanggal_lahir',
        'tempat_lahir',
        'alamat',
        'no_telepon',
        'email',
        'nama_orang_tua',
        'no_telepon_orang_tua',
        'no_kk',
        'nik',
        'no_akta_kelahiran',
        'tinggi_badan',
        'berat_badan',
        'jumlah_saudara_kandung',
        'anak_ke',
        'tinggal_bersama',
        'pendidikan_terakhir',
        'asal_sekolah',
        'tanggal_pendaftaran',
        'kk',
        'ktp_orang_tua',
        'akta_kelahiran',
        'foto_anak',
        'pemeriksaan_tes_rambut',
        'anamnesa',
        'tes_iq',
        'pemeriksaan_dokter_lab',
        'surat_pernyataan',
        'status',
    ];


    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_pendaftaran' => 'date',
        'vokasi_diikuti' => 'array',
        'kk' => 'boolean',
        'ktp_orang_tua' => 'boolean',
        'akta_kelahiran' => 'boolean',
        'foto_anak' => 'boolean',
        'pemeriksaan_tes_rambut' => 'boolean',
        'anamnesa' => 'boolean',
        'tes_iq' => 'boolean',
        'pemeriksaan_dokter_lab' => 'boolean',
        'surat_pernyataan' => 'boolean',
    ];

    public function therapyPrograms()
    {
        return $this->hasMany(TherapyProgram::class);
    }

    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }

    public function programs()
    {
        return $this->hasMany(Program::class);
    }

    public function guruAssignments()
    {
        return $this->hasMany(GuruAnakDidik::class);
    }

    public function approvalRequests()
    {
        return $this->hasMany(GuruAnakDidikApproval::class);
    }

    public function guruFokus()
    {
        return $this->belongsTo(Karyawan::class, 'guru_fokus_id');
    }

    // Get therapy types as array
    public function getTherapyTypesAttribute()
    {
        return $this->therapyPrograms()
            ->where('is_active', true)
            ->pluck('type_therapy')
            ->toArray();
    }
}
