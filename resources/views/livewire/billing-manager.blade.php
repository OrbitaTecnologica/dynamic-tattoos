<div class="space-y-6">
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold text-gray-900">Estado de suscripcion</h3>
        <p class="mt-2 text-sm text-gray-600">
            @switch($this->subscriptionStatus)
                @case('activa')
                    Tu suscripcion esta activa.
                    @break
                @case('grace_period')
                    Tu suscripcion esta en periodo de gracia.
                    @break
                @case('cancelada')
                    Tu suscripcion esta cancelada.
                    @break
                @default
                    Aun no tienes un plan activo.
            @endswitch
        </p>

        @if($this->currentPlan)
            <div class="mt-4 rounded-md bg-gray-50 p-4 text-sm text-gray-700">
                <p><span class="font-semibold">Plan:</span> {{ $this->currentPlan->name }}</p>
                <p><span class="font-semibold">Precio:</span> ${{ number_format((float) $this->currentPlan->price, 2) }} / {{ $this->currentPlan->billing_cycle }}</p>
                <p><span class="font-semibold">Maximo de tatuajes:</span> {{ $this->currentPlan->max_tattoos }}</p>
            </div>
        @endif

        @if($this->user->stripe_id)
            <form method="POST" action="{{ route('billing.portal') }}" class="mt-4">
                @csrf
                <button type="submit" class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-black">
                    Gestionar suscripcion en Stripe
                </button>
            </form>
        @endif
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold text-gray-900">Cambiar plan</h3>
        <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach($this->availablePlans as $plan)
                <div class="rounded-lg border border-gray-200 p-4">
                    <p class="text-sm font-semibold text-gray-900">{{ $plan->name }}</p>
                    <p class="mt-1 text-sm text-gray-600">${{ number_format((float) $plan->price, 2) }} / {{ $plan->billing_cycle }}</p>

                    <ul class="mt-3 space-y-1 text-xs text-gray-600">
                        @foreach($plan->features ?? [] as $feature)
                            <li>- {{ $feature }}</li>
                        @endforeach
                    </ul>

                    @if($plan->billing_cycle !== 'lifetime' && $plan->stripe_price_id)
                        <form method="POST" action="{{ route('billing.checkout', $plan) }}" class="mt-4">
                            @csrf
                            <button type="submit" class="w-full rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                                Seleccionar plan
                            </button>
                        </form>
                    @else
                        <p class="mt-4 text-xs text-amber-700">Plan no disponible para checkout automatico.</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
