class RouletteGame {
    constructor(canvasId) {
        this.canvas = document.getElementById(canvasId);
        if (!this.canvas) {
            console.error('Roulette Canvas not found');
            return;
        }
        this.ctx = this.canvas.getContext('2d');

        console.log('RouletteGame Initializing...');

        // 1. Try Global Variable
        if (typeof ROULETTE_CONFIG !== 'undefined') {
            this.config = ROULETTE_CONFIG;
        }
        // 2. Try Hidden Textarea (Robust Method)
        else {
            const storage = document.getElementById('roulette-config-storage');
            if (storage && storage.value) {
                console.log('Raw Config String:', storage.value); // Debug Log
                try {
                    this.config = JSON.parse(storage.value);
                    console.log('Loaded config from textarea:', this.config);
                } catch (e) {
                    console.error('Failed to parse config from textarea', e);
                    this.config = { items: [], settings: {} };
                }
            } else {
                console.warn('Textarea not found or empty');
                this.config = { items: [], settings: {} };
            }
        }

        this.items = this.config.items || [];

        // HARDCODED FALLBACK to ensure Roulette ALWAYS works even if config fails
        if (this.items.length === 0) {
            console.warn('Config failed. Using Hardcoded Defaults.');
            this.items = [
                { text: "1천", subText: "캡슐", color: "#FFFFFF", textColor: "#6A4DFF", weight: 0.5 },
                { text: "15", subText: "캡슐", color: "#F0F4FF", textColor: "#333", weight: 20 },
                { text: "25", subText: "캡슐", color: "#FFFFFF", textColor: "#333", weight: 20 },
                { text: "50", subText: "캡슐", color: "#F0F4FF", textColor: "#333", weight: 15 },
                { text: "100", subText: "캡슐", color: "#FFFFFF", textColor: "#333", "weight": 15 },
                { text: "150", subText: "캡슐", color: "#F0F4FF", textColor: "#333", weight: 10 },
                { text: "200", subText: "캡슐", color: "#FFFFFF", textColor: "#333", weight: 10 },
                { text: "500", subText: "캡슐", color: "#F0F4FF", textColor: "#333", weight: 4.5 },
                { text: "보너스", subText: "티켓 1장", color: "#dbeafe", textColor: "#2563eb", weight: 5 }
            ];
        }

        console.log('Roulette Items Loaded:', this.items.length, this.items);

        this.currentWaitTime = 0;
        this.isSpinning = false;
        this.rotationAngle = 0;

        this.audioCtx = null;

        this.init();
    }

    init() {
        this.resizeCanvas();
        this.drawWheel();

        const spinBtn = document.getElementById('spin-btn');
        if (spinBtn) {
            spinBtn.addEventListener('click', () => {
                console.log('Spin button clicked');
                this.spin();
            });
        } else {
            console.error('Spin button not found');
        }

        document.getElementById('close-result').addEventListener('click', () => {
            document.querySelector('.result-overlay').classList.remove('active');
        });

        window.addEventListener('resize', () => this.resizeCanvas());
    }

    initAudio() {
        if (!this.audioCtx) {
            this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        }
        if (this.audioCtx.state === 'suspended') {
            this.audioCtx.resume();
        }
    }

    playTickSound() {
        if (!this.audioCtx) return;
        const oscillator = this.audioCtx.createOscillator();
        const gainNode = this.audioCtx.createGain();
        oscillator.type = 'triangle';
        oscillator.frequency.setValueAtTime(800, this.audioCtx.currentTime);
        oscillator.frequency.exponentialRampToValueAtTime(100, this.audioCtx.currentTime + 0.1);
        gainNode.gain.setValueAtTime(0.05, this.audioCtx.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.001, this.audioCtx.currentTime + 0.1);
        oscillator.connect(gainNode);
        gainNode.connect(this.audioCtx.destination);
        oscillator.start();
        oscillator.stop(this.audioCtx.currentTime + 0.1);
    }

