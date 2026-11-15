<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Ubah Supplier</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <x-card>
                <form action="{{ route('suppliers.update', $supplier) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Supplier</label>
                        <input type="text" name="name" value="{{ old('name', $supplier->name) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        @error('name')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">No. Telepon</label>
                            <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @error('phone')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" value="{{ old('email', $supplier->email) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @error('email')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Alamat</label>
                        <textarea name="address" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('address', $supplier->address) }}</textarea>
                        @error('address')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md">
                            Simpan Perubahan
                        </button>
                        <a href="{{ route('suppliers.index') }}" class="text-sm text-gray-500">Batal</a>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
</x-app-layout>
