const request = require('supertest');
const app = require('./app');  // Import your app

describe('GET /', () => {
  it('should respond with the correct HTML content', async () => {
    const response = await request(app).get('/');

    // Check if the response status is OK
    expect(response.status).toBe(200);

    // Check if the response contains the correct title (h1 tag)
    expect(response.text).toContain('<h1>Welcome!</h1>');

  });
});
