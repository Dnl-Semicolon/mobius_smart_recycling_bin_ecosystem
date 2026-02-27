<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Explorer — Mobius Dev</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        pre.json { white-space: pre-wrap; word-break: break-all; }
    </style>
</head>
<body class="font-inter bg-gray-50 min-h-screen"
    x-data="apiExplorer()"
>
    {{-- Header --}}
    <header class="bg-white border-b border-gray-200/60 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 bg-emerald-600 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5" /></svg>
                </span>
                <span class="font-bold text-gray-900 tracking-tight">Mobius API Explorer</span>
                <span class="text-xs bg-amber-100 text-amber-700 font-medium px-2 py-0.5 rounded-full">DEV</span>
            </div>

            {{-- Auth status --}}
            <div class="flex items-center gap-3">
                <template x-if="token">
                    <div class="flex items-center gap-2">
                        <span class="text-xs bg-emerald-100 text-emerald-700 font-medium px-2 py-1 rounded-full" x-text="'Logged in as ' + userName"></span>
                        <button @click="logout()" class="text-xs text-red-600 hover:text-red-700 font-medium">Logout</button>
                    </div>
                </template>
                <template x-if="!token">
                    <span class="text-xs bg-gray-100 text-gray-600 font-medium px-2 py-1 rounded-full">Not authenticated</span>
                </template>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
        <div class="grid lg:grid-cols-12 gap-6">
            {{-- Left: Login + Endpoint List --}}
            <div class="lg:col-span-4 space-y-4">
                {{-- Login Panel --}}
                <div class="bg-white rounded-xl border border-gray-200/60 p-4" x-show="!token">
                    <h2 class="text-sm font-semibold text-gray-900 mb-3">Login</h2>
                    <div class="space-y-2">
                        <input x-model="loginEmail" type="email" placeholder="Email" class="w-full text-sm rounded-lg border border-gray-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                        <input x-model="loginPassword" type="password" placeholder="Password" class="w-full text-sm rounded-lg border border-gray-200 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                        <button @click="login()" :disabled="loginLoading" class="w-full text-sm font-medium bg-emerald-600 text-white rounded-lg px-3 py-2 hover:bg-emerald-700 transition-colors disabled:opacity-50">
                            <span x-show="!loginLoading">Get Token</span>
                            <span x-show="loginLoading">Authenticating...</span>
                        </button>
                    </div>
                    <template x-if="loginError">
                        <p class="text-xs text-red-600 mt-2" x-text="loginError"></p>
                    </template>
                </div>

                {{-- Endpoint List --}}
                @foreach ($endpoints as $group => $items)
                    <div class="bg-white rounded-xl border border-gray-200/60 overflow-hidden">
                        <div class="px-4 py-2.5 bg-gray-50/80 border-b border-gray-100">
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ $group }}</h3>
                        </div>
                        <div class="divide-y divide-gray-50">
                            @foreach ($items as $i => $ep)
                                <button
                                    @click="selectEndpoint({{ json_encode($ep) }})"
                                    class="w-full text-left px-4 py-2.5 hover:bg-gray-50 transition-colors flex items-center gap-2"
                                    :class="selected?.path === '{{ $ep['path'] }}' && selected?.method === '{{ $ep['method'] }}' ? 'bg-emerald-50' : ''"
                                >
                                    <span class="text-xs font-bold px-1.5 py-0.5 rounded {{ $ep['method'] === 'GET' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700' }}">{{ $ep['method'] }}</span>
                                    <span class="text-xs text-gray-700 truncate flex-1">{{ $ep['path'] }}</span>
                                    @if (!$ep['auth'])
                                        <span class="text-xs text-gray-400">open</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Right: Request/Response Panel --}}
            <div class="lg:col-span-8">
                <template x-if="!selected">
                    <div class="bg-white rounded-xl border border-gray-200/60 p-8 text-center">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.042 21.672 13.684 16.6m0 0-2.51 2.225.569-9.47 5.227 7.917-3.286-.672ZM12 2.25V4.5m5.834.166-1.591 1.591M20.25 10.5H18M7.757 14.743l-1.59 1.59M6 10.5H3.75m4.007-4.243-1.59-1.59" /></svg>
                        <p class="text-sm text-gray-500">Select an endpoint from the left to get started.</p>
                    </div>
                </template>

                <template x-if="selected">
                    <div class="space-y-4">
                        {{-- Endpoint Info --}}
                        <div class="bg-white rounded-xl border border-gray-200/60 p-5">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="text-sm font-bold px-2 py-1 rounded" :class="selected.method === 'GET' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700'" x-text="selected.method"></span>
                                <span class="text-sm font-medium text-gray-900" x-text="selected.description"></span>
                            </div>
                            <div class="flex items-center gap-2 mb-4">
                                <input x-model="requestPath" type="text" class="flex-1 text-sm font-mono rounded-lg border border-gray-200 px-3 py-2 bg-gray-50 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                            </div>

                            {{-- Request Body (for POST) --}}
                            <div x-show="selected.method === 'POST'" class="mb-4">
                                <label class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1 block">Request Body (JSON)</label>
                                <textarea x-model="requestBody" rows="4" class="w-full text-sm font-mono rounded-lg border border-gray-200 px-3 py-2 bg-gray-50 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                            </div>

                            {{-- Auth note --}}
                            <div x-show="selected.auth && !token" class="mb-4">
                                <p class="text-xs text-amber-600 bg-amber-50 rounded-lg px-3 py-2">This endpoint requires authentication. Login first.</p>
                            </div>

                            <button @click="sendRequest()" :disabled="loading" class="inline-flex items-center gap-2 text-sm font-medium bg-emerald-600 text-white rounded-lg px-5 py-2.5 hover:bg-emerald-700 transition-colors disabled:opacity-50">
                                <span x-show="!loading">Send Request</span>
                                <span x-show="loading">Sending...</span>
                            </button>
                        </div>

                        {{-- Response --}}
                        <div x-show="response !== null" class="bg-white rounded-xl border border-gray-200/60 overflow-hidden">
                            <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-gray-900">Response</h3>
                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-mono" :class="{
                                        'text-emerald-600': responseStatus >= 200 && responseStatus < 300,
                                        'text-amber-600': responseStatus >= 300 && responseStatus < 400,
                                        'text-red-600': responseStatus >= 400
                                    }" x-text="responseStatus"></span>
                                    <span class="text-xs text-gray-400" x-text="responseTime + 'ms'"></span>
                                </div>
                            </div>
                            <pre class="json p-5 text-sm font-mono text-gray-800 bg-gray-50 max-h-[600px] overflow-auto" x-text="response"></pre>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <script>
        function apiExplorer() {
            return {
                // Auth state
                token: null,
                userName: '',
                loginEmail: 'admin@mobius.test',
                loginPassword: 'password',
                loginLoading: false,
                loginError: '',

                // Endpoint state
                selected: null,
                requestPath: '',
                requestBody: '',

                // Response state
                loading: false,
                response: null,
                responseStatus: null,
                responseTime: null,

                selectEndpoint(ep) {
                    this.selected = ep;
                    this.requestPath = ep.path;
                    this.requestBody = ep.body || '';
                    this.response = null;
                    this.responseStatus = null;
                    this.responseTime = null;
                },

                async login() {
                    this.loginLoading = true;
                    this.loginError = '';
                    try {
                        const res = await fetch('/api/v1/auth/login', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                            body: JSON.stringify({
                                email: this.loginEmail,
                                password: this.loginPassword,
                                device_name: 'API Explorer'
                            })
                        });
                        const data = await res.json();
                        if (res.ok && data.token) {
                            this.token = data.token;
                            this.userName = data.user?.name || data.user?.email || 'User';
                        } else {
                            this.loginError = data.message || 'Login failed.';
                        }
                    } catch (e) {
                        this.loginError = 'Network error: ' + e.message;
                    }
                    this.loginLoading = false;
                },

                logout() {
                    this.token = null;
                    this.userName = '';
                },

                async sendRequest() {
                    this.loading = true;
                    this.response = null;
                    this.responseStatus = null;

                    const headers = { 'Accept': 'application/json', 'Content-Type': 'application/json' };
                    if (this.token) {
                        headers['Authorization'] = 'Bearer ' + this.token;
                    }

                    const opts = { method: this.selected.method, headers };
                    if (this.selected.method === 'POST' && this.requestBody.trim()) {
                        opts.body = this.requestBody;
                    }

                    const start = performance.now();
                    try {
                        const res = await fetch(this.requestPath, opts);
                        const elapsed = Math.round(performance.now() - start);
                        const text = await res.text();

                        this.responseStatus = res.status;
                        this.responseTime = elapsed;

                        try {
                            this.response = JSON.stringify(JSON.parse(text), null, 2);
                        } catch {
                            this.response = text;
                        }
                    } catch (e) {
                        this.responseStatus = 0;
                        this.responseTime = Math.round(performance.now() - start);
                        this.response = 'Network error: ' + e.message;
                    }
                    this.loading = false;
                }
            };
        }
    </script>
</body>
</html>
