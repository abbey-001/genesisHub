@extends('admin.layouts.app')

@section('title', 'Admin Telegram')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-1">Admin Telegram</h4>
                    <p class="text-muted mb-0">Invite admins, link chat IDs, and control notification categories by role.</p>
                </div>
                @if($botUsername)
                    <a href="https://t.me/{{ $botUsername }}" target="_blank" class="btn btn-primary">
                        <i class="bx bx-link-external me-1"></i> Open Bot
                    </a>
                @endif
            </div>
        </div>
    </div>

    @if(session('telegram_invite_url'))
        <div class="alert alert-info">
            <strong>Invite link:</strong>
            <code>{{ session('telegram_invite_url') }}</code>
        </div>
    @endif

    @if(!$botUsername)
        <div class="alert alert-warning">
            TELEGRAM_ADMIN_BOT_USERNAME is not configured, so invite links cannot be generated yet.
        </div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Admin</th>
                            <th>Role</th>
                            <th>Telegram</th>
                            <th>Notifications</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($admins as $admin)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $admin->name }}</div>
                                    <div class="text-muted small">{{ $admin->email }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary">
                                        {{ $admin->role?->display_name ?? 'No Role' }}
                                    </span>
                                </td>
                                <td>
                                    @if($admin->telegram_chat_id)
                                        <div class="text-success fw-semibold">Linked</div>
                                        <code>{{ $admin->telegram_chat_id }}</code>
                                        <div class="text-muted small">
                                            {{ $admin->telegram_linked_at?->format('d M Y, g:ia') }}
                                        </div>
                                    @elseif($admin->telegram_link_token)
                                        <div class="text-warning fw-semibold">Invite pending</div>
                                        <div class="text-muted small">
                                            {{ $admin->telegram_invited_at?->diffForHumans() ?? 'Recently generated' }}
                                        </div>
                                    @else
                                        <span class="text-muted">Not linked</span>
                                    @endif
                                </td>
                                <td style="min-width: 360px;">
                                    <form method="POST" action="{{ route('admin.admin.telegram.preferences', $admin) }}" class="row g-2">
                                        @csrf
                                        @method('PUT')
                                        @php
                                            $toggles = [
                                                'telegram_notify_orders' => 'Orders',
                                                'telegram_notify_payouts' => 'Payouts',
                                                'telegram_notify_sellers' => 'Sellers',
                                                'telegram_notify_reviews' => 'Reviews',
                                                'telegram_notify_deliveries' => 'Deliveries',
                                                'telegram_notify_riders' => 'Riders',
                                                'telegram_notify_system' => 'System',
                                            ];
                                        @endphp

                                        @foreach($toggles as $field => $label)
                                            <div class="col-auto">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" value="1"
                                                           id="{{ $field }}_{{ $admin->id }}"
                                                           name="{{ $field }}"
                                                           @checked($admin->{$field})>
                                                    <label class="form-check-label small" for="{{ $field }}_{{ $admin->id }}">
                                                        {{ $label }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach

                                        <div class="col-12">
                                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                                Save Preferences
                                            </button>
                                        </div>
                                    </form>
                                </td>
                                <td class="text-end" style="min-width: 260px;">
                                    <div class="d-flex justify-content-end gap-2 flex-wrap">
                                        <form method="POST" action="{{ route('admin.admin.telegram.invite', $admin) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-primary" @disabled(!$botUsername)>
                                                {{ $admin->telegram_chat_id ? 'Reinvite' : 'Send Invite' }}
                                            </button>
                                        </form>

                                        <button class="btn btn-sm btn-outline-secondary" type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#manual-chat-{{ $admin->id }}">
                                            Manual ID
                                        </button>

                                        @if($admin->telegram_chat_id)
                                            <form method="POST" action="{{ route('admin.admin.telegram.test', $admin) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success">Test</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.admin.telegram.unregister', $admin) }}"
                                                  onsubmit="return confirm('Unlink Telegram for this admin?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Unlink</button>
                                            </form>
                                        @endif
                                    </div>

                                    <div class="collapse mt-2" id="manual-chat-{{ $admin->id }}">
                                        <form method="POST" action="{{ route('admin.admin.telegram.register', $admin) }}" class="d-flex gap-2">
                                            @csrf
                                            <input type="text" name="telegram_chat_id" class="form-control form-control-sm"
                                                   placeholder="Telegram chat ID" value="{{ $admin->telegram_chat_id }}">
                                            <button type="submit" class="btn btn-sm btn-primary">Save</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
