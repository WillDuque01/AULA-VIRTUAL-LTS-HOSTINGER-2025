import './bootstrap';

import Alpine from 'alpinejs';
import registerCelebrations from './modules/celebrations.js';

window.Alpine = Alpine;

Alpine.start();

registerCelebrations();