    playWinSound() {
        if (!this.audioCtx) return;
        const now = this.audioCtx.currentTime;
        [0, 0.1, 0.2].forEach((delay, i) => {
            const osc = this.audioCtx.createOscillator();
            const gain = this.audioCtx.createGain();
            osc.type = 'sine';
            const freqs = [523.25, 659.25, 783.99];
            osc.frequency.value = freqs[i];
            gain.gain.setValueAtTime(0.1, now + delay);
            gain.gain.exponentialRampToValueAtTime(0.001, now + delay + 0.5);
            osc.connect(gain);
            gain.connect(this.audioCtx.destination);
            osc.start(now + delay);
            osc.stop(now + delay + 0.5);
        });
    }

    resizeCanvas() {
        const size = 600;
        this.canvas.width = size;
        this.canvas.height = size;
        this.drawWheel();
    }

    drawWheel() {
        if (this.items.length === 0) return;

        const arc = (2 * Math.PI) / this.items.length;
        const radius = this.canvas.width / 2;
        const ctx = this.ctx;
        const centerX = radius;
        const centerY = radius;

        ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        ctx.save();
        ctx.translate(centerX, centerY);
        ctx.rotate(-Math.PI / 2);

        const colors = [
            "#2563eb", "#7c3aed", "#db2777", "#ea580c",
            "#16a34a", "#0891b2", "#4f46e5", "#c026d3"
        ];

        this.items.forEach((item, index) => {
            const angle = index * arc;
            const itemColor = colors[index % colors.length];

            ctx.beginPath();
            ctx.moveTo(0, 0);
            ctx.arc(0, 0, radius, angle, angle + arc);
            const gradient = ctx.createRadialGradient(0, 0, 0, 0, 0, radius);
            gradient.addColorStop(0, '#1e293b');
            gradient.addColorStop(1, itemColor);
            ctx.fillStyle = gradient;
            ctx.fill();

            ctx.strokeStyle = "rgba(15, 23, 42, 1)";
            ctx.lineWidth = 4;
            ctx.stroke();

            ctx.save();
            ctx.translate(Math.cos(angle + arc / 2) * (radius * 0.75), Math.sin(angle + arc / 2) * (radius * 0.75));
            ctx.rotate(angle + arc / 2 + Math.PI / 2);
            ctx.textAlign = "center";
            ctx.fillStyle = "#ffffff";
            ctx.font = "bold 36px Pretendard";
            ctx.shadowColor = "rgba(0,0,0,0.8)";
            ctx.shadowBlur = 4;
            ctx.fillText(item.text, 0, 0);

            if (item.subText) {
                ctx.font = "18px Pretendard";
                ctx.fillStyle = "rgba(255,255,255,0.7)";
                ctx.fillText(item.subText, 0, 26);
            }
            ctx.restore();
        });
        ctx.restore();
    }

    spin() {
        if (this.isSpinning) {
            console.log('Already spinning');
            return;
        }

        this.initAudio();
        console.log('Requesting spin from server...');

        var params = {
            module: 'roulette',
            act: 'procRouletteSpin'
        };

        // Debug mode: Log the call
        if (typeof exec_json === 'undefined') {
            alert('Rhymix core JS not loaded. Cannot execute spin.');
            return;
        }

        exec_json('roulette.procRouletteSpin', params, (ret_obj) => {
            console.log('Server response:', ret_obj);

            if (ret_obj.error == -1) {
                alert(ret_obj.message);
                return;
            }

            // Fix: Rhymix controller added data into 'result' variable
            var data = ret_obj.result ? ret_obj.result : ret_obj;

            if (data.success == false) {
                alert(data.message || 'Error executing spin');
                return;
            }

            this.isSpinning = true;
            this.startSpinAnimation(data.index, data.item, data.remaining_point);

        }, (error) => {
            console.error('AJAX Error:', error);
            alert('서버 통신 중 오류가 발생했습니다. (Network Error)');
        });
    }

