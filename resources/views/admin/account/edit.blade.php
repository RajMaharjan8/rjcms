@extends('rjcms::layouts.admin')

@section('title', 'Account · ' . config('app.name'))
@section('page-title', 'My account')

@section('content')
@php($inputClass = 'w-full rounded border border-wp-border bg-white px-3 py-1.5 text-sm text-wp-ink shadow-sm transition focus:border-wp-blue focus:ring-1 focus:ring-wp-blue focus:outline-none')

<form method="POST" action="{{ route('admin.account.update') }}" class="max-w-3xl">
    @csrf
    @method('PUT')

    <x-admin.postbox title="Profile" body-class="px-5 py-1">
        <div class="divide-y divide-wp-border-light">
            <div class="grid gap-2 py-4 sm:grid-cols-[180px_minmax(0,1fr)] sm:items-start sm:gap-4">
                <label for="name" class="pt-1.5 text-sm font-semibold text-wp-ink">Username</label>
                <x-admin.field name="name">
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required class="{{ $inputClass }}">
                </x-admin.field>
            </div>
            <div class="grid gap-2 py-4 sm:grid-cols-[180px_minmax(0,1fr)] sm:items-start sm:gap-4">
                <label for="email" class="pt-1.5 text-sm font-semibold text-wp-ink">Email</label>
                <x-admin.field name="email" hint="You sign in with this email address.">
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required class="{{ $inputClass }}">
                </x-admin.field>
            </div>
        </div>
    </x-admin.postbox>

    <div class="mt-6">
        <x-admin.postbox title="Change Password" body-class="px-5 py-1">
            <div class="divide-y divide-wp-border-light">
                <div class="grid gap-2 py-4 sm:grid-cols-[180px_minmax(0,1fr)] sm:items-start sm:gap-4">
                    <label for="password" class="pt-1.5 text-sm font-semibold text-wp-ink">New password</label>
                    <x-admin.field name="password" hint="Leave blank to keep your current password. Minimum 8 characters.">
                        <input id="password" name="password" type="password" autocomplete="new-password" class="{{ $inputClass }}">
                    </x-admin.field>
                </div>
                <div class="grid gap-2 py-4 sm:grid-cols-[180px_minmax(0,1fr)] sm:items-start sm:gap-4">
                    <label for="password_confirmation" class="pt-1.5 text-sm font-semibold text-wp-ink">Confirm new password</label>
                    <x-admin.field name="password_confirmation">
                        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="{{ $inputClass }}">
                    </x-admin.field>
                </div>
                <div class="grid gap-2 py-4 sm:grid-cols-[180px_minmax(0,1fr)] sm:items-start sm:gap-4">
                    <label for="current_password" class="pt-1.5 text-sm font-semibold text-wp-ink">Current password</label>
                    <x-admin.field name="current_password" hint="Required only when setting a new password.">
                        <input id="current_password" name="current_password" type="password" autocomplete="current-password" class="{{ $inputClass }}">
                    </x-admin.field>
                </div>
            </div>

            <x-slot:footer>
                <div class="flex justify-end">
                    <x-admin.wp-button type="submit">Save Account</x-admin.wp-button>
                </div>
            </x-slot:footer>
        </x-admin.postbox>
    </div>
</form>
@endsection
