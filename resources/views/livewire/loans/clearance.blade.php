<x-app-layout>
    <div class="py-12 bg-gray-50">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-emerald-100 text-center">
                <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-800">Cetak Surat Bebas Pustaka</h2>
                <p class="text-gray-500 mb-8">Pastikan santri tidak memiliki pinjaman aktif atau denda.</p>

                <div class="max-w-md mx-auto">
                    <label class="block text-left text-sm font-medium text-gray-700 mb-2">Pilih/Scan RFID Santri</label>
                    <input type="text" id="member_search" autofocus 
                        class="w-full border-emerald-300 rounded-lg shadow-sm focus:ring-emerald-500 mb-4" 
                        placeholder="Masukkan Nama atau Tap Kartu...">
                    
                    <button onclick="checkClearance()" class="w-full bg-emerald-800 text-white py-3 rounded-xl font-bold hover:bg-emerald-900 transition">
                        Cek Kelayakan & Cetak
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>