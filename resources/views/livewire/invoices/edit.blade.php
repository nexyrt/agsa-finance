<div class="max-w-full mx-auto p-6" x-data="invoiceEditForm()" @click.away="closeAllDropdowns()">
    {{-- Header --}}
    <div class="mb-6 space-y-1">
        <h1
            class="text-4xl font-bold bg-gradient-to-r from-dark-900 via-primary-800 to-primary-800 dark:from-white dark:via-primary-200 dark:to-primary-200 bg-clip-text text-transparent">
            Edit Invoice
        </h1>
        <p class="text-dark-600 dark:text-dark-400 text-lg">Update invoice details and items</p>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div
            class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg flex items-start gap-3">
            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <p class="text-sm text-green-700 dark:text-green-300">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div
            class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg flex items-start gap-3">
            <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <p class="text-sm text-red-700 dark:text-red-300">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
            <h3 class="text-sm font-semibold text-red-900 dark:text-red-200 mb-2">Please fix:</h3>
            <ul class="list-disc list-inside text-sm text-red-700 dark:text-red-300 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="space-y-6">
        {{-- Invoice Info --}}
        <div class="bg-white dark:bg-dark-800 rounded-xl shadow-sm border border-dark-200 dark:border-dark-600 p-6">
            <h2 class="text-lg font-semibold text-dark-900 dark:text-dark-50 mb-4">Invoice Information</h2>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                {{-- Invoice Number (readonly) --}}
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-dark-900 dark:text-dark-50 mb-1">Invoice Number
                        *</label>
                    <input type="text" x-model="invoice.invoice_number" readonly
                        class="w-full px-3 py-2 text-sm border border-dark-200 dark:border-dark-600 rounded-lg bg-dark-100 dark:bg-dark-700 text-dark-900 dark:text-dark-50 cursor-not-allowed">
                </div>

                {{-- Client --}}
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-dark-900 dark:text-dark-50 mb-1">Billed To (Owner)
                        *</label>
                    <div class="relative">
                        <div x-show="!invoice.client_id" @click="selectOpen = !selectOpen"
                            class="w-full px-3 py-2 text-sm border border-dark-200 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-800 text-dark-500 dark:text-dark-400 cursor-pointer hover:border-primary-400 transition">
                            Select client...
                        </div>
                        <div x-show="invoice.client_id"
                            class="w-full px-3 py-2 text-sm border border-dark-200 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-800 flex items-center justify-between">
                            <span class="text-dark-900 dark:text-dark-50" x-text="invoice.client_name"></span>
                            <button @click="clearClient()" type="button"
                                class="text-dark-400 hover:text-red-500 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div x-show="selectOpen" x-transition
                            class="absolute z-50 mt-1 w-full bg-white dark:bg-dark-800 border border-dark-200 dark:border-dark-700 rounded-lg shadow-lg max-h-80 overflow-hidden">
                            <div class="p-2 border-b border-dark-200 dark:border-dark-700">
                                <input type="text" x-model="selectSearch" @click.stop placeholder="Search clients..."
                                    class="w-full px-3 py-2 border border-dark-200 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-800 text-dark-900 dark:text-dark-50 placeholder-dark-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            </div>
                            <div class="overflow-y-auto max-h-64">
                                <template x-for="client in filteredClients" :key="client.id">
                                    <div @click="selectClient(client)"
                                        class="px-4 py-3 hover:bg-primary-50 dark:hover:bg-primary-900/20 cursor-pointer transition border-b border-dark-100 dark:border-dark-700 last:border-0">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white font-semibold flex-shrink-0">
                                                <span x-text="client.name.charAt(0).toUpperCase()"></span>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="font-medium text-dark-900 dark:text-dark-50 truncate"
                                                    x-text="client.name"></div>
                                                <div class="text-sm text-dark-500 dark:text-dark-400 truncate"
                                                    x-text="client.email || '-'"></div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <div x-show="filteredClients.length === 0"
                                    class="px-4 py-8 text-center text-dark-500 dark:text-dark-400">
                                    <p class="text-sm">No clients found</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Issue Date --}}
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-dark-900 dark:text-dark-50 mb-1">Issue Date *</label>
                    <input type="date" x-model="invoice.issue_date"
                        class="w-full px-3 py-2 text-sm border border-dark-200 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-800 text-dark-900 dark:text-dark-50 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                </div>

                {{-- Due Date --}}
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-dark-900 dark:text-dark-50 mb-1">Due Date *</label>
                    <input type="date" x-model="invoice.due_date"
                        class="w-full px-3 py-2 text-sm border border-dark-200 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-800 text-dark-900 dark:text-dark-50 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                </div>
            </div>
        </div>

        {{-- Invoice Items --}}
        <div class="bg-white dark:bg-dark-800 rounded-xl shadow-sm border border-dark-200 dark:border-dark-600 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-dark-900 dark:text-dark-50">Invoice Items</h2>
                <div class="flex items-center gap-2">
                    <input type="number" x-model="bulkCount" min="1" max="50"
                        class="w-16 px-2 py-2 text-sm text-center border border-dark-200 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-800 text-dark-900 dark:text-dark-50 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <button @click="bulkAddItems()" type="button"
                        class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4v16m8-8H4" />
                        </svg>
                        Add Item
                    </button>
                </div>
            </div>

            {{-- Table Header --}}
            <div class="hidden lg:grid lg:grid-cols-24 gap-3 px-4 py-3 bg-dark-100 dark:bg-dark-900 rounded-lg mb-2">
                <div class="col-span-1 text-xs font-semibold text-dark-700 dark:text-dark-300 uppercase text-center">#
                </div>
                <div class="col-span-4 text-xs font-semibold text-dark-700 dark:text-dark-300 uppercase">Client</div>
                <div class="col-span-5 text-xs font-semibold text-dark-700 dark:text-dark-300 uppercase">Service</div>
                <div class="col-span-2 text-xs font-semibold text-dark-700 dark:text-dark-300 uppercase text-center">
                    Qty</div>
                <div class="col-span-3 text-xs font-semibold text-dark-700 dark:text-dark-300 uppercase">Unit Price
                </div>
                <div class="col-span-3 text-xs font-semibold text-dark-700 dark:text-dark-300 uppercase">Amount</div>
                <div class="col-span-3 text-xs font-semibold text-dark-700 dark:text-dark-300 uppercase">COGS</div>
                <div class="col-span-2 text-xs font-semibold text-dark-700 dark:text-dark-300 uppercase text-center">
                    Non-Profit</div>
                <div class="col-span-1 text-xs font-semibold text-dark-700 dark:text-dark-300 uppercase text-center">
                </div>
            </div>

            <div class="space-y-2">
                <template x-for="(item, index) in items" :key="item.id">
                    <div>
                        {{-- Desktop Row --}}
                        <div
                            class="hidden lg:grid lg:grid-cols-24 gap-3 p-4 bg-dark-50 dark:bg-dark-900/50 rounded-lg border border-dark-200 dark:border-dark-700 items-center">
                            {{-- No --}}
                            <div class="col-span-1 text-center">
                                <span class="text-sm font-semibold text-dark-700 dark:text-dark-300"
                                    x-text="index + 1"></span>
                            </div>

                            {{-- Client --}}
                            <div class="col-span-4">
                                <div class="relative">
                                    <div x-show="!item.client_id"
                                        @click="itemSelectOpen[item.id] = !itemSelectOpen[item.id]"
                                        class="w-full px-2 py-1.5 text-sm border border-dark-200 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-800 text-dark-500 dark:text-dark-400 cursor-pointer hover:border-primary-400 transition truncate">
                                        Select...
                                    </div>
                                    <div x-show="item.client_id"
                                        class="w-full px-2 py-1.5 text-sm border border-dark-200 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-800 flex items-center justify-between gap-1">
                                        <span class="text-dark-900 dark:text-dark-50 truncate text-xs"
                                            x-text="item.client_name"></span>
                                        <button @click="clearItemClient(item)" type="button"
                                            class="text-dark-400 hover:text-red-500 transition flex-shrink-0">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div x-show="itemSelectOpen[item.id]" x-transition
                                        class="absolute z-50 mt-1 w-64 bg-white dark:bg-dark-800 border border-dark-200 dark:border-dark-700 rounded-lg shadow-lg max-h-80 overflow-hidden">
                                        <div class="p-2 border-b border-dark-200 dark:border-dark-700">
                                            <input type="text" x-model="itemSelectSearch[item.id]" @click.stop
                                                placeholder="Search..."
                                                class="w-full px-2 py-1.5 text-sm border border-dark-200 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-800 text-dark-900 dark:text-dark-50 placeholder-dark-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                        </div>
                                        <div class="overflow-y-auto max-h-64">
                                            <template x-for="client in filteredItemClients(item.id)"
                                                :key="client.id">
                                                <div @click="selectItemClient(item, client)"
                                                    class="px-3 py-2 hover:bg-primary-50 dark:hover:bg-primary-900/20 cursor-pointer transition border-b border-dark-100 dark:border-dark-700 last:border-0">
                                                    <div class="flex items-center gap-2">
                                                        <div
                                                            class="w-8 h-8 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-xs font-semibold flex-shrink-0">
                                                            <span x-text="client.name.charAt(0).toUpperCase()"></span>
                                                        </div>
                                                        <div class="flex-1 min-w-0">
                                                            <div class="text-sm font-medium text-dark-900 dark:text-dark-50 truncate"
                                                                x-text="client.name"></div>
                                                            <div class="text-xs text-dark-500 dark:text-dark-400 truncate"
                                                                x-text="client.email || '-'"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                            <div x-show="filteredItemClients(item.id).length === 0"
                                                class="px-3 py-6 text-center text-dark-500 dark:text-dark-400">
                                                <p class="text-xs">No clients found</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Service --}}
                            <div class="col-span-5">
                                <div class="relative flex gap-1" @click.away="serviceSelectOpen[item.id] = false">
                                    <input type="text" x-model="item.service_name"
                                        class="flex-1 px-2 py-1.5 text-sm border border-dark-200 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-800 text-dark-900 dark:text-dark-50 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                        placeholder="Type service name...">
                                    <button @click="serviceSelectOpen[item.id] = !serviceSelectOpen[item.id]"
                                        type="button"
                                        class="px-2 py-1.5 text-sm border border-dark-200 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-800 text-dark-600 dark:text-dark-400 hover:bg-dark-50 dark:hover:bg-dark-700 transition flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                    <div x-show="serviceSelectOpen[item.id]" x-transition
                                        class="absolute z-50 mt-1 w-96 bg-white dark:bg-dark-800 border border-dark-200 dark:border-dark-700 rounded-lg shadow-lg max-h-80 overflow-hidden">
                                        <div class="p-2 border-b border-dark-200 dark:border-dark-700">
                                            <input type="text" x-model="serviceSelectSearch[item.id]" @click.stop
                                                placeholder="Search services..."
                                                class="w-full px-2 py-1.5 text-sm border border-dark-200 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-800 text-dark-900 dark:text-dark-50 placeholder-dark-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                        </div>
                                        <div class="overflow-y-auto max-h-64">
                                            <template x-for="service in filteredItemServices(item.id)"
                                                :key="service.id">
                                                <div @click="selectService(item, service)"
                                                    class="px-3 py-2 hover:bg-primary-50 dark:hover:bg-primary-900/20 cursor-pointer transition border-b border-dark-100 dark:border-dark-700 last:border-0">
                                                    <div class="font-medium text-sm text-dark-900 dark:text-dark-50"
                                                        x-text="service.name"></div>
                                                    <div class="flex items-center justify-between mt-0.5">
                                                        <span class="text-xs text-primary-600 dark:text-primary-400"
                                                            x-text="service.type"></span>
                                                        <span
                                                            class="text-xs font-semibold text-dark-700 dark:text-dark-300"
                                                            x-text="service.formatted_price"></span>
                                                    </div>
                                                </div>
                                            </template>
                                            <div x-show="filteredItemServices(item.id).length === 0"
                                                class="px-3 py-6 text-center text-dark-500 dark:text-dark-400">
                                                <p class="text-xs">No services found</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Qty --}}
                            <div class="col-span-2">
                                <input type="number" x-model="item.quantity" @input="calculateItem(item)"
                                    min="1"
                                    class="w-full px-2 py-1.5 text-sm text-center border border-dark-200 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-800 text-dark-900 dark:text-dark-50 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            </div>

                            {{-- Unit Price --}}
                            <div class="col-span-3">
                                <input type="text" x-model="item.unit_price"
                                    @input="item.unit_price = formatInput($event.target.value); calculateItem(item)"
                                    class="w-full px-2 py-1.5 text-sm border border-dark-200 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-800 text-dark-900 dark:text-dark-50 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                    placeholder="0">
                            </div>

                            {{-- Amount --}}
                            <div class="col-span-3">
                                <div class="px-2 py-1.5 text-sm border border-dark-200 dark:border-dark-600 rounded-lg bg-dark-100 dark:bg-dark-900 text-dark-900 dark:text-dark-50 font-semibold text-right"
                                    x-text="formatCurrency(item.amount)"></div>
                            </div>

                            {{-- COGS --}}
                            <div class="col-span-3">
                                <input type="text" x-model="item.cogs_amount"
                                    @input="item.cogs_amount = formatInput($event.target.value); calculateItem(item)"
                                    class="w-full px-2 py-1.5 text-sm border border-dark-200 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-800 text-dark-900 dark:text-dark-50 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                    placeholder="0">
                            </div>

                            {{-- Tax --}}
                            <div class="col-span-2 flex justify-center">
                                <label class="flex items-center gap-1.5 cursor-pointer">
                                    <input type="checkbox" x-model="item.is_tax_deposit"
                                        class="rounded border-dark-300 dark:border-dark-600 text-primary-600 focus:ring-2 focus:ring-primary-500">
                                    <span class="text-xs text-dark-600 dark:text-dark-400">Non-Profit</span>
                                </label>
                            </div>

                            {{-- Remove --}}
                            <div class="col-span-1 flex justify-center">
                                <button @click="removeItem(index)" type="button"
                                    class="p-1.5 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Mobile Card --}}
                        <div
                            class="block lg:hidden p-4 bg-dark-50 dark:bg-dark-900/50 rounded-lg border border-dark-200 dark:border-dark-700 space-y-3">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-dark-900 dark:text-dark-50"
                                        x-text="'#' + (index + 1)"></span>
                                    <label class="flex items-center gap-1.5 cursor-pointer">
                                        <input type="checkbox" x-model="item.is_tax_deposit"
                                            class="rounded border-dark-300 dark:border-dark-600 text-primary-600 focus:ring-2 focus:ring-primary-500">
                                        <span class="text-xs text-dark-600 dark:text-dark-400">Non-Profit</span>
                                    </label>
                                </div>
                                <button @click="removeItem(index)" type="button"
                                    class="text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 p-1 rounded transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>

                            <div class="space-y-2">
                                {{-- Client --}}
                                <div>
                                    <label
                                        class="block text-xs font-medium text-dark-600 dark:text-dark-400 mb-1">Client</label>
                                    <div class="relative">
                                        <div x-show="!item.client_id"
                                            @click="itemSelectOpen[item.id] = !itemSelectOpen[item.id]"
                                            class="w-full px-3 py-2 text-sm border border-dark-200 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-800 text-dark-500 dark:text-dark-400 cursor-pointer hover:border-primary-400 transition">
                                            Select client...
                                        </div>
                                        <div x-show="item.client_id"
                                            class="w-full px-3 py-2 text-sm border border-dark-200 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-800 flex items-center justify-between">
                                            <span class="text-dark-900 dark:text-dark-50"
                                                x-text="item.client_name"></span>
                                            <button @click="clearItemClient(item)" type="button"
                                                class="text-dark-400 hover:text-red-500 transition">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                        <div x-show="itemSelectOpen[item.id]" x-transition
                                            class="absolute z-50 mt-1 w-full bg-white dark:bg-dark-800 border border-dark-200 dark:border-dark-700 rounded-lg shadow-lg max-h-80 overflow-hidden">
                                            <div class="p-2 border-b border-dark-200 dark:border-dark-700">
                                                <input type="text" x-model="itemSelectSearch[item.id]" @click.stop
                                                    placeholder="Search clients..."
                                                    class="w-full px-3 py-2 text-sm border border-dark-200 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-800 text-dark-900 dark:text-dark-50 placeholder-dark-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                            </div>
                                            <div class="overflow-y-auto max-h-64">
                                                <template x-for="client in filteredItemClients(item.id)"
                                                    :key="client.id">
                                                    <div @click="selectItemClient(item, client)"
                                                        class="px-4 py-3 hover:bg-primary-50 dark:hover:bg-primary-900/20 cursor-pointer transition border-b border-dark-100 dark:border-dark-700 last:border-0">
                                                        <div class="flex items-center gap-3">
                                                            <div
                                                                class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white font-semibold flex-shrink-0">
                                                                <span
                                                                    x-text="client.name.charAt(0).toUpperCase()"></span>
                                                            </div>
                                                            <div class="flex-1 min-w-0">
                                                                <div class="font-medium text-dark-900 dark:text-dark-50 truncate"
                                                                    x-text="client.name"></div>
                                                                <div class="text-sm text-dark-500 dark:text-dark-400 truncate"
                                                                    x-text="client.email || '-'"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>
                                                <div x-show="filteredItemClients(item.id).length === 0"
                                                    class="px-4 py-8 text-center text-dark-500 dark:text-dark-400">
                                                    <p class="text-sm">No clients found</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Service --}}
                                <div>
                                    <label
                                        class="block text-xs font-medium text-dark-600 dark:text-dark-400 mb-1">Service
                                        Name</label>
                                    <div class="relative flex gap-2" @click.away="serviceSelectOpen[item.id] = false">
                                        <input type="text" x-model="item.service_name"
                                            class="flex-1 px-3 py-2 text-sm border border-dark-200 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-800 text-dark-900 dark:text-dark-50 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                            placeholder="Type service name...">
                                        <button @click="serviceSelectOpen[item.id] = !serviceSelectOpen[item.id]"
                                            type="button"
                                            class="px-3 py-2 border border-dark-200 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-800 text-dark-600 dark:text-dark-400 hover:bg-dark-50 dark:hover:bg-dark-700 transition flex-shrink-0">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                        <div x-show="serviceSelectOpen[item.id]" x-transition
                                            class="absolute z-50 mt-1 w-full bg-white dark:bg-dark-800 border border-dark-200 dark:border-dark-700 rounded-lg shadow-lg max-h-80 overflow-hidden">
                                            <div class="p-2 border-b border-dark-200 dark:border-dark-700">
                                                <input type="text" x-model="serviceSelectSearch[item.id]"
                                                    @click.stop placeholder="Search services..."
                                                    class="w-full px-3 py-2 text-sm border border-dark-200 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-800 text-dark-900 dark:text-dark-50 placeholder-dark-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                            </div>
                                            <div class="overflow-y-auto max-h-64">
                                                <template x-for="service in filteredItemServices(item.id)"
                                                    :key="service.id">
                                                    <div @click="selectService(item, service)"
                                                        class="px-4 py-3 hover:bg-primary-50 dark:hover:bg-primary-900/20 cursor-pointer transition border-b border-dark-100 dark:border-dark-700 last:border-0">
                                                        <div class="font-medium text-dark-900 dark:text-dark-50"
                                                            x-text="service.name"></div>
                                                        <div class="flex items-center justify-between mt-1">
                                                            <span
                                                                class="text-xs text-primary-600 dark:text-primary-400"
                                                                x-text="service.type"></span>
                                                            <span
                                                                class="text-sm font-semibold text-dark-700 dark:text-dark-300"
                                                                x-text="service.formatted_price"></span>
                                                        </div>
                                                    </div>
                                                </template>
                                                <div x-show="filteredItemServices(item.id).length === 0"
                                                    class="px-4 py-8 text-center text-dark-500 dark:text-dark-400">
                                                    <p class="text-sm">No services found</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Qty & Unit Price --}}
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-dark-600 dark:text-dark-400 mb-1">Quantity</label>
                                        <input type="number" x-model="item.quantity" @input="calculateItem(item)"
                                            min="1"
                                            class="w-full px-3 py-2 text-sm border border-dark-200 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-800 text-dark-900 dark:text-dark-50 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-dark-600 dark:text-dark-400 mb-1">Unit
                                            Price</label>
                                        <input type="text" x-model="item.unit_price"
                                            @input="item.unit_price = formatInput($event.target.value); calculateItem(item)"
                                            class="w-full px-3 py-2 text-sm border border-dark-200 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-800 text-dark-900 dark:text-dark-50 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                            placeholder="0">
                                    </div>
                                </div>

                                {{-- Amount & COGS --}}
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-dark-600 dark:text-dark-400 mb-1">Amount</label>
                                        <div class="px-3 py-2 text-sm border border-dark-200 dark:border-dark-600 rounded-lg bg-dark-100 dark:bg-dark-900 text-dark-900 dark:text-dark-50 font-semibold"
                                            x-text="formatCurrency(item.amount)"></div>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-dark-600 dark:text-dark-400 mb-1">COGS</label>
                                        <input type="text" x-model="item.cogs_amount"
                                            @input="item.cogs_amount = formatInput($event.target.value); calculateItem(item)"
                                            class="w-full px-3 py-2 text-sm border border-dark-200 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-800 text-dark-900 dark:text-dark-50 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                            placeholder="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <div x-show="items.length === 0"
                    class="p-12 text-center border-2 border-dashed border-dark-300 dark:border-dark-600 rounded-lg">
                    <svg class="w-16 h-16 mx-auto text-dark-400 dark:text-dark-500 mb-3" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-dark-600 dark:text-dark-400 text-sm">No items yet. Click "Add Item" to get started.
                    </p>
                </div>
            </div>
        </div>

        {{-- Invoice Summary --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="hidden lg:block"></div>

            <div
                class="bg-white dark:bg-dark-800 rounded-xl shadow-sm border border-dark-200 dark:border-dark-600 p-6">
                <h2 class="text-lg font-semibold text-dark-900 dark:text-dark-50 mb-6">Invoice Summary</h2>

                <div class="space-y-4">
                    {{-- Subtotal --}}
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-dark-600 dark:text-dark-400">Subtotal</span>
                        <span class="text-lg font-semibold text-dark-900 dark:text-dark-50"
                            x-text="formatCurrency(subtotal)"></span>
                    </div>

                    {{-- Discount --}}
                    <div class="pt-4 pb-4 border-t border-b border-dark-200 dark:border-dark-700 space-y-3">
                        <div class="flex flex-col gap-2">
                            <span class="text-sm font-medium text-dark-900 dark:text-dark-50">Discount</span>
                            <select x-model="discount.type"
                                class="w-full px-3 py-2 text-sm border border-dark-200 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-800 text-dark-900 dark:text-dark-50 focus:ring-2 focus:ring-primary-500 focus:border-transparent appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236b7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[length:1.25rem] bg-[right_0.5rem_center] bg-no-repeat pr-10">
                                <option value="fixed">Fixed Amount</option>
                                <option value="percentage">Percentage (%)</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <input type="text" x-model="discount.value"
                                @input="discount.value = formatInput($event.target.value)"
                                x-show="discount.type === 'fixed'" placeholder="Enter amount"
                                class="w-full px-3 py-2 text-sm border border-dark-200 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-800 text-dark-900 dark:text-dark-50 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <input type="number" x-model="discount.value" x-show="discount.type === 'percentage'"
                                placeholder="Enter percentage" min="0" max="100"
                                class="w-full px-3 py-2 text-sm border border-dark-200 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-800 text-dark-900 dark:text-dark-50 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <input type="text" x-model="discount.reason" placeholder="Reason (optional)"
                                class="w-full px-3 py-2 text-sm border border-dark-200 dark:border-dark-600 rounded-lg bg-white dark:bg-dark-800 text-dark-900 dark:text-dark-50 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        </div>

                        <div class="flex justify-between items-center" x-show="discountAmount > 0">
                            <span class="text-sm text-dark-600 dark:text-dark-400">Discount Applied</span>
                            <span class="text-sm font-medium text-red-600 dark:text-red-400"
                                x-text="'- ' + formatCurrency(discountAmount)"></span>
                        </div>
                    </div>

                    {{-- Total --}}
                    <div class="py-4 px-4 bg-primary-50 dark:bg-primary-900/20 rounded-lg">
                        <div class="flex justify-between items-center">
                            <span class="text-base font-semibold text-dark-900 dark:text-dark-50">Total Amount</span>
                            <span class="text-2xl font-bold text-primary-600 dark:text-primary-400"
                                x-text="formatCurrency(totalAmount)"></span>
                        </div>
                    </div>

                    {{-- Additional Info --}}
                    <div class="grid grid-cols-2 gap-3 pt-3">
                        <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                            <span class="text-xs text-green-700 dark:text-green-400 block mb-1">Net Profit</span>
                            <span class="text-base font-semibold text-green-600 dark:text-green-400"
                                x-text="formatCurrency(netProfit)"></span>
                        </div>
                        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <span class="text-xs text-blue-700 dark:text-blue-400 block mb-1">Non-Profit</span>
                            <span class="text-base font-semibold text-blue-600 dark:text-blue-400"
                                x-text="formatCurrency(taxDeposits)"></span>
                        </div>
                    </div>

                    {{-- Update Button --}}
                    <button @click="syncAndUpdate()" type="button" :disabled="saving || items.length === 0"
                        :class="saving || items.length === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-primary-700'"
                        class="w-full mt-4 px-6 py-3 bg-primary-600 text-white rounded-lg font-semibold transition flex items-center justify-center gap-2">
                        <svg x-show="saving" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <span x-text="saving ? 'Updating...' : 'Update Invoice'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function invoiceEditForm() {
        return {
            invoice: {
                invoice_number: '',
                client_id: null,
                client_name: '',
                issue_date: null,
                due_date: null,
            },
            items: [],
            discount: {
                type: 'fixed',
                value: 0,
                reason: ''
            },
            clients: @js($this->clients),
            services: @js($this->services),
            existingInvoice: @js($this->existingInvoiceData),
            existingItems: @js($this->existingItems),
            existingDiscount: @js($this->existingDiscount),
            nextId: 1,
            selectOpen: false,
            selectSearch: '',
            itemSelectOpen: {},
            itemSelectSearch: {},
            serviceSelectOpen: {},
            serviceSelectSearch: {},
            saving: false,
            bulkCount: 1,

            init() {
                // Load existing invoice data
                this.invoice = {
                    ...this.existingInvoice
                };

                // Load existing items with proper ID tracking
                this.items = this.existingItems.map(item => ({
                    ...item,
                    id: this.nextId++
                }));

                // Initialize dropdown states for existing items
                this.items.forEach(item => {
                    this.itemSelectOpen[item.id] = false;
                    this.itemSelectSearch[item.id] = '';
                    this.serviceSelectOpen[item.id] = false;
                    this.serviceSelectSearch[item.id] = '';
                });

                // Load existing discount
                this.discount = {
                    ...this.existingDiscount
                };
            },

            get filteredClients() {
                return this.filter(this.clients, this.selectSearch, ['name', 'email']);
            },
            filteredItemClients(id) {
                return this.filter(this.clients, this.itemSelectSearch[id], ['name', 'email']);
            },
            filteredItemServices(id) {
                return this.filter(this.services, this.serviceSelectSearch[id], ['name', 'type']);
            },
            get subtotal() {
                return this.items.filter(i => !i.is_tax_deposit).reduce((s, i) => s + (i.amount || 0), 0);
            },
            get totalProfit() {
                return this.items.filter(i => !i.is_tax_deposit).reduce((s, i) => s + (i.profit || 0), 0);
            },
            get netProfit() {
                return Math.max(0, this.totalProfit - this.discountAmount);
            },
            get taxDeposits() {
                return this.items.filter(i => i.is_tax_deposit).reduce((s, i) => s + (i.amount || 0), 0);
            },
            get discountAmount() {
                if (this.discount.type === 'fixed') return this.parse(this.discount.value);
                return Math.round((this.subtotal * (this.discount.value / 100)));
            },
            get totalAmount() {
                return Math.max(0, this.subtotal - this.discountAmount);
            },

            filter(arr, search, keys) {
                if (!search) return arr;
                const s = search.toLowerCase();
                return arr.filter(item => keys.some(k => item[k]?.toLowerCase().includes(s)));
            },

            selectClient(c) {
                this.invoice.client_id = c.id;
                this.invoice.client_name = c.name;
                this.selectOpen = false;
                this.selectSearch = '';
                this.items.forEach(i => {
                    if (!i.client_id) {
                        i.client_id = c.id;
                        i.client_name = c.name;
                    }
                });
            },
            clearClient() {
                this.invoice.client_id = null;
                this.invoice.client_name = '';
            },

            selectItemClient(item, c) {
                item.client_id = c.id;
                item.client_name = c.name;
                this.itemSelectOpen[item.id] = false;
            },
            clearItemClient(item) {
                item.client_id = null;
                item.client_name = '';
            },

            selectService(item, s) {
                item.service_name = s.name;
                item.unit_price = s.formatted_price.replace('Rp ', '');
                this.serviceSelectOpen[item.id] = false;
                this.calculateItem(item);
            },

            addItem() {
                const item = {
                    id: this.nextId++,
                    client_id: this.invoice.client_id || null,
                    client_name: this.invoice.client_name || '',
                    service_name: '',
                    quantity: 1,
                    unit_price: '',
                    amount: 0,
                    cogs_amount: '',
                    profit: 0,
                    is_tax_deposit: false
                };
                this.items.push(item);
                this.itemSelectOpen[item.id] = false;
                this.itemSelectSearch[item.id] = '';
                this.serviceSelectOpen[item.id] = false;
                this.serviceSelectSearch[item.id] = '';
            },

            bulkAddItems() {
                const count = parseInt(this.bulkCount) || 1;
                for (let i = 0; i < count; i++) {
                    this.addItem();
                }
                this.bulkCount = 1;
            },

            removeItem(index) {
                const id = this.items[index].id;
                ['itemSelectOpen', 'itemSelectSearch', 'serviceSelectOpen', 'serviceSelectSearch'].forEach(o =>
                    delete this[o][id]);
                this.items.splice(index, 1);
            },

            calculateItem(item) {
                const qty = parseInt(item.quantity) || 1,
                    price = this.parse(item.unit_price),
                    cogs = this.parse(item.cogs_amount);
                item.amount = qty * price;
                item.profit = item.amount - cogs;
            },

            parse(val) {
                return parseInt((val || '').toString().replace(/[^0-9]/g, '')) || 0;
            },
            formatInput(val) {
                const num = (val || '').toString().replace(/[^0-9]/g, '');
                if (!num) return '';
                return parseInt(num).toLocaleString('id-ID');
            },
            formatCurrency(val) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(val || 0);
            },
            closeAllDropdowns() {
                this.selectOpen = false;
                Object.keys(this.itemSelectOpen).forEach(k => this.itemSelectOpen[k] = false);
                Object.keys(this.serviceSelectOpen).forEach(k => this.serviceSelectOpen[k] = false);
            },

            async syncAndUpdate() {
                this.saving = true;
                try {
                    this.$wire.invoiceData = this.invoice;
                    this.$wire.items = this.items;

                    let discountValue = 0;
                    if (this.discount.type === 'percentage') {
                        discountValue = parseFloat(this.discount.value) || 0;
                    } else {
                        discountValue = this.parse(this.discount.value);
                    }

                    this.$wire.discount = {
                        type: this.discount.type,
                        value: discountValue,
                        reason: this.discount.reason || ''
                    };

                    await this.$wire.update();
                } catch (error) {
                    console.error(error);
                } finally {
                    this.saving = false;
                }
            }
        }
    }
</script>