    startSpinAnimation(selectedIndex, itemData, remainingPoints) {
        console.log('Starting animation:', { selectedIndex, itemData, remainingPoints });

        // Validate inputs
        if (typeof selectedIndex === 'undefined' || selectedIndex === null) {
            console.error('Invalid selectedIndex');
            return;
        }
        if (!itemData) {
            console.error('Invalid itemData');
            return;
        }

        document.getElementById('ticket-count').innerText = remainingPoints;

        const itemAngle = 360 / this.items.length;
        const targetDegreeOnCircle = (selectedIndex * itemAngle) + (itemAngle / 2);
        const targetRotationValue = 360 - targetDegreeOnCircle;
        const currentMod = this.rotationAngle % 360;
        let diff = targetRotationValue - currentMod;
        if (diff < 0) diff += 360;

        // Use config duration
        const duration = this.config.settings.spinDuration || 3000;
        const spins = 10;
        const totalRotationToAdd = (spins * 360) + diff;

        // Safety Check: If rotationAngle is corrupt, reset it
        if (isNaN(this.rotationAngle) || !isFinite(this.rotationAngle)) {
            console.warn('Rotation Angle was NaN/Infinity. Resetting to 0.');
            this.rotationAngle = 0;
        }

        const newRotationAngle = this.rotationAngle + totalRotationToAdd;

        console.log('Rotation Calculation:', {
            current: this.rotationAngle,
            target: newRotationAngle,
            diff,
            duration
        });

        this.animateSpin(this.rotationAngle, newRotationAngle, duration, itemData);
        this.rotationAngle = newRotationAngle;
    }

    animateSpin(startAngle, endAngle, duration, itemData) {
        const startTime = performance.now();
        const itemAngle = 360 / this.items.length;
        let lastTickSection = Math.floor(startAngle / itemAngle);
        const charImg = document.getElementById('spin-character');

        // Image Path Logic: Try to use absolute if possible, or relative
        // Since we are in modules/roulette/skins/default/js/script.js, images are in ../img/
        // But src is relative to index.php. So we need full path.
        const skinPath = "modules/roulette/skins/default/img/";

        if (charImg) charImg.src = skinPath + "normal.png";

        const easeOutQuart = (x) => 1 - Math.pow(1 - x, 4);

        const animate = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const ease = easeOutQuart(progress);
            const currentRotation = startAngle + (endAngle - startAngle) * ease;

            // Log every ~60 frames (approx 1 sec) or if stuck
            if (Math.random() < 0.01) console.log('Animating...', { progress, currentRotation });

            this.canvas.style.transform = `rotate(${currentRotation}deg)`;
            if (charImg) charImg.style.transform = `rotate(${currentRotation}deg)`;

            const currentTickSection = Math.floor(currentRotation / itemAngle);
            if (currentTickSection > lastTickSection) {
                this.playTickSound();
                lastTickSection = currentTickSection;
            }

            if (progress < 1) {
                requestAnimationFrame(animate);
            } else {
                console.log('Animation Complete.');
                this.isSpinning = false;
                const skinPath = "modules/roulette/skins/default/img/";
                if (charImg) charImg.src = skinPath + "dizzy.png";
                this.showResult(itemData);
            }
        };
        requestAnimationFrame(animate);
    }

    showResult(item) {
        if (!item) {
            console.error('showResult called with null item');
            return;
        }
        console.log('Showing result for:', item);

        this.playWinSound();
        const overlay = document.querySelector('.result-overlay');
        const valueDiv = overlay.querySelector('.result-value');
        const titleDiv = overlay.querySelector('.result-title');

        valueDiv.innerText = `${item.text} ${item.subText}`;
        titleDiv.innerText = "당첨!";
        overlay.classList.add('active');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const game = new RouletteGame('roulette-canvas');
});
