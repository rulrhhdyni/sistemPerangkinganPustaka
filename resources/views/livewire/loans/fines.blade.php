<x-app-layout>
    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-emerald-900 mb-6">Riwayat Denda Santri</h2>

            <div class="bg-white rounded-2xl shadow-sm border-t-4 border-amber-500 overflow-hidden">
                <table class="w-full text-left text-sm">
                    <thead class="bg-amber-50 text-amber-900 uppercase text-xs font-bold">
                        <tr>
                            <th class="px-6 py-4">Nama Santri</th>
                            <th class="px-6 py-4">Buku Terkait</th>
                            <th class="px-6 py-4">Jumlah Denda</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($fines as $fine)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $fine->member->name }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $fine->loan->book_title }}</td>
                            <td class="px-6 py-4 text-red-600 font-bold">Rp {{ number_format($fine->total_fines) }}</td>
                            <td class="px-6 py-4 text-center">
                                <form action="{{ route('fine.pay', $fine->id) }}" method="POST">
                                    @csrf
                                    <button class="bg-emerald-600 text-white px-4 py-1 rounded-full text-xs hover:bg-emerald-700">
                                        Lunaskan
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-400">Alhamdulillah, tidak ada tunggakan denda.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>