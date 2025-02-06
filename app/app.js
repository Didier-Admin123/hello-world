const express = require('express');
const path = require('path');
const app = express();
const port = 3000;

// Serve static files from the "public" directory
app.use(express.static(path.join(__dirname, 'public')));

// Define route to serve index.html
app.get('/', (req, res) => {
  res.sendFile(path.join(__dirname, 'public', 'index.html'));
});

// Start the server (only run in production or when not being tested)
if (require.main === module) {
  app.listen(port, '0.0.0.0', () => {
    console.log(`App listening at http://localhost:${port}`);
  });
}

// Export app for testing purposes
module.exports = app;
