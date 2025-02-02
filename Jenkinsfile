pipeline {
    agent any

    environment {
        REMOTE_HOST = "172.23.215.51"
        REMOTE_USER = "jenkins"
        APP_DIR = "/opt"
        GIT_REPO = "git@github.com:Didier-Admin123/hello-world.git"
        GIT_BRANCH = "ci-cd-pipeline"
        IMAGE_NAME = "didierdorcelus1/nodejs"
        IMAGE_TAG = "${BUILD_NUMBER}"
        DOCKER_CREDENTIALS = "docker_cred"
    }

    stages {
        stage('Clone Repository on Jenkins') {
            steps {
                sshagent(['git_cred_ssh']) {
                    sh """
                        export GIT_SSH_COMMAND='ssh -o StrictHostKeyChecking=no'
                        rm -rf hello-world || true
                        git clone -b ${GIT_BRANCH} --single-branch ${GIT_REPO} hello-world
                    """
                }
            }
        }

        stage('Copy App to Remote Server') {
            steps {
                sshagent(['git_cred_ssh']) {
                    sh """
                        scp -o StrictHostKeyChecking=no -r ${WORKSPACE}/hello-world ${REMOTE_USER}@${REMOTE_HOST}:${APP_DIR}
                    """
                }
            }
        }

        stage('Build Docker Image on Remote Server') {
            steps {
                sshagent(['git_cred_ssh']) {
                    sh """
                        ssh -o StrictHostKeyChecking=no ${REMOTE_USER}@${REMOTE_HOST} << 'EOF'
                        echo "Building Docker image on remote server..."
                        
                        # Navigate to the copied project directory
                        cd ${APP_DIR}/hello-world
                        
                        # Build the Docker image
                        docker build -t ${IMAGE_NAME}:${IMAGE_TAG} .
                        
                        exit
                        EOF
                    """
                }
            }
        }

        stage('Push Docker Image to DockerHub') {
            steps {
                sshagent(['git_cred_ssh']) {
                    sh """
                        ssh -o StrictHostKeyChecking=no ${REMOTE_USER}@${REMOTE_HOST} << 'EOF'
                        echo "Logging into DockerHub and pushing image..."
                        
                        # Ensure credentials are used correctly
                        echo "\$DOCKER_PASSWORD" | podman login --username "\$DOCKER_USERNAME" --password-stdin docker.io
        
                        # Verify login success
                        podman login --get-login docker.io
        
                        # Push the image
                        podman push ${IMAGE_NAME}:${IMAGE_TAG} docker.io/${IMAGE_NAME}:${IMAGE_TAG}
                        
                        exit
                        EOF
                    """
                }
            }

        stage('Run Application on Remote Server') {
            steps {
                sshagent(['git_cred_ssh']) {
                    sh """
                        ssh -o StrictHostKeyChecking=no ${REMOTE_USER}@${REMOTE_HOST} << 'EOF'
                        echo "Starting application on remote server..."
                        
                        # Pull the latest image
                        docker pull ${IMAGE_NAME}:${IMAGE_TAG}
                        
                        # Stop any existing container running the app
                        docker stop my-app || true
                        docker rm my-app || true
                        
                        # Run the new container
                        docker run -d --name my-app -p 3000:3000 ${IMAGE_NAME}:${IMAGE_TAG}
                        
                        exit
                        EOF
                    """
                }
            }
        }
    }

    post {
        success {
            echo "✅ Deployment successful!"
        }
        failure {
            echo "❌ Deployment failed. Check logs."
        }
    }
}
