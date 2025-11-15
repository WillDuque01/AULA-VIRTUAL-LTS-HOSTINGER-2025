import confetti from 'canvas-confetti';

const ensureToast = () => {
    if (window.toast) {
        return window.toast;
    }

    const containerId = 'celebration-toasts';
    let container = document.getElementById(containerId);
    if (! container) {
        container = document.createElement('div');
        container.id = containerId;
        container.style.position = 'fixed';
        container.style.right = '1.25rem';
        container.style.top = '1.25rem';
        container.style.display = 'flex';
        container.style.flexDirection = 'column';
        container.style.gap = '0.5rem';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
    }

    const toast = (message) => {
        const el = document.createElement('div');
        el.textContent = message;
        el.style.background = 'rgba(15,23,42,0.9)';
        el.style.color = 'white';
        el.style.padding = '0.75rem 1rem';
        el.style.borderRadius = '999px';
        el.style.boxShadow = '0 20px 45px rgba(15,23,42,0.25)';
        el.style.fontSize = '0.875rem';
        el.style.fontWeight = '600';
        el.style.opacity = '0';
        el.style.transform = 'translateY(-10px)';
        el.style.transition = 'all 300ms ease';

        container.appendChild(el);
        requestAnimationFrame(() => {
            el.style.opacity = '1';
            el.style.transform = 'translateY(0)';
        });

        setTimeout(() => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(-10px)';
            setTimeout(() => el.remove(), 300);
        }, 3200);
    };

    window.toast = toast;

    return toast;
};

const fireConfetti = () => {
    confetti({
        particleCount: 140,
        spread: 70,
        ticks: 220,
        gravity: 0.7,
        origin: { y: 0.6 },
    });

    confetti({
        particleCount: 80,
        spread: 100,
        ticks: 200,
        gravity: 0.5,
        origin: { x: 0.15, y: 0.7 },
    });
};

export default function registerCelebrations() {
    const toast = ensureToast();

    window.addEventListener('gamification:celebrate', (event) => {
        const { points = 0, streak = 1, badge, lesson_title: lessonTitle } = event.detail || {};

        fireConfetti();

        const parts = [
            `+${points} XP`,
            `Racha ${streak}`,
            badge,
            lessonTitle ? `✔ ${lessonTitle}` : null,
        ].filter(Boolean);

        toast(parts.join(' · '));
    });
}


