export const animatedCount = (value = 0, duration = 1000) => ({
    display: 0, // [AGENTE: GPT-5.1 CODEX] - Valor mostrado en UI
    target: Number(value) || 0, // [AGENTE: GPT-5.1 CODEX] - Objetivo final
    duration,
    init() {
        if (this.target === 0) {
            this.display = 0;
            return;
        } // [AGENTE: GPT-5.1 CODEX] - No animar cuando el objetivo es cero

        const frames = Math.max(12, Math.floor(this.duration / 16));
        const increment = this.target / frames;
        let step = 0;

        const tick = () => {
            step += 1;
            this.display = Math.round(Math.min(this.target, increment * step));

            if (step < frames) {
                requestAnimationFrame(tick);
            } else {
                this.display = this.target;
            }
        }; // [AGENTE: GPT-5.1 CODEX] - Interpolación suave

        requestAnimationFrame(tick);
    },
});

