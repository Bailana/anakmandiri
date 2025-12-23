@php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
@endphp

<!--  Brand demo (display only for navbar-full and hide on below xl) -->
@if(isset($navbarFull))
<div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-6">
    <a href="{{url('/')}}" class="app-brand-link gap-2">
        <span class="app-brand-logo demo">@include('_partials.macros')</span>
        <span class="app-brand-text demo menu-text fw-bold">{{config('variables.templateName')}}</span>
    </a>
</div>
@endif

<!-- ! Not required for layout-without-menu -->
@if(!isset($navbarHideToggle))
<div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 {{ isset($contentNavbar) ? ' d-xl-none ' : '' }}">
    <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
        <i class="icon-base ri ri-menu-line icon-md"></i>
    </a>
</div>
@endif

<div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
    <!-- Search removed per request -->
    <ul class="navbar-nav flex-row align-items-center ms-auto">
        <!-- Notifications -->
        <li class="nav-item dropdown-notifications dropdown me-3">
            <a class="nav-link" href="javascript:void(0)" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="ri-notification-3-line icon-base"></i>
                @if(Auth::check())
                @php
                // Do not show or count notifications for konsultan or terapis roles
                $skipNotifRoles = in_array(Auth::user()->role ?? '', ['konsultan', 'terapis']);
                if ($skipNotifRoles) {
                $unread = 0;
                $hasAccessRequestNotif = false;
                } else {
                $unread = Auth::user()->unreadNotifications->count();
                // detect if current user has any unread access_request notifications (any action)
                $hasAccessRequestNotif = false;
                foreach (Auth::user()->unreadNotifications as $n_chk) {
                if (isset($n_chk->data['type']) && $n_chk->data['type'] === 'access_request') {
                $hasAccessRequestNotif = true;
                break;
                }
                }
                }
                @endphp
                @if($unread > 0)
                <span class="badge bg-danger rounded-pill ms-1" id="nav-notif-count">{{ $unread }}</span>
                @else
                <span class="badge bg-secondary rounded-pill ms-1" id="nav-notif-count">0</span>
                @endif
                @endif
            </a>
            <ul class="dropdown-menu dropdown-menu-end p-0" style="min-width:320px;">
                <li class="dropdown-header fw-semibold px-3 py-2">Notifikasi
                    <small class="text-muted d-block">Terbaru</small>
                </li>
                <li>
                    <div class="list-group list-group-flush" id="nav-notifications-list">
                        @if(Auth::check() && !$skipNotifRoles)
                        @foreach(Auth::user()->unreadNotifications->take(6) as $n)
                        <div class="list-group-item d-flex justify-content-between align-items-start" data-notif-id="{{ $n->id }}">
                            <div class="me-2">
                                <p class="mb-0 small">{{ $n->data['message'] ?? 'Notifikasi baru' }}</p>
                                <small class="text-muted">{{ $n->created_at->diffForHumans() }}</small>
                            </div>
                            <div class="btn-group btn-group-sm">
                                @php $isAdmin = Auth::check() && (Auth::user()->role === 'admin'); @endphp
                                @if(!empty($n->data['approval_id']) && $isAdmin)
                                <button class="btn btn-icon btn-sm btn-success btn-accept-request" data-approval-id="{{ $n->data['approval_id'] }}" title="Terima"><i class="ri-check-line"></i></button>
                                <button class="btn btn-icon btn-sm btn-outline-danger btn-reject-notif ms-1" data-approval-id="{{ $n->data['approval_id'] }}" title="Tolak"><i class="ri-close-line"></i></button>
                                @else
                                {{-- No "Buka" button rendered per UI requirement --}}
                                @endif
                            </div>
                        </div>
                        @endforeach
                        @endif
                    </div>
                </li>
                <li>
                    <div class="dropdown-divider"></div>
                </li>
                @if(!(isset($hasAccessRequestNotif) && $hasAccessRequestNotif))
                <li class="px-3 py-2">
                    <div class="d-flex justify-content-between">
                        <button class="btn btn-sm btn-outline-secondary" id="btn-mark-all-read">Tandai semua dibaca</button>
                        @if(Auth::check() && Auth::user()->role === 'admin')
                        <a href="/guru-anak/approval-requests" class="btn btn-sm btn-primary">Lihat semua</a>
                        @endif
                    </div>
                </li>
                @endif
            </ul>
        </li>

        <!-- User -->
        <li class="nav-item navbar-dropdown dropdown-user dropdown">
            <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                <div class="avatar avatar-online">
                    @if(Auth::check() && Auth::user()->avatar)
                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" class="rounded-circle" />
                    @else
                    <img src="{{ asset('assets/img/avatars/1.svg') }}" alt="Default Avatar" class="rounded-circle" />
                    @endif
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="javascript:void(0);">
                        <div class="d-flex">
                            <div class="flex-shrink-0 me-3">
                                <div class="avatar avatar-online">
                                    @if(Auth::check() && Auth::user()->avatar)
                                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" class="w-px-40 h-auto rounded-circle" />
                                    @else
                                    <img src="{{ asset('assets/img/avatars/1.svg') }}" alt="Default Avatar" class="w-px-40 h-auto rounded-circle" />
                                    @endif
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0">{{ Auth::check() ? Auth::user()->name : 'John Doe' }}</h6>
                                <small class="text-body-secondary">{{ Auth::check() ? ucfirst(Auth::user()->role ?? 'User') : 'Admin' }}</small>
                            </div>
                        </div>
                    </a>
                </li>
                <li>
                    <div class="dropdown-divider my-1"></div>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('profile.show') }}">
                        <i class="icon-base ri ri-user-3-line icon-md me-3"></i>
                        <span>My Profile</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="javascript:void(0);">
                        <i class="icon-base ri ri-settings-4-line icon-md me-4"></i><span>Settings</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('auth-reset-password-basic') }}">
                        <i class="icon-base ri ri-lock-password-line icon-md me-3"></i>
                        <span>Reset Password</span>
                    </a>
                </li>
                <li>
                    <div class="dropdown-divider my-1"></div>
                </li>
                <li>
                    <div class="d-grid px-4 pt-2 pb-1">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger d-flex w-100">
                                <small class="align-middle">Logout</small>
                                <i class="ri ri-logout-box-r-line ms-2 icon-xs"></i>
                            </button>
                        </form>
                    </div>
                </li>
            </ul>
        </li>
        <!--/ User -->
    </ul>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const list = document.getElementById('nav-notifications-list');
        const CURRENT_USER_IS_ADMIN = @json(Auth::check() && Auth::user() && Auth::user()->role === 'admin');
        // mark single notification read when clicked
        if (list) {
            list.addEventListener('click', function(e) {
                const a = e.target.closest('a[data-notif-id]');
                if (!a) return;
                e.preventDefault();
                const id = a.getAttribute('data-notif-id');
                const href = a.getAttribute('href');
                fetch('{{ route("notifications.mark-read") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        id: id
                    })
                }).then(r => r.json()).then(j => {
                    if (j.success) {
                        // decrease badge
                        const badge = document.getElementById('nav-notif-count');
                        if (badge) {
                            let v = parseInt(badge.textContent || '0', 10) - 1;
                            if (v < 0) v = 0;
                            badge.textContent = v;
                        }
                        // remove item
                        a.remove();
                        // navigate to link
                        if (href && href !== 'javascript:void(0)') window.location = href;
                    }
                }).catch(() => {});
            });
        }

        const btnAll = document.getElementById('btn-mark-all-read');
        if (btnAll) {
            btnAll.addEventListener('click', function() {
                fetch('{{ route("notifications.mark-all-read") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                }).then(r => r.json()).then(j => {
                    if (j.success) {
                        const badge = document.getElementById('nav-notif-count');
                        if (badge) badge.textContent = '0';
                        // clear list
                        if (list) list.innerHTML = '<div class="p-3 text-center text-muted">Tidak ada notifikasi</div>';
                    }
                }).catch(() => {});
            });
        }

        // NOTE: dismiss button handlers removed as the 'Tutup' button is no longer rendered for admins

        // helper: show bootstrap toast (toastr-like)
        function showGlobalToast(message, variant = 'success', delay = 4000) {
            let container = document.getElementById('global-toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'global-toast-container';
                container.style.position = 'fixed';
                container.style.top = '1rem';
                container.style.right = '1rem';
                container.style.zIndex = 2000;
                document.body.appendChild(container);
            }
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-bg-${variant} border-0 mb-2`;
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'polite');
            toast.setAttribute('aria-atomic', 'true');
            toast.innerHTML = `<div class="d-flex"><div class="toast-body">${message}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>`;
            container.appendChild(toast);
            const bsToast = new bootstrap.Toast(toast, {
                delay: delay
            });
            bsToast.show();
            toast.addEventListener('hidden.bs.toast', function() {
                toast.remove();
            });
        }

        // attach accept handlers for approval requests
        document.querySelectorAll('.btn-accept-request').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                var approvalId = this.getAttribute('data-approval-id');
                if (!approvalId) return;
                var that = this;
                that.disabled = true;
                // capture notif id if present
                var notifEl = that.closest('[data-notif-id]');
                var notifId = notifEl ? notifEl.getAttribute('data-notif-id') : null;
                fetch('/guru-anak/approvals/' + approvalId + '/approve', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                }).then(r => r.json()).then(j => {
                    if (j.success) {
                        // mark notification as read (if id present)
                        if (notifId) {
                            fetch('{{ route("notifications.mark-read") }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    id: notifId
                                })
                            }).catch(() => {});
                        }
                        var el = notifEl;
                        if (el) el.remove();
                        const badge = document.getElementById('nav-notif-count');
                        if (badge) {
                            let v = parseInt(badge.textContent || '0', 10) - 1;
                            if (v < 0) v = 0;
                            badge.textContent = v;
                        }
                        // show toastr-like success with requester name if available
                        const requesterName = j.requester_name || (j.requester && j.requester.name) || null;
                        if (requesterName) {
                            showGlobalToast(`Akses PPI diberikan kepada ${requesterName}`, 'success', 5000);
                        } else {
                            showGlobalToast(j.message || 'Akses PPI diberikan', 'success', 5000);
                        }
                    } else {
                        alert(j.message || 'Gagal memproses permintaan');
                        that.disabled = false;
                    }
                }).catch(() => {
                    alert('Terjadi kesalahan jaringan');
                    that.disabled = false;
                });
            });
        });

        // attach reject handlers for approval requests (from navbar)
        document.querySelectorAll('.btn-reject-notif').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                var approvalId = this.getAttribute('data-approval-id');
                if (!approvalId) return;
                var that = this;
                var notes = prompt('Catatan penolakan (opsional)');
                that.disabled = true;
                var notifEl = that.closest('[data-notif-id]');
                var notifId = notifEl ? notifEl.getAttribute('data-notif-id') : null;
                fetch('/guru-anak/approvals/' + approvalId + '/reject', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        approval_notes: notes
                    })
                }).then(r => r.json()).then(j => {
                    if (j.success) {
                        // mark notification as read (if id present)
                        if (notifId) {
                            fetch('{{ route("notifications.mark-read") }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    id: notifId
                                })
                            }).catch(() => {});
                        }
                        var el = notifEl;
                        if (el) el.remove();
                        const badge = document.getElementById('nav-notif-count');
                        if (badge) {
                            let v = parseInt(badge.textContent || '0', 10) - 1;
                            if (v < 0) v = 0;
                            badge.textContent = v;
                        }
                        const requesterName = j.requester_name || null;
                        if (requesterName) showGlobalToast(`Permintaan akses ditolak untuk ${requesterName}`, 'danger', 5000);
                        else showGlobalToast(j.message || 'Permintaan akses ditolak', 'danger', 5000);
                    } else {
                        alert(j.message || 'Gagal memproses penolakan');
                        that.disabled = false;
                    }
                }).catch(() => {
                    alert('Terjadi kesalahan jaringan');
                    that.disabled = false;
                });
            });
        });

        // Polling: fetch unread notifications periodically and inject new ones
        function checkNewNotifications() {
            fetch('/notifications/unread-json', {
                    credentials: 'same-origin'
                })
                .then(r => r.json())
                .then(j => {
                    if (!j.success) return;
                    const items = j.notifications || [];
                    const badge = document.getElementById('nav-notif-count');
                    if (badge) badge.textContent = items.length;
                    if (!list) return;
                    const existing = new Set(Array.from(list.querySelectorAll('[data-notif-id]')).map(e => e.getAttribute('data-notif-id')));
                    // Add any new notifications to the top of the list
                    items.forEach(function(n) {
                        if (existing.has(n.id)) return;
                        const container = document.createElement('div');
                        container.className = 'list-group-item d-flex justify-content-between align-items-start';
                        container.setAttribute('data-notif-id', n.id);
                        const left = document.createElement('div');
                        left.className = 'me-2';
                        const msg = (n.data && n.data.message) ? n.data.message : 'Notifikasi baru';
                        const time = n.created_at ? n.created_at : '';
                        left.innerHTML = `<p class="mb-0 small">${msg}</p><small class="text-muted">${time}</small>`;
                        const btnGroup = document.createElement('div');
                        btnGroup.className = 'btn-group btn-group-sm';
                        if (n.data && n.data.approval_id && CURRENT_USER_IS_ADMIN) {
                            const accept = document.createElement('button');
                            accept.className = 'btn btn-icon btn-sm btn-success btn-accept-request';
                            accept.setAttribute('data-approval-id', n.data.approval_id);
                            accept.title = 'Terima';
                            accept.innerHTML = '<i class="ri-check-line"></i>';
                            const reject = document.createElement('button');
                            reject.className = 'btn btn-icon btn-sm btn-outline-danger btn-reject-notif ms-1';
                            reject.setAttribute('data-approval-id', n.data.approval_id);
                            reject.title = 'Tolak';
                            reject.innerHTML = '<i class="ri-close-line"></i>';
                            // attach handlers similar to existing ones
                            accept.addEventListener('click', function() {
                                const that = this;
                                that.disabled = true;
                                const notifEl = that.closest('[data-notif-id]');
                                const notifId = notifEl ? notifEl.getAttribute('data-notif-id') : null;
                                fetch('/guru-anak/approvals/' + that.getAttribute('data-approval-id') + '/approve', {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                        'Accept': 'application/json'
                                    }
                                }).then(r => r.json()).then(j => {
                                    if (j.success) {
                                        if (notifId) {
                                            fetch('{{ route("notifications.mark-read") }}', {
                                                method: 'POST',
                                                headers: {
                                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                                    'Content-Type': 'application/json',
                                                    'Accept': 'application/json'
                                                },
                                                body: JSON.stringify({
                                                    id: notifId
                                                })
                                            }).catch(() => {});
                                        }
                                        if (notifEl) notifEl.remove();
                                        if (badge) {
                                            let v = parseInt(badge.textContent || '0', 10) - 1;
                                            if (v < 0) v = 0;
                                            badge.textContent = v;
                                        }
                                        const requesterName = j.requester_name || (j.requester && j.requester.name) || null;
                                        if (requesterName) showGlobalToast(`Akses PPI diberikan kepada ${requesterName}`, 'success', 5000);
                                        else showGlobalToast(j.message || 'Akses PPI diberikan', 'success', 5000);
                                    } else {
                                        alert(j.message || 'Gagal memproses permintaan');
                                        that.disabled = false;
                                    }
                                }).catch(() => {
                                    alert('Terjadi kesalahan jaringan');
                                    that.disabled = false;
                                });
                            });
                            reject.addEventListener('click', function() {
                                const that = this;
                                that.disabled = true;
                                const notes = prompt('Catatan penolakan (opsional)');
                                const notifEl = that.closest('[data-notif-id]');
                                const notifId = notifEl ? notifEl.getAttribute('data-notif-id') : null;
                                fetch('/guru-anak/approvals/' + that.getAttribute('data-approval-id') + '/reject', {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                        'Accept': 'application/json',
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        approval_notes: notes
                                    })
                                }).then(r => r.json()).then(j => {
                                    if (j.success) {
                                        if (notifId) {
                                            fetch('{{ route("notifications.mark-read") }}', {
                                                method: 'POST',
                                                headers: {
                                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                                    'Content-Type': 'application/json',
                                                    'Accept': 'application/json'
                                                },
                                                body: JSON.stringify({
                                                    id: notifId
                                                })
                                            }).catch(() => {});
                                        }
                                        if (notifEl) notifEl.remove();
                                        if (badge) {
                                            let v = parseInt(badge.textContent || '0', 10) - 1;
                                            if (v < 0) v = 0;
                                            badge.textContent = v;
                                        }
                                        const requesterName = j.requester_name || null;
                                        if (requesterName) showGlobalToast(`Permintaan akses ditolak untuk ${requesterName}`, 'danger', 5000);
                                        else showGlobalToast(j.message || 'Permintaan akses ditolak', 'danger', 5000);
                                    } else {
                                        alert(j.message || 'Gagal memproses penolakan');
                                        that.disabled = false;
                                    }
                                }).catch(() => {
                                    alert('Terjadi kesalahan jaringan');
                                    that.disabled = false;
                                });
                            });
                            btnGroup.appendChild(accept);
                            btnGroup.appendChild(reject);
                        }
                        container.appendChild(left);
                        container.appendChild(btnGroup);
                        // insert at top
                        if (list.firstChild) list.insertBefore(container, list.firstChild);
                        else list.appendChild(container);
                    });
                })
                .catch(() => {});
        }

        // initial check and periodic polling every 5 seconds
        checkNewNotifications();
        setInterval(checkNewNotifications, 5000);
    });
</script>
@endpush