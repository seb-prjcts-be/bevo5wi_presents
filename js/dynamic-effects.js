// 5BEVO Dynamic Effects using p5.js
// Creates a less structured, more organic layout for the logo and color effects

let effectsGraphics;
// Convert hex colors to rgba with 90% alpha (10% transparency)
function hexToRgba(hex, alpha = 0.9) {
  let r = parseInt(hex.slice(1, 3), 16);
  let g = parseInt(hex.slice(3, 5), 16);
  let b = parseInt(hex.slice(5, 7), 16);
  return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

// Volledige kleurenpalet – elke keer 3 willekeurige kleuren kiezen
const hexColors = [
  '#61B199','#C0CE68','#F0E933','#FBBF2E','#F89A3C',
  '#F3722E','#EF4424','#ED1C26','#E92B59','#E23D96',
  '#BF428A','#9F4A81','#69619B','#367BB7','#0091D1'
];

function pickThreeColors() {
  const pool = [...hexColors];
  shuffleArray(pool);
  return pool.slice(0, 3).map(c => hexToRgba(c, 0.9));
}

// Color palette with 90% alpha (10% transparency)
let colorPalette = pickThreeColors();

// Fisher-Yates shuffle algorithm for arrays
function shuffleArray(array) {
  for (let i = array.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [array[i], array[j]] = [array[j], array[i]];
  }
  return array;
}

// P5 setup function - creates canvas and initializes the graphics
function setup() {
  createCanvas(windowWidth, 420).parent('dynamic-header');
  effectsGraphics = createGraphics(width, height);
  generateDesign();
}

// P5 draw function - runs continuously
function draw() {
  background(255);
  
  // Display the effects graphics
  image(effectsGraphics, 0, 0);
}

// Generate a new design with organic color blocks
function generateDesign() {
  // Witte achtergrond
  effectsGraphics.background(255);
  
  // Kies telkens 3 nieuwe willekeurige kleuren
  colorPalette = pickThreeColors();
  let designColors = [...colorPalette];
  
  // Create organic shapes and color blocks
  
  // 1. Create horizontal shapes
  for (let i = 0; i < 5; i++) {
    const colorIndex = i % designColors.length;
    effectsGraphics.fill(designColors[colorIndex]);
    effectsGraphics.noStroke();
    
    // Position horizontally across the canvas
    const startX = random(width * 0.3, width);
    const startY = random(height);
    
    // Random width and height (horizontal emphasis)
    const shapeWidth = random(100, width * 0.5);
    const shapeHeight = random(20, 60);
    
    // No rotation - keeping everything horizontal
    effectsGraphics.rect(startX, startY, shapeWidth, shapeHeight);
  }
  
  // Create scattered rectangles of various sizes
  for (let i = 0; i < 15; i++) {
    const colorIndex = i % designColors.length;
    effectsGraphics.fill(designColors[colorIndex]);
    effectsGraphics.noStroke();
    
    // Position more towards the right side but with some randomness
    const x = random(width * 0.3, width);
    const y = random(height);
    
    // Random dimensions
    const rectWidth = random(30, 200);
    const rectHeight = random(10, 60);
    
    // Using normal size rectangles with 10% transparency
    effectsGraphics.rect(x, y, rectWidth, rectHeight);
  }
  
  // 3. Create a few larger blocks for visual interest
  for (let i = 0; i < 3; i++) { // Back to 3 blocks with 10% transparency
    const colorIndex = floor(random(designColors.length));
    effectsGraphics.fill(designColors[colorIndex]);
    effectsGraphics.noStroke();
    
    const x = random(width * 0.5, width);
    const y = random(height);
    const blockWidth = random(100, 300); // Normal size with 10% transparency
    const blockHeight = random(50, 120);
    
    effectsGraphics.rect(x, y, blockWidth, blockHeight);
  }
  
  // 4. Add some horizontal rectangles instead of circles
  for (let i = 0; i < 5; i++) { // Back to 5 with 10% transparency
    const colorIndex = floor(random(designColors.length));
    effectsGraphics.fill(designColors[colorIndex]);
    effectsGraphics.noStroke();
    
    const x = random(width * 0.3, width);
    const y = random(height);
    const rectWidth = random(40, 120); // Normal size with 10% transparency
    const rectHeight = random(10, 30);
    
    effectsGraphics.rect(x, y, rectWidth, rectHeight);
  }
}

// Handle window resize
function windowResized() {
  resizeCanvas(windowWidth, 420);
  
  // Recreate the graphics buffer
  effectsGraphics = createGraphics(width, height);
  
  // Regenerate the design
  generateDesign();
}

// Regenerate design on mouse click
function mouseClicked(event) {
  // Niet onderscheppen als er op een link geklikt wordt
  if (event && event.target.closest('a')) return;

  // Only regenerate if the click is within the canvas
  if (mouseX >= 0 && mouseX < width && mouseY >= 0 && mouseY < height) {
    generateDesign();
    return false;
  }
}
