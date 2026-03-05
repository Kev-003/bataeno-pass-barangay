import { gsap } from "gsap";

export default function initDotGrid() {
    const wrapper = document.querySelector(".dot-grid__wrap");
    const canvas = wrapper?.querySelector(".dot-grid__canvas");
    if (!wrapper || !canvas) return;

    // Prevent duplicate initializations
    if (wrapper.dataset.dotGridInitialized === "true") return;
    wrapper.dataset.dotGridInitialized = "true";

    const ctx = canvas.getContext("2d");
    let dots = [];

    const config = {
        dotSize: 2,
        gap: 42,
        baseColor: "#e2e8f033",
        activeColor: "#60a5fa",
        proximity: 180,
        returnDuration: 0.8,
    };

    let baseRgb = hexToRgb(config.baseColor);
    let activeRgb = hexToRgb(config.activeColor);

    const pointer = { x: -1000, y: -1000 };

    function hexToRgb(hex) {
        const m = hex.match(
            /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})?$/i,
        );
        return m
            ? {
                  r: parseInt(m[1], 16),
                  g: parseInt(m[2], 16),
                  b: parseInt(m[3], 16),
                  a: m[4] ? parseInt(m[4], 16) / 255 : 1,
              }
            : { r: 50, g: 50, b: 150, a: 1 };
    }

    function buildGrid() {
        const rect = wrapper.getBoundingClientRect();
        const width = rect.width;
        const height = rect.height;
        const dpr = window.devicePixelRatio || 1;

        canvas.width = width * dpr;
        canvas.height = height * dpr;
        canvas.style.width = `${width}px`;
        canvas.style.height = `${height}px`;
        ctx.scale(dpr, dpr);

        const cell = config.dotSize + config.gap;
        const cols = Math.ceil(width / cell) + 1;
        const rows = Math.ceil(height / cell) + 1;

        dots = [];
        for (let y = 0; y < rows; y++) {
            for (let x = 0; x < cols; x++) {
                dots.push({
                    cx: x * cell,
                    cy: y * cell,
                    xOffset: 0,
                    yOffset: 0,
                    _inertiaApplied: false,
                });
            }
        }
    }

    function draw() {
        // If wrapper is gone from DOM, stop loop
        if (!document.body.contains(wrapper)) return;

        ctx.clearRect(0, 0, canvas.width, canvas.height);
        const proxSq = config.proximity * config.proximity;

        dots.forEach((dot) => {
            const dx = dot.cx - pointer.x;
            const dy = dot.cy - pointer.y;
            const dsq = dx * dx + dy * dy;

            let style;
            let radius = config.dotSize / 2;

            if (dsq <= proxSq) {
                const dist = Math.sqrt(dsq);
                const t = 1 - dist / config.proximity;
                const r = Math.round(baseRgb.r + (activeRgb.r - baseRgb.r) * t);
                const g = Math.round(baseRgb.g + (activeRgb.g - baseRgb.g) * t);
                const b = Math.round(baseRgb.b + (activeRgb.b - baseRgb.b) * t);
                const a = baseRgb.a + (activeRgb.a - baseRgb.a) * t;
                style = `rgba(${r},${g},${b},${a})`;
                radius = radius * (1 + t * 2);
            } else {
                style = `rgba(${baseRgb.r},${baseRgb.g},${baseRgb.b},${baseRgb.a})`;
            }

            ctx.fillStyle = style;
            ctx.beginPath();
            ctx.arc(
                dot.cx + dot.xOffset,
                dot.cy + dot.yOffset,
                radius,
                0,
                Math.PI * 2,
            );
            ctx.fill();
        });
        requestAnimationFrame(draw);
    }

    const handleMouseMove = (e) => {
        // Disable interaction on mobile/touch devices
        if (
            window.matchMedia("(pointer: coarse)").matches ||
            window.innerWidth < 768
        ) {
            return;
        }

        const rect = canvas.getBoundingClientRect();
        pointer.x = e.clientX - rect.left;
        pointer.y = e.clientY - rect.top;

        dots.forEach((dot) => {
            const dist = Math.hypot(dot.cx - pointer.x, dot.cy - pointer.y);
            if (dist < config.proximity && !dot._inertiaApplied) {
                dot._inertiaApplied = true;
                gsap.to(dot, {
                    xOffset: (dot.cx - pointer.x) * 0.15,
                    yOffset: (dot.cy - pointer.y) * 0.15,
                    duration: 0.3,
                    ease: "power2.out",
                    onComplete: () => {
                        gsap.to(dot, {
                            xOffset: 0,
                            yOffset: 0,
                            duration: config.returnDuration,
                            ease: "elastic.out(1, 0.5)",
                        });
                        dot._inertiaApplied = false;
                    },
                });
            }
        });
    };

    window.addEventListener("mousemove", handleMouseMove);
    window.addEventListener("resize", buildGrid);

    buildGrid();
    draw();
}
