// Color blocks pattern implementation
// Based on the styleguide and p5.js design example

// Color palette from the styleguide
const colorPalette = [
  '#61B199', '#C0CE68', '#F0E933', '#FBBF2E', '#F89A3C',
  '#F3722E', '#EF4424', '#ED1C26', '#E92B59', '#E23D96',
  '#BF428A', '#9F4A81', '#69619B', '#367BB7', '#0091D1'
];

// Logo paths
const logos = [
  'huisstijl/logo/Logo-groot-wit.jpg',
  'huisstijl/logo/Logo-groot-zwart.jpg'
];

// Generate and draw the color blocks
function generateColorBlocks() {
  // Get the header and footer containers
  const headerContainer = document.getElementById('header-colors');
  const footerContainer = document.getElementById('footer-colors');
  
  if (!headerContainer || !footerContainer) return;
  
  // Clear existing content
  headerContainer.innerHTML = '';
  footerContainer.innerHTML = '';
  
  // Add random logo to header
  addRandomLogo(headerContainer);
  
  // Select two random colors from the palette to use for both header and footer
  const selectedColors = [];
  while (selectedColors.length < 2) {
    const randomIndex = Math.floor(Math.random() * colorPalette.length);
    const color = colorPalette[randomIndex];
    if (!selectedColors.includes(color)) {
      selectedColors.push(color);
    }
  }
  
  // Create the color blocks with the same colors for both header and footer
  createColorBlocks(headerContainer, 10, selectedColors);
  createColorBlocks(footerContainer, 5, selectedColors);
}

// Add a random logo to the top-left corner
function addRandomLogo(container) {
  // Randomly select one of the two logos
  const randomIndex = Math.floor(Math.random() * logos.length);
  const selectedLogo = logos[randomIndex];
  
  // Create the logo image
  const logoImg = document.createElement('img');
  logoImg.src = selectedLogo;
  logoImg.alt = '5BEVO Logo';
  logoImg.className = 'site-logo';
  
  // Add the logo to the header container
  container.appendChild(logoImg);
}

// Create color blocks in a container
function createColorBlocks(container, numBlocks, colors) {
  // Create a wrapper for right-aligned blocks
  const wrapper = document.createElement('div');
  wrapper.style.display = 'flex';
  wrapper.style.justifyContent = 'flex-end';
  wrapper.style.width = '100%';
  wrapper.style.height = '100%';
  container.appendChild(wrapper);
  
  // Create a container for the blocks
  const blocksContainer = document.createElement('div');
  blocksContainer.style.display = 'flex';
  blocksContainer.style.flexDirection = 'column';
  blocksContainer.style.width = '40%'; // Only take up 40% of the width on the right
  blocksContainer.style.height = '100%';
  wrapper.appendChild(blocksContainer);
  
  // Use the same row height for both header and footer
  const rowHeight = '42px'; // Consistent height for both header and footer
  const numRows = container.id === 'header-colors' ? 10 : 5; // 10 rows for header, 5 for footer
  
  // Create rows of blocks
  for (let i = 0; i < numRows; i++) {
    // Create a row
    const rowDiv = document.createElement('div');
    rowDiv.style.display = 'flex';
    rowDiv.style.justifyContent = 'flex-end';
    rowDiv.style.height = rowHeight;
    rowDiv.style.flexGrow = '1';
    blocksContainer.appendChild(rowDiv);
    
    // Random number of blocks in this row (1-3)
    const blocksInThisRow = Math.floor(Math.random() * 3) + 1;
    
    for (let j = 0; j < blocksInThisRow; j++) {
      // Create a block
      const block = document.createElement('div');
      
      // Random width between 50-150px
      const blockWidth = Math.floor(Math.random() * 100) + 50;
      block.style.width = blockWidth + 'px';
      block.style.height = '100%';
      
      // Alternate between the two selected colors
      const colorIndex = (i + j) % 2;
      block.style.backgroundColor = colors[colorIndex];
      
      rowDiv.appendChild(block);
    }
  }
}

// Initialize when the page loads
document.addEventListener('DOMContentLoaded', function() {
  generateColorBlocks();
  
  // Regenerate on click
  document.querySelectorAll('.color-blocks').forEach(container => {
    container.addEventListener('click', generateColorBlocks);
  });
});
