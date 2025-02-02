# Use official Node.js image as base
FROM node:16

# Set the working directory in the container
WORKDIR /app

# Copy package.json and package-lock.json for installing dependencies
COPY app/package*.json ./

# Install dependencies
RUN npm install

# Copy the rest of the app files
COPY app/ .

# Expose the port the app runs on
EXPOSE 3000

# Run the app
CMD ["npm", "start"]
