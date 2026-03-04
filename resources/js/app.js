import "./bootstrap";
import initDotGrid from "./animations/DotGrid";
import Grainient from "./animations/Grainient";
import "./bootstrap";
import { NfcHandler } from "@brynrgnzls/nfc-listener";
import QrScanner from "qr-scanner";

// Expose it globally for Blade scripts
window.QrScanner = QrScanner;
window.NfcHandler = NfcHandler;

import lineageD3 from "./components/LineageD3";

// Register Alpine data components
const registerComponents = () => {
    if (window.Alpine) {
        window.Alpine.data("grainient", Grainient);
        window.Alpine.data("lineageD3", lineageD3);
    }
};

if (window.Alpine) {
    registerComponents();
} else {
    document.addEventListener("alpine:init", registerComponents);
}

document.addEventListener("livewire:init", () => {
    setupRealtimeNotifications();
});

window.initDotGrid = initDotGrid;
window.Grainient = Grainient;

document.addEventListener("DOMContentLoaded", () => {
    window.initDotGrid();
});

// Re-setup on Livewire navigation
document.addEventListener("livewire:navigated", () => {
    window.initDotGrid();
    setupRealtimeNotifications();
});

function setupRealtimeNotifications() {
    const config = window.AppConfig;
    if (!config || !config.userId || !window.Echo) return;

    // 1. New Request -> Official
    if (config.barangayId && config.isOfficial) {
        window.Echo.private(`barangay.${config.barangayId}.requests`)
            .listen(".DocumentRequestCreated", (e) => {
                showToast(
                    "New Request",
                    `${e.requester} requested a ${e.document_type}`,
                    "info",
                );
                // Refresh Notification dropdown if it exists
                if (window.Livewire) {
                    window.Livewire.dispatch("notificationReceived");
                }
            })
            .listen(".ResidencyRequestSubmitted", (e) => {
                showToast(
                    "New Residency Application",
                    `${e.residentName} applied for residency in Brgy. ${e.barangayName}`,
                    "info",
                );
                if (window.Livewire)
                    window.Livewire.dispatch("notificationReceived");
            });
    }

    // 2. Document Issued -> Resident
    window.Echo.private(`resident.${config.userId}.documents`).listen(
        ".DocumentIssued",
        (e) => {
            showToast(
                "Document Issued!",
                `Your ${e.document_type} is now ready for download.`,
                "success",
            );
            if (window.Livewire) {
                window.Livewire.dispatch("notificationReceived");
            }
        },
    );
}

function showToast(title, message, type = "info") {
    const container =
        document.getElementById("toast-container") || createToastContainer();
    const toast = document.createElement("div");

    // Premium styling matching the app's aesthetic
    const bgColor =
        type === "success"
            ? "bg-emerald-600"
            : type === "error"
              ? "bg-rose-600"
              : "bg-blue-600";
    const borderColor =
        type === "success"
            ? "border-emerald-400"
            : type === "error"
              ? "border-rose-400"
              : "border-blue-400";

    toast.className = `flex flex-col p-4 mb-4 text-white rounded-2xl shadow-2xl transform translate-x-full transition-all duration-300 ease-out border-l-4 ${bgColor} ${borderColor} z-[9999] opacity-100 pointer-events-auto`;

    toast.innerHTML = `
        <div class="flex justify-between items-start">
            <h4 class="font-bold text-sm uppercase tracking-wider">${title}</h4>
            <button class="ml-4 text-white/50 hover:text-white transition text-xl leading-none">&times;</button>
        </div>
        <p class="text-xs mt-1 text-white/90 leading-relaxed">${message}</p>
    `;

    container.appendChild(toast);

    // Slide-in animation
    requestAnimationFrame(() => {
        toast.classList.remove("translate-x-full");
        toast.classList.add("translate-x-0");
    });

    toast.querySelector("button").onclick = () => removeToast(toast);

    // Auto-remove
    setTimeout(() => removeToast(toast), 5000);
}

function createToastContainer() {
    const container = document.createElement("div");
    container.id = "toast-container";
    container.className =
        "fixed top-6 right-6 z-[9999] w-80 space-y-4 pointer-events-none";
    document.body.appendChild(container);
    return container;
}

function removeToast(toast) {
    if (!toast) return;
    toast.classList.remove("translate-x-0");
    toast.classList.add("translate-x-full", "opacity-0");
    setTimeout(() => toast.remove(), 400);
}
