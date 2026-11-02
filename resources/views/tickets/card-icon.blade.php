<svg class="bt-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($icon)
        @case('group')<circle cx="12" cy="7" r="3"/><circle cx="4.5" cy="9" r="2"/><circle cx="19.5" cy="9" r="2"/><path d="M7 21v-3a5 5 0 0 1 10 0v3M1 19v-2a4 4 0 0 1 5-4M23 19v-2a4 4 0 0 0-5-4"/>@break
        @case('leaf')<path d="M5 20C2 7 12 3 21 3c-1 9-5 15-13 14M4 22 17 7"/>@break
        @case('person')<circle cx="12" cy="7" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2Z"/>@break
        @case('phone')<path d="m5 3 4 4-2 3c2 3 4 5 7 6l3-2 4 4-2 3C11 22 2 13 2 6Z"/>@break
        @case('calendar')<rect x="3" y="5" width="18" height="17" rx="2"/><path d="M7 2v6M17 2v6M3 11h18M7 15h2M13 15h2M7 18h2"/>@break
        @case('coins')<ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v12c0 4 16 4 16 0V5M4 9c0 4 16 4 16 0M4 13c0 4 16 4 16 0"/>@break
        @case('hourglass')<path d="M5 2h14M5 22h14M7 2v5l5 5-5 5v5M17 2v5l-5 5 5 5v5"/>@break
        @case('trend')<path d="m3 18 6-7 5 4 7-11M16 4h5v5"/>@break
    @endswitch
</svg>
