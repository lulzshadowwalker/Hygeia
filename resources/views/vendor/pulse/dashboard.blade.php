<x-pulse>
    {{-- Server & App Status --}}
    <livewire:pulse.servers cols="full" />
    <livewire:pulse.usage cols="4" rows="2" />
    <livewire:pulse.usage-hours cols="4" />

    {{-- System Performance --}}
    <livewire:pulse.queues cols="4" />
    <livewire:pulse.cache cols="4" />
    <livewire:pulse.log-files cols="4" />

    {{-- Real-time Activity --}}
    <livewire:reverb.connections cols="full" />
    <livewire:reverb.messages cols="full" />

    {{-- Request Monitoring --}}
    <livewire:requests-graph cols="6" />
    <livewire:requests cols="6" />

    {{-- Performance Issues --}}
    <livewire:pulse.slow-queries cols="full" />
    <livewire:pulse.slow-requests cols="4" />
    <livewire:pulse.slow-jobs cols="4" />
    <livewire:pulse.slow-outgoing-requests cols="4" />

    {{-- Issues & Errors --}}
    <livewire:pulse.exceptions cols="6" />
    <livewire:pulse.validation-errors cols="6" />

    {{-- Additional Info --}}
    <livewire:pulse.about-application cols="full" />
</x-pulse>
