@extends('layouts/contentNavbarLayout')

@section('title', 'Permintaan Akses Anak')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Permintaan Akses Anak</h5>
        <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">Kembali</a>
      </div>
      <div class="card-body">
        @if($requests && $requests->count())
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>#</th>
                <th>Anak</th>
                <th>Pengaju</th>
                <th>Alasan</th>
                <th>Diajukan</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              @foreach($requests as $req)
              <tr data-req-id="{{ $req->id }}">
                <td>{{ $loop->iteration + (($requests->currentPage()-1) * $requests->perPage()) }}</td>
                <td>{{ $req->anakDidik->nama ?? '-' }}</td>
                <td>{{ $req->requesterUser->name ?? '-' }}</td>
                <td>{{ $req->reason ?? '-' }}</td>
                <td>{{ $req->created_at->diffForHumans() }}</td>
                <td class="text-capitalize">{{ $req->status }}</td>
                <td style="white-space:nowrap;">
                  @php $isPending = ($req->status === 'pending'); @endphp
                  <div class="d-flex gap-1">
                    @if($isPending)
                    <button class="btn btn-icon btn-sm btn-success btn-approve-request" data-id="{{ $req->id }}" title="Terima"><i class="ri-check-line"></i></button>
                    <button class="btn btn-icon btn-sm btn-outline-danger btn-reject-request" data-id="{{ $req->id }}" title="Tolak"><i class="ri-close-line"></i></button>
                    @else
                    <span class="text-muted">-</span>
                    @endif
                    <button class="btn btn-icon btn-sm btn-outline-secondary btn-edit-request" data-id="{{ $req->id }}" title="Edit"><i class="ri-edit-2-line"></i></button>
                    <button class="btn btn-icon btn-sm btn-outline-danger btn-delete-request" data-id="{{ $req->id }}" title="Hapus"><i class="ri-delete-bin-line"></i></button>
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div class="mt-3">
          {{ $requests->links() }}
        </div>
        @else
        <div class="text-center text-muted p-4">Tidak ada permintaan akses.</div>
        @endif
      </div>
    </div>
  </div>
</div>

@push('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    function csrfHeader() {
      return {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json'
      };
    }

    document.querySelectorAll('.btn-approve-request').forEach(function(btn) {
      btn.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        if (!id) return;
        this.disabled = true;
        fetch(`/guru-anak/approvals/${id}/approve`, {
            method: 'POST',
            headers: csrfHeader()
          })
          .then(r => r.json()).then(j => {
            if (j.success) {
              const tr = document.querySelector(`tr[data-req-id="${id}"]`);
              if (tr) tr.remove();
            } else {
              alert(j.message || 'Gagal menyetujui');
              this.disabled = false;
            }
          }).catch(() => {
            alert('Terjadi kesalahan jaringan');
            this.disabled = false;
          });
      });
    });

    document.querySelectorAll('.btn-reject-request').forEach(function(btn) {
      btn.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        if (!id) return;
        const notes = prompt('Catatan penolakan (opsional)');
        this.disabled = true;
        fetch(`/guru-anak/approvals/${id}/reject`, {
          method: 'POST',
          headers: Object.assign(csrfHeader(), {
            'Content-Type': 'application/json'
          }),
          body: JSON.stringify({
            approval_notes: notes
          })
        }).then(r => r.json()).then(j => {
          if (j.success) {
            const tr = document.querySelector(`tr[data-req-id="${id}"]`);
            if (tr) tr.remove();
          } else {
            alert(j.message || 'Gagal menolak');
            this.disabled = false;
          }
        }).catch(() => {
          alert('Terjadi kesalahan jaringan');
          this.disabled = false;
        });
      });
    });

    // edit reason
    document.querySelectorAll('.btn-edit-request').forEach(function(btn) {
      btn.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        if (!id) return;
        const newReason = prompt('Ubah alasan permintaan (kosongkan untuk tidak mengubah)');
        if (newReason === null) return;
        this.disabled = true;
        fetch(`/guru-anak/approvals/${id}`, {
          method: 'PUT',
          headers: Object.assign(csrfHeader(), {
            'Content-Type': 'application/json'
          }),
          body: JSON.stringify({
            reason: newReason
          })
        }).then(r => r.json()).then(j => {
          if (j.success) {
            // update reason cell in row
            const tr = document.querySelector(`tr[data-req-id="${id}"]`);
            if (tr) {
              const td = tr.querySelector('td:nth-child(4)');
              if (td) td.textContent = newReason || '-';
            }
          } else {
            alert(j.message || 'Gagal mengubah');
            this.disabled = false;
          }
        }).catch(() => {
          alert('Terjadi kesalahan jaringan');
          this.disabled = false;
        });
      });
    });

    // delete request
    document.querySelectorAll('.btn-delete-request').forEach(function(btn) {
      btn.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        if (!id) return;
        if (!confirm('Hapus permintaan akses ini?')) return;
        this.disabled = true;
        fetch(`/guru-anak/approvals/${id}`, {
          method: 'DELETE',
          headers: csrfHeader()
        }).then(r => r.json()).then(j => {
          if (j.success) {
            const tr = document.querySelector(`tr[data-req-id="${id}"]`);
            if (tr) tr.remove();
          } else {
            alert(j.message || 'Gagal menghapus');
            this.disabled = false;
          }
        }).catch(() => {
          alert('Terjadi kesalahan jaringan');
          this.disabled = false;
        });
      });
    });
  });
</script>
@endpush

@endsection