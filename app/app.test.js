// app.test.js
const request = require('supertest');
const app = require('./app');  // Import your app

describe('GET /', () => {
  it('should respond with Hello, Jenkins! 🚀', async () => {
    const response = await request(app).get('/');
    expect(response.text).toBe('Hello, Welcome WebAPP! 🚀');
    expect(response.status).toBe(200);
  });
});
