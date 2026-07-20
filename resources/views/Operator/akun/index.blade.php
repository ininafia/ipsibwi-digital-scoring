@extends('Operator.layout.app')

@section('content')
<div class="p-6 h-full flex flex-col">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Manajemen Akun</h1>
            <p class="text-gray-500 text-sm mt-1">Kelola dan reset password seluruh akun pengguna.</p>
        </div>
    </div>

    {{-- Notifikasi --}}
    @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Tabel Users --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex-1 flex flex-col">
        <div class="overflow-x-auto flex-1">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100">
                        <th class="px-6 py-4 text-sm font-semibold text-gray-600">ID</th>
                        <th class="px-6 py-4 text-sm font-semibold text-gray-600">Username</th>
                        <th class="px-6 py-4 text-sm font-semibold text-gray-600">Role / Akses</th>
                        <th class="px-6 py-4 text-sm font-semibold text-gray-600">Status</th>
                        <th class="px-6 py-4 text-sm font-semibold text-gray-600 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($users as $user)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $user->id }}</td>
                            <td class="px-6 py-4">
                                <span class="font-medium text-gray-800">{{ $user->username }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $user->role_name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if($user->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Non-Aktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button type="button" 
                                    onclick="promptResetPassword({{ $user->id }}, '{{ $user->username }}')"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-orange-50 text-orange-600 hover:bg-orange-100 hover:text-orange-700 transition-colors tooltip"
                                    title="Reset Password">
                                    <i class="fa-solid fa-key"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Form tersembunyi untuk reset password --}}
<form id="formResetPassword" method="POST" action="{{ route('operator.akun.reset-password') }}" style="display: none;">
    @csrf
    <input type="hidden" name="user_id" id="reset_user_id">
    <input type="hidden" name="new_password" id="reset_new_password">
</form>

<script>
function promptResetPassword(userId, username) {
    Swal.fire({
        title: 'Reset Password',
        html: `Masukkan password baru untuk akun <b>${username}</b>:`,
        input: 'password',
        inputAttributes: {
            autocapitalize: 'off',
            autocorrect: 'off',
            minlength: 4,
            placeholder: 'Minimal 4 karakter'
        },
        showCancelButton: true,
        confirmButtonText: 'Reset Password',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#f97316', // orange-500
        showLoaderOnConfirm: true,
        preConfirm: (newPassword) => {
            if (!newPassword || newPassword.length < 4) {
                Swal.showValidationMessage('Password minimal 4 karakter');
                return false;
            }
            return newPassword;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('reset_user_id').value = userId;
            document.getElementById('reset_new_password').value = result.value;
            document.getElementById('formResetPassword').submit();
        }
    });
}
</script>
@endsection
