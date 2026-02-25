@vite(['resources/js/app.js'])
<style>
    /* Hide tenant avatar and chevron to make it look static */
    .fi-tenant-menu img,
    .fi-tenant-menu .fi-avatar,
    .fi-tenant-menu svg {
        display: none !important;
    }

    .fi-tenant-menu button {
        cursor: default !important;
        pointer-events: none !important;
        background: transparent !important;
    }

    /* Style the main tenant name (Barangay Name) */
    .fi-tenant-menu button>span>span {
        font-weight: 700 !important;
        text-transform: uppercase !important;
        color: #2563eb !important;
        font-size: 14px !important;
        letter-spacing: -0.025em !important;
    }

    /* Inject the "Official's Dashboard" Subtitle */
    .fi-tenant-menu button>span::after {
        content: "Official's Dashboard";
        display: block;
        font-size: 10px !important;
        font-weight: 500 !important;
        color: #6b7280 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.1em !important;
        line-height: 1 !important;
        margin-top: 2px !important;
    }
</style>