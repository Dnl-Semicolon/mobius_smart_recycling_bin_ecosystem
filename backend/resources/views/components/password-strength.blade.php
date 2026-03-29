@props([
    'name' => 'password',
    'label' => 'Password',
    'confirm' => false,
    'confirmLabel' => 'Confirm Password',
    'required' => true,
])

<div
    x-data="{
        show: false,
        showConfirm: false,
        password: '',
        get hasMinLength() { return this.password.length >= 8 },
        get hasUppercase() { return /[A-Z]/.test(this.password) },
        get hasLowercase() { return /[a-z]/.test(this.password) },
        get hasNumber() { return /[0-9]/.test(this.password) },
        get hasSymbol() { return /[^A-Za-z0-9]/.test(this.password) },
        get criteria() {
            return [
                { label: 'At least 8 characters', passed: this.hasMinLength },
                { label: 'One uppercase letter', passed: this.hasUppercase },
                { label: 'One lowercase letter', passed: this.hasLowercase },
                { label: 'One number', passed: this.hasNumber },
                { label: 'One symbol (!@#$...)', passed: this.hasSymbol },
            ]
        },
        get passedCount() { return this.criteria.filter(c => c.passed).length },
        get allPassed() { return this.passedCount === 5 },
        get strengthLabel() {
            if (this.password.length === 0) return '';
            if (this.passedCount <= 2) return 'Weak';
            if (this.passedCount <= 4) return 'Fair';
            return 'Strong';
        },
        get strengthColor() {
            if (this.passedCount <= 2) return 'red';
            if (this.passedCount <= 4) return 'amber';
            return 'emerald';
        },
    }"
    class="space-y-4"
>
    {{-- Password field --}}
    <div class="space-y-1.5">
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-600">{{ $label }}</label>
        <div class="relative">
            <input
                :type="show ? 'text' : 'password'"
                name="{{ $name }}"
                id="{{ $name }}"
                x-model="password"
                @if($required) required @endif
                class="w-full rounded-xl border border-gray-200/80 bg-white/60 px-4 pr-10 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-500/10"
            >
            <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors cursor-pointer">
                <x-heroicon-o-eye x-show="!show" class="w-4 h-4" />
                <x-heroicon-o-eye-slash x-show="show" x-cloak class="w-4 h-4" />
            </button>
        </div>

        {{-- Static hint when empty --}}
        <p x-show="password.length === 0" class="text-xs text-gray-400">Must include uppercase, lowercase, number, and symbol.</p>

        {{-- Criteria checklist --}}
        <div x-show="password.length > 0" x-cloak class="space-y-1">
            <template x-for="item in criteria" :key="item.label">
                <div class="flex items-center gap-1.5">
                    <svg x-show="item.passed" class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    <svg x-show="!item.passed" x-cloak class="w-3.5 h-3.5 text-gray-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span class="text-xs transition-colors" :class="item.passed ? 'text-emerald-600' : 'text-gray-400'" x-text="item.label"></span>
                </div>
            </template>
        </div>

        {{-- Strength bar --}}
        <div x-show="password.length > 0" x-cloak class="flex items-center gap-2.5">
            <div class="flex gap-1 flex-1">
                <template x-for="i in 5" :key="i">
                    <div class="h-1 flex-1 rounded-full transition-colors duration-200"
                         :class="i <= passedCount
                             ? (strengthColor === 'red' ? 'bg-red-400' : strengthColor === 'amber' ? 'bg-amber-400' : 'bg-emerald-500')
                             : 'bg-gray-200'">
                    </div>
                </template>
            </div>
            <span class="text-xs font-medium shrink-0 transition-colors"
                  :class="strengthColor === 'red' ? 'text-red-500' : strengthColor === 'amber' ? 'text-amber-500' : 'text-emerald-600'"
                  x-text="strengthLabel"></span>
        </div>

        @error($name)
            <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
        @enderror
    </div>

    {{-- Confirmation field --}}
    @if($confirm)
        <div class="space-y-1.5">
            <label for="{{ $name }}_confirmation" class="block text-sm font-medium text-gray-600">{{ $confirmLabel }}</label>
            <div class="relative">
                <input
                    :type="showConfirm ? 'text' : 'password'"
                    name="{{ $name }}_confirmation"
                    id="{{ $name }}_confirmation"
                    @if($required) required @endif
                    class="w-full rounded-xl border border-gray-200/80 bg-white/60 px-4 pr-10 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-500/10"
                >
                <button type="button" @click="showConfirm = !showConfirm" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors cursor-pointer">
                    <x-heroicon-o-eye x-show="!showConfirm" class="w-4 h-4" />
                    <x-heroicon-o-eye-slash x-show="showConfirm" x-cloak class="w-4 h-4" />
                </button>
            </div>
        </div>
    @endif
</div>
