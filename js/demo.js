const container = document.getElementById("container");
const pickupSound = new Audio('/1xd3-for-fun/assets/pop-high.mp3');
pickupSound.volume = 0.1;
const dropSound = new Audio('/1xd3-for-fun/assets/pop-low.mp3');
dropSound.volume = 0.15;
const hoverSound = new Audio('/1xd3-for-fun/assets/hover-sound.mp3');
hoverSound.volume = 0.15;


const cards = [
    { id: 'card1', label: 'Block 1', color: '#5ab576' },
    { id: 'card2', label: 'Block 2', color: '#6C63FF' },
    { id: 'card3', label: 'Block 3', color: '#FF6584' },
];

cards.forEach(cardData => {
    const card = document.createElement('div');
    card.id = cardData.id;
    card.className = 'card';
    card.textContent = cardData.label;
    card.style.background = cardData.color;
    card.style.left = Math.random() * (container.offsetWidth  - 100) + 'px';
    card.style.top  = Math.random() * (container.offsetHeight - 100) + 'px';
    container.appendChild(card);
    initCard(card);
});

function initCard(card) {
    let newX = 0, newY = 0, startX = 0, startY = 0, isDragging = false;

    card.addEventListener('mousedown', mouseDown);

    function mouseDown(e) {
        e.preventDefault();
        isDragging = true;
        card.classList.add('dragging');
        startX = e.clientX;
        startY = e.clientY;
        playPickup();
        card.style.zIndex = 10;
        document.addEventListener('mousemove', mouseMove);
        document.addEventListener('mouseup', mouseUp);
    }

    function mouseMove(e) {
        if (!isDragging) return;

        const rect = container.getBoundingClientRect();
        if (
            e.clientX < rect.left  ||
            e.clientX > rect.right ||
            e.clientY < rect.top   ||
            e.clientY > rect.bottom
        ) {
            mouseUp();
            return;
        }

        newX = e.clientX - startX;
        newY = e.clientY - startY;
        startX = e.clientX;
        startY = e.clientY;

        const minX = 0;
        const minY = 0;
        const maxX = container.offsetWidth  - card.offsetWidth;
        const maxY = container.offsetHeight - card.offsetHeight;

        card.style.left = Math.max(minX, Math.min(card.offsetLeft + newX, maxX)) + 'px';
        card.style.top  = Math.max(minY, Math.min(card.offsetTop  + newY, maxY)) + 'px';
    }

    function mouseUp() {
        isDragging = false;
        card.classList.remove('dragging');
        card.style.zIndex = 1;
        document.removeEventListener('mousemove', mouseMove);
        document.removeEventListener('mouseup', mouseUp);
        playDrop();
    }
}

function playPickup() {
    pickupSound.currentTime = 0;
    pickupSound.play();
}

function playDrop() {
    dropSound.currentTime = 0;
    dropSound.play();
}