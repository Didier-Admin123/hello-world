// app.js

const express = require('express');
const app = express();
const port = 3000;


// Define a simple route
app.get('/', (req, res) => {
  res.send('Hello, Welcome WebAPP! 🚀');
});

// Start the server (only run in production or when not being tested)
if (require.main === module) {
        app.listen(port, '0.0.0.0', () => {
                console.log(`App listening at http://localhost:${port}`);
});

}

// Export app for testing purposes
module.exports = app;
