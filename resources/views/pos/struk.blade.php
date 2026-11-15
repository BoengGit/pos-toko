<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Struk Transaksi</h2></x-slot>

    <style>
        @page {
            size: 58mm auto;
            margin: 0;
        }

        @media print {
            nav, header, .no-print {
                display: none !important;
            }
            html, body {
                width: 58mm !important;
                margin: 0 !important;
                padding: 0 !important;
                background: white !important;
            }
            .thermal-wrapper {
                padding: 0 !important;
                margin: 0 !important;
            }
            .thermal-receipt {
                width: 58mm !important;
                max-width: 58mm !important;
                margin: 0 !important;
                padding: 3mm !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                font-size: 11px !important;
                line-height: 1.35 !important;
            }
        }
    </style>

    <div class="thermal-wrapper py-8">
        <div class="thermal-receipt max-w-sm mx-auto sm:px-6 font-mono">
            <x-card>
                <div class="text-center border-b border-dashed border-gray-300 pb-2 mb-2">
                    <p class="font-bold text-base">POS BAROKAH MART</p>
                    <p class="text-xs text-gray-500">{{ $transaction->invoice_number }}</p>
                </div>

                <div class="text-xs text-gray-600 border-b border-dashed border-gray-300 pb-2 mb-2">
                    <p>Tanggal : {{ $transaction->created_at->format('d/m/Y H:i') }}</p>
                    <p>Kasir   : {{ $transaction->user->name }}</p>
                </div>

                <div class="border-b border-dashed border-gray-300 pb-2 mb-2 space-y-1">
                    @foreach ($transaction->details as $detail)
                        <div class="text-xs">
                            <p class="font-medium">{{ $detail->product->name }}</p>
                            <div class="flex justify-between text-gray-600">
                                <span>{{ $detail->qty }} x Rp{{ number_format($detail->price, 0, ',', '.') }}</span>
                                <span class="text-gray-900">Rp{{ number_format($detail->subtotal, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="text-xs space-y-1 border-b border-dashed border-gray-300 pb-2 mb-2">
                    <div class="flex justify-between font-bold text-sm">
                        <span>TOTAL</span>
                        <span>Rp{{ number_format($transaction->total, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Bayar</span>
                        <span>Rp{{ number_format($transaction->pay, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Kembali</span>
                        <span>Rp{{ number_format($transaction->change, 0, ',', '.') }}</span>
                    </div>
                </div>

                <p class="text-center text-xs text-gray-500 mt-2">
                    Terima kasih telah berbelanja!
                </p>

                <div class="no-print mt-4 space-y-2 font-sans">
                    <button type="button" onclick="window.print()"
                        class="w-full bg-gray-800 text-white py-2 rounded-md text-sm">
                        Cetak Thermal (58mm)
                    </button>
                    <a href="{{ route('pos.index') }}" class="block text-center bg-indigo-600 text-white py-2 rounded-md text-sm">
                        Transaksi Baru
                    </a>
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
